<div class="page-header page-header-modern users-page-header">
    <div>
        <span class="breadcrumb">Usuários &gt; Editar</span>
        <h1>Editar usuário</h1>
        <p>Atualize dados cadastrais, perfil, situação de acesso e política de senha do usuário.</p>
    </div>

    <a class="button button-light" href="<?= e(url('/usuarios/' . $usuario['id'])); ?>">Voltar</a>
</div>

<?php require view_path('components/form_errors'); ?>
<?php require view_path('usuarios/partials/form'); ?>
