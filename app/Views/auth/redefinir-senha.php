<section class="auth-recovery-shell">
    <div class="auth-recovery-intro">
        <div class="auth-hero-brand">
            <img class="auth-logo-mark" src="<?= e(url('/assets/img/logo-cedec.png')); ?>" alt="">
            <span>Corpo de Bombeiros Militar do Pará e<br>Coordenadoria Estadual de Proteção e Defesa Civil</span>
        </div>
        <div>
            <p class="eyebrow">Nova senha</p>
            <h1>Redefinir senha</h1>
            <p>Crie uma senha forte para recuperar o acesso ao DGD. O link é temporário e será invalidado após o uso.</p>
        </div>
        <div class="auth-recovery-steps" aria-label="Requisitos da senha">
            <span>Mínimo de 10 caracteres</span>
            <span>Letras maiúsculas e minúsculas</span>
            <span>Pelo menos um número</span>
        </div>
    </div>

    <div class="auth-panel auth-recovery-card">
        <div class="auth-card-header">
            <img class="auth-logo-mark auth-logo-card" src="<?= e(url('/assets/img/logo-cedec.png')); ?>" alt="CEDEC-PA">
            <div>
                <p class="eyebrow">Segurança da conta</p>
                <h1>Criar nova senha</h1>
            </div>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error" role="alert">
                <?php foreach ($errors as $error): ?>
                    <p><?= e($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form class="form-panel auth-form" method="post" action="<?= e(url('/recuperar-senha/' . $token)); ?>" aria-label="Redefinir senha">
            <?= csrf_input(); ?>
            <label>
                <span>Nova senha</span>
                <span class="password-input-shell" data-password-shell>
                    <input type="password" name="nova_senha" autocomplete="new-password" minlength="10" required data-password-reveal>
                    <button class="password-toggle-button" type="button" aria-label="Mostrar senha" aria-pressed="false" title="Mostrar senha" data-password-toggle>
                        <img class="password-eye-icon" src="<?= e(url('/assets/icons/icon-password-eye.svg')); ?>" alt="" aria-hidden="true">
                    </button>
                </span>
            </label>
            <label>
                <span>Confirmar nova senha</span>
                <span class="password-input-shell" data-password-shell>
                    <input type="password" name="confirmar_senha" autocomplete="new-password" minlength="10" required data-password-reveal>
                    <button class="password-toggle-button" type="button" aria-label="Mostrar senha" aria-pressed="false" title="Mostrar senha" data-password-toggle>
                        <img class="password-eye-icon" src="<?= e(url('/assets/icons/icon-password-eye.svg')); ?>" alt="" aria-hidden="true">
                    </button>
                </span>
            </label>
            <button type="submit">Redefinir senha</button>
        </form>

        <footer class="auth-card-footer">
            <a href="<?= e(url('/login')); ?>">Voltar para o login</a>
            <span>Use uma senha diferente da anterior</span>
        </footer>
    </div>
</section>
