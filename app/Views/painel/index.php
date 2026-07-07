<div class="page-header">
    <div>
        <span class="breadcrumb">Painel</span>
        <h1>Painel</h1>
    </div>
    <?php if (can('decretos.criar')): ?>
        <a class="button button-primary" href="<?= e(url('/decretos/novo')); ?>">Novo cadastro</a>
    <?php endif; ?>
</div>

<section class="dashboard-grid">
    <div><strong><?= e($resumo['total_desastres'] ?? 0); ?></strong><span>Desastres no ano</span></div>
    <div><strong><?= e($resumo['total_decretos_municipais'] ?? 0); ?></strong><span>Decretos municipais</span></div>
    <div><strong><?= e($resumo['homologados'] ?? 0); ?></strong><span>Homologados</span></div>
    <div><strong><?= e($resumo['enviados_pge'] ?? 0); ?></strong><span>Enviados a PGE</span></div>
    <div><strong><?= e($resumo['pendentes_pge'] ?? 0); ?></strong><span>Pendencias PGE</span></div>
    <div><strong><?= e($resumo['total_afetados'] ?? 0); ?></strong><span>Total de afetados</span></div>
</section>

<section class="section-block">
    <h2>Registros recentes</h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Protocolo</th>
                    <th>Município</th>
                    <th>Data</th>
                    <th>Homologacao</th>
                    <th>Reconhecimento</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentes as $registro): ?>
                    <tr>
                        <td><a href="<?= e(url('/decretos/' . $registro['id'])); ?>"><?= e($registro['protocolo_dgd']); ?></a></td>
                        <td><?= e($registro['municipio']); ?></td>
                        <td><?= e($registro['data_desastre']); ?></td>
                        <td><?= status_badge($registro['homologacao']); ?></td>
                        <td><?= status_badge($registro['reconhecimento']); ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($recentes === []): ?>
                    <tr><td colspan="5">Nenhum registro recente.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
