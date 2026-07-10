<div class="page-header page-header-modern password-page-header">
    <div>
        <span class="breadcrumb">Segurança &gt; Alterar senha</span>
        <h1>Alterar senha</h1>
        <p>Atualize sua senha de acesso ao DGD mantendo uma combinação segura e diferente da senha atual.</p>
    </div>

    <a class="button button-light" href="<?= e(url('/painel')); ?>">Voltar ao painel</a>
</div>

<?php require view_path('components/form_errors'); ?>

<section class="password-change-shell">
    <aside class="password-guidance-card">
        <span>Proteção da conta</span>
        <h2>Use uma senha forte</h2>
        <p>Prefira uma combinação com letras, números e caracteres especiais. Evite reutilizar senhas de outros serviços.</p>
        <div class="password-guidance-list">
            <span>Mínimo de 8 caracteres</span>
            <span>Diferente da senha atual</span>
            <span>Confirmação obrigatória</span>
        </div>
    </aside>

    <form method="post" action="<?= e(url('/alterar-senha')); ?>" class="form-grid password-change-form">
        <?= csrf_input(); ?>

        <section class="span-2 form-section password-form-section">
            <div class="form-section-heading">
                <div>
                    <span>01</span>
                    <h2>Credenciais de acesso</h2>
                </div>
                <p>Informe sua senha atual e cadastre a nova senha que será usada nos próximos acessos.</p>
            </div>

            <div class="password-form-grid">
                <div class="field span-2">
                    <label for="senha_atual">Senha atual</label>
                    <div class="password-input-shell" data-password-shell>
                        <input id="senha_atual" name="senha_atual" type="password" required autocomplete="current-password" data-password-reveal>
                        <button type="button" class="password-toggle-button" data-password-toggle aria-label="Mostrar senha" aria-pressed="false">
                            <img class="password-eye-icon" src="<?= e(url('/assets/icons/icon-password-eye.svg')); ?>" alt="">
                        </button>
                    </div>
                </div>

                <div class="field">
                    <label for="nova_senha">Nova senha</label>
                    <div class="password-input-shell" data-password-shell>
                        <input id="nova_senha" name="nova_senha" type="password" required autocomplete="new-password" data-password-reveal>
                        <button type="button" class="password-toggle-button" data-password-toggle aria-label="Mostrar senha" aria-pressed="false">
                            <img class="password-eye-icon" src="<?= e(url('/assets/icons/icon-password-eye.svg')); ?>" alt="">
                        </button>
                    </div>
                </div>

                <div class="field">
                    <label for="confirmar_nova_senha">Confirmar nova senha</label>
                    <div class="password-input-shell" data-password-shell>
                        <input id="confirmar_nova_senha" name="confirmar_nova_senha" type="password" required autocomplete="new-password" data-password-reveal>
                        <button type="button" class="password-toggle-button" data-password-toggle aria-label="Mostrar senha" aria-pressed="false">
                            <img class="password-eye-icon" src="<?= e(url('/assets/icons/icon-password-eye.svg')); ?>" alt="">
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <div class="form-actions form-actions-sticky span-2">
            <button type="submit" class="button button-primary">Salvar nova senha</button>
            <a class="button button-light" href="<?= e(url('/painel')); ?>">Cancelar</a>
        </div>
    </form>
</section>
