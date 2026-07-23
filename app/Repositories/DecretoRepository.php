<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class DecretoRepository
{
    public function paginate(array $filters, int $page, int $limit = 20): array
    {
        $limit = min(max($limit, 1), 20);
        $offset = max($page - 1, 0) * $limit;
        [$where, $params] = $this->buildWhere($filters);

        $countSql = 'SELECT COUNT(*) FROM vw_decretos_listagem WHERE ativo = 1' . $where;
        $countStmt = Database::connection()->prepare($countSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $sql = 'SELECT * FROM vw_decretos_listagem WHERE ativo = 1' . $where .
            ' ORDER BY protocolo_ano DESC, protocolo_sequencial DESC LIMIT :limit OFFSET :offset';
        $stmt = Database::connection()->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $registros = $stmt->fetchAll();
        $this->adicionarResumoEntregas($registros);

        return [
            'registros' => $registros,
            'paginacao' => [
                'pagina' => $page,
                'limite' => $limit,
                'total' => $total,
                'paginas' => max((int) ceil($total / $limit), 1),
            ],
        ];
    }

    /**
     * Anexa os itens de ajuda agrupados a todos os decretos da página em uma única consulta.
     * Entregas repetidas do mesmo tipo são consolidadas por quantidade e valor.
     *
     * @param array<int, array<string, mixed>> $registros
     */
    private function adicionarResumoEntregas(array &$registros): void
    {
        if ($registros === []) {
            return;
        }

        $ids = array_values(array_filter(array_map(
            static fn (array $registro): int => (int) ($registro['id'] ?? 0),
            $registros
        )));

        if ($ids === []) {
            return;
        }

        $placeholders = implode(', ', array_fill(0, count($ids), '?'));
        $stmt = Database::connection()->prepare(
            'SELECT
                e.desastre_id,
                t.nome AS tipo_ajuda_nome,
                t.unidade_medida,
                COALESCE(SUM(e.quantidade), 0) AS quantidade,
                COALESCE(SUM(e.valor_total), 0) AS valor_total
             FROM decreto_entregas e
             INNER JOIN tipos_ajuda t ON t.id = e.tipo_ajuda_id
             WHERE e.desastre_id IN (' . $placeholders . ')
             GROUP BY e.desastre_id, t.id, t.nome, t.unidade_medida
             ORDER BY t.nome'
        );
        $stmt->execute($ids);

        $entregasPorDecreto = [];
        foreach ($stmt->fetchAll() as $entrega) {
            $entregasPorDecreto[(int) $entrega['desastre_id']][] = [
                'tipo_ajuda_nome' => (string) $entrega['tipo_ajuda_nome'],
                'unidade_medida' => (string) $entrega['unidade_medida'],
                'quantidade' => (float) $entrega['quantidade'],
                'valor_total' => (float) $entrega['valor_total'],
            ];
        }

        foreach ($registros as &$registro) {
            $entregas = $entregasPorDecreto[(int) $registro['id']] ?? [];
            $registro['entregas_ajuda'] = $entregas;
            $registro['valor_total_entregas'] = array_sum(array_column($entregas, 'valor_total'));
        }
        unset($registro);
    }

    public function resumo(array $filters = []): array
    {
        [$where, $params] = $this->buildWhere($filters);
        $stmt = Database::connection()->prepare(
            'SELECT
                COUNT(*) AS total_registros,
                COALESCE(SUM(total_afetados), 0) AS total_afetados,
                COALESCE(SUM(CASE WHEN status_prazo_pge_calculado = \'PENDENTE\' THEN 1 ELSE 0 END), 0) AS pendentes_pge,
                COALESCE(SUM(CASE WHEN homologacao_codigo = \'HOMOLOGADO\' THEN 1 ELSE 0 END), 0) AS homologados,
                COALESCE(SUM(CASE WHEN reconhecimento_codigo = \'RECONHECIDO\' THEN 1 ELSE 0 END), 0) AS reconhecidos
             FROM vw_decretos_listagem
             WHERE ativo = 1' . $where
        );
        $stmt->execute($params);
        $resumo = $stmt->fetch() ?: [];

        $whereEntregas = $where;
        $paramsEntregas = $params;

        if (($filters['tipo_ajuda_id'] ?? '') !== '') {
            $whereEntregas .= ' AND e.tipo_ajuda_id = :tipo_ajuda_entrega_id';
            $paramsEntregas['tipo_ajuda_entrega_id'] = (int) $filters['tipo_ajuda_id'];
        }

        $entregasStmt = Database::connection()->prepare(
            'SELECT
                COALESCE(SUM(e.quantidade), 0) AS quantidade_entregue,
                COALESCE(SUM(e.valor_total), 0) AS valor_total_entregue
             FROM decreto_entregas e
             INNER JOIN vw_decretos_listagem ON vw_decretos_listagem.id = e.desastre_id
             WHERE vw_decretos_listagem.ativo = 1' . $whereEntregas
        );
        $entregasStmt->execute($paramsEntregas);
        $entregas = $entregasStmt->fetch() ?: [];

        return [
            'total_registros' => (int) ($resumo['total_registros'] ?? 0),
            'total_afetados' => (int) ($resumo['total_afetados'] ?? 0),
            'pendentes_pge' => (int) ($resumo['pendentes_pge'] ?? 0),
            'homologados' => (int) ($resumo['homologados'] ?? 0),
            'reconhecidos' => (int) ($resumo['reconhecidos'] ?? 0),
            'quantidade_entregue' => (float) ($entregas['quantidade_entregue'] ?? 0),
            'valor_total_entregue' => (float) ($entregas['valor_total_entregue'] ?? 0),
        ];
    }

    public function findById(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM desastres WHERE id = :id AND excluido_em IS NULL LIMIT 1');
        $stmt->execute(['id' => $id]);
        $registro = $stmt->fetch();

        return $registro ?: null;
    }

    public function detalhe(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT
                v.*,
                d.observacoes,
                cs.descricao AS cobrade_descricao
             FROM vw_decretos_listagem v
             INNER JOIN desastres d ON d.id = v.id
             INNER JOIN cobrade_subtipos cs ON cs.id = d.cobrade_subtipo_id
             WHERE v.id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $registro = $stmt->fetch();

        return $registro ?: null;
    }

    public function create(array $data): int
    {
        $fields = array_keys($data);
        $columns = implode(', ', $fields);
        $placeholders = ':' . implode(', :', $fields);

        $stmt = Database::connection()->prepare("INSERT INTO desastres ({$columns}) VALUES ({$placeholders})");
        $stmt->execute($data);

        return (int) Database::connection()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $sets = [];

        foreach (array_keys($data) as $field) {
            $sets[] = "{$field} = :{$field}";
        }

        $data['id'] = $id;
        $stmt = Database::connection()->prepare('UPDATE desastres SET ' . implode(', ', $sets) . ' WHERE id = :id AND excluido_em IS NULL');
        $stmt->execute($data);
    }

    public function updateProtocolo(int $id, string $protocolo): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE desastres
             SET protocolo_dgd = :protocolo
             WHERE id = :id
               AND excluido_em IS NULL'
        );
        $stmt->execute(['id' => $id, 'protocolo' => $protocolo]);
    }

    public function softDelete(int $id, int $userId): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE desastres
             SET ativo = 0, excluido_por = :user_id, excluido_em = NOW()
             WHERE id = :id AND excluido_em IS NULL'
        );
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }

    public function updateStatus(int $id, string $field, int $value, int $userId): void
    {
        $allowed = ['homologacao_status_id', 'reconhecimento_status_id', 'status_envio_pge_id', 'analista_id'];

        if (!in_array($field, $allowed, true)) {
            throw new \InvalidArgumentException('Campo de status invalido.');
        }

        $this->updateStatusFields($id, [$field => $value], $userId);
    }

    public function updateStatusFields(int $id, array $data, int $userId): void
    {
        $allowed = [
            'homologacao_status_id',
            'reconhecimento_status_id',
            'status_envio_pge_id',
            'analista_id',
            'data_decreto_homologacao',
            'data_envio_pge',
            'data_conclusao_pge',
            'status_envio_pge_antes_homologacao_id',
            'data_conclusao_pge_antes_homologacao',
        ];
        $sets = [];

        foreach (array_keys($data) as $field) {
            if (!in_array($field, $allowed, true)) {
                throw new \InvalidArgumentException('Campo de status invalido.');
            }

            $sets[] = "{$field} = :{$field}";
        }

        if ($sets === []) {
            return;
        }

        $data['id'] = $id;
        $data['user_id'] = $userId;

        $stmt = Database::connection()->prepare(
            'UPDATE desastres SET ' . implode(', ', $sets) . ', atualizado_por = :user_id WHERE id = :id AND excluido_em IS NULL'
        );
        $stmt->execute($data);
    }

    private function buildWhere(array $filters): array
    {
        $where = '';
        $params = [];

        $map = [
            'ano' => ['sql' => 'protocolo_ano = :ano', 'cast' => 'int'],
            'municipio_id' => ['sql' => 'municipio_id = :municipio_id', 'cast' => 'int'],
            'tipo_decreto' => ['sql' => 'tipo_decreto = :tipo_decreto', 'cast' => 'string'],
            'homologacao_status_id' => ['sql' => 'homologacao_status_id = :homologacao_status_id', 'cast' => 'int'],
            'reconhecimento_status_id' => ['sql' => 'reconhecimento_status_id = :reconhecimento_status_id', 'cast' => 'int'],
            'status_envio_pge_id' => ['sql' => 'status_envio_pge_id = :status_envio_pge_id', 'cast' => 'int'],
            'analista_id' => ['sql' => 'analista_id = :analista_id', 'cast' => 'int'],
        ];

        foreach ($map as $key => $definition) {
            if (($filters[$key] ?? '') === '') {
                continue;
            }

            $where .= ' AND ' . $definition['sql'];
            $params[$key] = $definition['cast'] === 'int' ? (int) $filters[$key] : (string) $filters[$key];
        }

        if (!empty($filters['protocolo'])) {
            $where .= ' AND protocolo_dgd LIKE :protocolo';
            $params['protocolo'] = '%' . $filters['protocolo'] . '%';
        }

        if (($filters['tipo_ajuda_id'] ?? '') !== '') {
            $where .= ' AND EXISTS (
                SELECT 1
                FROM decreto_entregas entrega_filtro
                WHERE entrega_filtro.desastre_id = vw_decretos_listagem.id
                  AND entrega_filtro.tipo_ajuda_id = :tipo_ajuda_id
            )';
            $params['tipo_ajuda_id'] = (int) $filters['tipo_ajuda_id'];
        }

        if (!empty($filters['status_prazo_pge'])) {
            $where .= ' AND status_prazo_pge_calculado = :status_prazo_pge';
            $params['status_prazo_pge'] = $filters['status_prazo_pge'];
        }

        if (!empty($filters['data_desastre_inicio'])) {
            $where .= ' AND data_desastre >= :data_desastre_inicio';
            $params['data_desastre_inicio'] = $filters['data_desastre_inicio'];
        }

        if (!empty($filters['data_desastre_fim'])) {
            $where .= ' AND data_desastre <= :data_desastre_fim';
            $params['data_desastre_fim'] = $filters['data_desastre_fim'];
        }

        return [$where, $params];
    }
}
