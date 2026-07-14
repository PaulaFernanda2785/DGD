<?php
    $totalRegistros = (int) ($paginacao['total'] ?? count($registros));
    $totalAfetadosPagina = array_sum(array_map(static fn (array $registro): int => (int) ($registro['total_afetados'] ?? 0), $registros));
    $pgePendentesPagina = count(array_filter($registros, static fn (array $registro): bool => (string) ($registro['status_prazo_pge_calculado'] ?? '') === 'PENDENTE'));
    $homologadosPagina = count(array_filter($registros, static fn (array $registro): bool => (string) ($registro['homologacao_codigo'] ?? '') === 'HOMOLOGADO'));
    $formatDate = static fn (mixed $value): string => !empty($value) ? date('d/m/Y', strtotime((string) $value)) : '-';
    $dash = static fn (mixed $value): string => trim((string) $value) !== '' ? (string) $value : '-';
    $cobradeSymbolUrl = static function (mixed $path, mixed $codigo): ?string {
        $path = trim(str_replace('\\', '/', (string) $path));

        if ($path !== '') {
            if (preg_match('#^https?://#i', $path) === 1) {
                return $path;
            }

            $path = ltrim($path, '/');
            $path = preg_replace('#^public/#', '', $path) ?? $path;
            $path = preg_replace('#^cobrade_simbologia/#', 'assets/images/cobrade_simbologia/', $path) ?? $path;

            return url('/' . $path);
        }

        $codigo = trim((string) $codigo);

        if ($codigo === '') {
            return null;
        }

        return url('/assets/images/cobrade_simbologia/simbologia_cobrade_' . str_replace('.', '_', $codigo) . '.png');
    };
?>

<div class="page-header page-header-modern decrees-page-header">
    <div>
        <span class="breadcrumb">Decretos &gt; Listagem</span>
        <h1>Decretos</h1>
        <p>Acompanhamento operacional dos decretos municipais, homologação, reconhecimento e PGE.</p>
    </div>

    <?php if (can('decretos.criar')): ?>
        <a class="button button-primary" href="<?= e(url('/decretos/novo')); ?>">Novo cadastro</a>
    <?php endif; ?>
</div>

<section class="decree-overview-grid" aria-label="Resumo da listagem">
    <div>
        <span>Registros filtrados</span>
        <strong><?= e($totalRegistros); ?></strong>
    </div>
    <div>
        <span>Afetados nesta página</span>
        <strong><?= e(number_format($totalAfetadosPagina, 0, ',', '.')); ?></strong>
    </div>
    <div>
        <span>PGE pendente</span>
        <strong><?= e($pgePendentesPagina); ?></strong>
    </div>
    <div>
        <span>Homologados</span>
        <strong><?= e($homologadosPagina); ?></strong>
    </div>
</section>

<?php require view_path('decretos/partials/filtros'); ?>

<section class="decree-list" aria-label="Lista de decretos">
    <?php foreach ($registros as $registro): ?>
        <?php
            $simbologiaCobradeUrl = $cobradeSymbolUrl($registro['cobrade_simbologia'] ?? null, $registro['cobrade_codigo'] ?? null);
            $pgeResultadoCodigo = (string) ($registro['status_envio_pge_codigo'] ?? '');
            $pgeResultadoLabel = match ($pgeResultadoCodigo) {
                'APROVADO' => 'Aprovado',
                'REPROVADO' => 'Reprovado',
                default => 'Conclusão',
            };
            $pgeResultadoData = in_array($pgeResultadoCodigo, ['APROVADO', 'REPROVADO'], true)
                ? ($registro['data_decreto_homologacao'] ?? $registro['data_conclusao_pge'] ?? null)
                : ($registro['data_conclusao_pge'] ?? null);
        ?>
        <article class="decree-card">
            <header class="decree-card-header">
                <div class="decree-card-title">
                    <span><?= e($registro['protocolo_ano'] . '/' . $registro['protocolo_sequencial']); ?></span>
                    <h2><?= e($registro['municipio']); ?></h2>
                    <p><?= e($registro['protocolo_dgd']); ?></p>
                </div>

                <div class="decree-card-actions">
                    <a class="button button-light" href="<?= e(url('/decretos/' . $registro['id'])); ?>">Ver</a>
                    <button
                        type="button"
                        class="button button-light"
                        data-decree-print-open
                        data-report-url="<?= e(url('/decretos/' . $registro['id'] . '/relatorio-impressao')); ?>"
                    >Imprimir</button>
                    <?php if (can('decretos.editar')): ?>
                        <a class="button button-secondary" href="<?= e(url('/decretos/' . $registro['id'] . '/editar')); ?>">Editar</a>
                    <?php endif; ?>
                </div>
            </header>

            <div class="decree-card-body">
                <div class="decree-main-info">
                    <div class="decree-disaster-info">
                        <span>Tipo de desastre</span>
                        <div class="decree-disaster-row">
                            <div class="decree-disaster-symbol" aria-hidden="true">
                                <?php if ($simbologiaCobradeUrl !== null): ?>
                                    <img src="<?= e($simbologiaCobradeUrl); ?>" alt="" loading="lazy" decoding="async">
                                <?php else: ?>
                                    <span>COBRADE</span>
                                <?php endif; ?>
                            </div>
                            <strong><?= e($registro['cobrade_codigo'] . ' - ' . $registro['cobrade_subtipo']); ?></strong>
                        </div>
                    </div>
                    <div>
                        <span>Decreto municipal</span>
                        <strong><?= e($formatDate($registro['data_decreto_municipal'] ?? null)); ?></strong>
                    </div>
                    <div>
                        <span>Dias do decreto</span>
                        <strong><?= e($dash($registro['total_dias_decreto'] ?? null)); ?></strong>
                    </div>
                    <div>
                        <span>Afetados</span>
                        <strong><?= e(number_format((int) ($registro['total_afetados'] ?? 0), 0, ',', '.')); ?></strong>
                    </div>
                    <div>
                        <span>Dias PGE</span>
                        <strong><?= e($dash($registro['duracao_pge_dias'] ?? null)); ?></strong>
                    </div>
                    <div>
                        <span>Status PGE</span>
                        <?= status_badge($registro['status_prazo_pge_calculado'] ?? null); ?>
                    </div>
                    <div>
                        <span>Analista</span>
                        <strong><?= e($dash($registro['analista'] ?? null)); ?></strong>
                    </div>
                </div>

                <div class="decree-status-grid">
                    <div class="decree-status-block">
                        <span>Homologação</span>
                        <?php if (can('decretos.editar_status_listagem')): ?>
                            <?php $campo = 'homologacao_status_id'; $valorAtual = $registro[$campo]; $opcoes = $dominios['statusHomologacao']; require view_path('decretos/partials/status_form'); ?>
                        <?php else: ?>
                            <?= status_badge($registro['homologacao']); ?>
                        <?php endif; ?>
                    </div>

                    <div class="decree-status-block">
                        <span>Reconhecimento</span>
                        <?php if (can('decretos.editar_status_listagem')): ?>
                            <?php $campo = 'reconhecimento_status_id'; $valorAtual = $registro[$campo]; $opcoes = $dominios['statusReconhecimento']; require view_path('decretos/partials/status_form'); ?>
                        <?php else: ?>
                            <?= status_badge($registro['reconhecimento']); ?>
                        <?php endif; ?>
                    </div>

                    <div class="decree-status-block">
                        <span>Envio à PGE</span>
                        <?= status_badge($registro['status_envio_pge']); ?>
                        <small class="pge-status-meta">
                            Envio: <?= e($formatDate($registro['data_envio_pge'] ?? null)); ?>
                            <span><?= e($pgeResultadoLabel); ?>: <?= e($formatDate($pgeResultadoData)); ?></span>
                        </small>
                    </div>
                </div>
            </div>
        </article>
    <?php endforeach; ?>

    <?php if ($registros === []): ?>
        <div class="decree-empty-state" role="status" aria-live="polite">
            <div class="decree-empty-icon" aria-hidden="true">
                <span></span>
            </div>
            <div class="decree-empty-copy">
                <strong>Nenhum decreto encontrado</strong>
                <p>Revise os filtros aplicados ou cadastre um novo decreto para iniciar o acompanhamento.</p>
            </div>
            <div class="decree-empty-actions">
                <a class="button button-light" href="<?= e(url('/decretos')); ?>">Limpar filtros</a>
                <?php if (can('decretos.criar')): ?>
                    <a class="button button-primary" href="<?= e(url('/decretos/novo')); ?>">Novo cadastro</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</section>

<?php require view_path('decretos/partials/paginacao'); ?>
