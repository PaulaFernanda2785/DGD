<?php

declare(strict_types=1);

namespace App\Core;

class Auth
{
    public static function check(): bool
    {
        return self::id() !== null && Session::get('usuario_ativo') === 1;
    }

    public static function id(): ?int
    {
        $id = Session::get('usuario_id');

        return $id === null ? null : (int) $id;
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        return [
            'id' => self::id(),
            'nome' => Session::get('usuario_nome'),
            'perfil_codigo' => Session::get('usuario_perfil'),
            'ativo' => Session::get('usuario_ativo'),
        ];
    }

    public static function perfil(): ?string
    {
        return Session::get('usuario_perfil');
    }

    public static function mustChangePassword(): bool
    {
        return self::check() && Session::get('must_change_password') === true;
    }
}
