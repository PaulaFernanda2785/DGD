<?php
    $possuiCompdec = (string) old('tem_compdec', $compdec['tem_compdec'] ?? '0');
    $compdecLatitude = (string) old('latitude', $compdec['latitude'] ?? '');
    $compdecLongitude = (string) old('longitude', $compdec['longitude'] ?? '');
    $ubmId = (int) old('ubm_id', $ubm['id'] ?? 0);
    $ubmLatitude = (string) old('ubm_latitude', $ubm['latitude'] ?? '');
    $ubmLongitude = (string) old('ubm_longitude', $ubm['longitude'] ?? '');
    $ubmAtiva = (string) old('ubm_ativo', (string) ($ubm['ativo'] ?? '1'));
    $fotoUrl = compdec_photo_url($compdec['foto_coordenador'] ?? null);
    $mapData = [
        'compdec' => [
            'latitude' => $compdecLatitude,
            'longitude' => $compdecLongitude,
            'label' => (string) ($compdec['municipio'] ?? 'COMPDEC'),
        ],
        'ubm' => [
            'id' => $ubmId > 0 ? $ubmId : null,
            'latitude' => $ubmLatitude,
            'longitude' => $ubmLongitude,
            'label' => (string) old('ubm_nome', $compdec['ubm_nome'] ?? 'UBM'),
        ],
        'ubms' => array_map(static fn (array $item): array => [
            'id' => (int) ($item['id'] ?? 0),
            'nome' => (string) ($item['nome'] ?? ''),
            'municipio' => (string) ($item['municipio'] ?? ''),
            'regiao_integracao' => (string) ($item['regiao_integracao'] ?? ''),
            'latitude' => $item['latitude'] !== null ? (string) $item['latitude'] : '',
            'longitude' => $item['longitude'] !== null ? (string) $item['longitude'] : '',
            'ativo' => (int) ($item['ativo'] ?? 1) === 1,
        ], $ubmOptions ?? []),
    ];
?>

<div class="page-header page-header-modern compdec-edit-header">
    <div>
        <span class="breadcrumb">COMPDECs &gt; Editar</span>
        <h1><?= e($compdec['municipio']); ?></h1>
        <p>Atualize cadastro municipal, coordenador, foto, ponto da COMPDEC e UBM vinculada.</p>
    </div>

    <div class="actions">
        <a class="button button-light" href="<?= e(url('/compdecs/' . $compdec['id'])); ?>">Voltar</a>
    </div>
</div>

<?php require view_path('components/form_errors'); ?>

<form method="post" action="<?= e(url('/compdecs/' . $compdec['id'] . '/editar')); ?>" enctype="multipart/form-data" class="form-grid compdec-edit-form">
    <?= csrf_input(); ?>

    <section class="span-2 form-section compdec-edit-summary-section">
        <div class="compdec-edit-summary-grid">
            <article>
                <span>Município</span>
                <strong><?= e($compdec['municipio']); ?></strong>
                <small><?= e($compdec['regiao_integracao'] ?? 'Região não informada'); ?></small>
            </article>
            <article>
                <span>Situação</span>
                <strong><?= $possuiCompdec === '1' ? 'Possui COMPDEC' : 'Não possui COMPDEC'; ?></strong>
                <small>Código IBGE: <?= e($compdec['municipio_codigo'] ?? '-'); ?></small>
            </article>
            <article>
                <span>UBM atual</span>
                <strong><?= e($compdec['ubm_nome'] ?? 'Não foi registrado'); ?></strong>
                <small>Seleção inteligente pela base local.</small>
            </article>
        </div>
    </section>

    <section class="span-2 form-section compdec-edit-profile-section">
        <div class="form-section-heading">
            <div>
                <span>01</span>
                <h2>Coordenador e foto</h2>
            </div>
            <p>Atualize o responsável municipal e substitua a foto por seleção, arrastar ou colar imagem.</p>
        </div>

        <div class="compdec-edit-profile">
            <div class="compdec-photo-uploader" data-compdec-photo-dropzone tabindex="0" role="button" aria-describedby="coordenadorFotoFeedback">
                <div class="compdec-photo-preview" data-compdec-photo-preview data-empty="<?= $fotoUrl === null ? '1' : '0'; ?>">
                    <?php if ($fotoUrl !== null): ?>
                        <img src="<?= e($fotoUrl); ?>" alt="<?= e($compdec['coordenador'] ?? 'Foto do coordenador'); ?>">
                    <?php else: ?>
                        <span>Sem foto cadastrada</span>
                    <?php endif; ?>
                </div>
                <div class="compdec-photo-controls">
                    <span class="compdec-photo-title">Foto do coordenador</span>
                    <span class="compdec-photo-text">Arraste uma imagem, cole do clipboard ou clique para selecionar.</span>
                    <input id="foto_coordenador" name="foto_coordenador" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" data-compdec-photo-input>
                    <small class="field-helper" id="coordenadorFotoFeedback" data-compdec-photo-feedback>Formatos aceitos: JPG, PNG ou WEBP. Limite de 5 MB.</small>
                    <button type="button" class="button button-light" data-compdec-photo-clear hidden>Descartar nova foto</button>
                </div>
            </div>

            <div class="compdec-edit-grid">
                <div class="field">
                    <label for="coordenador">Coordenador</label>
                    <input id="coordenador" name="coordenador" maxlength="180" value="<?= e(old('coordenador', $compdec['coordenador'] ?? '')); ?>" placeholder="Nome do coordenador">
                </div>

                <div class="field">
                    <label for="tem_compdec">Situação da COMPDEC</label>
                    <select id="tem_compdec" name="tem_compdec">
                        <option value="1" <?= $possuiCompdec === '1' ? 'selected' : ''; ?>>Possui COMPDEC</option>
                        <option value="0" <?= $possuiCompdec === '0' ? 'selected' : ''; ?>>Não possui COMPDEC</option>
                    </select>
                </div>

                <div class="field">
                    <label for="telefone">Telefone</label>
                    <input id="telefone" name="telefone" maxlength="80" value="<?= e(old('telefone', $compdec['telefone'] ?? '')); ?>" placeholder="(00) 00000-0000">
                </div>

                <div class="field">
                    <label for="email">E-mail</label>
                    <input id="email" name="email" type="email" maxlength="180" value="<?= e(old('email', $compdec['email'] ?? '')); ?>" placeholder="email@dominio.gov.br">
                </div>

                <div class="field compdec-field-full">
                    <label for="endereco">Endereço</label>
                    <input id="endereco" name="endereco" maxlength="255" value="<?= e(old('endereco', $compdec['endereco'] ?? '')); ?>" placeholder="Endereço da COMPDEC ou da prefeitura">
                </div>
            </div>
        </div>
    </section>

    <section class="span-2 form-section compdec-edit-location-section">
        <div class="form-section-heading">
            <div>
                <span>02</span>
                <h2>Município e ponto da COMPDEC</h2>
            </div>
            <p>Dados territoriais e geolocalização municipal usada como referência operacional.</p>
        </div>

        <div class="compdec-edit-location-layout">
            <div class="compdec-edit-grid">
                <div class="field">
                    <label>Município</label>
                    <input value="<?= e($compdec['municipio']); ?>" readonly>
                </div>

                <div class="field">
                    <label>Código IBGE</label>
                    <input value="<?= e($compdec['municipio_codigo'] ?? ''); ?>" readonly>
                </div>

                <div class="field">
                    <label for="regiao_integracao">Região de integração</label>
                    <input id="regiao_integracao" name="regiao_integracao" maxlength="180" value="<?= e(old('regiao_integracao', $compdec['regiao_integracao'] ?? '')); ?>" placeholder="Região de integração">
                </div>

                <div class="field">
                    <label for="prefeito">Prefeito</label>
                    <input id="prefeito" name="prefeito" maxlength="180" value="<?= e(old('prefeito', $compdec['prefeito'] ?? '')); ?>" placeholder="Nome do prefeito">
                </div>

                <div class="field">
                    <label for="data_atualizacao">Data de atualização</label>
                    <input id="data_atualizacao" name="data_atualizacao" value="<?= e(old('data_atualizacao', $compdec['data_atualizacao'] ?? '')); ?>" placeholder="Atualização automática" readonly>
                </div>

                <div class="field">
                    <label for="latitude">Latitude da COMPDEC</label>
                    <input id="compdec_latitude" name="latitude" inputmode="decimal" value="<?= e($compdecLatitude); ?>" placeholder="-1.45500000">
                </div>

                <div class="field">
                    <label for="longitude">Longitude da COMPDEC</label>
                    <input id="compdec_longitude" name="longitude" inputmode="decimal" value="<?= e($compdecLongitude); ?>" placeholder="-48.49000000">
                </div>
            </div>

            <div class="compdec-map-card">
                <div class="compdec-map-toolbar">
                    <button type="button" class="button button-light" id="compdec-open-map">Atualizar mapa</button>
                    <button type="button" class="button button-light" id="compdec-use-current">Usar localização atual</button>
                    <button type="button" class="button button-light" id="compdec-clear">Limpar ponto</button>
                </div>
                <div id="compdec-map" class="compdec-map"></div>
                <p id="compdec-map-feedback" class="field-helper">Clique no mapa ou arraste o marcador para ajustar o ponto da COMPDEC.</p>
            </div>
        </div>
    </section>

    <section class="span-2 form-section compdec-edit-ubm-section">
        <div class="form-section-heading">
            <div>
                <span>03</span>
                <h2>UBM vinculada e geolocalização</h2>
            </div>
            <p>Selecione uma UBM da base local para preencher coordenadas automaticamente ou ajuste manualmente.</p>
        </div>

        <div class="compdec-edit-location-layout">
            <div>
                <div class="compdec-edit-grid">
                    <div class="field compdec-field-full">
                        <label for="ubm_nome">UBM vinculada</label>
                        <input type="hidden" id="ubm_id" name="ubm_id" value="<?= $ubmId; ?>">
                        <input id="ubm_nome" name="ubm_nome" maxlength="180" value="<?= e(old('ubm_nome', $compdec['ubm_nome'] ?? '')); ?>" autocomplete="off" placeholder="Digite para localizar uma UBM cadastrada">
                        <div class="compdec-ubm-smart-panel" id="ubm-smart-panel" data-state="neutral">
                            <strong>Seleção inteligente da UBM</strong>
                            <span>Digite o nome da unidade e selecione uma opção cadastrada para preencher latitude e longitude automaticamente.</span>
                        </div>
                    </div>

                    <div class="field">
                        <label for="ubm_latitude">Latitude da UBM</label>
                        <input id="ubm_latitude" name="ubm_latitude" inputmode="decimal" value="<?= e($ubmLatitude); ?>" placeholder="-1.45500000">
                    </div>

                    <div class="field">
                        <label for="ubm_longitude">Longitude da UBM</label>
                        <input id="ubm_longitude" name="ubm_longitude" inputmode="decimal" value="<?= e($ubmLongitude); ?>" placeholder="-48.49000000">
                    </div>
                </div>

                <div class="compdec-status-card">
                    <span class="compdec-status-label">Exibição operacional da UBM</span>
                    <div class="compdec-status-options" role="radiogroup" aria-label="Status operacional da UBM">
                        <label class="compdec-status-option">
                            <input type="radio" name="ubm_ativo" value="1" <?= $ubmAtiva === '1' ? 'checked' : ''; ?>>
                            <span><strong>Ativa</strong><small>Aparece nas camadas operacionais.</small></span>
                        </label>
                        <label class="compdec-status-option">
                            <input type="radio" name="ubm_ativo" value="0" <?= $ubmAtiva === '0' ? 'checked' : ''; ?>>
                            <span><strong>Inativa</strong><small>Oculta das camadas operacionais.</small></span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="compdec-map-card">
                <div class="compdec-map-toolbar">
                    <button type="button" class="button button-light" id="ubm-open-map">Atualizar mapa</button>
                    <button type="button" class="button button-light" id="ubm-use-compdec">Usar ponto da COMPDEC</button>
                    <button type="button" class="button button-light" id="ubm-use-current">Usar localização atual</button>
                    <button type="button" class="button button-light" id="ubm-clear">Limpar ponto</button>
                </div>
                <div id="ubm-map" class="compdec-map compdec-map-large"></div>
                <p id="ubm-map-feedback" class="field-helper">Clique no mapa ou arraste o ícone da unidade para ajustar a UBM.</p>
            </div>
        </div>
    </section>

    <div class="form-actions form-actions-sticky span-2">
        <button type="submit" class="button button-primary">Salvar alterações</button>
        <a class="button button-light" href="<?= e(url('/compdecs/' . $compdec['id'])); ?>">Cancelar</a>
    </div>
</form>

<script type="application/json" id="compdec-form-map-data"><?= json_encode($mapData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?></script>
