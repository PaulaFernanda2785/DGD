<div class="page-header page-header-modern">
    <div>
        <span class="breadcrumb">Decretos &gt; Novo cadastro</span>
        <h1>Novo cadastro</h1>
        <p>Registre o desastre com localizacao, classificacao COBRADE, status operacionais, danos humanos e anexos previstos.</p>
    </div>
    <div class="page-header-badge">Cadastro inicial</div>
</div>

<?php require view_path('components/form_errors'); ?>
<?php $action = url('/decretos'); $submit = 'Cadastrar desastre'; require view_path('decretos/partials/form'); ?>
