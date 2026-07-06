<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

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
        $from = $this->config['from'] ?? [];

        if (($this->config['mailer'] ?? 'log') !== 'smtp') {
            return false;
        }

        if (trim((string) ($smtp['host'] ?? '')) === '' || (int) ($smtp['port'] ?? 0) <= 0) {
            return false;
        }

        if ((bool) ($smtp['auth'] ?? true) && trim((string) ($smtp['username'] ?? '')) === '') {
            return false;
        }

        return filter_var($from['address'] ?? '', FILTER_VALIDATE_EMAIL) !== false;
    }

    public function send(string $to, string $subject, string $html, string $text): bool
    {
        if (!$this->isConfigured() || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        try {
            $this->sendSmtp($to, $subject, $html, $text);
            return true;
        } catch (RuntimeException $exception) {
            error_log('[EmailService] Falha SMTP: ' . $exception->getMessage());
            return false;
        }
    }

    private function sendSmtp(string $to, string $subject, string $html, string $text): void
    {
        $smtp = $this->config['smtp'];
        $from = $this->config['from'];
        $host = (string) $smtp['host'];
        $port = (int) $smtp['port'];
        $timeout = max(5, (int) ($smtp['timeout'] ?? 15));
        $encryption = strtolower((string) ($smtp['encryption'] ?? 'tls'));
        $transport = $encryption === 'ssl' ? 'ssl://' . $host : $host;
        $sslOptions = [
            'verify_peer' => (bool) ($smtp['verify_peer'] ?? true),
            'verify_peer_name' => (bool) ($smtp['verify_peer'] ?? true),
            'peer_name' => $host,
            'SNI_enabled' => true,
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

            $fromAddress = (string) $from['address'];
            $this->command($socket, 'MAIL FROM:<' . $fromAddress . '>', [250]);
            $this->command($socket, 'RCPT TO:<' . $to . '>', [250, 251]);
            $this->command($socket, 'DATA', [354]);
            $this->write($socket, $this->message($to, $subject, $html, $text) . "\r\n.");
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
        $fromHeader = $this->formatAddress((string) $from['address'], (string) $from['name']);
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
            $this->dotStuff($text),
            '--' . $boundary,
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
            '',
            $this->dotStuff($html),
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
        fwrite($socket, $line . "\r\n");
    }

    private function expect($socket, array $expectedCodes): string
    {
        $response = '';

        while (($line = fgets($socket, 515)) !== false) {
            $response .= $line;

            if (preg_match('/^(\d{3})\s/', $line, $matches)) {
                $code = (int) $matches[1];

                if (!in_array($code, $expectedCodes, true)) {
                    throw new RuntimeException('Resposta SMTP inesperada: ' . trim($line));
                }

                return $response;
            }
        }

        throw new RuntimeException('Servidor SMTP nao respondeu.');
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
}
