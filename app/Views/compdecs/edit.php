<div class="page-header">
    <div>
        <span class="breadcrumb">COMPDECs &gt; Editar</span>
        <h1><?= e($compdec['municipio']); ?></h1>
    </div>
</div>

<?php require view_path('components/form_errors'); ?>

<form method="post" action="<?= e(url('/compdecs/' . $compdec['id'] . '/editar')); ?>" enctype="multipart/form-data" class="form-grid">
    <?= csrf_input(); ?>

    <div class="field span-2 compdec-photo-edit">
        <?= compdec_photo_thumb($compdec['foto_coordenador'] ?? null, $compdec['coordenador'] ?? 'Coordenador COMPDEC', 'compdec-photo-large'); ?>
        <label for="foto_coordenador">Foto do coordenador</label>
        <input id="foto_coordenador" name="foto_coordenador" type="file" accept="image/jpeg,image/png">
    </div>

    <div class="field">
        <label>Municipio</label>
        <input value="<?= e($compdec['municipio']); ?>" disabled>
    </div>

    <div class="field">
        <label>Codigo IBGE</label>
        <input value="<?= e($compdec['municipio_codigo'] ?? ''); ?>" disabled>
    </div>

    <div class="field">
        <label for="regiao_integracao">Regiao de integracao</label>
        <input id="regiao_integracao" name="regiao_integracao" value="<?= e(old('regiao_integracao', $compdec['regiao_integracao'] ?? '')); ?>">
    </div>

    <div class="field">
        <label for="tem_compdec">Possui COMPDEC</label>
        <select id="tem_compdec" name="tem_compdec">
            <option value="1" <?= (string) old('tem_compdec', $compdec['tem_compdec'] ?? '0') === '1' ? 'selected' : ''; ?>>Sim</option>
            <option value="0" <?= (string) old('tem_compdec', $compdec['tem_compdec'] ?? '0') === '0' ? 'selected' : ''; ?>>Não</option>
        </select>
    </div>

    <div class="field">
        <label for="prefeito">Prefeito</label>
        <input id="prefeito" name="prefeito" value="<?= e(old('prefeito', $compdec['prefeito'] ?? '')); ?>">
    </div>

    <div class="field">
        <label for="ubm_nome">UBM atuante</label>
        <input id="ubm_nome" name="ubm_nome" value="<?= e(old('ubm_nome', $compdec['ubm_nome'] ?? '')); ?>">
    </div>

    <div class="field">
        <label for="coordenador">Coordenador</label>
        <input id="coordenador" name="coordenador" value="<?= e(old('coordenador', $compdec['coordenador'] ?? '')); ?>">
    </div>

    <div class="field">
        <label for="telefone">Telefone</label>
        <input id="telefone" name="telefone" value="<?= e(old('telefone', $compdec['telefone'] ?? '')); ?>">
    </div>

    <div class="field">
        <label for="email">E-mail</label>
        <input id="email" name="email" type="email" value="<?= e(old('email', $compdec['email'] ?? '')); ?>">
    </div>

    <div class="field">
        <label for="data_atualizacao">Data de atualizacao</label>
        <input id="data_atualizacao" name="data_atualizacao" value="<?= e(old('data_atualizacao', $compdec['data_atualizacao'] ?? '')); ?>">
    </div>

    <div class="field span-2">
        <label for="endereco">Endereco</label>
        <input id="endereco" name="endereco" value="<?= e(old('endereco', $compdec['endereco'] ?? '')); ?>">
    </div>

    <div class="form-actions span-2">
        <button type="submit" class="button button-primary">Salvar alteracoes</button>
        <a class="button button-light" href="<?= e(url('/compdecs/' . $compdec['id'])); ?>">Cancelar</a>
    </div>
</form>
