<div class="page-header">
    <div>
        <span class="breadcrumb">Usuarios</span>
        <h1>Usuarios</h1>
    </div>

    <a class="button button-primary" href="<?= e(url('/usuarios/novo')); ?>">Novo usuario</a>
</div>

<form method="get" action="<?= e(url('/usuarios')); ?>" class="filters">
    <input name="busca" value="<?= e($filtros['busca'] ?? ''); ?>" placeholder="Buscar por nome, e-mail ou CPF">

    <select name="perfil_id">
        <option value="">Todos os perfis</option>
        <?php foreach ($perfis as $perfil): ?>
            <option value="<?= e($perfil['id']); ?>" <?= (string) ($filtros['perfil_id'] ?? '') === (string) $perfil['id'] ? 'selected' : ''; ?>>
                <?= e($perfil['nome']); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="ativo">
        <option value="">Todas as situações</option>
        <option value="1" <?= (string) ($filtros['ativo'] ?? '') === '1' ? 'selected' : ''; ?>>Ativo</option>
        <option value="0" <?= (string) ($filtros['ativo'] ?? '') === '0' ? 'selected' : ''; ?>>Inativo</option>
    </select>

    <button type="submit" class="button button-secondary">Filtrar</button>
    <a class="button button-light" href="<?= e(url('/usuarios')); ?>">Limpar</a>
</form>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>E-mail</th>
                <th>Perfil</th>
                <th>Situacao</th>
                <th>Ultimo acesso</th>
                <th>Acoes</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $usuario): ?>
                <tr>
                    <td><?= e($usuario['nome']); ?></td>
                    <td><?= e($usuario['email']); ?></td>
                    <td><?= e($usuario['perfil_nome']); ?></td>
                    <td><?= (int) $usuario['ativo'] === 1 ? 'Ativo' : 'Inativo'; ?></td>
                    <td><?= e($usuario['ultimo_acesso_em'] ?? '-'); ?></td>
                    <td class="actions">
                        <a href="<?= e(url('/usuarios/' . $usuario['id'])); ?>">Ver</a>
                        <a href="<?= e(url('/usuarios/' . $usuario['id'] . '/editar')); ?>">Editar</a>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if ($usuarios === []): ?>
                <tr>
                    <td colspan="6">Nenhum usuario encontrado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
