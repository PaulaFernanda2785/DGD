<form method="get" action="<?= e(url('/decretos')); ?>" class="filters decrees-filters decree-filter-panel">
    <div class="field">
        <label for="filtro_ano">Ano</label>
        <input id="filtro_ano" name="ano" value="<?= e($filtros['ano'] ?? ''); ?>" placeholder="2026">
    </div>

    <div class="field">
        <label for="filtro_protocolo">Protocolo</label>
        <input id="filtro_protocolo" name="protocolo" value="<?= e($filtros['protocolo'] ?? ''); ?>" placeholder="DGD-2026">
    </div>

    <div class="field">
        <label for="filtro_municipio">Município</label>
        <select id="filtro_municipio" name="municipio_id">
            <option value="">Todos os municípios</option>
            <?php foreach ($dominios['municipios'] as $municipio): ?>
                <option value="<?= e($municipio['id']); ?>" <?= (string) ($filtros['municipio_id'] ?? '') === (string) $municipio['id'] ? 'selected' : ''; ?>>
                    <?= e($municipio['nome']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="field">
        <label for="filtro_homologacao">Homologação</label>
        <select id="filtro_homologacao" name="homologacao_status_id">
            <option value="">Todas</option>
            <?php foreach ($dominios['statusHomologacao'] as $status): ?>
                <option value="<?= e($status['id']); ?>" <?= (string) ($filtros['homologacao_status_id'] ?? '') === (string) $status['id'] ? 'selected' : ''; ?>>
                    <?= e($status['nome']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="field">
        <label for="filtro_reconhecimento">Reconhecimento</label>
        <select id="filtro_reconhecimento" name="reconhecimento_status_id">
            <option value="">Todos</option>
            <?php foreach ($dominios['statusReconhecimento'] as $status): ?>
                <option value="<?= e($status['id']); ?>" <?= (string) ($filtros['reconhecimento_status_id'] ?? '') === (string) $status['id'] ? 'selected' : ''; ?>>
                    <?= e($status['nome']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="field">
        <label for="filtro_pge">Envio à PGE</label>
        <select id="filtro_pge" name="status_envio_pge_id">
            <option value="">Todos</option>
            <?php foreach ($dominios['statusEnvioPge'] as $status): ?>
                <option value="<?= e($status['id']); ?>" <?= (string) ($filtros['status_envio_pge_id'] ?? '') === (string) $status['id'] ? 'selected' : ''; ?>>
                    <?= e($status['nome']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="decree-filter-actions">
        <button class="button button-secondary" type="submit">Filtrar</button>
        <a class="button button-light" href="<?= e(url('/decretos')); ?>">Limpar</a>
    </div>
</form>
