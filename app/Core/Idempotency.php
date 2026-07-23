<?php
declare(strict_types=1);
namespace App\Core;
class Idempotency
{
    private const SESSION_KEY = '_idempotency_tokens';
    private const WINDOW_SECONDS = 5;
    public static function input(): string { return '<input type="hidden" name="_idempotency_token" value="' . e(bin2hex(random_bytes(24))) . '">'; }
    public static function reserve(?string $token): bool
    {
        if (!is_string($token) || !preg_match('/^[a-f0-9]{48}$/', $token)) { return true; }
        $now = time(); $tokens = Session::get(self::SESSION_KEY, []); $tokens = is_array($tokens) ? array_filter($tokens, static fn($time) => $now - (int) $time < self::WINDOW_SECONDS) : [];
        if (isset($tokens[$token])) { Session::put(self::SESSION_KEY, $tokens); return false; }
        $tokens[$token] = $now; Session::put(self::SESSION_KEY, $tokens); return true;
    }
}
