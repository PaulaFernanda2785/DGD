<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Csrf;
use App\Core\HttpException;
use App\Core\Idempotency;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class CsrfMiddleware
{
    public function handle(Request $request, Response $response, array $params = []): void
    {
        if ($request->method() !== 'POST') {
            return;
        }

        $token = $request->post('_csrf_token');

        if (Csrf::validate(is_string($token) ? $token : null)) {
            if (Idempotency::reserve($request->post('_idempotency_token'))) { return; }
            if ($request->expectsJson()) { $response->json(['success' => false, 'message' => 'Esta ação já está sendo processada. Aguarde alguns segundos antes de tentar novamente.'], 409); }
            Session::flash('warning', 'Esta ação já foi recebida. Para evitar duplicidade, nenhum novo registro foi criado.');
            throw new HttpException(409, 'Esta ação já foi recebida. Atualize a página antes de tentar novamente.');
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
