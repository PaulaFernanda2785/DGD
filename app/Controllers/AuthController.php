<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Services\AuditoriaService;
use App\Services\AuthService;

class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct(\App\Core\Request $request, \App\Core\Response $response)
    {
        parent::__construct($request, $response);
        $this->authService = new AuthService();
    }

    public function login(): void
    {
        $this->view('auth/login', [
            'title' => 'Entrar',
        ], 'public');
    }

    public function authenticate(): void
    {
        $email = (string) $this->request->post('email', '');
        $senha = (string) $this->request->post('senha', '');

        $result = $this->authService->attempt($email, $senha, $this->request->ip(), $this->request->userAgent());

        if (!$result['success']) {
            Session::flash('error', $result['message']);
            Session::flash('_old', ['email' => $email]);
            $this->redirect('/login');
        }

        if (!empty($result['redirect'])) {
            $this->redirect((string) $result['redirect']);
        }

        (new AuditoriaService())->registrar('auth', 'login', [], $this->request);
        Session::flash('success', 'Login realizado com sucesso.');

        $this->redirect('/painel');
    }

    public function logout(): void
    {
        (new AuditoriaService())->registrar('auth', 'logout', [], $this->request);
        $this->authService->logout();
        $this->redirect('/login');
    }
}
