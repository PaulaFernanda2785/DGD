<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Csrf;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;

class CsrfMiddleware
{
    public function handle(Request $request, Response $response, array $params = []): void
    {
        if ($request->method() !== 'POST') {
            return;
        }

        $token = $request->post('_csrf_token');

        if (Csrf::validate(is_string($token) ? $token : null)) {
            return;
        }

        if ($request->expectsJson()) {
            $response->json([
                'success' => false,
                'message' => 'Token de seguranca invalido. Atualize a pagina e tente novamente.',
            ], 419);
        }

        throw new HttpException(419, 'Token de seguranca invalido. Atualize a pagina e tente novamente.');
    }
}
