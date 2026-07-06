<form method="post" action="<?= e($action); ?>" class="form-grid decree-form">
    <?= csrf_input(); ?>

    <?php if (!empty($registro['protocolo_dgd'])): ?>
        <div class="field span-2">
            <label>Protocolo DGD</label>
            <input value="<?= e($registro['protocolo_dgd']); ?>" disabled>
        </div>
    <?php endif; ?>

    <fieldset class="span-2">
        <legend>Identificacao e localizacao</legend>
        <div class="form-grid inner">
            <div class="field">
                <label for="municipio_id">Municipio</label>
                <select id="municipio_id" name="municipio_id" required>
                    <option value="">Selecione</option>
                    <?php foreach ($dominios['municipios'] as $municipio): ?>
                        <?php $selected = (string) old('municipio_id', $registro['municipio_id'] ?? '') === (string) $municipio['id']; ?>
                        <option value="<?= e($municipio['id']); ?>" <?= $selected ? 'selected' : ''; ?>><?= e($municipio['nome']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field">
                <label for="ubm_id">UBM atuante</label>
                <select id="ubm_id" name="ubm_id">
                    <option value="">Nao informado</option>
                    <?php foreach ($dominios['ubms'] as $ubm): ?>
                        <?php $selected = (string) old('ubm_id', $registro['ubm_id'] ?? '') === (string) $ubm['id']; ?>
                        <option value="<?= e($ubm['id']); ?>" <?= $selected ? 'selected' : ''; ?>><?= e($ubm['nome']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field">
                <label for="tipo_decreto_id">Tipo de decreto</label>
                <select id="tipo_decreto_id" name="tipo_decreto_id" required>
                    <option value="">Selecione</option>
                    <?php foreach ($dominios['tiposDecreto'] as $tipo): ?>
                        <?php $selected = (string) old('tipo_decreto_id', $registro['tipo_decreto_id'] ?? '') === (string) $tipo['id']; ?>
                        <option value="<?= e($tipo['id']); ?>" <?= $selected ? 'selected' : ''; ?>><?= e($tipo['nome']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field">
                <label for="data_desastre">Data do desastre</label>
                <input id="data_desastre" name="data_desastre" type="date" value="<?= e(old('data_desastre', $registro['data_desastre'] ?? '')); ?>" required>
            </div>
        </div>
    </fieldset>

    <fieldset class="span-2">
        <legend>COBRADE</legend>
        <div class="form-grid inner">
            <div class="field">
                <label for="cobrade_grupo_id">Grupo</label>
                <select id="cobrade_grupo_id" data-cobrade="grupo">
                    <option value="">Selecione</option>
                    <?php foreach ($dominios['cobradeGrupos'] as $grupo): ?>
                        <option value="<?= e($grupo['id']); ?>"><?= e($grupo['nome']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="cobrade_subgrupo_id">Subgrupo</label>
                <select id="cobrade_subgrupo_id" data-cobrade="subgrupo"><option value="">Selecione</option></select>
            </div>
            <div class="field">
                <label for="cobrade_tipo_id">Tipo</label>
                <select id="cobrade_tipo_id" data-cobrade="tipo"><option value="">Selecione</option></select>
            </div>
            <div class="field">
                <label for="cobrade_subtipo_id">Subtipo</label>
                <select id="cobrade_subtipo_id" name="cobrade_subtipo_id" data-cobrade="subtipo" data-current="<?= e(old('cobrade_subtipo_id', $registro['cobrade_subtipo_id'] ?? '')); ?>" required>
                    <option value="<?= e(old('cobrade_subtipo_id', $registro['cobrade_subtipo_id'] ?? '')); ?>"><?= !empty($registro['cobrade_subtipo_id']) ? 'Subtipo atual selecionado' : 'Selecione'; ?></option>
                </select>
            </div>
            <div class="field span-2">
                <label>Descricao COBRADE</label>
                <div id="cobrade-descricao" class="readonly-box">Selecione um subtipo para exibir a descricao.</div>
            </div>
        </div>
    </fieldset>

    <fieldset class="span-2">
        <legend>Decreto, homologacao, reconhecimento e PGE</legend>
        <div class="form-grid inner">
            <div class="field"><label>Protocolo S2ID</label><input name="protocolo_s2id" value="<?= e(old('protocolo_s2id', $registro['protocolo_s2id'] ?? '')); ?>"></div>
            <div class="field"><label>Numero decreto municipal</label><input name="numero_decreto_municipal" value="<?= e(old('numero_decreto_municipal', $registro['numero_decreto_municipal'] ?? '')); ?>"></div>
            <div class="field"><label>Data decreto municipal</label><input name="data_decreto_municipal" type="date" value="<?= e(old('data_decreto_municipal', $registro['data_decreto_municipal'] ?? '')); ?>"></div>
            <div class="field"><label>Numero decreto estadual</label><input name="numero_decreto_homologacao_estadual" value="<?= e(old('numero_decreto_homologacao_estadual', $registro['numero_decreto_homologacao_estadual'] ?? '')); ?>"></div>
            <div class="field"><label>Data homologacao</label><input name="data_decreto_homologacao" type="date" value="<?= e(old('data_decreto_homologacao', $registro['data_decreto_homologacao'] ?? '')); ?>"></div>
            <div class="field">
                <label>Homologacao</label>
                <?php $name = 'homologacao_status_id'; $options = $dominios['statusHomologacao']; require view_path('decretos/partials/select'); ?>
            </div>
            <div class="field">
                <label>Reconhecimento</label>
                <?php $name = 'reconhecimento_status_id'; $options = $dominios['statusReconhecimento']; require view_path('decretos/partials/select'); ?>
            </div>
            <div class="field"><label>Protocolo PAE/PGE</label><input name="protocolo_pae_pge" value="<?= e(old('protocolo_pae_pge', $registro['protocolo_pae_pge'] ?? '')); ?>"></div>
            <div class="field"><label>Data envio PGE</label><input name="data_envio_pge" type="date" value="<?= e(old('data_envio_pge', $registro['data_envio_pge'] ?? '')); ?>"></div>
            <div class="field">
                <label>Status envio PGE</label>
                <?php $name = 'status_envio_pge_id'; $options = $dominios['statusEnvioPge']; require view_path('decretos/partials/select'); ?>
            </div>
            <div class="field">
                <label>Analista</label>
                <select name="analista_id">
                    <option value="">Nao informado</option>
                    <?php foreach ($dominios['analistas'] as $analista): ?>
                        <?php $selected = (string) old('analista_id', $registro['analista_id'] ?? '') === (string) $analista['id']; ?>
                        <option value="<?= e($analista['id']); ?>" <?= $selected ? 'selected' : ''; ?>><?= e($analista['nome']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </fieldset>

    <fieldset class="span-2">
        <legend>Recursos e danos humanos</legend>
        <div class="form-grid inner">
            <div class="field">
                <label>Recurso resposta</label>
                <?php $name = 'recurso_resposta_status_id'; $options = $dominios['statusRecurso']; require view_path('decretos/partials/select'); ?>
            </div>
            <div class="field">
                <label>Recurso reconstrucao</label>
                <?php $name = 'recurso_reconstrucao_status_id'; $options = $dominios['statusRecurso']; require view_path('decretos/partials/select'); ?>
            </div>
            <?php foreach (['numero_obitos' => 'Obitos', 'numero_feridos' => 'Feridos', 'numero_enfermos' => 'Enfermos', 'numero_desabrigados' => 'Desabrigados', 'numero_desalojados' => 'Desalojados', 'numero_outros_afetados' => 'Outros afetados'] as $field => $label): ?>
                <div class="field">
                    <label><?= e($label); ?></label>
                    <input class="affected-input" name="<?= e($field); ?>" type="number" min="0" value="<?= e(old($field, $registro[$field] ?? '0')); ?>">
                </div>
            <?php endforeach; ?>
            <div class="field">
                <label>Total de afetados</label>
                <input id="total-afetados-preview" value="0" disabled>
            </div>
        </div>
    </fieldset>

    <div class="field span-2">
        <label>Observacoes</label>
        <textarea name="observacoes" rows="4"><?= e(old('observacoes', $registro['observacoes'] ?? '')); ?></textarea>
    </div>

    <div class="form-actions span-2">
        <button type="submit" class="button button-primary"><?= e($submit); ?></button>
        <a class="button button-light" href="<?= e(url('/decretos')); ?>">Cancelar</a>
    </div>
</form>
