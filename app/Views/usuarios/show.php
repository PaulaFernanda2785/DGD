<div class="page-header">
    <div>
        <span class="breadcrumb">Usuarios &gt; Detalhe</span>
        <h1><?= e($usuario['nome']); ?></h1>
    </div>

    <a class="button button-primary" href="<?= e(url('/usuarios/' . $usuario['id'] . '/editar')); ?>">Editar</a>
</div>

<section class="detail-grid">
    <div><strong>E-mail</strong><span><?= e($usuario['email']); ?></span></div>
    <div><strong>Perfil</strong><span><?= e($usuario['perfil_nome']); ?></span></div>
    <div><strong>CPF</strong><span><?= e($usuario['cpf'] ?? '-'); ?></span></div>
    <div><strong>Telefone</strong><span><?= e($usuario['telefone'] ?? '-'); ?></span></div>
    <div><strong>Cargo</strong><span><?= e($usuario['cargo'] ?? '-'); ?></span></div>
    <div><strong>Instituicao</strong><span><?= e($usuario['instituicao']); ?></span></div>
    <div><strong>Situacao</strong><span><?= (int) $usuario['ativo'] === 1 ? 'Ativo' : 'Inativo'; ?></span></div>
    <div><strong>Ultimo acesso</strong><span><?= e($usuario['ultimo_acesso_em'] ?? '-'); ?></span></div>
</section>

<form method="post" action="<?= e(url('/usuarios/' . $usuario['id'] . '/excluir')); ?>" class="danger-form">
    <?= csrf_input(); ?>
    <button type="submit" class="button button-danger" data-confirm="Deseja realmente remover este usuario da listagem?">
        Excluir logicamente
    </button>
</form>
