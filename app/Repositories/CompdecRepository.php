<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class CompdecRepository
{
    public function paginate(array $filters, int $page, int $limit = 20): array
    {
        $limit = min(max($limit, 1), 50);
        $offset = max($page - 1, 0) * $limit;
        [$where, $params] = $this->buildWhere($filters);

        $countStmt = Database::connection()->prepare('SELECT COUNT(*) FROM compdecs c' . $where);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $stmt = Database::connection()->prepare(
            'SELECT c.*, m.id AS municipio_id
             FROM compdecs c
             LEFT JOIN municipios m ON m.codigo_ibge = CAST(c.municipio_codigo AS UNSIGNED)
             ' . $where . '
             ORDER BY c.municipio ASC
             LIMIT :limit OFFSET :offset'
        );

        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return [
            'compdecs' => $stmt->fetchAll(),
            'paginacao' => [
                'pagina' => $page,
                'limite' => $limit,
                'total' => $total,
                'paginas' => max((int) ceil($total / $limit), 1),
            ],
        ];
    }

    public function regioes(): array
    {
        $stmt = Database::connection()->query(
            'SELECT DISTINCT regiao_integracao
             FROM compdecs
             WHERE regiao_integracao IS NOT NULL AND regiao_integracao <> \'\'
             ORDER BY regiao_integracao ASC'
        );

        return array_column($stmt->fetchAll(), 'regiao_integracao');
    }

    public function findById(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT c.*, m.id AS municipio_id
             FROM compdecs c
             LEFT JOIN municipios m ON m.codigo_ibge = CAST(c.municipio_codigo AS UNSIGNED)
             WHERE c.id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $compdec = $stmt->fetch();

        return $compdec ?: null;
    }

    public function findByMunicipioId(int $municipioId): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT c.*, m.id AS municipio_id
             FROM municipios m
             INNER JOIN compdecs c ON CAST(c.municipio_codigo AS UNSIGNED) = m.codigo_ibge
             WHERE m.id = :municipio_id
             LIMIT 1'
        );
        $stmt->execute(['municipio_id' => $municipioId]);
        $compdec = $stmt->fetch();

        return $compdec ?: null;
    }

    public function update(int $id, array $data): void
    {
        $sets = [];

        foreach (array_keys($data) as $field) {
            $sets[] = "{$field} = :{$field}";
        }

        $data['id'] = $id;
        $stmt = Database::connection()->prepare('UPDATE compdecs SET ' . implode(', ', $sets) . ' WHERE id = :id');
        $stmt->execute($data);
    }

    public function syncUbm(array $compdec): ?int
    {
        $municipioId = (int) ($compdec['municipio_id'] ?? 0);
        $ubmNome = trim((string) ($compdec['ubm_nome'] ?? ''));

        if ($municipioId <= 0 || $ubmNome === '') {
            return null;
        }

        $stmt = Database::connection()->prepare(
            'SELECT id
             FROM ubms
             WHERE municipio_id = :municipio_id
               AND (nome = :nome OR descricao IN (\'Fonte: Multirriscos COMPDEC\', \'Fonte: COMPDEC DGD\'))
             ORDER BY id ASC
             LIMIT 1'
        );
        $stmt->execute([
            'municipio_id' => $municipioId,
            'nome' => $ubmNome,
        ]);
        $ubmId = $stmt->fetchColumn();

        if ($ubmId) {
            $update = Database::connection()->prepare(
                'UPDATE ubms
                 SET nome = :nome, descricao = :descricao, ativo = 1
                 WHERE id = :id'
            );
            $update->execute([
                'id' => (int) $ubmId,
                'nome' => $ubmNome,
                'descricao' => 'Fonte: COMPDEC DGD',
            ]);

            return (int) $ubmId;
        }

        $insert = Database::connection()->prepare(
            'INSERT INTO ubms (municipio_id, codigo, nome, descricao, ativo)
             VALUES (:municipio_id, NULL, :nome, :descricao, 1)'
        );
        $insert->execute([
            'municipio_id' => $municipioId,
            'nome' => $ubmNome,
            'descricao' => 'Fonte: COMPDEC DGD',
        ]);

        return (int) Database::connection()->lastInsertId();
    }

    private function buildWhere(array $filters): array
    {
        $where = '';
        $params = [];

        if (trim((string) ($filters['busca'] ?? '')) !== '') {
            $where .= ' WHERE (c.municipio LIKE :busca_municipio OR c.prefeito LIKE :busca_prefeito OR c.coordenador LIKE :busca_coordenador OR c.ubm_nome LIKE :busca_ubm)';
            $busca = '%' . trim((string) $filters['busca']) . '%';
            $params['busca_municipio'] = $busca;
            $params['busca_prefeito'] = $busca;
            $params['busca_coordenador'] = $busca;
            $params['busca_ubm'] = $busca;
        }

        if (($filters['tem_compdec'] ?? '') !== '') {
            $where .= $where === '' ? ' WHERE ' : ' AND ';
            $where .= 'c.tem_compdec = :tem_compdec';
            $params['tem_compdec'] = (int) $filters['tem_compdec'];
        }

        if (trim((string) ($filters['regiao_integracao'] ?? '')) !== '') {
            $where .= $where === '' ? ' WHERE ' : ' AND ';
            $where .= 'c.regiao_integracao = :regiao_integracao';
            $params['regiao_integracao'] = trim((string) $filters['regiao_integracao']);
        }

        return [$where, $params];
    }
}
