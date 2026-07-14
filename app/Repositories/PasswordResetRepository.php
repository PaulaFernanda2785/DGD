<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class PasswordResetRepository
{
    public function invalidatePendingByUser(int $usuarioId): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE recuperacoes_senha
             SET usado_em = CURRENT_TIMESTAMP
             WHERE usuario_id = :usuario_id AND usado_em IS NULL'
        );
        $stmt->execute(['usuario_id' => $usuarioId]);
    }

    public function create(array $data): int
    {
        $expiresMinutes = max(1, min(1440, (int) ($data['expira_minutos'] ?? 60)));
        $stmt = Database::connection()->prepare(
            'INSERT INTO recuperacoes_senha (
                usuario_id, token_hash, email_solicitado, ip_solicitacao, user_agent, expira_em
            ) VALUES (
                :usuario_id, :token_hash, :email_solicitado, :ip_solicitacao, :user_agent,
                TIMESTAMPADD(MINUTE, :expira_minutos, CURRENT_TIMESTAMP)
            )'
        );
        $stmt->execute([
            'usuario_id' => $data['usuario_id'],
            'token_hash' => $data['token_hash'],
            'email_solicitado' => $data['email_solicitado'],
            'ip_solicitacao' => $data['ip_solicitacao'],
            'user_agent' => $data['user_agent'],
            'expira_minutos' => $expiresMinutes,
        ]);

        return (int) Database::connection()->lastInsertId();
    }

    public function findValidByTokenHash(string $tokenHash, int $validForMinutes = 60): ?array
    {
        $validForMinutes = max(1, min(1440, $validForMinutes));
        $stmt = Database::connection()->prepare(
            'SELECT r.*, u.nome, u.email, u.ativo
             FROM recuperacoes_senha r
             INNER JOIN usuarios u ON u.id = r.usuario_id
             WHERE r.token_hash = :token_hash
               AND r.usado_em IS NULL
               AND TIMESTAMPADD(MINUTE, :validade_minutos, r.criado_em) >= CURRENT_TIMESTAMP
               AND u.excluido_em IS NULL
               AND u.ativo = 1
             LIMIT 1'
        );
        $stmt->execute([
            'token_hash' => $tokenHash,
            'validade_minutos' => $validForMinutes,
        ]);
        $reset = $stmt->fetch();

        return $reset ?: null;
    }

    public function markUsed(int $id): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE recuperacoes_senha
             SET usado_em = CURRENT_TIMESTAMP
             WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
    }
}
