<?php $edicao = !empty($tipo['id']); ?>
<div class="page-header page-header-modern aid-type-form-header">
    <div><span class="breadcrumb">Tipos de ajuda &gt; <?= $edicao ? 'Edição' : 'Novo cadastro'; ?></span><h1><?= e($title); ?></h1><p>Configure o material, sua unidade de distribuição e a disponibilidade operacional.</p></div>
    <span class="status-badge <?= (int) $tipo['ativo'] === 1 ? 'badge-success' : 'badge-muted'; ?>"><?= (int) $tipo['ativo'] === 1 ? 'Ativo' : 'Inativo'; ?></span>
</div>
<?php if (!empty($errors['geral'])): ?><div class="alert alert-error"><?= e($errors['geral'][0]); ?></div><?php endif; ?>
<form method="post" class="aid-type-form" action="<?= e($edicao ? url('/tipos-ajuda/'.$tipo['id'].'/editar') : url('/tipos-ajuda')); ?>">
    <?= csrf_input(); ?>
    <section class="aid-type-form-card">
        <header><span>01</span><div><h2>Identificação do item</h2><p>Use um nome claro para facilitar a localização nas ações de ajuda.</p></div></header>
        <div class="aid-type-form-grid">
            <label class="field"><span>Nome do tipo de ajuda</span><input name="nome" required maxlength="180" value="<?= e($tipo['nome']); ?>" placeholder="Ex.: Cesta Básica" autofocus><small>Máximo de 180 caracteres.</small></label>
            <label class="field"><span>Unidade de medida</span><input name="unidade_medida" required maxlength="50" value="<?= e($tipo['unidade_medida']); ?>" placeholder="Ex.: Kit, unidade ou litro"><small>Máximo de 50 caracteres.</small></label>
        </div>
    </section>
    <section class="aid-type-form-card aid-type-status-card">
        <header><span>02</span><div><h2>Disponibilidade</h2><p>Itens inativos permanecem no histórico, mas não devem ser usados em novos registros.</p></div></header>
        <label class="field"><span>Status operacional</span><select name="ativo"><option value="1" <?= (int)$tipo['ativo']===1?'selected':''; ?>>Ativo — disponível para novas entregas</option><option value="0" <?= (int)$tipo['ativo']===0?'selected':''; ?>>Inativo — indisponível para novas entregas</option></select></label>
    </section>
    <footer class="aid-type-form-actions"><a class="button button-light" href="<?= e(url('/tipos-ajuda')); ?>">Cancelar</a><button type="submit" class="button button-primary"><?= $edicao ? 'Salvar alterações' : 'Cadastrar tipo de ajuda'; ?></button></footer>
</form>
