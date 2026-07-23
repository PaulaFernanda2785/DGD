<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class TipoAjudaRepository
{
    public function find(int $id): ?array { $stmt = Database::connection()->prepare('SELECT id, nome, unidade_medida, ativo FROM tipos_ajuda WHERE id = :id'); $stmt->execute(['id' => $id]); return $stmt->fetch() ?: null; }
    public function salvar(?int $id, string $nome, string $unidade, bool $ativo): void { $pdo = Database::connection(); if ($id === null) { $stmt = $pdo->prepare('INSERT INTO tipos_ajuda (nome, unidade_medida, ativo) VALUES (:nome,:unidade,:ativo)'); $stmt->execute(['nome'=>$nome,'unidade'=>$unidade,'ativo'=>$ativo?1:0]); return; } $stmt=$pdo->prepare('UPDATE tipos_ajuda SET nome=:nome, unidade_medida=:unidade, ativo=:ativo WHERE id=:id'); $stmt->execute(['id'=>$id,'nome'=>$nome,'unidade'=>$unidade,'ativo'=>$ativo?1:0]); }
    public function status(int $id, bool $ativo): void { $stmt=Database::connection()->prepare('UPDATE tipos_ajuda SET ativo=:ativo WHERE id=:id'); $stmt->execute(['id'=>$id,'ativo'=>$ativo?1:0]); }
    public function contarEntregas(int $id): int { $stmt=Database::connection()->prepare('SELECT COUNT(*) FROM decreto_entregas WHERE tipo_ajuda_id=:id'); $stmt->execute(['id'=>$id]); return (int) $stmt->fetchColumn(); }
    public function excluir(int $id): void { $stmt=Database::connection()->prepare('DELETE FROM tipos_ajuda WHERE id=:id'); $stmt->execute(['id'=>$id]); }
    public function listar(array $filtros): array
    {
        $where = ['1 = 1'];
        $params = [];
        if (($filtros['busca'] ?? '') !== '') { $where[] = '(nome LIKE :busca OR unidade_medida LIKE :busca)'; $params['busca'] = '%' . $filtros['busca'] . '%'; }
        if (in_array($filtros['status'] ?? '', ['ativo', 'inativo'], true)) { $where[] = 'ativo = :ativo'; $params['ativo'] = $filtros['status'] === 'ativo' ? 1 : 0; }
        if (($filtros['unidade'] ?? '') !== '') { $where[] = 'unidade_medida = :unidade'; $params['unidade'] = $filtros['unidade']; }
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT t.id, t.nome, t.unidade_medida, t.ativo, t.criado_em, COALESCE((SELECT SUM(e.quantidade) FROM decreto_entregas e WHERE e.tipo_ajuda_id = t.id), 0) AS entregas_registradas FROM tipos_ajuda t WHERE ' . implode(' AND ', $where) . ' ORDER BY t.ativo DESC, t.nome ASC');
        $stmt->execute($params);
        $tipos = $stmt->fetchAll();
        $resumo = $pdo->query('SELECT COUNT(*) total, COALESCE(SUM(ativo = 1), 0) ativos, COALESCE(SUM(ativo = 0), 0) inativos, COUNT(DISTINCT unidade_medida) unidades FROM tipos_ajuda')->fetch() ?: [];
        $unidades = $pdo->query('SELECT DISTINCT unidade_medida FROM tipos_ajuda ORDER BY unidade_medida')->fetchAll();
        return compact('tipos', 'resumo', 'unidades');
    }
}
