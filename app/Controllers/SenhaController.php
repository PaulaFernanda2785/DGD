<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Repositories\UsuarioRepository;
use App\Services\TwoFactorAuthService;
use App\Services\UsuarioService;

class SenhaController extends Controller
{
    private UsuarioService $usuarioService;

    public function __construct(\App\Core\Request $request, \App\Core\Response $response)
    {
        parent::__construct($request, $response);
        $this->usuarioService = new UsuarioService();
    }

    public function edit(): void
    {
        $this->view('senha/edit', [
            'title' => 'Alterar senha',
            'errors' => Session::consumeFlash('errors', []),
        ]);
    }

    public function update(): void
    {
        $result = $this->usuarioService->alterarSenhaPropria(Auth::id() ?? 0, $this->request->post());

        if (!$result['success']) {
            Session::flash('errors', $result['errors']);
            $this->redirect('/alterar-senha');
        }

        Session::flash('success', 'Senha alterada com sucesso.');
        Session::forget('must_change_password');

        $usuario = (new UsuarioRepository())->findById(Auth::id() ?? 0);

        if ($usuario && (int) ($usuario['two_factor_enabled'] ?? 0) !== 1) {
            (new TwoFactorAuthService())->startChallenge($usuario);
            $this->redirect('/2fa/configurar');
        }

        $this->redirect('/painel');
    }
}
