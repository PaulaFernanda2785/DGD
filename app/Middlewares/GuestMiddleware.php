<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;

class GuestMiddleware
{
    public function handle(Request $request, Response $response, array $params = []): void
    {
        if (!Auth::check()) {
            return;
        }

        $response->redirect(Auth::mustChangePassword() ? '/alterar-senha' : '/painel');
    }
}
