<?php

declare(strict_types=1);

namespace App\Core;

class Permission
{
    public static function can(string $permission): bool
    {
        $perfil = Auth::perfil();

        if ($perfil === null) {
            return false;
        }

        $permissions = config('permissions.' . $perfil, []);

        return in_array($permission, $permissions, true);
    }
}
