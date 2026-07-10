<?php
    $totalUsuarios = count($usuarios);
    $ativos = count(array_filter($usuarios, static fn (array $usuario): bool => (int) $usuario['ativo'] === 1));
    $inativos = $totalUsuarios - $ativos;
    $perfilAtual = (string) ($filtros['perfil_id'] ?? '');
    $situacaoAtual = (string) ($filtros['ativo'] ?? '');
?>

<div class="page-header page-header-modern users-page-header">
    <div>
        <span class="breadcrumb">Usuários</span>
        <h1>Usuários</h1>
        <p>Gerencie acessos, perfis, situação de uso e dados institucionais dos usuários do DGD.</p>
    </div>

    <?php if (can('usuarios.criar')): ?>
        <a class="button button-primary" href="<?= e(url('/usuarios/novo')); ?>">Novo usuário</a>
    <?php endif; ?>
</div>

<section class="users-overview-grid">
    <article>
        <span>Total listado</span>
        <strong><?= e($totalUsuarios); ?></strong>
        <small>Resultado conforme filtros atuais.</small>
    </article>
    <article>
        <span>Ativos</span>
        <strong><?= e($ativos); ?></strong>
        <small>Usuários habilitados para acesso.</small>
    </article>
    <article>
        <span>Inativos</span>
        <strong><?= e($inativos); ?></strong>
        <small>Usuários sem acesso ao sistema.</small>
    </article>
</section>

<form method="get" action="<?= e(url('/usuarios')); ?>" class="users-filter-panel">
    <div class="field users-filter-search">
        <label for="busca">Busca inteligente</label>
        <input id="busca" name="busca" value="<?= e($filtros['busca'] ?? ''); ?>" placeholder="Nome, e-mail ou CPF">
    </div>

    <div class="field">
        <label for="perfil_id">Perfil</label>
        <select id="perfil_id" name="perfil_id">
            <option value="">Todos os perfis</option>
            <?php foreach ($perfis as $perfil): ?>
                <option value="<?= e($perfil['id']); ?>" <?= $perfilAtual === (string) $perfil['id'] ? 'selected' : ''; ?>>
                    <?= e($perfil['nome']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="field">
        <label for="ativo">Situação</label>
        <select id="ativo" name="ativo">
            <option value="">Todas as situações</option>
            <option value="1" <?= $situacaoAtual === '1' ? 'selected' : ''; ?>>Ativo</option>
            <option value="0" <?= $situacaoAtual === '0' ? 'selected' : ''; ?>>Inativo</option>
        </select>
    </div>

    <div class="users-filter-actions">
        <button type="submit" class="button button-primary">Filtrar</button>
        <a class="button button-light" href="<?= e(url('/usuarios')); ?>">Limpar</a>
    </div>
</form>

<?php if ($usuarios === []): ?>
    <section class="users-empty-state">
        <strong>Nenhum usuário encontrado</strong>
        <p>Ajuste os filtros ou cadastre um novo usuário para liberar acesso ao sistema.</p>
    </section>
<?php else: ?>
    <section class="users-card-list" aria-label="Listagem de usuários">
        <?php foreach ($usuarios as $usuario): ?>
            <?php
                $isActive = (int) $usuario['ativo'] === 1;
                $statusText = $isActive ? 'Ativo' : 'Inativo';
                $nextStatus = $isActive ? 0 : 1;
                $statusAction = $isActive ? 'Inativar' : 'Ativar';
                $confirmText = $isActive
                    ? 'Deseja realmente inativar este usuário?'
                    : 'Deseja realmente ativar este usuário?';
            ?>
            <article class="user-card <?= $isActive ? 'is-active' : 'is-inactive'; ?>">
                <div class="user-card-main">
                    <div class="user-card-avatar" aria-hidden="true"><?= e(strtoupper(substr((string) $usuario['nome'], 0, 1))); ?></div>
                    <div>
                        <span><?= e($usuario['perfil_nome']); ?></span>
                        <h2><?= e($usuario['nome']); ?></h2>
                        <p><?= e($usuario['email']); ?></p>
                    </div>
                </div>

                <div class="user-card-meta">
                    <div>
                        <span>Situação</span>
                        <?= status_badge($statusText); ?>
                    </div>
                    <div>
                        <span>CPF</span>
                        <strong><?= e($usuario['cpf'] ?? 'Não registrado'); ?></strong>
                    </div>
                    <div>
                        <span>Instituição</span>
                        <strong><?= e($usuario['instituicao'] ?? 'CEDEC-PA'); ?></strong>
                    </div>
                    <div>
                        <span>Último acesso</span>
                        <strong><?= e($usuario['ultimo_acesso_em'] ?? 'Sem acesso registrado'); ?></strong>
                    </div>
                </div>

                <div class="user-card-actions">
                    <a class="button button-light" href="<?= e(url('/usuarios/' . $usuario['id'])); ?>">Ver</a>
                    <?php if (can('usuarios.editar')): ?>
                        <a class="button button-light" href="<?= e(url('/usuarios/' . $usuario['id'] . '/editar')); ?>">Editar</a>
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
            </article>
        <?php endforeach; ?>
    </section>
<?php endif; ?>
