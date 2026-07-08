<div class="page-header">
    <div>
        <span class="breadcrumb">COMPDECs &gt; Detalhe</span>
        <h1><?= e($compdec['municipio']); ?></h1>
    </div>

    <div class="actions">
        <a class="button button-light" href="<?= e(url('/compdecs')); ?>">Voltar</a>
        <?php if (can('compdecs.editar')): ?>
            <a class="button button-primary" href="<?= e(url('/compdecs/' . $compdec['id'] . '/editar')); ?>">Editar</a>
        <?php endif; ?>
    </div>
</div>

<section class="section-block compdec-profile">
    <div>
        <?= compdec_photo_thumb($compdec['foto_coordenador'] ?? null, $compdec['coordenador'] ?? 'Coordenador COMPDEC', 'compdec-photo-large'); ?>
    </div>
    <div>
        <h2><?= e($compdec['coordenador'] ?? 'Coordenador não informado'); ?></h2>
        <p><?= e($compdec['municipio']); ?> - <?= e($compdec['regiao_integracao'] ?? 'Região não informada'); ?></p>
        <p><?= e($compdec['telefone'] ?? '-'); ?> | <?= e($compdec['email'] ?? '-'); ?></p>
    </div>
</section>

<section class="detail-grid">
    <div><strong>Código IBGE</strong><span><?= e($compdec['municipio_codigo'] ?? '-'); ?></span></div>
    <div><strong>Região de integração</strong><span><?= e($compdec['regiao_integracao'] ?? '-'); ?></span></div>
    <div><strong>Possui COMPDEC</strong><span><?= (int) $compdec['tem_compdec'] === 1 ? 'Sim' : 'Não'; ?></span></div>
    <div><strong>UBM atuante</strong><span><?= e($compdec['ubm_nome'] ?? '-'); ?></span></div>
    <div><strong>Prefeito</strong><span><?= e($compdec['prefeito'] ?? '-'); ?></span></div>
    <div><strong>Coordenador</strong><span><?= e($compdec['coordenador'] ?? '-'); ?></span></div>
    <div><strong>Telefone</strong><span><?= e($compdec['telefone'] ?? '-'); ?></span></div>
    <div><strong>E-mail</strong><span><?= e($compdec['email'] ?? '-'); ?></span></div>
    <div><strong>Endereço</strong><span><?= e($compdec['endereco'] ?? '-'); ?></span></div>
    <div><strong>Data de atualização</strong><span><?= e($compdec['data_atualizacao'] ?? '-'); ?></span></div>
    <div><strong>Latitude</strong><span><?= e($compdec['latitude'] ?? '-'); ?></span></div>
    <div><strong>Longitude</strong><span><?= e($compdec['longitude'] ?? '-'); ?></span></div>
</section>
