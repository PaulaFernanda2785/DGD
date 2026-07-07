<?php

declare(strict_types=1);

namespace App\Core;

use Throwable;

class App
{
    private Router $router;
    private Request $request;
    private Response $response;

    public function __construct(private readonly array $config)
    {
        $this->configureErrors();

        $this->request = new Request();
        $this->response = new Response();
        $this->router = new Router($this->request, $this->response);

        Session::start($this->config['session']);
        $this->loadRoutes();
    }

    public function run(): void
    {
        try {
            $this->router->dispatch();
        } catch (HttpException $exception) {
            $this->response->setStatusCode($exception->statusCode());

            if ($this->request->expectsJson()) {
                $this->response->json([
                    'success' => false,
                    'message' => $exception->getMessage(),
                ], $exception->statusCode());
            }

            View::renderError($exception->statusCode(), $exception->getMessage());
        } catch (Throwable $exception) {
            Logger::error($exception);
            $this->response->setStatusCode(500);

            $message = $this->config['debug']
                ? $exception->getMessage()
                : 'Nao foi possivel processar a solicitacao.';

            if ($this->request->expectsJson()) {
                $this->response->json([
                    'success' => false,
                    'message' => $message,
                ], 500);
            }

            View::renderError(500, $message);
        }
    }

    private function loadRoutes(): void
    {
        $routes = require CONFIG_PATH . DIRECTORY_SEPARATOR . 'routes.php';

        foreach ($routes as $route) {
            [$method, $path, $handler, $middlewares] = $route + [null, null, null, []];
            $this->router->add($method, $path, $handler, $middlewares);
        }
    }

    private function configureErrors(): void
    {
        error_reporting(E_ALL);

        if (($this->config['debug'] ?? false) === true) {
            ini_set('display_errors', '1');
            return;
        }

        ini_set('display_errors', '0');
    }
}
