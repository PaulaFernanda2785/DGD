<div class="page-header page-header-modern">
    <div>
        <span class="breadcrumb">Decretos &gt; Editar</span>
        <h1>Editar desastre</h1>
        <p>Atualize os dados do registro mantendo a classificacao, status, danos humanos e anexos organizados.</p>
    </div>
    <div class="page-header-badge">Edicao</div>
</div>

<?php require view_path('components/form_errors'); ?>
<?php $action = url('/decretos/' . $registro['id'] . '/editar'); $submit = 'Salvar alteracoes'; require view_path('decretos/partials/form'); ?>
