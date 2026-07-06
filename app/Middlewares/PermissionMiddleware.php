<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\HttpException;
use App\Core\Permission;
use App\Core\Request;
use App\Core\Response;

class PermissionMiddleware
{
    public function __construct(private readonly string $permission)
    {
    }

    public function handle(Request $request, Response $response, array $params = []): void
    {
        if (Permission::can($this->permission)) {
            return;
        }

        if ($request->expectsJson()) {
            $response->json([
                'success' => false,
                'message' => 'Acesso nao autorizado para o seu perfil de usuario.',
            ], 403);
        }

        throw new HttpException(403, 'Acesso nao autorizado para o seu perfil de usuario.');
    }
}
