<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;
use Throwable;

class EmailService
{
    private array $config;

    public function __construct(?array $config = null)
    {
        $this->config = $config ?? config('mail');
    }

    public function isConfigured(): bool
    {
        $smtp = $this->config['smtp'] ?? [];

        if (strtolower(trim((string) ($this->config['mailer'] ?? 'log'))) !== 'smtp') {
            return false;
        }

        if (trim((string) ($smtp['host'] ?? '')) === '' || (int) ($smtp['port'] ?? 0) <= 0) {
            return false;
        }

        if (
            (bool) ($smtp['auth'] ?? true)
            && (
                trim((string) ($smtp['username'] ?? '')) === ''
                || (string) ($smtp['password'] ?? '') === ''
            )
        ) {
            return false;
        }

        return filter_var($this->fromAddress(), FILTER_VALIDATE_EMAIL) !== false;
    }

    public function send(string $to, string $subject, string $html, string $text): bool
    {
        if (!$this->isConfigured()) {
            $this->logDelivery('configuracao_invalida');
            return false;
        }

        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->logDelivery('destinatario_invalido');
            return false;
        }

        try {
            $this->sendSmtp($to, $subject, $html, $text);
            $this->logDelivery('aceito_pelo_smtp');
            return true;
        } catch (Throwable $exception) {
            error_log('[EmailService] Falha SMTP: ' . $exception->getMessage());
            $this->logDelivery('falha_smtp', $exception);
            return false;
        }
    }

    private function sendSmtp(string $to, string $subject, string $html, string $text): void
    {
        $smtp = $this->config['smtp'];
        $from = $this->config['from'];
        $host = trim((string) $smtp['host']);
        $port = (int) $smtp['port'];
        $timeout = max(5, (int) ($smtp['timeout'] ?? 15));
        $encryption = strtolower(trim((string) ($smtp['encryption'] ?? 'ssl')));

        if (!in_array($encryption, ['ssl', 'tls', 'none', ''], true)) {
            throw new RuntimeException('Tipo de criptografia SMTP nao suportado.');
        }

        $transport = $encryption === 'ssl' ? 'ssl://' . $host : $host;
        $sslOptions = [
            'verify_peer' => (bool) ($smtp['verify_peer'] ?? true),
            'verify_peer_name' => (bool) ($smtp['verify_peer'] ?? true),
            'peer_name' => $host,
            'SNI_enabled' => true,
            'allow_self_signed' => false,
        ];

        $caFile = $this->caFile((string) ($smtp['ca_file'] ?? ''));

        if ($caFile !== null) {
            $sslOptions['cafile'] = $caFile;
        }

        $context = stream_context_create(['ssl' => $sslOptions]);

        $socket = @stream_socket_client($transport . ':' . $port, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $context);

        if (!is_resource($socket)) {
            throw new RuntimeException('Nao foi possivel conectar ao servidor SMTP: ' . $errstr);
        }

        stream_set_timeout($socket, $timeout);

        try {
            $this->expect($socket, [220]);
            $this->command($socket, 'EHLO ' . $this->hostname(), [250]);

            if ($encryption === 'tls') {
                $this->command($socket, 'STARTTLS', [220]);

                if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new RuntimeException('Nao foi possivel iniciar STARTTLS.');
                }

                $this->command($socket, 'EHLO ' . $this->hostname(), [250]);
            }

            if ((bool) ($smtp['auth'] ?? true)) {
                $this->command($socket, 'AUTH LOGIN', [334]);
                $this->command($socket, base64_encode((string) $smtp['username']), [334], false);
                $this->command($socket, base64_encode((string) $smtp['password']), [235], false);
            }

            $fromAddress = $this->fromAddress();
            $this->command($socket, 'MAIL FROM:<' . $fromAddress . '>', [250]);
            $this->command($socket, 'RCPT TO:<' . $to . '>', [250, 251]);
            $this->command($socket, 'DATA', [354]);
            $this->writeRaw($socket, $this->dotStuff($this->message($to, $subject, $html, $text)) . "\r\n.\r\n");
            $this->expect($socket, [250]);
            $this->command($socket, 'QUIT', [221]);
        } finally {
            fclose($socket);
        }
    }

    private function message(string $to, string $subject, string $html, string $text): string
    {
        $from = $this->config['from'];
        $boundary = 'DGD-' . bin2hex(random_bytes(12));
        $fromHeader = $this->formatAddress($this->fromAddress(), (string) ($from['name'] ?? 'DGD - CEDEC-PA'));
        $headers = [
            'Date: ' . date(DATE_RFC2822),
            'From: ' . $fromHeader,
            'To: <' . $to . '>',
            'Subject: ' . $this->encodeHeader($subject),
            'MIME-Version: 1.0',
            'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
        ];

        $body = [
            '--' . $boundary,
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
            '',
            $text,
            '--' . $boundary,
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
            '',
            $html,
            '--' . $boundary . '--',
        ];

        return implode("\r\n", $headers) . "\r\n\r\n" . implode("\r\n", $body);
    }

    private function command($socket, string $command, array $expectedCodes, bool $sensitive = true): string
    {
        $this->write($socket, $command);

        try {
            return $this->expect($socket, $expectedCodes);
        } catch (RuntimeException $exception) {
            throw new RuntimeException($sensitive ? $exception->getMessage() : 'Falha durante autenticacao SMTP.');
        }
    }

    private function write($socket, string $line): void
    {
        $this->writeRaw($socket, $line . "\r\n");
    }

    private function writeRaw($socket, string $content): void
    {
        $length = strlen($content);
        $written = 0;

        while ($written < $length) {
            $result = fwrite($socket, substr($content, $written));

            if ($result === false || $result === 0) {
                throw new RuntimeException('Nao foi possivel escrever no servidor SMTP.');
            }

            $written += $result;
        }
    }

    private function expect($socket, array $expectedCodes): string
    {
        $response = '';

        while (true) {
            $line = fgets($socket, 512);

            if ($line === false) {
                $metadata = stream_get_meta_data($socket);

                throw new RuntimeException(!empty($metadata['timed_out'])
                    ? 'Tempo limite excedido ao aguardar o servidor SMTP.'
                    : 'Servidor SMTP nao respondeu.');
            }

            $response .= $line;

            if (isset($line[3]) && $line[3] === '-') {
                continue;
            }

            $code = preg_match('/^(\d{3})\s/', $line, $matches) ? (int) $matches[1] : 0;

            if (!in_array($code, $expectedCodes, true)) {
                throw new RuntimeException('Servidor SMTP retornou o codigo ' . $code . '.');
            }

            return $response;
        }
    }

    private function formatAddress(string $email, string $name): string
    {
        $name = trim($name);

        if ($name === '') {
            return '<' . $email . '>';
        }

        return $this->encodeHeader($name) . ' <' . $email . '>';
    }

    private function encodeHeader(string $value): string
    {
        return '=?UTF-8?B?' . base64_encode($value) . '?=';
    }

    private function dotStuff(string $body): string
    {
        $body = str_replace(["\r\n", "\r"], "\n", $body);
        $body = preg_replace('/^\./m', '..', $body) ?? $body;

        return str_replace("\n", "\r\n", $body);
    }

    private function hostname(): string
    {
        $host = parse_url((string) config('app.url', ''), PHP_URL_HOST);

        return is_string($host) && $host !== '' ? $host : 'localhost';
    }

    private function caFile(string $path): ?string
    {
        $path = trim($path);

        return $path !== '' && is_file($path) ? $path : null;
    }

    private function fromAddress(): string
    {
        $fromAddress = trim((string) ($this->config['from']['address'] ?? ''));

        if ($fromAddress !== '') {
            return $fromAddress;
        }

        return trim((string) ($this->config['smtp']['username'] ?? ''));
    }

    private function logDelivery(string $status, ?Throwable $exception = null): void
    {
        $directory = STORAGE_PATH . DIRECTORY_SEPARATOR . 'logs';

        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            return;
        }

        $context = [
            'status' => $status,
            'transport' => strtolower(trim((string) ($this->config['mailer'] ?? 'log'))),
            'host_configurado' => trim((string) ($this->config['smtp']['host'] ?? '')) !== '',
            'porta' => (int) ($this->config['smtp']['port'] ?? 0),
            'criptografia' => strtolower(trim((string) ($this->config['smtp']['encryption'] ?? ''))),
            'verificacao_tls' => (bool) ($this->config['smtp']['verify_peer'] ?? true),
        ];

        if ($exception !== null) {
            $context['erro'] = $exception::class;
            $context['mensagem'] = $this->sanitizeLogMessage($exception->getMessage());
        }

        $line = sprintf(
            "[%s] %s%s",
            date('Y-m-d H:i:s'),
            json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            PHP_EOL
        );

        file_put_contents($directory . DIRECTORY_SEPARATOR . 'mail.log', $line, FILE_APPEND | LOCK_EX);
    }

    private function sanitizeLogMessage(string $message): string
    {
        $message = preg_replace('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', '[email]', $message) ?? $message;

        return substr(str_replace(["\r", "\n"], ' ', $message), 0, 240);
    }
}
