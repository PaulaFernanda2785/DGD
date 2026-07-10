<?php
    $value = static function (mixed $content): string {
        $content = trim((string) ($content ?? ''));

        return $content !== '' ? $content : 'Não registrado';
    };
    $isActive = (int) $usuario['ativo'] === 1;
    $nextStatus = $isActive ? 0 : 1;
    $statusAction = $isActive ? 'Inativar' : 'Ativar';
    $confirmText = $isActive
        ? 'Deseja realmente inativar este usuário?'
        : 'Deseja realmente ativar este usuário?';
?>

<div class="page-header page-header-modern users-page-header">
    <div>
        <span class="breadcrumb">Usuários &gt; Detalhe</span>
        <h1><?= e($usuario['nome']); ?></h1>
        <p>Consulte os dados de acesso, perfil, situação, instituição e histórico operacional do usuário.</p>
    </div>

    <div class="actions">
        <a class="button button-light" href="<?= e(url('/usuarios')); ?>">Voltar</a>
        <?php if (can('usuarios.editar')): ?>
            <a class="button button-primary" href="<?= e(url('/usuarios/' . $usuario['id'] . '/editar')); ?>">Editar</a>
        <?php endif; ?>
    </div>
</div>

<section class="user-detail-hero <?= $isActive ? 'is-active' : 'is-inactive'; ?>">
    <div class="user-card-main">
        <div class="user-card-avatar" aria-hidden="true"><?= e(strtoupper(substr((string) $usuario['nome'], 0, 1))); ?></div>
        <div>
            <span><?= e($usuario['perfil_nome']); ?></span>
            <h2><?= e($usuario['nome']); ?></h2>
            <p><?= e($usuario['email']); ?></p>
        </div>
    </div>

    <div class="user-detail-status">
        <?= status_badge($isActive ? 'Ativo' : 'Inativo'); ?>
        <?php if (can('usuarios.editar')): ?>
            <form method="post" action="<?= e(url('/usuarios/' . $usuario['id'] . '/status')); ?>">
                <?= csrf_input(); ?>
                <input type="hidden" name="ativo" value="<?= e($nextStatus); ?>">
                <button
                    type="submit"
                    class="button <?= $isActive ? 'button-warning' : 'button-primary'; ?>"
                    data-confirm="<?= e($confirmText); ?>"
                >
                    <?= e($statusAction); ?>
                </button>
            </form>
        <?php endif; ?>
    </div>
</section>

<section class="form-section user-detail-section">
    <div class="form-section-heading">
        <div>
            <span>01</span>
            <h2>Dados do usuário</h2>
        </div>
        <p>Informações cadastrais e institucionais usadas para identificação e contato.</p>
    </div>

    <div class="user-detail-grid">
        <article><span>E-mail</span><strong><?= e($usuario['email']); ?></strong></article>
        <article><span>Perfil</span><strong><?= e($usuario['perfil_nome']); ?></strong></article>
        <article><span>CPF</span><strong><?= e($value($usuario['cpf'] ?? null)); ?></strong></article>
        <article><span>Telefone</span><strong><?= e($value($usuario['telefone'] ?? null)); ?></strong></article>
        <article><span>Cargo</span><strong><?= e($value($usuario['cargo'] ?? null)); ?></strong></article>
        <article><span>Instituição</span><strong><?= e($value($usuario['instituicao'] ?? null)); ?></strong></article>
        <article><span>Situação</span><strong><?= $isActive ? 'Ativo' : 'Inativo'; ?></strong></article>
        <article><span>Último acesso</span><strong><?= e($value($usuario['ultimo_acesso_em'] ?? null)); ?></strong></article>
        <article><span>Troca senha no próximo acesso</span><strong><?= (int) ($usuario['trocar_senha_proximo_acesso'] ?? 0) === 1 ? 'Sim' : 'Não'; ?></strong></article>
        <article><span>2º fator</span><strong><?= (int) ($usuario['two_factor_enabled'] ?? 0) === 1 ? 'Configurado' : 'Não configurado'; ?></strong></article>
    </div>
</section>

<?php if (can('usuarios.excluir')): ?>
    <form method="post" action="<?= e(url('/usuarios/' . $usuario['id'] . '/excluir')); ?>" class="danger-form user-danger-zone">
        <?= csrf_input(); ?>
        <div>
            <strong>Exclusão lógica</strong>
            <span>Remove o usuário da listagem e bloqueia novos acessos.</span>
        </div>
        <button type="submit" class="button button-danger" data-confirm="Deseja realmente remover este usuário da listagem?">
            Excluir logicamente
        </button>
    </form>
<?php endif; ?>
