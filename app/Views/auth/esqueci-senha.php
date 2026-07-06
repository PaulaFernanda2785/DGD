<section class="auth-recovery-shell">
    <div class="auth-recovery-intro">
        <div class="auth-hero-brand">
            <img class="auth-logo-mark" src="<?= e(url('/assets/img/logo-cedec.png')); ?>" alt="">
            <span>Corpo de Bombeiros Militar do Pará e<br>Coordenadoria Estadual de Proteção e Defesa Civil</span>
        </div>
        <div>
            <p class="eyebrow">Recuperação de acesso</p>
            <h1>Esqueci minha senha</h1>
            <p>Informe o e-mail cadastrado no DGD. Se a conta estiver ativa, enviaremos um link temporário para redefinir a senha.</p>
        </div>
        <div class="auth-recovery-steps" aria-label="Etapas da recuperação de senha">
            <span>1. Informe seu e-mail</span>
            <span>2. Verifique as instruções</span>
            <span>3. Cadastre uma nova senha</span>
        </div>
    </div>

    <div class="auth-panel auth-recovery-card">
        <div class="auth-card-header">
            <img class="auth-logo-mark auth-logo-card" src="<?= e(url('/assets/img/logo-cedec.png')); ?>" alt="CEDEC-PA">
            <div>
                <p class="eyebrow">Segurança da conta</p>
                <h1>Recuperar acesso</h1>
            </div>
        </div>

        <?php require view_path('components/flash'); ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error" role="alert">
                <?php foreach ($errors as $error): ?>
                    <p><?= e($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form class="form-panel auth-form" method="post" action="<?= e(url('/esqueci-senha')); ?>" aria-label="Esqueci minha senha">
            <?= csrf_input(); ?>
            <label>
                <span>E-mail institucional</span>
                <input type="email" name="email" value="<?= e(old('email')); ?>" autocomplete="email" placeholder="usuario@instituicao.gov.br" required>
            </label>
            <button type="submit">Enviar instruções</button>
        </form>

        <footer class="auth-card-footer">
            <a href="<?= e(url('/login')); ?>">Voltar para o login</a>
            <span>O link expira em 60 minutos e só pode ser usado uma vez</span>
        </footer>
    </div>
</section>
