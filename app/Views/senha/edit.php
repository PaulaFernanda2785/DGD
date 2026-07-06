<div class="page-header">
    <div>
        <span class="breadcrumb">Alterar senha</span>
        <h1>Alterar senha</h1>
    </div>
</div>

<?php require view_path('components/form_errors'); ?>

<form method="post" action="<?= e(url('/alterar-senha')); ?>" class="form-stack narrow">
    <?= csrf_input(); ?>

    <label for="senha_atual">Senha atual</label>
    <input id="senha_atual" name="senha_atual" type="password" required autocomplete="current-password">

    <label for="nova_senha">Nova senha</label>
    <input id="nova_senha" name="nova_senha" type="password" required autocomplete="new-password">

    <label for="confirmar_nova_senha">Confirmar nova senha</label>
    <input id="confirmar_nova_senha" name="confirmar_nova_senha" type="password" required autocomplete="new-password">

    <button type="submit" class="button button-primary">Salvar nova senha</button>
</form>
