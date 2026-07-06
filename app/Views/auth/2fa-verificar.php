<?php $email = (string) ($pending['email'] ?? ''); ?>

<section class="auth-recovery-shell two-factor-shell two-factor-verify-shell">
    <div class="auth-recovery-intro two-factor-intro">
        <div class="auth-hero-brand">
            <img class="auth-logo-mark" src="<?= e(url('/assets/img/logo-cedec.png')); ?>" alt="">
            <span>Corpo de Bombeiros Militar do Pará e<br>Coordenadoria Estadual de Proteção e Defesa Civil</span>
        </div>

        <div>
            <p class="eyebrow">Segunda etapa</p>
            <h1>Informe o código do autenticador</h1>
            <p>Abra o aplicativo autenticador no celular e digite o código temporário vinculado à sua conta institucional.</p>
        </div>

        <div class="auth-recovery-steps">
            <span>O código muda a cada poucos segundos</span>
            <span>Confira se o horário do celular está automático</span>
            <span>A etapa expira em <?= e($ttlMinutes); ?> minutos</span>
        </div>
    </div>

    <div class="auth-panel auth-recovery-card two-factor-card two-factor-verify-card">
        <div class="auth-card-header">
            <img class="auth-logo-mark auth-logo-card" src="<?= e(url('/assets/img/logo-cedec.png')); ?>" alt="CEDEC-PA">
            <div>
                <p class="eyebrow">Acesso protegido</p>
                <h1>Validar código</h1>
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

        <form class="form-panel auth-form two-factor-code-form" method="post" action="<?= e(url('/2fa/verificar')); ?>" autocomplete="off" aria-label="Validar código de autenticação">
            <?= csrf_input(); ?>
            <label>
                <span>Código de 6 dígitos</span>
                <input type="text" name="codigo" inputmode="numeric" pattern="[0-9]{6}" minlength="6" maxlength="6" autocomplete="one-time-code" placeholder="000000" required autofocus>
            </label>
            <button type="submit">Validar e acessar</button>
        </form>

        <footer class="auth-card-footer auth-login-footer">
            <strong>Não reconhece esta tentativa?</strong>
            <span>Cancele o acesso e avise o administrador do sistema.</span>
            <a href="<?= e(url('/2fa/cancelar')); ?>">Cancelar e voltar ao login</a>
        </footer>
    </div>
</section>
