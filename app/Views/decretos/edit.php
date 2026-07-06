<div class="page-header">
    <div>
        <span class="breadcrumb">Decretos &gt; Editar</span>
        <h1>Editar desastre</h1>
    </div>
</div>

<?php require view_path('components/form_errors'); ?>
<?php $action = url('/decretos/' . $registro['id'] . '/editar'); $submit = 'Salvar alteracoes'; require view_path('decretos/partials/form'); ?>
