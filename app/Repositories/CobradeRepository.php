<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class CobradeRepository
{
    public function grupos(): array
    {
        $stmt = Database::connection()->query('SELECT id, codigo, nome FROM cobrade_grupos WHERE ativo = 1 ORDER BY codigo ASC');

        return $stmt->fetchAll();
    }

    public function subgrupos(?int $grupoId): array
    {
        $sql = 'SELECT id, grupo_id, codigo, nome FROM cobrade_subgrupos WHERE ativo = 1';
        $params = [];

        if ($grupoId) {
            $sql .= ' AND grupo_id = :grupo_id';
            $params['grupo_id'] = $grupoId;
        }

        $sql .= ' ORDER BY codigo ASC';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function tipos(?int $subgrupoId): array
    {
        $sql = 'SELECT id, subgrupo_id, codigo, nome FROM cobrade_tipos WHERE ativo = 1';
        $params = [];

        if ($subgrupoId) {
            $sql .= ' AND subgrupo_id = :subgrupo_id';
            $params['subgrupo_id'] = $subgrupoId;
        }

        $sql .= ' ORDER BY codigo ASC';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function subtipos(?int $tipoId): array
    {
        $sql = 'SELECT id, tipo_id, codigo, nome, descricao, simbologia FROM cobrade_subtipos WHERE ativo = 1';
        $params = [];

        if ($tipoId) {
            $sql .= ' AND tipo_id = :tipo_id';
            $params['tipo_id'] = $tipoId;
        }

        $sql .= ' ORDER BY codigo ASC';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function subtiposComHierarquia(): array
    {
        $stmt = Database::connection()->query(
            'SELECT
                cs.id,
                cs.tipo_id,
                cs.codigo,
                cs.nome,
                cs.descricao,
                cs.simbologia,
                ct.id AS tipo_id,
                ct.nome AS tipo_nome,
                csg.id AS subgrupo_id,
                csg.nome AS subgrupo_nome,
                cg.id AS grupo_id,
                cg.nome AS grupo_nome
             FROM cobrade_subtipos cs
             INNER JOIN cobrade_tipos ct ON ct.id = cs.tipo_id
             INNER JOIN cobrade_subgrupos csg ON csg.id = ct.subgrupo_id
             INNER JOIN cobrade_grupos cg ON cg.id = csg.grupo_id
             WHERE cs.ativo = 1 AND ct.ativo = 1 AND csg.ativo = 1 AND cg.ativo = 1
             ORDER BY cs.codigo ASC'
        );

        return $stmt->fetchAll();
    }

    public function detalhe(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT
                cs.id,
                cs.codigo,
                cs.nome,
                cs.descricao,
                cs.simbologia,
                ct.id AS tipo_id,
                ct.nome AS tipo_nome,
                csg.id AS subgrupo_id,
                csg.nome AS subgrupo_nome,
                cg.id AS grupo_id,
                cg.nome AS grupo_nome
             FROM cobrade_subtipos cs
             INNER JOIN cobrade_tipos ct ON ct.id = cs.tipo_id
             INNER JOIN cobrade_subgrupos csg ON csg.id = ct.subgrupo_id
             INNER JOIN cobrade_grupos cg ON cg.id = csg.grupo_id
             WHERE cs.id = :id AND cs.ativo = 1
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $detalhe = $stmt->fetch();

        return $detalhe ?: null;
    }
}
