<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class TipoAjudaRepository
{
    public function listar(array $filtros): array
    {
        $where = ['1 = 1'];
        $params = [];
        if (($filtros['busca'] ?? '') !== '') { $where[] = '(nome LIKE :busca OR unidade_medida LIKE :busca)'; $params['busca'] = '%' . $filtros['busca'] . '%'; }
        if (in_array($filtros['status'] ?? '', ['ativo', 'inativo'], true)) { $where[] = 'ativo = :ativo'; $params['ativo'] = $filtros['status'] === 'ativo' ? 1 : 0; }
        if (($filtros['unidade'] ?? '') !== '') { $where[] = 'unidade_medida = :unidade'; $params['unidade'] = $filtros['unidade']; }
        $pdo = Database::connection('cadastro_emergencial');
        $stmt = $pdo->prepare('SELECT id, nome, unidade_medida, ativo, criado_em FROM tipos_ajuda WHERE ' . implode(' AND ', $where) . ' ORDER BY ativo DESC, nome ASC');
        $stmt->execute($params);
        $tipos = $stmt->fetchAll();
        $resumo = $pdo->query('SELECT COUNT(*) total, COALESCE(SUM(ativo = 1), 0) ativos, COALESCE(SUM(ativo = 0), 0) inativos, COUNT(DISTINCT unidade_medida) unidades FROM tipos_ajuda')->fetch() ?: [];
        $unidades = $pdo->query('SELECT DISTINCT unidade_medida FROM tipos_ajuda ORDER BY unidade_medida')->fetchAll();
        return compact('tipos', 'resumo', 'unidades');
    }
}
