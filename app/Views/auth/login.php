<section class="auth-shell">
    <div class="auth-hero" aria-hidden="true">
        <div class="auth-hero-brand">
            <img class="auth-logo-mark" src="<?= e(url('/assets/img/logo-cedec.png')); ?>" alt="">
            <span>Corpo de Bombeiros Militar do Pará e<br>Coordenadoria Estadual de Proteção e Defesa Civil</span>
        </div>
        <div class="auth-hero-copy">
            <p class="eyebrow">Sistema DGD</p>
            <h1>Gestão estadual de desastres e decretos</h1>
            <p>Cadastro, acompanhamento, homologação e reconhecimento de desastres em um fluxo institucional seguro.</p>
        </div>
        <div class="auth-hero-metrics">
            <div>
                <strong>COBRADE</strong>
                <span>Classificação padronizada dos eventos registrados</span>
            </div>
            <div>
                <strong>2FA</strong>
                <span>Acesso protegido por senha e código temporário</span>
            </div>
            <div>
                <strong>PGE</strong>
                <span>Controle de prazos, status e documentos anexados</span>
            </div>
        </div>
    </div>

    <div class="auth-panel">
        <div class="auth-card-header">
            <img class="auth-logo-mark auth-logo-card" src="<?= e(url('/assets/img/logo-cedec.png')); ?>" alt="CEDEC-PA">
            <div>
                <p class="eyebrow">Acesso restrito</p>
                <h1>Entrar no DGD</h1>
            </div>
        </div>

        <?php require view_path('components/flash'); ?>

        <form class="form-panel auth-form" method="post" action="<?= e(url('/login')); ?>" aria-label="Login">
            <?= csrf_input(); ?>
            <label>
                <span>E-mail institucional</span>
                <input type="email" name="email" value="<?= e(old('email')); ?>" autocomplete="username" placeholder="usuario@instituicao.gov.br" required>
            </label>
            <label>
                <span>Senha</span>
                <span class="password-input-shell" data-password-shell>
                    <input type="password" name="senha" autocomplete="current-password" placeholder="Digite sua senha" required data-password-reveal>
                    <button class="password-toggle-button" type="button" aria-label="Mostrar senha" aria-pressed="false" title="Mostrar senha" data-password-toggle>
                        <img class="password-eye-icon" src="<?= e(url('/assets/icons/icon-password-eye.svg')); ?>" alt="" aria-hidden="true">
                    </button>
                </span>
            </label>
            <div class="auth-form-links">
                <a href="<?= e(url('/esqueci-senha')); ?>">Esqueci minha senha</a>
            </div>
            <button type="submit">Entrar</button>
        </form>

        <footer class="auth-card-footer auth-login-footer">
            <strong>Ambiente institucional da CEDEC-PA</strong>
            <span>Acesso restrito a usuários autorizados para gestão e acompanhamento de desastres.</span>
            <small>Sistema de Gerenciamento de Desastres - DGD</small>
        </footer>
    </div>
</section>
