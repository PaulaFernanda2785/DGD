<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Auth;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;

class AuthMiddleware
{
    public function handle(Request $request, Response $response, array $params = []): void
    {
        if (Auth::check()) {
            if (Auth::mustChangePassword() && !in_array($request->path(), ['/alterar-senha', '/logout'], true)) {
                if ($request->expectsJson()) {
                    $response->json([
                        'success' => false,
                        'message' => 'Troca de senha obrigatoria antes de acessar o sistema.',
                    ], 403);
                }

                $response->redirect('/alterar-senha');
            }

            return;
        }

        if ($request->expectsJson()) {
            $response->json([
                'success' => false,
                'message' => 'Autenticacao obrigatoria.',
            ], 401);
        }

        if ($request->method() === 'GET') {
            $response->redirect('/login');
        }

        throw new HttpException(401, 'Autenticacao obrigatoria.');
    }
}
