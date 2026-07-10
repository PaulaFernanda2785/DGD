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

    public function resumo(array $filters): array
    {
        [$where, $params] = $this->buildWhere($filters);
        $stmt = Database::connection()->prepare(
            'SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN c.tem_compdec = 1 THEN 1 ELSE 0 END) AS com_compdec,
                SUM(CASE WHEN c.tem_compdec = 0 THEN 1 ELSE 0 END) AS sem_compdec
             FROM compdecs c' . $where
        );
        $stmt->execute($params);
        $row = $stmt->fetch() ?: [];

        return [
            'total' => (int) ($row['total'] ?? 0),
            'com_compdec' => (int) ($row['com_compdec'] ?? 0),
            'sem_compdec' => (int) ($row['sem_compdec'] ?? 0),
        ];
    }

    public function ubms(): array
    {
        $stmt = Database::connection()->query(
            'SELECT DISTINCT ubm_nome
             FROM compdecs
             WHERE ubm_nome IS NOT NULL
               AND ubm_nome <> \'\'
               AND ubm_nome NOT IN (\'Nao foi registrado\', \'Não foi registrado\')
             ORDER BY ubm_nome ASC'
        );

        return array_column($stmt->fetchAll(), 'ubm_nome');
    }

    public function ubmOptions(int $limit = 1000): array
    {
        $latitudeSql = $this->ubmLatitudeSql();
        $longitudeSql = $this->ubmLongitudeSql();
        $stmt = Database::connection()->prepare(
            "SELECT
                u.id,
                u.nome,
                m.codigo_ibge AS municipio_codigo,
                COALESCE(c.municipio, m.nome) AS municipio,
                c.regiao_integracao,
                {$latitudeSql} AS latitude,
                {$longitudeSql} AS longitude,
                u.ativo
             FROM ubms u
             LEFT JOIN municipios m ON m.id = u.municipio_id
             LEFT JOIN compdecs c ON CAST(c.municipio_codigo AS UNSIGNED) = m.codigo_ibge
             ORDER BY u.nome ASC, municipio ASC
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', min(max($limit, 1), 2000), \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function findUbmById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $latitudeSql = $this->ubmLatitudeSql();
        $longitudeSql = $this->ubmLongitudeSql();
        $stmt = Database::connection()->prepare(
            "SELECT
                u.*,
                m.codigo_ibge AS municipio_codigo,
                m.nome AS municipio,
                c.regiao_integracao,
                {$latitudeSql} AS latitude,
                {$longitudeSql} AS longitude
             FROM ubms u
             LEFT JOIN municipios m ON m.id = u.municipio_id
             LEFT JOIN compdecs c ON CAST(c.municipio_codigo AS UNSIGNED) = m.codigo_ibge
             WHERE u.id = :id
             LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        $ubm = $stmt->fetch();

        return $ubm ?: null;
    }

    public function findUniqueUbmByName(string $name): ?array
    {
        $name = trim($name);

        if ($name === '') {
            return null;
        }

        $latitudeSql = $this->ubmLatitudeSql();
        $longitudeSql = $this->ubmLongitudeSql();
        $stmt = Database::connection()->prepare(
            "SELECT
                u.*,
                m.codigo_ibge AS municipio_codigo,
                m.nome AS municipio,
                c.regiao_integracao,
                {$latitudeSql} AS latitude,
                {$longitudeSql} AS longitude
             FROM ubms u
             LEFT JOIN municipios m ON m.id = u.municipio_id
             LEFT JOIN compdecs c ON CAST(c.municipio_codigo AS UNSIGNED) = m.codigo_ibge
             WHERE u.nome = :name
             ORDER BY u.id ASC
             LIMIT 2"
        );
        $stmt->execute(['name' => $name]);
        $rows = $stmt->fetchAll();

        return count($rows) === 1 ? $rows[0] : null;
    }

    public function countUbmsByName(string $name): int
    {
        $name = trim($name);

        if ($name === '') {
            return 0;
        }

        $stmt = Database::connection()->prepare('SELECT COUNT(*) FROM ubms WHERE nome = :name');
        $stmt->execute(['name' => $name]);

        return (int) $stmt->fetchColumn();
    }

    public function findUbmByCompdec(array $compdec): ?array
    {
        $name = trim((string) ($compdec['ubm_nome'] ?? ''));

        if ($name === '') {
            return null;
        }

        $latitudeSql = $this->ubmLatitudeSql();
        $longitudeSql = $this->ubmLongitudeSql();
        $stmt = Database::connection()->prepare(
            "SELECT
                u.*,
                m.codigo_ibge AS municipio_codigo,
                m.nome AS municipio,
                c.regiao_integracao,
                {$latitudeSql} AS latitude,
                {$longitudeSql} AS longitude
             FROM ubms u
             LEFT JOIN municipios m ON m.id = u.municipio_id
             LEFT JOIN compdecs c ON CAST(c.municipio_codigo AS UNSIGNED) = m.codigo_ibge
             WHERE u.nome = :name
             ORDER BY
                CASE WHEN {$latitudeSql} IS NOT NULL AND {$longitudeSql} IS NOT NULL THEN 0 ELSE 1 END,
                CASE WHEN u.ativo = 1 THEN 0 ELSE 1 END,
                u.id ASC
             LIMIT 1"
        );
        $stmt->execute(['name' => $name]);
        $ubm = $stmt->fetch();

        return $ubm ?: null;
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

    public function syncUbm(array $compdec, ?float $latitude = null, ?float $longitude = null, bool $ativo = true, ?int $preferredId = null): ?int
    {
        $municipioId = (int) ($compdec['municipio_id'] ?? 0);
        $ubmNome = trim((string) ($compdec['ubm_nome'] ?? ''));
        $hasGeoColumns = $this->ubmGeoColumnsAvailable();

        if ($municipioId <= 0 || $ubmNome === '') {
            return null;
        }

        if ($preferredId !== null && $preferredId > 0) {
            $sql = $hasGeoColumns
                ? 'UPDATE ubms
                   SET descricao = :descricao,
                       latitude = :latitude,
                       longitude = :longitude,
                       ativo = :ativo
                   WHERE id = :id'
                : 'UPDATE ubms
                   SET descricao = :descricao,
                       ativo = :ativo
                   WHERE id = :id';
            $params = [
                'id' => $preferredId,
                'descricao' => 'Fonte: COMPDEC DGD',
                'ativo' => $ativo ? 1 : 0,
            ];

            if ($hasGeoColumns) {
                $params['latitude'] = $latitude;
                $params['longitude'] = $longitude;
            }

            $update = Database::connection()->prepare($sql);
            $update->execute($params);

            return $preferredId;
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
            $sql = $hasGeoColumns
                ? 'UPDATE ubms
                   SET nome = :nome,
                       descricao = :descricao,
                       latitude = :latitude,
                       longitude = :longitude,
                       ativo = :ativo
                   WHERE id = :id'
                : 'UPDATE ubms
                   SET nome = :nome,
                       descricao = :descricao,
                       ativo = :ativo
                   WHERE id = :id';
            $params = [
                'id' => (int) $ubmId,
                'nome' => $ubmNome,
                'descricao' => 'Fonte: COMPDEC DGD',
                'ativo' => $ativo ? 1 : 0,
            ];

            if ($hasGeoColumns) {
                $params['latitude'] = $latitude;
                $params['longitude'] = $longitude;
            }

            $update = Database::connection()->prepare($sql);
            $update->execute($params);

            return (int) $ubmId;
        }

        $sql = $hasGeoColumns
            ? 'INSERT INTO ubms (municipio_id, codigo, nome, descricao, latitude, longitude, ativo)
               VALUES (:municipio_id, NULL, :nome, :descricao, :latitude, :longitude, :ativo)'
            : 'INSERT INTO ubms (municipio_id, codigo, nome, descricao, ativo)
               VALUES (:municipio_id, NULL, :nome, :descricao, :ativo)';
        $params = [
            'municipio_id' => $municipioId,
            'nome' => $ubmNome,
            'descricao' => 'Fonte: COMPDEC DGD',
            'ativo' => $ativo ? 1 : 0,
        ];

        if ($hasGeoColumns) {
            $params['latitude'] = $latitude;
            $params['longitude'] = $longitude;
        }

        $insert = Database::connection()->prepare($sql);
        $insert->execute($params);

        return (int) Database::connection()->lastInsertId();
    }

    private function ubmLatitudeSql(): string
    {
        return $this->ubmGeoColumnsAvailable() ? 'COALESCE(u.latitude, c.latitude)' : 'c.latitude';
    }

    private function ubmLongitudeSql(): string
    {
        return $this->ubmGeoColumnsAvailable() ? 'COALESCE(u.longitude, c.longitude)' : 'c.longitude';
    }

    private function ubmGeoColumnsAvailable(): bool
    {
        static $available = null;

        if ($available !== null) {
            return $available;
        }

        $stmt = Database::connection()->query(
            'SELECT COUNT(*)
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = \'ubms\'
               AND COLUMN_NAME IN (\'latitude\', \'longitude\')'
        );
        $available = (int) $stmt->fetchColumn() === 2;

        return $available;
    }

    private function buildWhere(array $filters): array
    {
        $where = '';
        $params = [];

        if (trim((string) ($filters['busca'] ?? '')) !== '') {
            $where .= ' WHERE (c.municipio LIKE :busca_municipio OR c.prefeito LIKE :busca_prefeito OR c.coordenador LIKE :busca_coordenador OR c.ubm_nome LIKE :busca_ubm OR c.email LIKE :busca_email OR c.telefone LIKE :busca_telefone)';
            $busca = '%' . trim((string) $filters['busca']) . '%';
            $params['busca_municipio'] = $busca;
            $params['busca_prefeito'] = $busca;
            $params['busca_coordenador'] = $busca;
            $params['busca_ubm'] = $busca;
            $params['busca_email'] = $busca;
            $params['busca_telefone'] = $busca;
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

        if (trim((string) ($filters['ubm'] ?? '')) !== '') {
            $where .= $where === '' ? ' WHERE ' : ' AND ';
            $where .= 'c.ubm_nome = :ubm';
            $params['ubm'] = trim((string) $filters['ubm']);
        }

        return [$where, $params];
    }
}
