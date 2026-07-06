<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class AnexoRepository
{
    public function findById(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT a.*, ta.nome AS tipo_anexo
             FROM desastre_anexos a
             INNER JOIN tipos_anexo ta ON ta.id = a.tipo_anexo_id
             WHERE a.id = :id AND a.excluido_em IS NULL AND a.ativo = 1
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $anexo = $stmt->fetch();

        return $anexo ?: null;
    }

    public function byDesastre(int $desastreId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT a.*, ta.nome AS tipo_anexo
             FROM desastre_anexos a
             INNER JOIN tipos_anexo ta ON ta.id = a.tipo_anexo_id
             WHERE a.desastre_id = :desastre_id
               AND a.excluido_em IS NULL
               AND a.ativo = 1
             ORDER BY a.enviado_em DESC'
        );
        $stmt->execute(['desastre_id' => $desastreId]);

        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO desastre_anexos (
                desastre_id, tipo_anexo_id, nome_original, nome_arquivo, caminho_armazenado,
                extensao, mime_type, tamanho_bytes, hash_sha256, descricao, enviado_por
             ) VALUES (
                :desastre_id, :tipo_anexo_id, :nome_original, :nome_arquivo, :caminho_armazenado,
                :extensao, :mime_type, :tamanho_bytes, :hash_sha256, :descricao, :enviado_por
             )'
        );
        $stmt->execute($data);

        return (int) Database::connection()->lastInsertId();
    }

    public function softDelete(int $id, int $userId): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE desastre_anexos
             SET ativo = 0, excluido_por = :user_id, excluido_em = NOW()
             WHERE id = :id AND excluido_em IS NULL'
        );
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }
}
