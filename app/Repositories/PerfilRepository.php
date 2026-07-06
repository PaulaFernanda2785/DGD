<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class PerfilRepository
{
    public function allActive(): array
    {
        $stmt = Database::connection()->query(
            'SELECT id, codigo, nome, nivel_acesso FROM perfis WHERE ativo = 1 ORDER BY nivel_acesso DESC, nome ASC'
        );

        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM perfis WHERE id = :id AND ativo = 1 LIMIT 1');
        $stmt->execute(['id' => $id]);
        $perfil = $stmt->fetch();

        return $perfil ?: null;
    }
}
