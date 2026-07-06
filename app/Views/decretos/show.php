<div class="page-header">
    <div>
        <span class="breadcrumb">Decretos &gt; Detalhe</span>
        <h1><?= e($registro['protocolo_dgd']); ?></h1>
    </div>

    <div class="actions">
        <a class="button button-light" href="<?= e(url('/decretos')); ?>">Voltar</a>
        <?php if (can('decretos.editar')): ?>
            <a class="button button-primary" href="<?= e(url('/decretos/' . $registro['id'] . '/editar')); ?>">Editar</a>
        <?php endif; ?>
    </div>
</div>

<section class="detail-grid">
    <div><strong>Municipio</strong><span><?= e($registro['municipio']); ?></span></div>
    <div><strong>UBM atuante</strong><span><?= e($registro['ubm_atuante'] ?? '-'); ?></span></div>
    <div><strong>Tipo de decreto</strong><span><?= e($registro['tipo_decreto']); ?></span></div>
    <div><strong>Data do desastre</strong><span><?= e($registro['data_desastre']); ?></span></div>
    <div><strong>COBRADE</strong><span><?= e($registro['cobrade_codigo'] . ' - ' . $registro['cobrade_subtipo']); ?></span></div>
    <div><strong>Grupo</strong><span><?= e($registro['cobrade_grupo']); ?></span></div>
    <div><strong>Decreto municipal</strong><span><?= e($registro['numero_decreto_municipal'] ?? '-'); ?></span></div>
    <div><strong>Data decreto municipal</strong><span><?= e($registro['data_decreto_municipal'] ?? '-'); ?></span></div>
    <div><strong>Homologacao</strong><span><?= status_badge($registro['homologacao']); ?></span></div>
    <div><strong>Reconhecimento</strong><span><?= status_badge($registro['reconhecimento']); ?></span></div>
    <div><strong>Envio PGE</strong><span><?= status_badge($registro['status_envio_pge']); ?></span></div>
    <div><strong>Prazo PGE</strong><span><?= status_badge($registro['status_prazo_pge_calculado']); ?></span></div>
    <div><strong>Total afetados</strong><span><?= e($registro['total_afetados']); ?></span></div>
    <div><strong>Analista</strong><span><?= e($registro['analista'] ?? '-'); ?></span></div>
</section>

<section class="section-block">
    <h2>Danos humanos</h2>
    <div class="detail-grid compact">
        <div><strong>Obitos</strong><span><?= e($registro['numero_obitos']); ?></span></div>
        <div><strong>Feridos</strong><span><?= e($registro['numero_feridos']); ?></span></div>
        <div><strong>Enfermos</strong><span><?= e($registro['numero_enfermos']); ?></span></div>
        <div><strong>Desabrigados</strong><span><?= e($registro['numero_desabrigados']); ?></span></div>
        <div><strong>Desalojados</strong><span><?= e($registro['numero_desalojados']); ?></span></div>
        <div><strong>Outros afetados</strong><span><?= e($registro['numero_outros_afetados']); ?></span></div>
    </div>
</section>

<section class="section-block">
    <h2>Anexos</h2>

    <?php if (can('anexos.upload')): ?>
        <form method="post" action="<?= e(url('/decretos/' . $registro['id'] . '/anexos')); ?>" enctype="multipart/form-data" class="filters upload-form">
            <?= csrf_input(); ?>
            <select name="tipo_anexo_id" required>
                <option value="">Tipo de anexo</option>
                <?php foreach ($dominios['tiposAnexo'] as $tipo): ?>
                    <option value="<?= e($tipo['id']); ?>"><?= e($tipo['nome']); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="file" name="arquivo" required>
            <input name="descricao" placeholder="Descricao opcional">
            <button type="submit" class="button button-secondary">Enviar</button>
        </form>
    <?php endif; ?>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Arquivo</th>
                    <th>Tamanho</th>
                    <th>Enviado em</th>
                    <th>Acoes</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($registro['anexos'] as $anexo): ?>
                    <tr>
                        <td><?= e($anexo['tipo_anexo']); ?></td>
                        <td><?= e($anexo['nome_original']); ?></td>
                        <td><?= e(number_format((int) $anexo['tamanho_bytes'] / 1024, 1, ',', '.')); ?> KB</td>
                        <td><?= e($anexo['enviado_em']); ?></td>
                        <td class="actions">
                            <a href="<?= e(url('/anexos/' . $anexo['id'] . '/download')); ?>">Baixar</a>
                            <?php if (can('anexos.excluir')): ?>
                                <form method="post" action="<?= e(url('/anexos/' . $anexo['id'] . '/excluir')); ?>">
                                    <?= csrf_input(); ?>
                                    <button class="link-button" type="submit" data-confirm="Deseja remover este anexo?">Excluir</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if ($registro['anexos'] === []): ?>
                    <tr><td colspan="5">Nenhum anexo cadastrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php if (can('decretos.excluir')): ?>
    <form method="post" action="<?= e(url('/decretos/' . $registro['id'] . '/excluir')); ?>" class="danger-form">
        <?= csrf_input(); ?>
        <button type="submit" class="button button-danger" data-confirm="Deseja realmente excluir este registro da listagem?">
            Excluir logicamente
        </button>
    </form>
<?php endif; ?>
