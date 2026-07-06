<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Services\PasswordRecoveryService;

class PasswordRecoveryController extends Controller
{
    public function forgot(): void
    {
        $this->view('auth/esqueci-senha', [
            'title' => 'Esqueci minha senha',
            'email' => '',
            'errors' => Session::consumeFlash('errors', []),
        ], 'public');
    }

    public function request(): void
    {
        $email = (string) $this->request->post('email', '');
        $result = (new PasswordRecoveryService())->requestReset($email, $this->request->ip(), $this->request->userAgent());

        Session::flash('success', $result['message']);
        Session::flash('_old', ['email' => $email]);

        if (!empty($result['local_link'])) {
            Session::flash('success', $result['message'] . ' Como o envio SMTP esta indisponivel neste ambiente, o link local foi registrado em storage/logs/password_recovery_links.log.');
        }

        $this->redirect('/esqueci-senha');
    }

    public function resetForm(string $token): void
    {
        if ((new PasswordRecoveryService())->validateToken($token) === null) {
            Session::flash('error', 'Token invalido ou expirado. Solicite uma nova recuperacao.');
            $this->redirect('/esqueci-senha');
        }

        $this->view('auth/redefinir-senha', [
            'title' => 'Redefinir senha',
            'token' => $token,
            'errors' => Session::consumeFlash('errors', []),
        ], 'public');
    }

    public function reset(string $token): void
    {
        $result = (new PasswordRecoveryService())->resetPassword($token, $this->request->post());

        if (!$result['success']) {
            Session::flash('errors', [$result['message']]);
            $this->redirect('/recuperar-senha/' . $token);
        }

        Session::flash('success', $result['message']);
        $this->redirect('/login');
    }
}
