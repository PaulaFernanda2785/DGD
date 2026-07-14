<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Repositories\PasswordResetRepository;
use App\Repositories\UsuarioRepository;
use Throwable;

class PasswordRecoveryService
{
    private const EXPIRES_MINUTES = 60;
    private const THROTTLE_LIMIT = 3;
    private const THROTTLE_SECONDS = 900;

    public function __construct(
        private readonly UsuarioRepository $usuarios = new UsuarioRepository(),
        private readonly PasswordResetRepository $resets = new PasswordResetRepository(),
        private readonly EmailService $email = new EmailService()
    ) {
    }

    public function requestReset(string $email, ?string $ip, ?string $userAgent): array
    {
        $email = mb_strtolower(trim($email));

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->registerAttempt($email, $ip);
            $this->logRecoveryEvent('email_invalido', $email);
            return $this->genericResponse();
        }

        if ($this->tooManyAttempts($email, $ip)) {
            $this->logRecoveryEvent('limite_excedido', $email);
            return $this->genericResponse();
        }

        $this->registerAttempt($email, $ip);
        $usuario = $this->usuarios->findByEmail($email);

        if (!$usuario || (int) $usuario['ativo'] !== 1) {
            $this->logRecoveryEvent('usuario_inexistente_ou_inativo', $email);
            return $this->genericResponse();
        }

        $token = $this->generateToken();
        $this->resets->invalidatePendingByUser((int) $usuario['id']);
        $this->resets->create([
            'usuario_id' => (int) $usuario['id'],
            'token_hash' => $this->hashToken($token),
            'email_solicitado' => $email,
            'ip_solicitacao' => $ip,
            'user_agent' => $userAgent ? substr($userAgent, 0, 255) : null,
            'expira_minutos' => self::EXPIRES_MINUTES,
        ]);

        $resetUrl = rtrim((string) config('app.url', 'http://dgd.local'), '/') . '/recuperar-senha/' . $token;
        $emailSent = $this->sendRecoveryEmail((string) $usuario['email'], (string) $usuario['nome'], $resetUrl);
        $this->logRecoveryEvent($emailSent ? 'email_aceito_pelo_smtp' : 'falha_no_envio', $email);

        $response = $this->genericResponse();

        if ($emailSent) {
            $response['email_sent'] = true;
            return $response;
        }

        if ($this->isLocalDebugEnvironment()) {
            $this->logLocalLink($resetUrl);
            $response['local_link'] = true;
        }

        return $response;
    }

    public function validateToken(string $token): ?array
    {
        if (!preg_match('/^[A-Za-z0-9_-]{32,120}$/', $token)) {
            return null;
        }

        return $this->resets->findValidByTokenHash($this->hashToken($token), self::EXPIRES_MINUTES);
    }

    public function resetPassword(string $token, array $data): array
    {
        $reset = $this->validateToken($token);

        if (!$reset) {
            return ['success' => false, 'message' => 'Token invalido ou expirado. Solicite uma nova recuperacao.'];
        }

        $novaSenha = (string) ($data['nova_senha'] ?? '');
        $confirmacao = (string) ($data['confirmar_senha'] ?? '');

        if (strlen($novaSenha) < 10) {
            return ['success' => false, 'message' => 'A nova senha deve ter no minimo 10 caracteres.'];
        }

        if (!preg_match('/[A-Z]/', $novaSenha) || !preg_match('/[a-z]/', $novaSenha) || !preg_match('/\d/', $novaSenha)) {
            return ['success' => false, 'message' => 'Use letras maiusculas, minusculas e pelo menos um numero.'];
        }

        if ($novaSenha !== $confirmacao) {
            return ['success' => false, 'message' => 'A confirmacao da senha nao confere.'];
        }

        try {
            Database::beginTransaction();
            $this->usuarios->updatePassword((int) $reset['usuario_id'], password_hash($novaSenha, PASSWORD_DEFAULT));
            $this->resets->markUsed((int) $reset['id']);
            Database::commit();
        } catch (Throwable) {
            Database::rollBack();
            return ['success' => false, 'message' => 'Nao foi possivel redefinir a senha. Solicite uma nova recuperacao.'];
        }

        return ['success' => true, 'message' => 'Senha redefinida com sucesso. Entre com a nova senha.'];
    }

    private function genericResponse(): array
    {
        return [
            'success' => true,
            'message' => 'Se o e-mail estiver cadastrado e ativo, enviaremos as instrucoes de recuperacao.',
        ];
    }

    private function generateToken(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }

    private function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    private function logLocalLink(string $url): void
    {
        $line = '[' . date('Y-m-d H:i:s') . '] ' . $url . PHP_EOL;
        file_put_contents(STORAGE_PATH . '/logs/password_recovery_links.log', $line, FILE_APPEND | LOCK_EX);
    }

    private function sendRecoveryEmail(string $email, string $nome, string $resetUrl): bool
    {
        $safeName = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8');
        $safeUrl = htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8');

        $html = '<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><title>Recuperacao de senha</title></head>'
            . '<body style="margin:0;padding:0;background:#f4f7fb;color:#172331;font-family:Arial,Helvetica,sans-serif;">'
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f7fb;padding:24px;"><tr><td align="center">'
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border:1px solid #d8e2eb;border-radius:8px;"><tr><td style="padding:28px;">'
            . '<p style="margin:0 0 10px;color:#15735f;font-size:12px;font-weight:700;text-transform:uppercase;">Seguranca da conta</p>'
            . '<h1 style="margin:0 0 16px;font-size:26px;line-height:1.2;">Recuperacao de senha - DGD</h1>'
            . '<p style="margin:0 0 16px;color:#617084;">Ola, ' . $safeName . '. Recebemos uma solicitacao para redefinir sua senha no DGD.</p>'
            . '<p style="margin:0 0 24px;color:#617084;">Este link expira em ' . self::EXPIRES_MINUTES . ' minutos e pode ser usado apenas uma vez.</p>'
            . '<p style="margin:0 0 24px;"><a href="' . $safeUrl . '" style="display:inline-block;padding:12px 18px;border-radius:8px;background:#15735f;color:#ffffff;text-decoration:none;font-weight:700;">Redefinir senha</a></p>'
            . '<p style="margin:0 0 10px;color:#617084;font-size:14px;">Se o botao nao funcionar, acesse:</p>'
            . '<p style="margin:0;word-break:break-all;color:#15735f;font-size:14px;">' . $safeUrl . '</p>'
            . '<hr style="border:0;border-top:1px solid #d8e2eb;margin:24px 0;">'
            . '<p style="margin:0;color:#617084;font-size:13px;">Se voce nao solicitou essa recuperacao, ignore este e-mail.</p>'
            . '</td></tr></table></td></tr></table></body></html>';

        $text = implode(PHP_EOL, [
            'Recuperacao de senha - DGD',
            '',
            'Ola, ' . $nome . '.',
            'Recebemos uma solicitacao para redefinir sua senha no DGD.',
            'Link: ' . $resetUrl,
            '',
            'Este link expira em ' . self::EXPIRES_MINUTES . ' minutos e pode ser usado apenas uma vez.',
            'Se voce nao solicitou essa recuperacao, ignore este e-mail.',
        ]);

        try {
            return $this->email->send($email, 'Recuperacao de senha - DGD', $html, $text);
        } catch (Throwable $exception) {
            error_log('[PasswordRecoveryService] Falha no envio: ' . $exception::class);

            return false;
        }
    }

    private function tooManyAttempts(string $email, ?string $ip): bool
    {
        $entry = $this->throttleEntries()[$this->throttleKey($email, $ip)] ?? null;

        return is_array($entry)
            && (int) ($entry['count'] ?? 0) >= self::THROTTLE_LIMIT
            && (int) ($entry['expires_at'] ?? 0) > time();
    }

    private function registerAttempt(string $email, ?string $ip): void
    {
        $entries = $this->throttleEntries();
        $key = $this->throttleKey($email, $ip);
        $entry = $entries[$key] ?? ['count' => 0, 'expires_at' => 0];

        if ((int) ($entry['expires_at'] ?? 0) <= time()) {
            $entry = ['count' => 0, 'expires_at' => time() + self::THROTTLE_SECONDS];
        }

        $entry['count'] = (int) $entry['count'] + 1;
        $entries[$key] = $entry;

        file_put_contents($this->throttlePath(), json_encode($entries, JSON_UNESCAPED_UNICODE), LOCK_EX);
    }

    private function throttleEntries(): array
    {
        $path = $this->throttlePath();

        if (!is_file($path)) {
            return [];
        }

        $data = json_decode((string) file_get_contents($path), true);

        return is_array($data) ? $data : [];
    }

    private function throttleKey(string $email, ?string $ip): string
    {
        return hash('sha256', $email . '|' . (string) $ip);
    }

    private function throttlePath(): string
    {
        return STORAGE_PATH . '/cache/password_recovery_throttle.json';
    }

    private function isLocalDebugEnvironment(): bool
    {
        if ((string) config('app.env', 'local') !== 'local' || !(bool) config('app.debug', false)) {
            return false;
        }

        $host = strtolower((string) parse_url((string) config('app.url', ''), PHP_URL_HOST));

        return in_array($host, ['localhost', '127.0.0.1', '::1'], true) || str_ends_with($host, '.local');
    }

    private function logRecoveryEvent(string $status, string $email): void
    {
        $directory = STORAGE_PATH . DIRECTORY_SEPARATOR . 'logs';

        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            return;
        }

        $context = [
            'status' => $status,
            'email_hash' => substr(hash('sha256', mb_strtolower(trim($email))), 0, 16),
        ];
        $line = sprintf(
            "[%s] %s%s",
            date('Y-m-d H:i:s'),
            json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            PHP_EOL
        );

        file_put_contents($directory . DIRECTORY_SEPARATOR . 'password_recovery.log', $line, FILE_APPEND | LOCK_EX);
    }
}
