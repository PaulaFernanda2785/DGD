<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

class PainelService
{
    public function resumo(array $filters = []): array
    {
        try {
            [$where, $params] = $this->decretoWhere($filters);
            $stmt = Database::connection()->prepare(
                'SELECT
                    COUNT(*) AS total_desastres,
                    SUM(CASE WHEN numero_decreto_municipal IS NOT NULL AND numero_decreto_municipal <> \'\' THEN 1 ELSE 0 END) AS total_decretos_municipais,
                    SUM(CASE WHEN homologacao_codigo = \'SOLICITADO\' THEN 1 ELSE 0 END) AS homologacoes_solicitadas,
                    SUM(CASE WHEN homologacao_codigo = \'HOMOLOGADO\' THEN 1 ELSE 0 END) AS homologados,
                    SUM(CASE WHEN homologacao_codigo = \'NAO_HOMOLOGADO\' THEN 1 ELSE 0 END) AS nao_homologados,
                    SUM(CASE WHEN reconhecimento_codigo = \'RECONHECIDO\' THEN 1 ELSE 0 END) AS reconhecidos,
                    SUM(CASE WHEN homologacao_codigo = \'ENVIADO_PGE\' THEN 1 ELSE 0 END) AS enviados_pge,
                    SUM(CASE WHEN status_prazo_pge_calculado = \'PENDENTE\' THEN 1 ELSE 0 END) AS pendentes_pge,
                    SUM(COALESCE(total_afetados, 0)) AS total_afetados,
                    COUNT(DISTINCT municipio_id) AS municipios_com_registro
                 FROM vw_decretos_listagem
                 WHERE ativo = 1' . $where
            );
            $stmt->execute($params);
            $resumo = $stmt->fetch();
        } catch (\Throwable) {
            $resumo = null;
        }

        return array_map('intval', $resumo ?: [
            'total_desastres' => 0,
            'total_decretos_municipais' => 0,
            'homologacoes_solicitadas' => 0,
            'homologados' => 0,
            'nao_homologados' => 0,
            'reconhecidos' => 0,
            'enviados_pge' => 0,
            'pendentes_pge' => 0,
            'total_afetados' => 0,
            'municipios_com_registro' => 0,
        ]);
    }

    public function indicadores(array $filters = []): array
    {
        try {
            [$where, $params] = $this->decretoWhere($filters);
            $stmt = Database::connection()->prepare(
                'SELECT
                    municipio,
                    COUNT(*) AS total,
                    SUM(COALESCE(total_afetados, 0)) AS afetados
                 FROM vw_decretos_listagem
                 WHERE ativo = 1' . $where . '
                 GROUP BY municipio
                 ORDER BY total DESC, afetados DESC, municipio ASC
                 LIMIT 6'
            );
            $stmt->execute($params);

            return $stmt->fetchAll();
        } catch (\Throwable) {
            return [];
        }
    }

    public function recentes(array $filters = []): array
    {
        try {
            [$where, $params] = $this->decretoWhere($filters);
            $stmt = Database::connection()->prepare(
                'SELECT id, protocolo_dgd, municipio, data_desastre, homologacao, reconhecimento, cobrade_tipo, status_envio_pge
                 FROM vw_decretos_listagem
                 WHERE ativo = 1' . $where . '
                 ORDER BY criado_em DESC
                 LIMIT 8'
            );
            $stmt->execute($params);

            return $stmt->fetchAll();
        } catch (\Throwable) {
            return [];
        }
    }

    public function mapa(array $filters = []): array
    {
        return [
            'compdecs' => $this->compdecPoints($filters),
            'ubms' => $this->ubmPoints($filters),
            'desastres' => $this->desastrePoints($filters),
        ];
    }

    public function opcoesFiltros(): array
    {
        return [
            'anos' => $this->anos(),
            'municipios' => $this->municipios(),
            'regioes' => $this->regioes(),
            'tipos_decreto' => $this->tiposDecreto(),
            'homologacoes' => $this->homologacoes(),
            'reconhecimentos' => $this->reconhecimentos(),
            'status_pge' => [
                'NO PRAZO' => 'No prazo',
                'PENDENTE' => 'Pendente',
                'APROVADO' => 'Aprovado',
                'REPROVADO' => 'Reprovado',
                'NAO REGISTRADO' => 'Não registrado',
            ],
        ];
    }

    public function relatorio(array $filters = []): array
    {
        return [
            'filters' => $filters,
            'opcoes' => $this->opcoesFiltros(),
            'resumo' => $this->resumo($filters),
            'indicadores' => $this->indicadores($filters),
            'mapa' => $this->mapa($filters),
            'recentes' => $this->recentes($filters),
            'registros' => $this->registrosRelatorio($filters),
        ];
    }

    private function registrosRelatorio(array $filters): array
    {
        try {
            [$where, $params] = $this->decretoWhere($filters);
            $stmt = Database::connection()->prepare(
                'SELECT
                    id,
                    protocolo_dgd,
                    municipio,
                    compdec_regiao_integracao,
                    ubm_atuante,
                    tipo_decreto,
                    cobrade_codigo,
                    cobrade_subtipo,
                    data_desastre,
                    numero_decreto_municipal,
                    homologacao,
                    reconhecimento,
                    status_envio_pge,
                    data_envio_pge,
                    duracao_pge_dias,
                    status_prazo_pge_calculado,
                    total_afetados
                 FROM vw_decretos_listagem
                 WHERE ativo = 1' . $where . '
                 ORDER BY protocolo_ano DESC, protocolo_sequencial DESC
                 LIMIT 200'
            );
            $stmt->execute($params);

            return $stmt->fetchAll();
        } catch (\Throwable) {
            return [];
        }
    }

    private function desastrePoints(array $filters): array
    {
        try {
            [$where, $params] = $this->decretoWhere($filters, 'v');
            $stmt = Database::connection()->prepare(
                'SELECT
                    v.municipio_id,
                    v.municipio,
                    m.uf,
                    m.codigo_ibge,
                    m.latitude,
                    m.longitude,
                    COUNT(*) AS total_desastres,
                    SUM(COALESCE(v.total_afetados, 0)) AS total_afetados,
                    SUM(CASE WHEN v.homologacao_codigo = \'HOMOLOGADO\' THEN 1 ELSE 0 END) AS homologados,
                    SUM(CASE WHEN v.status_prazo_pge_calculado = \'PENDENTE\' THEN 1 ELSE 0 END) AS pendentes_pge,
                    MAX(v.data_desastre) AS ultimo_desastre,
                    SUBSTRING_INDEX(GROUP_CONCAT(v.protocolo_dgd ORDER BY v.criado_em DESC SEPARATOR \'||\'), \'||\', 1) AS protocolo_dgd,
                    MAX(v.cobrade_tipo) AS cobrade_tipo,
                    MAX(v.cobrade_simbologia) AS cobrade_simbologia
                 FROM vw_decretos_listagem v
                 INNER JOIN municipios m ON m.id = v.municipio_id
                 WHERE v.ativo = 1
                   AND m.latitude IS NOT NULL
                   AND m.longitude IS NOT NULL' . $where . '
                 GROUP BY v.municipio_id, v.municipio, m.uf, m.codigo_ibge, m.latitude, m.longitude
                 ORDER BY total_desastres DESC, v.municipio ASC'
            );
            $stmt->execute($params);

            return array_map([$this, 'normalizePoint'], $stmt->fetchAll());
        } catch (\Throwable) {
            return [];
        }
    }

    private function ubmPointsFromCompdecs(array $filters): array
    {
        try {
            [$where, $params] = $this->compdecWhere($filters, 'c', 'm');
            $stmt = Database::connection()->prepare(
                'SELECT
                    MIN(c.id) AS id,
                    c.ubm_nome AS nome,
                    MIN(m.id) AS municipio_id,
                    MIN(c.municipio) AS municipio,
                    MIN(m.uf) AS uf,
                    MIN(c.municipio_codigo) AS codigo_ibge,
                    AVG(COALESCE(c.latitude, m.latitude)) AS latitude,
                    AVG(COALESCE(c.longitude, m.longitude)) AS longitude,
                    MIN(c.regiao_integracao) AS regiao_integracao,
                    COUNT(DISTINCT COALESCE(m.id, c.municipio_codigo)) AS municipios_vinculados,
                    1 AS ativo
                 FROM compdecs c
                 LEFT JOIN municipios m ON m.codigo_ibge = CAST(c.municipio_codigo AS UNSIGNED)
                 WHERE c.ubm_nome IS NOT NULL
                   AND TRIM(c.ubm_nome) <> \'\'
                   AND c.ubm_nome NOT IN (\'Nao foi registrado\', \'Não foi registrado\', \'NÃ£o foi registrado\')
                   AND COALESCE(c.latitude, m.latitude) IS NOT NULL
                   AND COALESCE(c.longitude, m.longitude) IS NOT NULL' . $where . '
                 GROUP BY c.ubm_nome
                 ORDER BY c.ubm_nome ASC'
            );
            $stmt->execute($params);

            return array_map([$this, 'normalizePoint'], $stmt->fetchAll());
        } catch (\Throwable) {
            return [];
        }
    }

    private function compdecPoints(array $filters): array
    {
        try {
            [$where, $params] = $this->compdecWhere($filters, 'c', 'm');
            $stmt = Database::connection()->prepare(
                'SELECT
                    c.id,
                    m.id AS municipio_id,
                    c.municipio,
                    m.uf,
                    c.municipio_codigo AS codigo_ibge,
                    COALESCE(c.latitude, m.latitude) AS latitude,
                    COALESCE(c.longitude, m.longitude) AS longitude,
                    c.tem_compdec,
                    c.regiao_integracao,
                    c.coordenador,
                    c.telefone,
                    c.email,
                    c.ubm_nome
                 FROM compdecs c
                 LEFT JOIN municipios m ON m.codigo_ibge = CAST(c.municipio_codigo AS UNSIGNED)
                 WHERE COALESCE(c.latitude, m.latitude) IS NOT NULL
                   AND COALESCE(c.longitude, m.longitude) IS NOT NULL' . $where . '
                 ORDER BY c.municipio ASC'
            );
            $stmt->execute($params);

            return array_map([$this, 'normalizePoint'], $stmt->fetchAll());
        } catch (\Throwable) {
            return [];
        }
    }

    private function ubmPoints(array $filters): array
    {
        try {
            [$where, $params] = $this->ubmWhere($filters, 'u', 'm', 'c');
            $stmt = Database::connection()->prepare(
                'SELECT
                    MIN(u.id) AS id,
                    u.nome,
                    MIN(m.id) AS municipio_id,
                    MIN(COALESCE(c.municipio, m.nome)) AS municipio,
                    MIN(m.uf) AS uf,
                    MIN(m.codigo_ibge) AS codigo_ibge,
                    AVG(COALESCE(u.latitude, c.latitude, m.latitude)) AS latitude,
                    AVG(COALESCE(u.longitude, c.longitude, m.longitude)) AS longitude,
                    MIN(c.regiao_integracao) AS regiao_integracao,
                    COUNT(DISTINCT m.id) AS municipios_vinculados,
                    MAX(u.ativo) AS ativo
                 FROM ubms u
                 LEFT JOIN municipios m ON m.id = u.municipio_id
                 LEFT JOIN compdecs c ON CAST(c.municipio_codigo AS UNSIGNED) = m.codigo_ibge
                 WHERE u.ativo = 1
                   AND COALESCE(u.latitude, c.latitude, m.latitude) IS NOT NULL
                   AND COALESCE(u.longitude, c.longitude, m.longitude) IS NOT NULL' . $where . '
                 GROUP BY u.nome
                 ORDER BY u.nome ASC'
            );
            $stmt->execute($params);

            $points = array_map([$this, 'normalizePoint'], $stmt->fetchAll());

            return $points !== [] ? $points : $this->ubmPointsFromCompdecs($filters);
        } catch (\Throwable) {
            return $this->ubmPointsFromCompdecs($filters);
        }
    }

    private function decretoWhere(array $filters, string $alias = ''): array
    {
        $prefix = $alias !== '' ? $alias . '.' : '';
        $where = '';
        $params = [];

        if (trim((string) ($filters['ano'] ?? '')) !== '') {
            $where .= ' AND ' . $prefix . 'protocolo_ano = :ano';
            $params['ano'] = (int) $filters['ano'];
        }

        if (trim((string) ($filters['municipio_id'] ?? '')) !== '') {
            $where .= ' AND ' . $prefix . 'municipio_id = :municipio_id';
            $params['municipio_id'] = (int) $filters['municipio_id'];
        }

        if (trim((string) ($filters['regiao_integracao'] ?? '')) !== '') {
            $where .= ' AND ' . $prefix . 'compdec_regiao_integracao = :regiao_integracao';
            $params['regiao_integracao'] = trim((string) $filters['regiao_integracao']);
        }

        if (trim((string) ($filters['tipo_decreto_id'] ?? '')) !== '') {
            $where .= ' AND ' . $prefix . 'tipo_decreto = (SELECT nome FROM tipos_decreto WHERE id = :tipo_decreto_id LIMIT 1)';
            $params['tipo_decreto_id'] = (int) $filters['tipo_decreto_id'];
        }

        if (trim((string) ($filters['homologacao_status_id'] ?? '')) !== '') {
            $where .= ' AND ' . $prefix . 'homologacao_status_id = :homologacao_status_id';
            $params['homologacao_status_id'] = (int) $filters['homologacao_status_id'];
        }

        if (trim((string) ($filters['reconhecimento_status_id'] ?? '')) !== '') {
            $where .= ' AND ' . $prefix . 'reconhecimento_status_id = :reconhecimento_status_id';
            $params['reconhecimento_status_id'] = (int) $filters['reconhecimento_status_id'];
        }

        if (trim((string) ($filters['status_prazo_pge'] ?? '')) !== '') {
            $where .= ' AND ' . $prefix . 'status_prazo_pge_calculado = :status_prazo_pge';
            $params['status_prazo_pge'] = trim((string) $filters['status_prazo_pge']);
        }

        return [$where, $params];
    }

    private function compdecWhere(array $filters, string $compdecAlias, string $municipioAlias): array
    {
        $where = '';
        $params = [];

        if (trim((string) ($filters['municipio_id'] ?? '')) !== '') {
            $where .= ' AND ' . $municipioAlias . '.id = :municipio_id';
            $params['municipio_id'] = (int) $filters['municipio_id'];
        }

        if (trim((string) ($filters['regiao_integracao'] ?? '')) !== '') {
            $where .= ' AND ' . $compdecAlias . '.regiao_integracao = :regiao_integracao';
            $params['regiao_integracao'] = trim((string) $filters['regiao_integracao']);
        }

        return [$where, $params];
    }

    private function ubmWhere(array $filters, string $ubmAlias, string $municipioAlias, string $compdecAlias): array
    {
        unset($ubmAlias);
        $where = '';
        $params = [];

        if (trim((string) ($filters['municipio_id'] ?? '')) !== '') {
            $where .= ' AND ' . $municipioAlias . '.id = :municipio_id';
            $params['municipio_id'] = (int) $filters['municipio_id'];
        }

        if (trim((string) ($filters['regiao_integracao'] ?? '')) !== '') {
            $where .= ' AND ' . $compdecAlias . '.regiao_integracao = :regiao_integracao';
            $params['regiao_integracao'] = trim((string) $filters['regiao_integracao']);
        }

        return [$where, $params];
    }

    private function anos(): array
    {
        try {
            $stmt = Database::connection()->query(
                'SELECT DISTINCT protocolo_ano AS ano
                 FROM vw_decretos_listagem
                 WHERE ativo = 1
                 ORDER BY protocolo_ano DESC'
            );

            $anos = array_column($stmt->fetchAll(), 'ano');

            return $anos !== [] ? $anos : [(int) date('Y')];
        } catch (\Throwable) {
            return [(int) date('Y')];
        }
    }

    private function municipios(): array
    {
        try {
            $stmt = Database::connection()->query(
                'SELECT id, nome, uf
                 FROM municipios
                 WHERE ativo = 1
                 ORDER BY nome ASC'
            );

            return $stmt->fetchAll();
        } catch (\Throwable) {
            return [];
        }
    }

    private function regioes(): array
    {
        try {
            $stmt = Database::connection()->query(
                'SELECT DISTINCT regiao_integracao
                 FROM compdecs
                 WHERE regiao_integracao IS NOT NULL AND regiao_integracao <> \'\'
                 ORDER BY regiao_integracao ASC'
            );

            return array_column($stmt->fetchAll(), 'regiao_integracao');
        } catch (\Throwable) {
            return [];
        }
    }

    private function tiposDecreto(): array
    {
        try {
            $stmt = Database::connection()->query(
                'SELECT id, nome
                 FROM tipos_decreto
                 WHERE ativo = 1
                 ORDER BY ordem ASC, nome ASC'
            );

            return $stmt->fetchAll();
        } catch (\Throwable) {
            return [];
        }
    }

    private function homologacoes(): array
    {
        try {
            $stmt = Database::connection()->query(
                'SELECT id, nome
                 FROM status_homologacao
                 WHERE ativo = 1
                 ORDER BY ordem ASC, nome ASC'
            );

            return $stmt->fetchAll();
        } catch (\Throwable) {
            return [];
        }
    }

    private function reconhecimentos(): array
    {
        try {
            $stmt = Database::connection()->query(
                'SELECT id, nome
                 FROM status_reconhecimento
                 WHERE ativo = 1
                 ORDER BY ordem ASC, nome ASC'
            );

            return $stmt->fetchAll();
        } catch (\Throwable) {
            return [];
        }
    }

    private function normalizePoint(array $point): array
    {
        foreach (['latitude', 'longitude'] as $field) {
            $point[$field] = (float) str_replace(',', '.', (string) ($point[$field] ?? '0'));
        }

        return $point;
    }
}
