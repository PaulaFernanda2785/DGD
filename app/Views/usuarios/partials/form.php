<?php

$isEdit = isset($usuario);
$action = $isEdit ? url('/usuarios/' . $usuario['id'] . '/editar') : url('/usuarios');
$submit = $isEdit ? 'Salvar alteracoes' : 'Criar usuario';
?>

<form method="post" action="<?= e($action); ?>" class="form-grid">
    <?= csrf_input(); ?>

    <div class="field span-2">
        <label for="nome">Nome completo</label>
        <input id="nome" name="nome" value="<?= e(old('nome', $usuario['nome'] ?? '')); ?>" required>
    </div>

    <div class="field">
        <label for="email">E-mail</label>
        <input id="email" name="email" type="email" value="<?= e(old('email', $usuario['email'] ?? '')); ?>" required>
    </div>

    <div class="field">
        <label for="perfil_id">Perfil</label>
        <select id="perfil_id" name="perfil_id" required>
            <option value="">Selecione</option>
            <?php foreach ($perfis as $perfil): ?>
                <?php $selected = (string) old('perfil_id', $usuario['perfil_id'] ?? '') === (string) $perfil['id']; ?>
                <option value="<?= e($perfil['id']); ?>" <?= $selected ? 'selected' : ''; ?>>
                    <?= e($perfil['nome']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="field">
        <label for="cpf">CPF</label>
        <input id="cpf" name="cpf" value="<?= e(old('cpf', $usuario['cpf'] ?? '')); ?>">
    </div>

    <div class="field">
        <label for="telefone">Telefone</label>
        <input id="telefone" name="telefone" value="<?= e(old('telefone', $usuario['telefone'] ?? '')); ?>">
    </div>

    <div class="field">
        <label for="cargo">Cargo</label>
        <input id="cargo" name="cargo" value="<?= e(old('cargo', $usuario['cargo'] ?? '')); ?>">
    </div>

    <div class="field">
        <label for="instituicao">Instituicao</label>
        <input id="instituicao" name="instituicao" value="<?= e(old('instituicao', $usuario['instituicao'] ?? 'CEDEC-PA')); ?>">
    </div>

    <div class="field">
        <label for="ativo">Situacao</label>
        <select id="ativo" name="ativo">
            <?php $ativo = (string) old('ativo', $usuario['ativo'] ?? '1'); ?>
            <option value="1" <?= $ativo === '1' ? 'selected' : ''; ?>>Ativo</option>
            <option value="0" <?= $ativo === '0' ? 'selected' : ''; ?>>Inativo</option>
        </select>
    </div>

    <div class="field">
        <label for="senha"><?= $isEdit ? 'Nova senha' : 'Senha inicial'; ?></label>
        <input id="senha" name="senha" type="password" <?= $isEdit ? '' : 'required'; ?> autocomplete="new-password">
    </div>

    <div class="field">
        <label for="senha_confirmacao">Confirmar senha</label>
        <input id="senha_confirmacao" name="senha_confirmacao" type="password" <?= $isEdit ? '' : 'required'; ?> autocomplete="new-password">
    </div>

    <label class="check-field span-2">
        <input type="checkbox" name="trocar_senha_proximo_acesso" value="1" <?= old('trocar_senha_proximo_acesso', $usuario['trocar_senha_proximo_acesso'] ?? '0') ? 'checked' : ''; ?>>
        Exigir troca de senha no proximo acesso
    </label>

    <div class="form-actions span-2">
        <button type="submit" class="button button-primary"><?= e($submit); ?></button>
        <a class="button button-light" href="<?= e(url('/usuarios')); ?>">Cancelar</a>
    </div>
</form>
