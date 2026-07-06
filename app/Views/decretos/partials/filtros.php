<form method="get" action="<?= e(url('/decretos')); ?>" class="filters decrees-filters">
    <input name="ano" value="<?= e($filtros['ano'] ?? ''); ?>" placeholder="Ano">
    <input name="protocolo" value="<?= e($filtros['protocolo'] ?? ''); ?>" placeholder="Protocolo DGD">

    <select name="municipio_id">
        <option value="">Municipio</option>
        <?php foreach ($dominios['municipios'] as $municipio): ?>
            <option value="<?= e($municipio['id']); ?>" <?= (string) ($filtros['municipio_id'] ?? '') === (string) $municipio['id'] ? 'selected' : ''; ?>>
                <?= e($municipio['nome']); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="homologacao_status_id">
        <option value="">Homologacao</option>
        <?php foreach ($dominios['statusHomologacao'] as $status): ?>
            <option value="<?= e($status['id']); ?>" <?= (string) ($filtros['homologacao_status_id'] ?? '') === (string) $status['id'] ? 'selected' : ''; ?>>
                <?= e($status['nome']); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="reconhecimento_status_id">
        <option value="">Reconhecimento</option>
        <?php foreach ($dominios['statusReconhecimento'] as $status): ?>
            <option value="<?= e($status['id']); ?>" <?= (string) ($filtros['reconhecimento_status_id'] ?? '') === (string) $status['id'] ? 'selected' : ''; ?>>
                <?= e($status['nome']); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="status_envio_pge_id">
        <option value="">Envio PGE</option>
        <?php foreach ($dominios['statusEnvioPge'] as $status): ?>
            <option value="<?= e($status['id']); ?>" <?= (string) ($filtros['status_envio_pge_id'] ?? '') === (string) $status['id'] ? 'selected' : ''; ?>>
                <?= e($status['nome']); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button class="button button-secondary" type="submit">Filtrar</button>
    <a class="button button-light" href="<?= e(url('/decretos')); ?>">Limpar</a>
</form>
