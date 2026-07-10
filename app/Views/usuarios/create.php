<div class="page-header page-header-modern users-page-header">
    <div>
        <span class="breadcrumb">Usuários &gt; Novo usuário</span>
        <h1>Novo usuário</h1>
        <p>Cadastre um novo acesso com perfil, dados institucionais, senha inicial e regras do primeiro login.</p>
    </div>

    <a class="button button-light" href="<?= e(url('/usuarios')); ?>">Voltar</a>
</div>

<?php require view_path('components/form_errors'); ?>
<?php require view_path('usuarios/partials/form'); ?>
