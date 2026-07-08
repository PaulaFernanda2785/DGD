<form method="post" action="<?= e($action); ?>" enctype="multipart/form-data" class="form-grid decree-form decree-form-modern" data-history-modal data-history-summary="<?= e(!empty($registro['id']) ? 'Edição do decreto ' . ($registro['protocolo_dgd'] ?? '') : 'Cadastro de novo decreto'); ?>">
    <?php
        $formatDate = static fn (mixed $value): string => !empty($value) ? date('d/m/Y', strtotime((string) $value)) : '-';
        $dash = static fn (mixed $value): string => trim((string) $value) !== '' ? (string) $value : '-';
    ?>
    <?= csrf_input(); ?>
    <input type="hidden" name="historico_observacao" data-history-observation>

    <div class="form-intro span-2">
        <div>
            <span>Formulário operacional</span>
            <strong>Preencha os dados essenciais antes de salvar o registro.</strong>
        </div>
        <p><span class="required-dot"></span> Campos marcados como obrigatórios precisam ser informados para gerar o protocolo DGD.</p>
    </div>

    <?php if (!empty($registro['protocolo_dgd'])): ?>
        <div class="field field-highlight span-2">
            <label>Protocolo DGD</label>
            <input value="<?= e($registro['protocolo_dgd']); ?>" disabled>
        </div>
    <?php endif; ?>

    <fieldset class="span-2 form-section">
        <legend>Identificação e localização</legend>
        <div class="form-section-heading">
            <div>
                <span>01</span>
                <h2>Município e dados da COMPDEC</h2>
            </div>
            <p>Ao selecionar o município, o sistema preenche automaticamente a UBM atuante e os dados da COMPDEC quando houver registro.</p>
        </div>
        <div class="form-grid inner">
            <div class="field">
                <label for="municipio_id">Município <span class="required-label">Obrigatório</span></label>
                <select id="municipio_id" name="municipio_id" required data-ubm-municipio>
                    <option value="">Selecione</option>
                    <?php foreach ($dominios['municipios'] as $municipio): ?>
                        <?php $selected = (string) old('municipio_id', $registro['municipio_id'] ?? '') === (string) $municipio['id']; ?>
                        <option value="<?= e($municipio['id']); ?>" <?= $selected ? 'selected' : ''; ?>><?= e($municipio['nome']); ?></option>
                    <?php endforeach; ?>
                </select>
                <small>Define o município do desastre e carrega automaticamente os dados da COMPDEC.</small>
            </div>

            <div class="field">
                <label for="ubm_atuante">UBM atuante</label>
                <input id="ubm_atuante" value="<?= e(old('ubm_atuante', $registro['ubm_atuante'] ?? 'Não foi registrado')); ?>" readonly data-compdec-field="ubm_nome">
            </div>

            <div class="field">
                <label for="compdec_situacao">Situação da COMPDEC</label>
                <input id="compdec_situacao" value="<?= e(old('compdec_situacao', isset($registro['compdec_id']) && $registro['compdec_id'] ? 'Possui COMPDEC' : 'Não foi registrado')); ?>" readonly data-compdec-field="situacao_compdec">
            </div>

            <div class="field">
                <label for="compdec_regiao_integracao">Região de integração</label>
                <input id="compdec_regiao_integracao" name="compdec_regiao_integracao" value="<?= e(old('compdec_regiao_integracao', $registro['compdec_regiao_integracao'] ?? '')); ?>" readonly data-compdec-field="regiao_integracao">
            </div>

            <div class="field">
                <label for="compdec_prefeito">Prefeito</label>
                <input id="compdec_prefeito" name="compdec_prefeito" value="<?= e(old('compdec_prefeito', $registro['compdec_prefeito'] ?? '')); ?>" readonly data-compdec-field="prefeito">
            </div>

            <div class="field">
                <label for="compdec_coordenador">Coordenador</label>
                <input id="compdec_coordenador" name="compdec_coordenador" value="<?= e(old('compdec_coordenador', $registro['compdec_coordenador'] ?? '')); ?>" readonly data-compdec-field="coordenador">
            </div>

            <div class="field">
                <label for="compdec_telefone">Telefone</label>
                <input id="compdec_telefone" name="compdec_telefone" value="<?= e(old('compdec_telefone', $registro['compdec_telefone'] ?? '')); ?>" readonly data-compdec-field="telefone">
            </div>

            <div class="field span-2">
                <label for="compdec_email">E-mail</label>
                <input id="compdec_email" name="compdec_email" value="<?= e(old('compdec_email', $registro['compdec_email'] ?? '')); ?>" readonly data-compdec-field="email">
            </div>

            <div class="field span-2 decree-type-field">
                <div class="decree-type-heading">
                    <span class="field-label">Tipo de decreto <span class="required-label">Obrigatório</span></span>
                    <small>Informe se o ato municipal é de emergência ou calamidade.</small>
                </div>
                <div class="segmented-options decree-type-options" role="radiogroup" aria-label="Tipo de decreto">
                    <?php foreach ($dominios['tiposDecreto'] as $index => $tipo): ?>
                        <?php $selected = (string) old('tipo_decreto_id', $registro['tipo_decreto_id'] ?? '') === (string) $tipo['id']; ?>
                        <label class="segmented-option">
                            <input
                                type="radio"
                                name="tipo_decreto_id"
                                value="<?= e($tipo['id']); ?>"
                                <?= $index === 0 ? 'required' : ''; ?>
                                <?= $selected ? 'checked' : ''; ?>
                            >
                            <span><?= e($tipo['nome']); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="field">
                <label for="data_desastre">Data do desastre <span class="required-label">Obrigatório</span></label>
                <input id="data_desastre" name="data_desastre" type="date" value="<?= e(old('data_desastre', $registro['data_desastre'] ?? '')); ?>" required>
                <small>Não pode ser uma data futura.</small>
            </div>
        </div>
    </fieldset>

    <fieldset class="span-2 form-section">
        <legend>COBRADE</legend>
        <div class="form-section-heading">
            <div>
                <span>02</span>
                <h2>Classificação oficial COBRADE</h2>
            </div>
            <p>Selecione a sequência oficial do evento. Cada escolha libera o próximo nível da classificação.</p>
        </div>
        <div class="cobrade-step-grid">
            <label class="modern-field" for="cobrade_grupo_id">
                <span>Grupo COBRADE <strong>Obrigatório</strong></span>
                <select id="cobrade_grupo_id" name="cobrade_grupo_id" data-cobrade="grupo" required>
                    <option value="">Selecione</option>
                    <?php foreach ($dominios['cobradeGrupos'] as $grupo): ?>
                        <?php $selected = (string) old('cobrade_grupo_id', $registro['cobrade_grupo_id'] ?? '') === (string) $grupo['id']; ?>
                        <option value="<?= e($grupo['id']); ?>" <?= $selected ? 'selected' : ''; ?>><?= e(($grupo['codigo'] ? $grupo['codigo'] . ' - ' : '') . $grupo['nome']); ?></option>
                    <?php endforeach; ?>
                </select>
                <small>Grande grupo do desastre.</small>
            </label>

            <label class="modern-field" for="cobrade_subgrupo_id">
                <span>Subgrupo COBRADE <strong>Obrigatório</strong></span>
                <select id="cobrade_subgrupo_id" name="cobrade_subgrupo_id" data-cobrade="subgrupo" data-current="<?= e(old('cobrade_subgrupo_id', $registro['cobrade_subgrupo_id'] ?? '')); ?>" required disabled>
                    <option value="">Selecione um grupo primeiro</option>
                    <?php foreach ($dominios['cobradeSubgrupos'] ?? [] as $subgrupo): ?>
                        <?php $selected = (string) old('cobrade_subgrupo_id', $registro['cobrade_subgrupo_id'] ?? '') === (string) $subgrupo['id']; ?>
                        <option value="<?= e($subgrupo['id']); ?>" data-grupo-id="<?= e($subgrupo['grupo_id']); ?>" <?= $selected ? 'selected' : ''; ?>>
                            <?= e(($subgrupo['codigo'] ? $subgrupo['codigo'] . ' - ' : '') . $subgrupo['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small>Filtrado pelo grupo selecionado.</small>
            </label>

            <label class="modern-field" for="cobrade_tipo_id">
                <span>Tipo COBRADE <strong>Obrigatório</strong></span>
                <select id="cobrade_tipo_id" name="cobrade_tipo_id" data-cobrade="tipo" data-current="<?= e(old('cobrade_tipo_id', $registro['cobrade_tipo_id'] ?? '')); ?>" required disabled>
                    <option value="">Selecione um subgrupo primeiro</option>
                    <?php foreach ($dominios['cobradeTipos'] ?? [] as $tipo): ?>
                        <?php $selected = (string) old('cobrade_tipo_id', $registro['cobrade_tipo_id'] ?? '') === (string) $tipo['id']; ?>
                        <option value="<?= e($tipo['id']); ?>" data-subgrupo-id="<?= e($tipo['subgrupo_id']); ?>" <?= $selected ? 'selected' : ''; ?>>
                            <?= e(($tipo['codigo'] ? $tipo['codigo'] . ' - ' : '') . $tipo['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small>Categoria específica do evento.</small>
            </label>

            <label class="modern-field" for="cobrade_subtipo_id">
                <span>Subtipo COBRADE <strong>Obrigatório</strong></span>
                <select id="cobrade_subtipo_id" name="cobrade_subtipo_id" data-cobrade="subtipo" data-current="<?= e(old('cobrade_subtipo_id', $registro['cobrade_subtipo_id'] ?? '')); ?>" required disabled>
                    <option value="">Selecione um tipo primeiro</option>
                    <?php foreach ($dominios['cobradeSubtipos'] ?? [] as $subtipo): ?>
                        <?php $selected = (string) old('cobrade_subtipo_id', $registro['cobrade_subtipo_id'] ?? '') === (string) $subtipo['id']; ?>
                        <option
                            value="<?= e($subtipo['id']); ?>"
                            data-grupo-id="<?= e($subtipo['grupo_id']); ?>"
                            data-subgrupo-id="<?= e($subtipo['subgrupo_id']); ?>"
                            data-tipo-id="<?= e($subtipo['tipo_id']); ?>"
                            data-codigo="<?= e($subtipo['codigo']); ?>"
                            data-nome="<?= e($subtipo['nome']); ?>"
                            data-grupo-nome="<?= e($subtipo['grupo_nome']); ?>"
                            data-subgrupo-nome="<?= e($subtipo['subgrupo_nome']); ?>"
                            data-tipo-nome="<?= e($subtipo['tipo_nome']); ?>"
                            data-descricao="<?= e($subtipo['descricao']); ?>"
                            data-simbologia="<?= e($subtipo['simbologia']); ?>"
                            <?= $selected ? 'selected' : ''; ?>
                        >
                            <?= e(($subtipo['codigo'] ? $subtipo['codigo'] . ' - ' : '') . $subtipo['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small>A simbologia oficial aparece abaixo.</small>
            </label>
        </div>

        <div class="cobrade-preview modern-cobrade-preview" data-cobrade-preview hidden>
            <div class="cobrade-symbol-frame">
                <img src="" alt="" data-cobrade-preview-img hidden>
                <span data-cobrade-preview-symbol>COBRADE</span>
            </div>
            <div>
                <span>Classificação selecionada</span>
                <strong data-cobrade-preview-title></strong>
                <small data-cobrade-preview-meta></small>
                <p id="cobrade-descricao" data-cobrade-preview-descricao></p>
            </div>
        </div>
    </fieldset>

    <fieldset class="span-2 form-section">
        <legend>Decreto, homologação, reconhecimento e PGE</legend>
        <div class="form-section-heading">
            <div>
                <span>03</span>
                <h2>Atos e acompanhamento institucional</h2>
            </div>
            <p>Informe protocolos, datas e situações de homologação, reconhecimento federal, envio à PGE e análise técnica.</p>
        </div>
        <div class="form-grid inner">
            <div class="field"><label>Protocolo S2ID</label><input name="protocolo_s2id" value="<?= e(old('protocolo_s2id', $registro['protocolo_s2id'] ?? '')); ?>"></div>
            <div class="field"><label>Número do decreto municipal</label><input name="numero_decreto_municipal" value="<?= e(old('numero_decreto_municipal', $registro['numero_decreto_municipal'] ?? '')); ?>"></div>
            <div class="field"><label>Data do decreto municipal</label><input name="data_decreto_municipal" type="date" value="<?= e(old('data_decreto_municipal', $registro['data_decreto_municipal'] ?? '')); ?>"></div>
            <div class="field"><label>Número do decreto estadual</label><input name="numero_decreto_homologacao_estadual" value="<?= e(old('numero_decreto_homologacao_estadual', $registro['numero_decreto_homologacao_estadual'] ?? '')); ?>"></div>
            <div class="field"><label>Data de homologação</label><input name="data_decreto_homologacao" type="date" value="<?= e(old('data_decreto_homologacao', $registro['data_decreto_homologacao'] ?? '')); ?>"></div>
            <div class="field">
                <label>Homologação</label>
                <?php $name = 'homologacao_status_id'; $options = $dominios['statusHomologacao']; require view_path('decretos/partials/select'); ?>
            </div>
            <div class="field">
                <label>Reconhecimento</label>
                <?php $name = 'reconhecimento_status_id'; $options = $dominios['statusReconhecimento']; require view_path('decretos/partials/select'); ?>
            </div>
            <div class="field"><label>Protocolo PAE/PGE</label><input name="protocolo_pae_pge" value="<?= e(old('protocolo_pae_pge', $registro['protocolo_pae_pge'] ?? '')); ?>"></div>
            <div class="field pge-date-form-field" data-pge-date-form-field hidden>
                <label>Data de envio à PGE</label>
                <input name="data_envio_pge" type="date" value="<?= e(old('data_envio_pge', $registro['data_envio_pge'] ?? '')); ?>" data-pge-date-input>
                <small>Informe a data oficial do envio quando o status for Enviado à PGE.</small>
            </div>
            <div class="field">
                <label>Status de envio à PGE</label>
                <?php $name = 'status_envio_pge_id'; $options = $dominios['statusEnvioPge']; require view_path('decretos/partials/select'); ?>
                <small>Ao selecionar Enviado à PGE, informe a data oficial de envio.</small>
            </div>
            <div class="pge-consistency-panel span-2" aria-label="Resumo operacional da PGE">
                <div>
                    <span>Status de envio</span>
                    <?= status_badge($registro['status_envio_pge'] ?? 'Não registrado'); ?>
                </div>
                <div>
                    <span>Data de envio</span>
                    <strong><?= e($formatDate($registro['data_envio_pge'] ?? null)); ?></strong>
                </div>
                <div>
                    <span>Data de conclusão</span>
                    <strong><?= e($formatDate($registro['data_conclusao_pge'] ?? null)); ?></strong>
                </div>
                <div>
                    <span>Dias PGE</span>
                    <strong><?= e($dash($registro['duracao_pge_dias'] ?? null)); ?></strong>
                </div>
                <div>
                    <span>Prazo PGE</span>
                    <?= status_badge($registro['status_prazo_pge_calculado'] ?? 'Não iniciado'); ?>
                </div>
            </div>
            <div class="field">
                <label>Analista</label>
                <select name="analista_id">
                    <option value="">Não informado</option>
                    <?php foreach ($dominios['analistas'] as $analista): ?>
                        <?php $selected = (string) old('analista_id', $registro['analista_id'] ?? '') === (string) $analista['id']; ?>
                        <option value="<?= e($analista['id']); ?>" <?= $selected ? 'selected' : ''; ?>><?= e($analista['nome']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </fieldset>

    <fieldset class="span-2 form-section">
        <legend>Recursos e danos humanos</legend>
        <div class="form-section-heading">
            <div>
                <span>04</span>
                <h2>Recursos e danos humanos</h2>
            </div>
            <p>Atualize o status dos recursos e os quantitativos de pessoas afetadas. O total é calculado automaticamente.</p>
        </div>
        <div class="form-grid inner">
            <div class="field">
                <label>Recurso de resposta</label>
                <?php $name = 'recurso_resposta_status_id'; $options = $dominios['statusRecurso']; require view_path('decretos/partials/select'); ?>
            </div>
            <div class="field">
                <label>Recurso de reconstrução</label>
                <?php $name = 'recurso_reconstrucao_status_id'; $options = $dominios['statusRecurso']; require view_path('decretos/partials/select'); ?>
            </div>
            <?php foreach (['numero_obitos' => 'Óbitos', 'numero_feridos' => 'Feridos', 'numero_enfermos' => 'Enfermos', 'numero_desabrigados' => 'Desabrigados', 'numero_desalojados' => 'Desalojados', 'numero_outros_afetados' => 'Outros afetados'] as $field => $label): ?>
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

    <div class="field span-2 form-section standalone-field">
        <div class="form-section-heading">
            <div>
                <span>05</span>
                <h2>Observações complementares</h2>
            </div>
            <p>Registre informações relevantes que não estejam cobertas pelos campos estruturados.</p>
        </div>
        <label>Observações</label>
        <textarea name="observacoes" rows="4"><?= e(old('observacoes', $registro['observacoes'] ?? '')); ?></textarea>
    </div>

    <fieldset class="span-2 form-section">
        <legend>Anexos previstos</legend>
        <div class="form-section-heading">
            <div>
                <span>06</span>
                <h2>Documentos e evidências</h2>
            </div>
            <p>Anexe os documentos previstos por tipo. Você pode selecionar, arrastar ou colar arquivos em cada bloco.</p>
        </div>
        <div class="attachment-grid" data-attachment-area>
            <?php foreach ($dominios['tiposAnexo'] as $tipoAnexo): ?>
                <?php
                    $inputId = 'anexo_tipo_' . (int) $tipoAnexo['id'];
                    $descricaoId = 'anexo_descricao_' . (int) $tipoAnexo['id'];
                ?>
                <div class="attachment-dropzone" data-attachment-zone tabindex="0">
                    <div class="attachment-head">
                        <strong><?= e($tipoAnexo['nome']); ?></strong>
                        <?php if ((int) ($tipoAnexo['obrigatorio'] ?? 0) === 1): ?>
                            <span>Previsto</span>
                        <?php endif; ?>
                    </div>

                    <p>Selecione, arraste ou cole arquivos neste bloco.</p>

                    <input
                        id="<?= e($inputId); ?>"
                        class="attachment-input"
                        type="file"
                        name="anexos[<?= e($tipoAnexo['id']); ?>][]"
                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                        multiple
                        data-attachment-input
                    >

                    <label class="button button-light" for="<?= e($inputId); ?>">Selecionar arquivos</label>

                    <input
                        id="<?= e($descricaoId); ?>"
                        name="anexo_descricao[<?= e($tipoAnexo['id']); ?>]"
                        placeholder="Descrição opcional"
                    >

                    <ul class="attachment-list" data-attachment-list>
                        <li>Nenhum arquivo selecionado.</li>
                    </ul>
                </div>
            <?php endforeach; ?>
        </div>
    </fieldset>

    <div class="form-actions form-actions-sticky span-2">
        <button type="submit" class="button button-primary"><?= e($submit); ?></button>
        <a class="button button-light" href="<?= e(url('/decretos')); ?>">Cancelar</a>
    </div>
</form>
