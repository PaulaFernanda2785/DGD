<?php

declare(strict_types=1);

$jsonAttr = static fn (mixed $value): string => e(json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR));
$filters = array_merge([
    'ano' => '',
    'municipio_id' => '',
    'regiao_integracao' => '',
    'tipo_decreto_id' => '',
    'homologacao_status_id' => '',
    'reconhecimento_status_id' => '',
    'status_prazo_pge' => '',
], is_array($filters ?? null) ? $filters : []);
$opcoes = is_array($opcoes ?? null) ? $opcoes : [];
$resumo = array_merge([
    'total_desastres' => 0,
    'total_decretos_municipais' => 0,
    'homologados' => 0,
    'nao_homologados' => 0,
    'reconhecidos' => 0,
    'enviados_pge' => 0,
    'pendentes_pge' => 0,
    'total_afetados' => 0,
    'municipios_com_registro' => 0,
], is_array($resumo ?? null) ? $resumo : []);
$mapa = array_merge([
    'compdecs' => [],
    'ubms' => [],
    'desastres' => [],
], is_array($mapa ?? null) ? $mapa : []);
$compdecsComTotal = count(array_filter($mapa['compdecs'], static fn (array $point): bool => (int) ($point['tem_compdec'] ?? 0) === 1));
$compdecsSemTotal = max(count($mapa['compdecs']) - $compdecsComTotal, 0);
$totalPontos = count($mapa['compdecs']) + count($mapa['ubms']) + count($mapa['desastres']);
$formatNumber = static fn (mixed $value): string => number_format((float) $value, 0, ',', '.');
$formatDate = static function (?string $date): string {
    if (!$date) {
        return '-';
    }

    try {
        return (new DateTimeImmutable($date))->format('d/m/Y');
    } catch (Throwable) {
        return $date;
    }
};
$reportQuery = http_build_query(array_filter($filters, static fn (mixed $value): bool => trim((string) $value) !== ''));
$reportUrl = url('/painel/relatorio-impressao' . ($reportQuery !== '' ? '?' . $reportQuery : ''));
?>

<section class="panel-page">
    <header class="page-header page-header-modern panel-hero">
        <div>
            <span class="breadcrumb">Painel</span>
            <h1>Central de indicadores</h1>
            <p>Visão territorial dos decretos, COMPDECs e UBMs, com filtros integrados e mapa operacional em tela ampla.</p>
        </div>

        <div class="panel-hero-actions">
            <button
                type="button"
                class="button button-secondary"
                data-panel-print-open
                data-report-base-url="<?= e(url('/painel/relatorio-impressao')); ?>"
                data-report-url="<?= e($reportUrl); ?>"
            >Gerar relatório</button>
            <?php if (can('decretos.visualizar')): ?>
                <a class="button button-light" href="<?= e(url('/decretos')); ?>">Ver decretos</a>
            <?php endif; ?>
            <?php if (can('decretos.criar')): ?>
                <a class="button button-primary" href="<?= e(url('/decretos/novo')); ?>">Novo cadastro</a>
            <?php endif; ?>
        </div>
    </header>

    <section class="panel-filter-card" aria-label="Filtros do painel">
        <div class="section-heading">
            <div>
                <span>Filtros inteligentes</span>
                <h2>Recorte dos indicadores</h2>
            </div>
            <small><?= e($totalPontos); ?> ponto(s) disponíveis no mapa conforme o recorte atual.</small>
        </div>

        <form method="get" action="<?= e(url('/painel')); ?>" class="panel-filter-form">
            <div class="panel-filter-primary">
            <label class="modern-field panel-filter-year" for="panel_ano">
                <span>Ano</span>
                <input id="panel_ano" name="ano" value="<?= e($filters['ano']); ?>" inputmode="numeric" maxlength="4" pattern="[0-9]{4}" placeholder="<?= e((string) date('Y')); ?>">
                <small>Ano do protocolo DGD.</small>
            </label>

            <label class="modern-field panel-filter-city" for="panel_municipio">
                <span>Município</span>
                <select id="panel_municipio" name="municipio_id">
                    <option value="">Todos</option>
                    <?php foreach (($opcoes['municipios'] ?? []) as $municipio): ?>
                        <option value="<?= e($municipio['id'] ?? ''); ?>"<?= (string) $filters['municipio_id'] === (string) ($municipio['id'] ?? '') ? ' selected' : ''; ?>>
                            <?= e($municipio['nome'] ?? ''); ?> / <?= e($municipio['uf'] ?? 'PA'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small>Filtra decretos, COMPDECs e UBMs vinculadas.</small>
            </label>

            <label class="modern-field panel-filter-region" for="panel_regiao">
                <span>Região de integração</span>
                <select id="panel_regiao" name="regiao_integracao">
                    <option value="">Todas</option>
                    <?php foreach (($opcoes['regioes'] ?? []) as $regiao): ?>
                        <option value="<?= e($regiao); ?>"<?= (string) $filters['regiao_integracao'] === (string) $regiao ? ' selected' : ''; ?>><?= e($regiao); ?></option>
                    <?php endforeach; ?>
                </select>
                <small>Base da COMPDEC.</small>
            </label>

            </div>

            <div class="panel-filter-secondary">
            <label class="modern-field" for="panel_tipo">
                <span>Tipo de decreto</span>
                <select id="panel_tipo" name="tipo_decreto_id">
                    <option value="">Todos</option>
                    <?php foreach (($opcoes['tipos_decreto'] ?? []) as $tipo): ?>
                        <option value="<?= e($tipo['id'] ?? ''); ?>"<?= (string) $filters['tipo_decreto_id'] === (string) ($tipo['id'] ?? '') ? ' selected' : ''; ?>>
                            <?= e($tipo['nome'] ?? ''); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small>Afeta os indicadores de desastres.</small>
            </label>

            <label class="modern-field" for="panel_homologacao">
                <span>Homologação</span>
                <select id="panel_homologacao" name="homologacao_status_id">
                    <option value="">Todas</option>
                    <?php foreach (($opcoes['homologacoes'] ?? []) as $status): ?>
                        <option value="<?= e($status['id'] ?? ''); ?>"<?= (string) $filters['homologacao_status_id'] === (string) ($status['id'] ?? '') ? ' selected' : ''; ?>>
                            <?= e($status['nome'] ?? ''); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small>Situação institucional do decreto.</small>
            </label>

            <label class="modern-field" for="panel_reconhecimento">
                <span>Reconhecimento</span>
                <select id="panel_reconhecimento" name="reconhecimento_status_id">
                    <option value="">Todos</option>
                    <?php foreach (($opcoes['reconhecimentos'] ?? []) as $status): ?>
                        <option value="<?= e($status['id'] ?? ''); ?>"<?= (string) $filters['reconhecimento_status_id'] === (string) ($status['id'] ?? '') ? ' selected' : ''; ?>>
                            <?= e($status['nome'] ?? ''); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small>Situação do reconhecimento federal.</small>
            </label>

            <label class="modern-field" for="panel_pge">
                <span>Status PGE</span>
                <select id="panel_pge" name="status_prazo_pge">
                    <option value="">Todos</option>
                    <?php foreach (($opcoes['status_pge'] ?? []) as $codigo => $label): ?>
                        <option value="<?= e($codigo); ?>"<?= (string) $filters['status_prazo_pge'] === (string) $codigo ? ' selected' : ''; ?>><?= e($label); ?></option>
                    <?php endforeach; ?>
                </select>
                <small>Prazo calculado pela regra atual.</small>
            </label>

            </div>

            <div class="panel-filter-actions">
                <button type="submit" class="button button-primary">Aplicar filtros</button>
                <a class="button button-light" href="<?= e(url('/painel')); ?>">Limpar</a>
            </div>
        </form>
    </section>

    <section class="panel-indicator-grid" aria-label="Indicadores principais">
        <article class="panel-indicator-neutral">
            <span>Total de registros</span>
            <strong><?= e($formatNumber($resumo['total_desastres'])); ?></strong>
            <small>Registros de desastres conforme o recorte.</small>
        </article>
        <article class="panel-indicator-info">
            <span>Municípios</span>
            <strong><?= e($formatNumber($resumo['municipios_com_registro'])); ?></strong>
            <small>Com decreto registrado.</small>
        </article>
        <article class="panel-indicator-compdec-ok">
            <span>Com COMPDEC</span>
            <strong><?= e($formatNumber($compdecsComTotal)); ?></strong>
            <small>Municípios com coordenadoria cadastrada.</small>
        </article>
        <article class="panel-indicator-compdec-missing">
            <span>Sem COMPDEC</span>
            <strong><?= e($formatNumber($compdecsSemTotal)); ?></strong>
            <small>Municípios sem coordenadoria registrada.</small>
        </article>
        <article class="panel-indicator-info">
            <span>Decretos municipais</span>
            <strong><?= e($formatNumber($resumo['total_decretos_municipais'])); ?></strong>
            <small>Com número informado.</small>
        </article>
        <article class="panel-indicator-success">
            <span>Homologados</span>
            <strong><?= e($formatNumber($resumo['homologados'])); ?></strong>
            <small>Ciclo estadual aprovado.</small>
        </article>
        <article class="panel-indicator-success">
            <span>Reconhecidos</span>
            <strong><?= e($formatNumber($resumo['reconhecidos'])); ?></strong>
            <small>Reconhecimento federal aprovado.</small>
        </article>
        <article class="panel-indicator-warning">
            <span>Pendências PGE</span>
            <strong><?= e($formatNumber($resumo['pendentes_pge'])); ?></strong>
            <small>Fora do prazo calculado.</small>
        </article>
        <article class="panel-indicator-neutral">
            <span>Afetados</span>
            <strong><?= e($formatNumber($resumo['total_afetados'])); ?></strong>
            <small>Total humano registrado.</small>
        </article>
    </section>

    <section class="panel-map-section" aria-label="Mapa territorial do DGD">
        <div class="panel-map-heading">
            <div class="section-heading">
                <div>
                    <span>Mapa operacional</span>
                    <h2>Camadas territoriais</h2>
                </div>
                <small>Os pontos se agrupam ou se expandem automaticamente conforme o zoom.</small>
            </div>

            <div class="panel-layer-toggles" aria-label="Camadas do mapa">
                <label class="panel-layer-option panel-layer-disasters">
                    <input type="checkbox" data-panel-layer-toggle value="desastres" checked>
                    <span><i></i><strong>Desastres</strong><small>Decretos registrados</small></span>
                </label>
                <label class="panel-layer-option panel-layer-compdecs">
                    <input type="checkbox" data-panel-layer-toggle value="compdecs" checked>
                    <span><i></i><strong>COMPDECs</strong><small>Com e sem cadastro</small></span>
                </label>
                <label class="panel-layer-option panel-layer-ubms">
                    <input type="checkbox" data-panel-layer-toggle value="ubms" checked>
                    <span><i></i><strong>UBM</strong><small>Unidades atuantes</small></span>
                </label>
            </div>
        </div>

        <div
            class="panel-map-shell"
            data-panel-map
            data-layers="<?= $jsonAttr($mapa); ?>"
            data-default-lat="-3.79"
            data-default-lng="-52.48"
        >
            <div class="panel-map-canvas" data-panel-map-canvas role="application" aria-label="Mapa com camadas de COMPDEC, UBM e desastres"></div>
            <div class="panel-map-footer">
                <p data-panel-map-status><?= $totalPontos === 0 ? 'Nenhum ponto com coordenada disponível para exibir no mapa.' : 'Mapa carregando camadas territoriais do DGD.'; ?></p>
                <div class="panel-map-legend" aria-label="Legenda">
                    <span><i data-layer="desastres"></i>Desastres <b data-panel-legend-count="desastres"><?= e($formatNumber(count($mapa['desastres']))); ?></b></span>
                    <span><i data-layer="compdecs-com"></i>Com COMPDEC <b data-panel-legend-count="compdecs-com"><?= e($formatNumber($compdecsComTotal)); ?></b></span>
                    <span><i data-layer="compdecs-sem"></i>Sem COMPDEC <b data-panel-legend-count="compdecs-sem"><?= e($formatNumber($compdecsSemTotal)); ?></b></span>
                    <span><i data-layer="ubms"></i>UBM <b data-panel-legend-count="ubms"><?= e($formatNumber(count($mapa['ubms']))); ?></b></span>
                    <span><i data-layer="cluster"></i>Agrupado D/C/U</span>
                </div>
            </div>
        </div>
    </section>

    <section class="panel-bottom-grid">
        <div class="panel-ranking-card">
            <div class="section-heading">
                <div>
                    <span>Municípios críticos</span>
                    <h2>Maior concentração de registros</h2>
                </div>
            </div>

            <div class="panel-ranking-list">
                <?php foreach (($indicadores ?? []) as $item): ?>
                    <article>
                        <div>
                            <strong><?= e($item['municipio'] ?? '-'); ?></strong>
                            <span><?= e($formatNumber($item['afetados'] ?? 0)); ?> afetado(s)</span>
                        </div>
                        <b><?= e($formatNumber($item['total'] ?? 0)); ?></b>
                    </article>
                <?php endforeach; ?>
                <?php if (($indicadores ?? []) === []): ?>
                    <div class="panel-empty-state">Nenhum indicador para o recorte atual.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="panel-recent-card">
            <div class="section-heading">
                <div>
                    <span>Atualizações</span>
                    <h2>Registros recentes</h2>
                </div>
            </div>

            <div class="panel-recent-list">
                <?php foreach ($recentes as $registro): ?>
                    <article>
                        <div>
                            <a href="<?= e(url('/decretos/' . $registro['id'])); ?>"><?= e($registro['protocolo_dgd']); ?></a>
                            <strong><?= e($registro['municipio']); ?></strong>
                            <span><?= e($registro['cobrade_tipo'] ?? 'Desastre'); ?> · <?= e($formatDate($registro['data_desastre'] ?? null)); ?></span>
                        </div>
                        <div class="panel-recent-status">
                            <?= status_badge($registro['homologacao'] ?? null); ?>
                            <?= status_badge($registro['status_envio_pge'] ?? null); ?>
                        </div>
                    </article>
                <?php endforeach; ?>
                <?php if ($recentes === []): ?>
                    <div class="panel-empty-state">Nenhum registro recente para o recorte atual.</div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</section>
