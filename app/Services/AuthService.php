<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Session;
use App\Repositories\UsuarioRepository;
use DateTimeImmutable;

class AuthService
{
    private UsuarioRepository $usuarios;

    public function __construct()
    {
        $this->usuarios = new UsuarioRepository();
    }

    public function attempt(string $email, string $password, ?string $ip, ?string $userAgent): array
    {
        $email = mb_strtolower(trim($email));
        $usuario = $this->usuarios->findByEmail($email);

        if (!$usuario) {
            $this->usuarios->logLogin(null, $email, false, 'usuario_nao_encontrado', $ip, $userAgent);
            return ['success' => false, 'message' => 'Usuario ou senha invalidos.'];
        }

        if ((int) $usuario['ativo'] !== 1) {
            $this->usuarios->logLogin((int) $usuario['id'], $email, false, 'usuario_inativo', $ip, $userAgent);
            return ['success' => false, 'message' => 'Usuario sem permissao de acesso. Procure o administrador do sistema.'];
        }

        if ($this->isBlocked($usuario['bloqueado_ate'] ?? null)) {
            $this->usuarios->logLogin((int) $usuario['id'], $email, false, 'usuario_bloqueado', $ip, $userAgent);
            return ['success' => false, 'message' => 'Usuario bloqueado temporariamente. Tente novamente mais tarde.'];
        }

        if (!password_verify($password, $usuario['senha_hash'])) {
            $this->usuarios->registerFailedLogin((int) $usuario['id']);
            $this->usuarios->logLogin((int) $usuario['id'], $email, false, 'senha_incorreta', $ip, $userAgent);
            return ['success' => false, 'message' => 'Usuario ou senha invalidos.'];
        }

        $this->usuarios->resetLoginFailures((int) $usuario['id']);
        $this->usuarios->logLogin((int) $usuario['id'], $email, true, null, $ip, $userAgent);

        return [
            'success' => true,
            'redirect' => (new TwoFactorAuthService())->startChallenge($usuario),
        ];
    }

    public function logout(): void
    {
        Session::destroy();
    }

    private function isBlocked(?string $blockedUntil): bool
    {
        if (!$blockedUntil) {
            return false;
        }

        return new DateTimeImmutable($blockedUntil) > new DateTimeImmutable();
    }
}
