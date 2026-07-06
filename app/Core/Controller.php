<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    public function __construct(
        protected Request $request,
        protected Response $response
    ) {
    }

    protected function view(string $view, array $data = [], string $layout = 'app'): void
    {
        View::render($view, $data, $layout);
    }

    protected function json(array $data, int $statusCode = 200): void
    {
        $this->response->json($data, $statusCode);
    }

    protected function redirect(string $path): void
    {
        $this->response->redirect($path);
    }

    protected function requirePermission(string $permission): void
    {
        if (!Permission::can($permission)) {
            throw new HttpException(403, 'Acesso nao autorizado para o seu perfil de usuario.');
        }
    }
}
