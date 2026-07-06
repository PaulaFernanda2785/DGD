<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Session;
use App\Repositories\UsuarioRepository;

class TwoFactorAuthService
{
    private const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    private const PENDING_SESSION_KEY = 'two_factor_pending_user';
    private const SETUP_SECRET_SESSION_KEY = 'two_factor_setup_secret';
    private const SETUP_USER_SESSION_KEY = 'two_factor_setup_user_id';
    private const PENDING_TTL_SECONDS = 600;
    private const MAX_FAILURES = 5;
    private const PERIOD_SECONDS = 30;
    private const DIGITS = 6;

    public function __construct(private readonly UsuarioRepository $usuarios = new UsuarioRepository())
    {
    }

    public function startChallenge(array $usuario): string
    {
        Session::regenerate();
        Session::forget('usuario_id');

        $secret = $this->normalizeSecret((string) ($usuario['two_factor_secret'] ?? ''));
        $enabled = (int) ($usuario['two_factor_enabled'] ?? 0) === 1 && $secret !== '';

        Session::put(self::PENDING_SESSION_KEY, [
            'id' => (int) $usuario['id'],
            'nome' => (string) $usuario['nome'],
            'email' => (string) $usuario['email'],
            'perfil_codigo' => (string) $usuario['perfil_codigo'],
            'ativo' => (int) $usuario['ativo'],
            'must_change_password' => (int) ($usuario['trocar_senha_proximo_acesso'] ?? 0) === 1,
            'two_factor_enabled' => $enabled ? 1 : 0,
            'two_factor_secret' => $secret,
            'failed_attempts' => 0,
            'expires_at' => time() + self::PENDING_TTL_SECONDS,
        ]);

        Session::forget(self::SETUP_SECRET_SESSION_KEY);
        Session::forget(self::SETUP_USER_SESSION_KEY);

        return $enabled ? '/2fa/verificar' : '/2fa/configurar';
    }

    public function pendingUser(): ?array
    {
        $pending = Session::get(self::PENDING_SESSION_KEY);

        if (!is_array($pending) || (int) ($pending['expires_at'] ?? 0) <= time()) {
            $this->clearFlow();
            return null;
        }

        if ((int) ($pending['id'] ?? 0) <= 0 || trim((string) ($pending['email'] ?? '')) === '') {
            $this->clearFlow();
            return null;
        }

        return $pending;
    }

    public function requiresSetup(array $pending): bool
    {
        return (int) ($pending['two_factor_enabled'] ?? 0) !== 1
            || $this->normalizeSecret((string) ($pending['two_factor_secret'] ?? '')) === '';
    }

    public function setupSecretForPendingUser(int $usuarioId): string
    {
        $sessionUserId = (int) Session::get(self::SETUP_USER_SESSION_KEY, 0);
        $secret = $this->normalizeSecret((string) Session::get(self::SETUP_SECRET_SESSION_KEY, ''));

        if ($sessionUserId === $usuarioId && strlen($secret) >= 32) {
            return $secret;
        }

        $secret = $this->generateSecret();
        Session::put(self::SETUP_USER_SESSION_KEY, $usuarioId);
        Session::put(self::SETUP_SECRET_SESSION_KEY, $secret);

        return $secret;
    }

    public function enableForPendingUser(int $usuarioId, string $secret): void
    {
        $secret = $this->normalizeSecret($secret);
        $this->usuarios->enableTwoFactor($usuarioId, $secret);
        $this->clearFlow();
    }

    public function completeLogin(array $pending): void
    {
        Session::regenerate();
        Session::put('usuario_id', (int) $pending['id']);
        Session::put('usuario_nome', (string) $pending['nome']);
        Session::put('usuario_perfil', (string) $pending['perfil_codigo']);
        Session::put('usuario_ativo', (int) $pending['ativo']);
        Session::put('login_at', time());
        Session::put('must_change_password', !empty($pending['must_change_password']));

        $this->usuarios->markTwoFactorVerified((int) $pending['id']);
        $this->clearFlow();
    }

    public function clearFlow(): void
    {
        Session::forget(self::PENDING_SESSION_KEY);
        Session::forget(self::SETUP_SECRET_SESSION_KEY);
        Session::forget(self::SETUP_USER_SESSION_KEY);
    }

    public function ttlMinutes(): int
    {
        return (int) floor(self::PENDING_TTL_SECONDS / 60);
    }

    public function maxFailures(): int
    {
        return self::MAX_FAILURES;
    }

    public function registerFailure(): int
    {
        $pending = $this->pendingUser();

        if ($pending === null) {
            return self::MAX_FAILURES;
        }

        $attempts = (int) ($pending['failed_attempts'] ?? 0) + 1;
        $pending['failed_attempts'] = $attempts;
        Session::put(self::PENDING_SESSION_KEY, $pending);

        return $attempts;
    }

    public function verifyCode(string $secret, string $code, int $window = 1): bool
    {
        $secret = $this->normalizeSecret($secret);
        $code = preg_replace('/\D+/', '', $code) ?? '';

        if ($secret === '' || strlen($code) !== self::DIGITS) {
            return false;
        }

        $timeSlice = (int) floor(time() / self::PERIOD_SECONDS);

        for ($offset = -$window; $offset <= $window; $offset++) {
            if (hash_equals($this->totp($secret, $timeSlice + $offset), $code)) {
                return true;
            }
        }

        return false;
    }

    public function provisioningUri(string $issuer, string $accountName, string $secret): string
    {
        $issuer = $this->compactIssuer($issuer !== '' ? $issuer : 'DGD');
        $label = rawurlencode($issuer) . ':' . rawurlencode(trim($accountName));

        return 'otpauth://totp/' . $label
            . '?secret=' . rawurlencode($this->normalizeSecret($secret))
            . '&issuer=' . rawurlencode($issuer);
    }

    public function formatSecretForDisplay(string $secret): string
    {
        return trim(chunk_split($this->normalizeSecret($secret), 4, ' '));
    }

    public function generateSecret(int $length = 32): string
    {
        $bytes = random_bytes($length);
        $bits = '';

        foreach (str_split($bytes) as $byte) {
            $bits .= str_pad(decbin(ord($byte)), 8, '0', STR_PAD_LEFT);
        }

        $secret = '';

        foreach (str_split($bits, 5) as $chunk) {
            if (strlen($chunk) < 5) {
                $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
            }

            $secret .= self::BASE32_ALPHABET[bindec($chunk)];
        }

        return substr($secret, 0, max(16, min(64, $length)));
    }

    private function totp(string $secret, int $timeSlice): string
    {
        $key = $this->base32Decode($secret);
        $counter = max(0, $timeSlice);
        $binaryCounter = pack('N*', intdiv($counter, 0x100000000), $counter & 0xFFFFFFFF);
        $hash = hash_hmac('sha1', $binaryCounter, $key, true);
        $offset = ord(substr($hash, -1)) & 0x0F;
        $value = (
            ((ord($hash[$offset]) & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8) |
            (ord($hash[$offset + 3]) & 0xFF)
        );

        return str_pad((string) ($value % (10 ** self::DIGITS)), self::DIGITS, '0', STR_PAD_LEFT);
    }

    private function base32Decode(string $secret): string
    {
        $bits = '';

        foreach (str_split($this->normalizeSecret($secret)) as $char) {
            $index = strpos(self::BASE32_ALPHABET, $char);

            if ($index !== false) {
                $bits .= str_pad(decbin($index), 5, '0', STR_PAD_LEFT);
            }
        }

        $binary = '';

        foreach (str_split($bits, 8) as $byte) {
            if (strlen($byte) === 8) {
                $binary .= chr(bindec($byte));
            }
        }

        return $binary;
    }

    private function normalizeSecret(string $secret): string
    {
        return preg_replace('/[^A-Z2-7]/', '', strtoupper($secret)) ?? '';
    }

    private function compactIssuer(string $issuer): string
    {
        $normalized = function_exists('iconv')
            ? iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', trim($issuer))
            : trim($issuer);

        $normalized = preg_replace('/[^A-Za-z0-9]+/', '', (string) $normalized) ?? '';

        return $normalized !== '' && strlen($normalized) <= 24 ? $normalized : 'DGD';
    }
}
