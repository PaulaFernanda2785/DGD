<div class="page-header">
    <div>
        <span class="breadcrumb">Decretos &gt; Listagem</span>
        <h1>Decretos</h1>
    </div>

    <?php if (can('decretos.criar')): ?>
        <a class="button button-primary" href="<?= e(url('/decretos/novo')); ?>">Novo cadastro</a>
    <?php endif; ?>
</div>

<?php require view_path('decretos/partials/filtros'); ?>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Seq.</th>
                <th>Protocolo</th>
                <th>Município</th>
                <th>Tipo de desastre</th>
                <th>Data decreto</th>
                <th>Dias decreto</th>
                <th>Homologacao</th>
                <th>Reconhecimento</th>
                <th>Afetados</th>
                <th>Dias PGE</th>
                <th>Envio PGE</th>
                <th>Prazo PGE</th>
                <th>Analista</th>
                <th>Acoes</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($registros as $registro): ?>
                <tr>
                    <td><?= e($registro['protocolo_ano'] . '/' . $registro['protocolo_sequencial']); ?></td>
                    <td><?= e($registro['protocolo_dgd']); ?></td>
                    <td><?= e($registro['municipio']); ?></td>
                    <td><?= e($registro['cobrade_codigo'] . ' - ' . $registro['cobrade_subtipo']); ?></td>
                    <td><?= e($registro['data_decreto_municipal'] ?? '-'); ?></td>
                    <td><?= e($registro['total_dias_decreto'] ?? '-'); ?></td>
                    <td>
                        <?php if (can('decretos.editar_status_listagem')): ?>
                            <?php $campo = 'homologacao_status_id'; $valorAtual = $registro[$campo]; $opcoes = $dominios['statusHomologacao']; require view_path('decretos/partials/status_form'); ?>
                        <?php else: ?>
                            <?= status_badge($registro['homologacao']); ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (can('decretos.editar_status_listagem')): ?>
                            <?php $campo = 'reconhecimento_status_id'; $valorAtual = $registro[$campo]; $opcoes = $dominios['statusReconhecimento']; require view_path('decretos/partials/status_form'); ?>
                        <?php else: ?>
                            <?= status_badge($registro['reconhecimento']); ?>
                        <?php endif; ?>
                    </td>
                    <td><?= e($registro['total_afetados']); ?></td>
                    <td><?= e($registro['duracao_pge_dias'] ?? '-'); ?></td>
                    <td>
                        <?php if (can('decretos.editar_status_listagem')): ?>
                            <?php $campo = 'status_envio_pge_id'; $valorAtual = $registro[$campo]; $opcoes = $dominios['statusEnvioPge']; require view_path('decretos/partials/status_form'); ?>
                        <?php else: ?>
                            <?= status_badge($registro['status_envio_pge']); ?>
                        <?php endif; ?>
                    </td>
                    <td><?= status_badge($registro['status_prazo_pge_calculado']); ?></td>
                    <td><?= e($registro['analista'] ?? '-'); ?></td>
                    <td class="actions">
                        <a href="<?= e(url('/decretos/' . $registro['id'])); ?>">Ver</a>
                        <?php if (can('decretos.editar')): ?>
                            <a href="<?= e(url('/decretos/' . $registro['id'] . '/editar')); ?>">Editar</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if ($registros === []): ?>
                <tr>
                    <td colspan="14">Nenhum registro encontrado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require view_path('decretos/partials/paginacao'); ?>
