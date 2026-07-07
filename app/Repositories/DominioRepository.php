<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class DominioRepository
{
    public function tiposDecreto(): array
    {
        return $this->all('tipos_decreto');
    }

    public function statusHomologacao(): array
    {
        return $this->all('status_homologacao');
    }

    public function statusReconhecimento(): array
    {
        return $this->all('status_reconhecimento');
    }

    public function statusRecurso(): array
    {
        return $this->all('status_recurso');
    }

    public function statusEnvioPge(): array
    {
        return $this->all('status_envio_pge');
    }

    public function tiposAnexo(): array
    {
        $stmt = Database::connection()->query('SELECT id, codigo, nome, obrigatorio FROM tipos_anexo WHERE ativo = 1 ORDER BY ordem ASC, nome ASC');

        return $stmt->fetchAll();
    }

    public function municipios(): array
    {
        $stmt = Database::connection()->query('SELECT id, nome, codigo_ibge FROM municipios WHERE ativo = 1 ORDER BY nome ASC');

        return $stmt->fetchAll();
    }

    public function ubms(): array
    {
        $stmt = Database::connection()->query('SELECT id, nome, municipio_id FROM ubms WHERE ativo = 1 ORDER BY nome ASC');

        return $stmt->fetchAll();
    }

    public function analistas(): array
    {
        $stmt = Database::connection()->query(
            "SELECT u.id, u.nome
             FROM usuarios u
             INNER JOIN perfis p ON p.id = u.perfil_id
             WHERE p.codigo = 'GESTOR'
               AND u.ativo = 1
               AND u.excluido_em IS NULL
             ORDER BY u.nome ASC"
        );

        return $stmt->fetchAll();
    }

    public function findMunicipio(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT id, nome FROM municipios WHERE id = :id AND ativo = 1 LIMIT 1');
        $stmt->execute(['id' => $id]);
        $municipio = $stmt->fetch();

        return $municipio ?: null;
    }

    public function findUbmForMunicipio(int $ubmId, int $municipioId): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT id, nome, municipio_id
             FROM ubms
             WHERE id = :id
               AND municipio_id = :municipio_id
               AND ativo = 1
             LIMIT 1'
        );
        $stmt->execute([
            'id' => $ubmId,
            'municipio_id' => $municipioId,
        ]);
        $ubm = $stmt->fetch();

        return $ubm ?: null;
    }

    private function all(string $table): array
    {
        $stmt = Database::connection()->query("SELECT id, codigo, nome FROM {$table} WHERE ativo = 1 ORDER BY ordem ASC, nome ASC");

        return $stmt->fetchAll();
    }
}
