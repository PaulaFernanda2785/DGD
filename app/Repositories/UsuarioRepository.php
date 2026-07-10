<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class UsuarioRepository
{
    public function findByEmail(string $email): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT u.*, p.codigo AS perfil_codigo, p.nome AS perfil_nome
             FROM usuarios u
             INNER JOIN perfis p ON p.id = u.perfil_id
             WHERE u.email = :email AND u.excluido_em IS NULL
             LIMIT 1'
        );
        $stmt->execute(['email' => mb_strtolower($email)]);
        $usuario = $stmt->fetch();

        return $usuario ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT u.*, p.codigo AS perfil_codigo, p.nome AS perfil_nome
             FROM usuarios u
             INNER JOIN perfis p ON p.id = u.perfil_id
             WHERE u.id = :id AND u.excluido_em IS NULL
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $usuario = $stmt->fetch();

        return $usuario ?: null;
    }

    public function paginate(array $filters = []): array
    {
        $sql = 'SELECT u.id, u.nome, u.email, u.cpf, u.instituicao, u.ativo, u.ultimo_acesso_em,
                       u.criado_em, p.nome AS perfil_nome, p.codigo AS perfil_codigo
                FROM usuarios u
                INNER JOIN perfis p ON p.id = u.perfil_id
                WHERE u.excluido_em IS NULL';
        $params = [];

        if (!empty($filters['busca'])) {
            $busca = '%' . trim((string) $filters['busca']) . '%';
            $sql .= ' AND (u.nome LIKE :busca_nome OR u.email LIKE :busca_email OR u.cpf LIKE :busca_cpf)';
            $params['busca_nome'] = $busca;
            $params['busca_email'] = $busca;
            $params['busca_cpf'] = $busca;
        }

        if (isset($filters['perfil_id']) && $filters['perfil_id'] !== '') {
            $sql .= ' AND u.perfil_id = :perfil_id';
            $params['perfil_id'] = (int) $filters['perfil_id'];
        }

        if (isset($filters['ativo']) && $filters['ativo'] !== '') {
            $sql .= ' AND u.ativo = :ativo';
            $params['ativo'] = (int) $filters['ativo'];
        }

        $sql .= ' ORDER BY u.nome ASC LIMIT 100';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO usuarios (
            perfil_id, nome, email, cpf, telefone, cargo, instituicao,
            senha_hash, ativo, trocar_senha_proximo_acesso, criado_por
        ) VALUES (
            :perfil_id, :nome, :email, :cpf, :telefone, :cargo, :instituicao,
            :senha_hash, :ativo, :trocar_senha_proximo_acesso, :criado_por
        )';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute([
            'perfil_id' => $data['perfil_id'],
            'nome' => $data['nome'],
            'email' => $data['email'],
            'cpf' => $data['cpf'] ?: null,
            'telefone' => $data['telefone'] ?: null,
            'cargo' => $data['cargo'] ?: null,
            'instituicao' => $data['instituicao'] ?: 'CEDEC-PA',
            'senha_hash' => $data['senha_hash'],
            'ativo' => $data['ativo'],
            'trocar_senha_proximo_acesso' => $data['trocar_senha_proximo_acesso'],
            'criado_por' => $data['criado_por'],
        ]);

        return (int) Database::connection()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $sql = 'UPDATE usuarios
                SET perfil_id = :perfil_id,
                    nome = :nome,
                    email = :email,
                    cpf = :cpf,
                    telefone = :telefone,
                    cargo = :cargo,
                    instituicao = :instituicao,
                    ativo = :ativo,
                    atualizado_por = :atualizado_por';
        $params = [
            'id' => $id,
            'perfil_id' => $data['perfil_id'],
            'nome' => $data['nome'],
            'email' => $data['email'],
            'cpf' => $data['cpf'] ?: null,
            'telefone' => $data['telefone'] ?: null,
            'cargo' => $data['cargo'] ?: null,
            'instituicao' => $data['instituicao'] ?: 'CEDEC-PA',
            'ativo' => $data['ativo'],
            'atualizado_por' => $data['atualizado_por'],
        ];

        if (!empty($data['senha_hash'])) {
            $sql .= ', senha_hash = :senha_hash, trocar_senha_proximo_acesso = :trocar_senha_proximo_acesso';
            $params['senha_hash'] = $data['senha_hash'];
            $params['trocar_senha_proximo_acesso'] = $data['trocar_senha_proximo_acesso'];
        }

        $sql .= ' WHERE id = :id AND excluido_em IS NULL';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
    }

    public function updatePassword(int $id, string $senhaHash): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE usuarios
             SET senha_hash = :senha_hash,
                 trocar_senha_proximo_acesso = 0,
                 atualizado_por = :atualizado_por
             WHERE id = :id AND excluido_em IS NULL'
        );
        $stmt->execute([
            'senha_hash' => $senhaHash,
            'atualizado_por' => $id,
            'id' => $id,
        ]);
    }

    public function updateStatus(int $id, int $ativo, int $updatedBy): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE usuarios
             SET ativo = :ativo,
                 atualizado_por = :atualizado_por
             WHERE id = :id AND excluido_em IS NULL'
        );
        $stmt->execute([
            'id' => $id,
            'ativo' => $ativo,
            'atualizado_por' => $updatedBy,
        ]);
    }

    public function softDelete(int $id, int $deletedBy): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE usuarios
             SET ativo = 0, excluido_por = :excluido_por, excluido_em = NOW()
             WHERE id = :id AND excluido_em IS NULL'
        );
        $stmt->execute(['id' => $id, 'excluido_por' => $deletedBy]);
    }

    public function countActiveAdminsExcept(?int $exceptId = null): int
    {
        $sql = "SELECT COUNT(*)
                FROM usuarios u
                INNER JOIN perfis p ON p.id = u.perfil_id
                WHERE p.codigo = 'ADMIN'
                  AND u.ativo = 1
                  AND u.excluido_em IS NULL";
        $params = [];

        if ($exceptId !== null) {
            $sql .= ' AND u.id <> :id';
            $params['id'] = $exceptId;
        }

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    public function logLogin(?int $usuarioId, ?string $email, bool $success, ?string $reason, ?string $ip, ?string $userAgent): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO login_logs (usuario_id, email_informado, sucesso, motivo_falha, ip, user_agent)
             VALUES (:usuario_id, :email, :sucesso, :motivo, :ip, :user_agent)'
        );
        $stmt->execute([
            'usuario_id' => $usuarioId,
            'email' => $email,
            'sucesso' => $success ? 1 : 0,
            'motivo' => $reason,
            'ip' => $ip,
            'user_agent' => $userAgent,
        ]);
    }

    public function registerFailedLogin(int $id): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE usuarios
             SET tentativas_login_falhas = tentativas_login_falhas + 1,
                 bloqueado_ate = CASE
                     WHEN tentativas_login_falhas + 1 >= 5 THEN DATE_ADD(NOW(), INTERVAL 15 MINUTE)
                     ELSE bloqueado_ate
                 END
             WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
    }

    public function resetLoginFailures(int $id): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE usuarios
             SET tentativas_login_falhas = 0,
                 bloqueado_ate = NULL,
                 ultimo_acesso_em = NOW()
             WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
    }

    public function enableTwoFactor(int $id, string $secret): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE usuarios
             SET two_factor_secret = :secret,
                 two_factor_enabled = 1,
                 two_factor_confirmed_at = NOW(),
                 two_factor_last_verified_at = NULL
             WHERE id = :id AND excluido_em IS NULL'
        );
        $stmt->execute(['secret' => $secret, 'id' => $id]);
    }

    public function markTwoFactorVerified(int $id): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE usuarios
             SET two_factor_last_verified_at = NOW(),
                 ultimo_acesso_em = NOW()
             WHERE id = :id AND excluido_em IS NULL'
        );
        $stmt->execute(['id' => $id]);
    }

    public function resetTwoFactor(int $id): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE usuarios
             SET two_factor_secret = NULL,
                 two_factor_enabled = 0,
                 two_factor_confirmed_at = NULL,
                 two_factor_last_verified_at = NULL
             WHERE id = :id AND excluido_em IS NULL'
        );
        $stmt->execute(['id' => $id]);
    }
}
