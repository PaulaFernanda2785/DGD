<?php
$email = (string) ($pending['email'] ?? '');
$googleAuthenticatorAndroidUrl = 'https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2';
$googleAuthenticatorIosUrl = 'https://apps.apple.com/app/google-authenticator/id388497605';
?>

<section class="auth-recovery-shell two-factor-shell">
    <div class="auth-recovery-intro two-factor-intro">
        <div class="auth-hero-brand">
            <img class="auth-logo-mark" src="<?= e(url('/assets/img/logo-cedec.png')); ?>" alt="">
            <span>Corpo de Bombeiros Militar do Pará e<br>Coordenadoria Estadual de Proteção e Defesa Civil</span>
        </div>

        <div>
            <p class="eyebrow">Credenciamento obrigatório</p>
            <h1>Ative a verificação em duas etapas</h1>
            <p>O acesso ao DGD exige senha e código temporário do aplicativo autenticador. Esta etapa protege sua conta institucional.</p>
        </div>

        <div class="auth-recovery-steps">
            <span>1. Instale o Google Authenticator ou app compatível</span>
            <span>2. Escaneie o QR Code de ativação</span>
            <span>3. Confirme o código de 6 dígitos</span>
        </div>
    </div>

    <div class="auth-panel auth-recovery-card two-factor-card">
        <div class="auth-card-header">
            <img class="auth-logo-mark auth-logo-card" src="<?= e(url('/assets/img/logo-cedec.png')); ?>" alt="CEDEC-PA">
            <div>
                <p class="eyebrow">Acesso protegido</p>
                <h1>Configurar 2FA</h1>
            </div>
        </div>

        <p class="two-factor-account">Conta: <strong><?= e($email); ?></strong></p>

        <?php require view_path('components/flash'); ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error" role="alert">
                <?php foreach ($errors as $error): ?>
                    <p><?= e($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="two-factor-downloads" aria-label="Aplicativos autenticadores recomendados">
            <a href="<?= e($googleAuthenticatorAndroidUrl); ?>" target="_blank" rel="noopener noreferrer" data-app-download data-download-title="Google Authenticator para Android" data-download-label="Google Play">
                <span>Android</span>
                <strong>Google Play</strong>
            </a>
            <a href="<?= e($googleAuthenticatorIosUrl); ?>" target="_blank" rel="noopener noreferrer" data-app-download data-download-title="Google Authenticator para iPhone" data-download-label="App Store">
                <span>iPhone</span>
                <strong>App Store</strong>
            </a>
        </div>

        <div class="two-factor-qr-panel">
            <div>
                <strong>QR Code de ativação</strong>
                <p>Abra o aplicativo autenticador e escaneie este código.</p>
                <button class="two-factor-inline-button" type="button" data-activation-qr-open>Abrir QR Code ampliado</button>
            </div>
            <div class="two-factor-qr" data-twofactor-qr data-qr-text="<?= e($provisioningUri); ?>">Gerando QR Code...</div>
        </div>

        <div class="two-factor-manual-key">
            <span>Chave manual</span>
            <code><?= e($secretDisplay); ?></code>
            <button type="button" data-copy-secret="<?= e($secret); ?>">Copiar chave</button>
        </div>

        <form class="form-panel auth-form" method="post" action="<?= e(url('/2fa/configurar')); ?>" autocomplete="off" aria-label="Confirmar credenciamento em duas etapas">
            <?= csrf_input(); ?>
            <label>
                <span>Código de 6 dígitos</span>
                <input type="text" name="codigo" inputmode="numeric" pattern="[0-9]{6}" minlength="6" maxlength="6" autocomplete="one-time-code" placeholder="000000" required autofocus>
            </label>
            <button type="submit">Confirmar credenciamento</button>
        </form>

        <footer class="auth-card-footer auth-login-footer">
            <strong>Validade temporária</strong>
            <span>Se esta etapa expirar, informe e-mail e senha novamente.</span>
            <a href="<?= e(url('/2fa/cancelar')); ?>">Cancelar e voltar ao login</a>
        </footer>
    </div>
</section>

<div class="two-factor-modal" data-app-download-modal hidden aria-hidden="true">
    <div class="two-factor-modal-backdrop" data-app-download-close></div>
    <section class="two-factor-modal-card" role="dialog" aria-modal="true" aria-labelledby="titulo-modal-app-auth">
        <button class="two-factor-modal-close" type="button" data-app-download-close aria-label="Fechar modal">x</button>
        <div class="two-factor-modal-heading">
            <span data-app-download-label>Aplicativo autenticador</span>
            <h2 id="titulo-modal-app-auth" data-app-download-title>Baixar Google Authenticator</h2>
            <p>Escaneie este QR Code com a câmera do celular para abrir a loja do aplicativo.</p>
        </div>
        <div class="two-factor-modal-qr" data-app-download-qr>Gerando QR Code...</div>
        <a class="two-factor-modal-link" href="#" target="_blank" rel="noopener noreferrer" data-app-download-link>Abrir loja neste dispositivo</a>
    </section>
</div>

<div class="two-factor-modal" data-activation-qr-modal hidden aria-hidden="true">
    <div class="two-factor-modal-backdrop" data-activation-qr-close></div>
    <section class="two-factor-modal-card two-factor-modal-card-wide" role="dialog" aria-modal="true" aria-labelledby="titulo-modal-qr-ativacao">
        <button class="two-factor-modal-close" type="button" data-activation-qr-close aria-label="Fechar modal">x</button>
        <div class="two-factor-modal-heading">
            <span>QR Code de ativação</span>
            <h2 id="titulo-modal-qr-ativacao">Escaneie pelo aplicativo autenticador</h2>
            <p>Mantenha o QR Code inteiro dentro do leitor do Google Authenticator ou aplicativo compatível.</p>
        </div>
        <div class="two-factor-modal-qr is-large" data-activation-qr-target data-qr-text="<?= e($provisioningUri); ?>">Gerando QR Code...</div>
    </section>
</div>
