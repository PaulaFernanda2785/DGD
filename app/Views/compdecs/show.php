<?php
    $value = static function (mixed $content): string {
        $content = trim((string) ($content ?? ''));

        return $content !== '' ? $content : 'Não foi registrado';
    };
    $hasCompdec = (int) ($compdec['tem_compdec'] ?? 0) === 1;
    $fotoUrl = compdec_photo_url($compdec['foto_coordenador'] ?? null);
    $compdecLatitude = (string) ($compdec['latitude'] ?? '');
    $compdecLongitude = (string) ($compdec['longitude'] ?? '');
    $ubmLatitude = (string) ($ubm['latitude'] ?? '');
    $ubmLongitude = (string) ($ubm['longitude'] ?? '');
    $ubmActive = (int) ($ubm['ativo'] ?? 1) === 1;
    $mapData = [
        'compdec' => [
            'latitude' => $compdecLatitude,
            'longitude' => $compdecLongitude,
            'label' => (string) ($compdec['municipio'] ?? 'COMPDEC'),
        ],
        'ubm' => [
            'id' => isset($ubm['id']) ? (int) $ubm['id'] : null,
            'latitude' => $ubmLatitude,
            'longitude' => $ubmLongitude,
            'label' => (string) ($compdec['ubm_nome'] ?? 'UBM'),
        ],
        'ubms' => [],
    ];
?>

<div class="page-header page-header-modern compdec-show-header">
    <div>
        <span class="breadcrumb">COMPDECs &gt; Detalhe</span>
        <h1><?= e($compdec['municipio']); ?></h1>
        <p>Visualize os dados institucionais da COMPDEC, contatos, endereço, UBM vinculada e pontos georreferenciados.</p>
    </div>

    <div class="actions">
        <a class="button button-light" href="<?= e(url('/compdecs')); ?>">Voltar</a>
        <?php if (can('compdecs.editar')): ?>
            <a class="button button-primary" href="<?= e(url('/compdecs/' . $compdec['id'] . '/editar')); ?>">Editar</a>
        <?php endif; ?>
    </div>
</div>

<section class="form-section compdec-show-summary-section">
    <div class="compdec-edit-summary-grid">
        <article>
            <span>Município</span>
            <strong><?= e($compdec['municipio']); ?></strong>
            <small>Código IBGE: <?= e($value($compdec['municipio_codigo'] ?? null)); ?></small>
        </article>
        <article>
            <span>Situação</span>
            <strong><?= $hasCompdec ? 'Possui COMPDEC' : 'Não possui COMPDEC'; ?></strong>
            <small><?= e($value($compdec['regiao_integracao'] ?? null)); ?></small>
        </article>
        <article>
            <span>UBM vinculada</span>
            <strong><?= e($value($compdec['ubm_nome'] ?? null)); ?></strong>
            <small><?= $ubmActive ? 'Ativa nas camadas operacionais' : 'Inativa nas camadas operacionais'; ?></small>
        </article>
    </div>
</section>

<section class="form-section compdec-show-profile-section">
    <div class="form-section-heading">
        <div>
            <span>01</span>
            <h2>Coordenador e contato</h2>
        </div>
        <p>Dados do responsável municipal, canais de contato e endereço institucional registrado.</p>
    </div>

    <div class="compdec-show-profile">
        <div class="compdec-show-photo">
            <?php if ($fotoUrl !== null): ?>
                <img src="<?= e($fotoUrl); ?>" alt="<?= e($value($compdec['coordenador'] ?? null)); ?>">
            <?php else: ?>
                <span>Sem foto cadastrada</span>
            <?php endif; ?>
        </div>

        <div class="compdec-show-detail-grid">
            <article>
                <span>Coordenador</span>
                <strong><?= e($value($compdec['coordenador'] ?? null)); ?></strong>
            </article>
            <article>
                <span>Telefone</span>
                <strong><?= e($value($compdec['telefone'] ?? null)); ?></strong>
            </article>
            <article>
                <span>E-mail</span>
                <strong><?= e($value($compdec['email'] ?? null)); ?></strong>
            </article>
            <article>
                <span>Prefeito</span>
                <strong><?= e($value($compdec['prefeito'] ?? null)); ?></strong>
            </article>
            <article class="compdec-show-detail-wide">
                <span>Endereço</span>
                <strong><?= e($value($compdec['endereco'] ?? null)); ?></strong>
            </article>
        </div>
    </div>
</section>

<section class="form-section compdec-show-location-section">
    <div class="form-section-heading">
        <div>
            <span>02</span>
            <h2>Município e ponto da COMPDEC</h2>
        </div>
        <p>Dados territoriais, atualização cadastral e ponto geográfico da COMPDEC.</p>
    </div>

    <div class="compdec-show-location-layout">
        <div class="compdec-show-detail-grid">
            <article>
                <span>Região de integração</span>
                <strong><?= e($value($compdec['regiao_integracao'] ?? null)); ?></strong>
            </article>
            <article>
                <span>Data de atualização</span>
                <strong><?= e($value($compdec['data_atualizacao'] ?? null)); ?></strong>
            </article>
            <article>
                <span>Latitude da COMPDEC</span>
                <strong><?= e($value($compdecLatitude)); ?></strong>
            </article>
            <article>
                <span>Longitude da COMPDEC</span>
                <strong><?= e($value($compdecLongitude)); ?></strong>
            </article>
        </div>

        <div class="compdec-map-card">
            <input id="compdec_latitude" type="hidden" value="<?= e($compdecLatitude); ?>">
            <input id="compdec_longitude" type="hidden" value="<?= e($compdecLongitude); ?>">
            <div id="compdec-map" class="compdec-map" data-readonly="true"></div>
            <p id="compdec-map-feedback" class="field-helper">Ponto da COMPDEC em modo visualização.</p>
        </div>
    </div>
</section>

<section class="form-section compdec-show-ubm-section">
    <div class="form-section-heading">
        <div>
            <span>03</span>
            <h2>UBM vinculada e geolocalização</h2>
        </div>
        <p>Unidade Bombeiro Militar associada ao município e coordenadas operacionais disponíveis.</p>
    </div>

    <div class="compdec-show-location-layout">
        <div class="compdec-show-detail-grid">
            <article class="compdec-show-detail-wide">
                <span>UBM atuante</span>
                <strong><?= e($value($compdec['ubm_nome'] ?? null)); ?></strong>
            </article>
            <article>
                <span>Status operacional</span>
                <strong><?= $ubmActive ? 'Ativa' : 'Inativa'; ?></strong>
            </article>
            <article>
                <span>Município da UBM</span>
                <strong><?= e($value($ubm['municipio'] ?? null)); ?></strong>
            </article>
            <article>
                <span>Latitude da UBM</span>
                <strong><?= e($value($ubmLatitude)); ?></strong>
            </article>
            <article>
                <span>Longitude da UBM</span>
                <strong><?= e($value($ubmLongitude)); ?></strong>
            </article>
        </div>

        <div class="compdec-map-card">
            <input id="ubm_latitude" type="hidden" value="<?= e($ubmLatitude); ?>">
            <input id="ubm_longitude" type="hidden" value="<?= e($ubmLongitude); ?>">
            <div id="ubm-map" class="compdec-map compdec-map-large" data-readonly="true"></div>
            <p id="ubm-map-feedback" class="field-helper">Ponto da UBM em modo visualização.</p>
        </div>
    </div>
</section>

<script type="application/json" id="compdec-form-map-data"><?= json_encode($mapData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?></script>
