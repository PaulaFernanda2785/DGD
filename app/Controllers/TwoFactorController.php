<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Services\AuditoriaService;
use App\Services\TwoFactorAuthService;

class TwoFactorController extends Controller
{
    public function configure(): void
    {
        $service = new TwoFactorAuthService();
        $pending = $service->pendingUser();

        if ($pending === null) {
            Session::flash('error', 'A verificacao em duas etapas expirou. Informe e-mail e senha novamente.');
            $this->redirect('/login');
        }

        if (!$service->requiresSetup($pending)) {
            $this->redirect('/2fa/verificar');
        }

        $secret = $service->setupSecretForPendingUser((int) $pending['id']);

        $this->view('auth/2fa-configurar', [
            'title' => 'Verificacao em duas etapas',
            'pending' => $pending,
            'secret' => $secret,
            'secretDisplay' => $service->formatSecretForDisplay($secret),
            'provisioningUri' => $service->provisioningUri((string) config('app.name', 'DGD'), (string) $pending['email'], $secret),
            'ttlMinutes' => $service->ttlMinutes(),
            'errors' => Session::consumeFlash('errors', []),
            'extraHead' => '<script src="' . e(url('/assets/vendor/qrcode/qrcode.js')) . '"></script>',
        ], 'public');
    }

    public function saveConfiguration(): void
    {
        $service = new TwoFactorAuthService();
        $pending = $service->pendingUser();

        if ($pending === null) {
            Session::flash('error', 'A verificacao em duas etapas expirou. Informe e-mail e senha novamente.');
            $this->redirect('/login');
        }

        $secret = $service->setupSecretForPendingUser((int) $pending['id']);

        if (!$service->verifyCode($secret, (string) $this->request->post('codigo', ''))) {
            $attempts = $service->registerFailure();

            if ($attempts >= $service->maxFailures()) {
                $service->clearFlow();
                Session::flash('error', 'A verificacao em duas etapas foi bloqueada por tentativas invalidas. Entre novamente.');
                $this->redirect('/login');
            }

            Session::flash('errors', ['Codigo invalido. Digite o codigo atual do aplicativo autenticador.']);
            $this->redirect('/2fa/configurar');
        }

        $service->enableForPendingUser((int) $pending['id'], $secret);
        Session::flash('success', 'Verificacao em duas etapas configurada com sucesso. Entre novamente e informe o codigo do autenticador.');
        $this->redirect('/login');
    }

    public function verify(): void
    {
        $service = new TwoFactorAuthService();
        $pending = $service->pendingUser();

        if ($pending === null) {
            Session::flash('error', 'A verificacao em duas etapas expirou. Informe e-mail e senha novamente.');
            $this->redirect('/login');
        }

        if ($service->requiresSetup($pending)) {
            $this->redirect('/2fa/configurar');
        }

        $this->view('auth/2fa-verificar', [
            'title' => 'Codigo de autenticacao',
            'pending' => $pending,
            'ttlMinutes' => $service->ttlMinutes(),
            'errors' => Session::consumeFlash('errors', []),
        ], 'public');
    }

    public function validate(): void
    {
        $service = new TwoFactorAuthService();
        $pending = $service->pendingUser();

        if ($pending === null) {
            Session::flash('error', 'A verificacao em duas etapas expirou. Informe e-mail e senha novamente.');
            $this->redirect('/login');
        }

        if (!$service->verifyCode((string) ($pending['two_factor_secret'] ?? ''), (string) $this->request->post('codigo', ''))) {
            $attempts = $service->registerFailure();

            if ($attempts >= $service->maxFailures()) {
                $service->clearFlow();
                Session::flash('error', 'A verificacao em duas etapas foi bloqueada por tentativas invalidas. Entre novamente.');
                $this->redirect('/login');
            }

            Session::flash('errors', ['Codigo invalido. Digite o codigo atual do aplicativo autenticador.']);
            $this->redirect('/2fa/verificar');
        }

        $service->completeLogin($pending);
        (new AuditoriaService())->registrar('auth', 'login_2fa', [], $this->request);
        Session::flash('success', 'Login realizado com sucesso.');
        $this->redirect(!empty($pending['must_change_password']) ? '/alterar-senha' : '/painel');
    }

    public function cancel(): void
    {
        (new TwoFactorAuthService())->clearFlow();
        Session::flash('error', 'A verificacao em duas etapas foi cancelada. Entre novamente para continuar.');
        $this->redirect('/login');
    }
}
