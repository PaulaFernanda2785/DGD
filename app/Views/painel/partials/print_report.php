<?php
    $valor = static fn (mixed $value): string => trim((string) $value) !== '' ? (string) $value : 'Não informado';
    $data = static fn (mixed $value): string => !empty($value) ? date('d/m/Y', strtotime((string) $value)) : 'Não informado';
    $numero = static fn (mixed $value): string => number_format((float) ($value ?? 0), 0, ',', '.');
    $usuario = \App\Core\Auth::user();
    $geradoEm ??= new DateTimeImmutable('now');
    $relatorio = is_array($relatorio ?? null) ? $relatorio : [];
    $filters = array_merge([
        'ano' => '',
        'municipio_id' => '',
        'regiao_integracao' => '',
        'tipo_decreto_id' => '',
        'homologacao_status_id' => '',
        'reconhecimento_status_id' => '',
        'status_prazo_pge' => '',
    ], is_array($relatorio['filters'] ?? null) ? $relatorio['filters'] : []);
    $opcoes = is_array($relatorio['opcoes'] ?? null) ? $relatorio['opcoes'] : [];
    $resumo = is_array($relatorio['resumo'] ?? null) ? $relatorio['resumo'] : [];
    $indicadores = is_array($relatorio['indicadores'] ?? null) ? $relatorio['indicadores'] : [];
    $mapa = array_merge(['compdecs' => [], 'ubms' => [], 'desastres' => []], is_array($relatorio['mapa'] ?? null) ? $relatorio['mapa'] : []);
    $registros = is_array($relatorio['registros'] ?? null) ? $relatorio['registros'] : [];
    $recentes = is_array($relatorio['recentes'] ?? null) ? $relatorio['recentes'] : [];
    $findOption = static function (array $items, mixed $id, string $idKey = 'id', string $labelKey = 'nome'): string {
        $id = (string) $id;

        if ($id === '') {
            return 'Todos';
        }

        foreach ($items as $item) {
            if ((string) ($item[$idKey] ?? '') === $id) {
                $label = (string) ($item[$labelKey] ?? '');
                $uf = (string) ($item['uf'] ?? '');

                return trim($label . ($uf !== '' ? ' / ' . $uf : '')) ?: 'Não informado';
            }
        }

        return 'Não informado';
    };
    $filtrosAplicados = [
        'Ano' => trim((string) ($filters['ano'] ?? '')) !== '' ? (string) $filters['ano'] : 'Todos os anos',
        'Município' => $findOption($opcoes['municipios'] ?? [], $filters['municipio_id'] ?? ''),
        'Região de integração' => trim((string) ($filters['regiao_integracao'] ?? '')) !== '' ? (string) $filters['regiao_integracao'] : 'Todas',
        'Tipo de decreto' => $findOption($opcoes['tipos_decreto'] ?? [], $filters['tipo_decreto_id'] ?? ''),
        'Homologação' => $findOption($opcoes['homologacoes'] ?? [], $filters['homologacao_status_id'] ?? ''),
        'Reconhecimento' => $findOption($opcoes['reconhecimentos'] ?? [], $filters['reconhecimento_status_id'] ?? ''),
        'Status PGE' => trim((string) ($filters['status_prazo_pge'] ?? '')) !== '' ? (string) (($opcoes['status_pge'][$filters['status_prazo_pge']] ?? $filters['status_prazo_pge'])) : 'Todos',
    ];
    $statusPgeHighlight = match ((string) ($filters['status_prazo_pge'] ?? '')) {
        'APROVADO' => 'decree-print-highlight--success',
        'NO PRAZO' => 'decree-print-highlight--info',
        'PENDENTE' => 'decree-print-highlight--warning',
        'REPROVADO' => 'decree-print-highlight--danger',
        'NAO REGISTRADO' => 'decree-print-highlight--muted',
        default => 'decree-print-highlight--muted',
    };
    $filterHighlightClasses = [
        'Ano' => 'decree-print-highlight decree-print-highlight--info',
        'Status PGE' => 'decree-print-highlight ' . $statusPgeHighlight,
    ];
    $compdecsComTotal = count(array_filter($mapa['compdecs'], static fn (array $point): bool => (int) ($point['tem_compdec'] ?? 0) === 1));
    $compdecsSemTotal = max(count($mapa['compdecs']) - $compdecsComTotal, 0);
    $totalPontos = count($mapa['compdecs']) + count($mapa['ubms']) + count($mapa['desastres']);
    $tituloRecorte = trim((string) ($filters['ano'] ?? '')) !== '' ? 'Painel DGD ' . $filters['ano'] : 'Painel DGD — Todos os anos';
    $sectionNumber = 1;
?>

<article class="decree-print-report panel-print-report" data-decree-print-content>
    <header class="decree-print-header">
        <div class="decree-print-brand">
            <img src="<?= e(url('/assets/img/logo-cedec.png')); ?>" alt="CEDEC-PA">
            <div>
                <strong>Defesa Civil do Estado do Pará</strong>
                <span>Sistema DGD - Gestão de Desastres e Decretos</span>
            </div>
        </div>
        <div class="decree-print-meta">
            <span>Relatório gerencial do painel</span>
            <strong>Central de indicadores</strong>
            <small>Gerado em <?= e($geradoEm->format('d/m/Y H:i')); ?> por <?= e($usuario['nome'] ?? 'Usuário autenticado'); ?></small>
        </div>
    </header>

    <section class="decree-print-cover panel-print-cover">
        <div>
            <span>Recorte operacional</span>
            <h2><?= e($tituloRecorte); ?></h2>
            <p><?= e($numero($resumo['total_desastres'] ?? 0)); ?> desastre(s), <?= e($numero($resumo['municipios_com_registro'] ?? 0)); ?> município(s) com registro e <?= e($numero($totalPontos)); ?> ponto(s) territoriais.</p>
        </div>
        <div class="decree-print-disaster-info panel-print-filter-brief">
            <div>
                <span>Filtros do relatório</span>
                <p><?= e(implode(' • ', array_map(static fn (string $label, string $value): string => $label . ': ' . $value, array_keys($filtrosAplicados), $filtrosAplicados))); ?></p>
            </div>
        </div>
    </section>

    <section class="decree-print-summary">
        <div><span>Total de registros</span><strong><?= e($numero($resumo['total_desastres'] ?? 0)); ?></strong></div>
        <div><span>Municípios</span><strong><?= e($numero($resumo['municipios_com_registro'] ?? 0)); ?></strong></div>
        <div class="decree-print-highlight decree-print-highlight--success"><span>Homologados</span><strong><?= e($numero($resumo['homologados'] ?? 0)); ?></strong></div>
        <div class="decree-print-highlight decree-print-highlight--success"><span>Reconhecidos</span><strong><?= e($numero($resumo['reconhecidos'] ?? 0)); ?></strong></div>
        <div class="decree-print-highlight decree-print-highlight--danger"><span>Pendências PGE</span><strong><?= e($numero($resumo['pendentes_pge'] ?? 0)); ?></strong></div>
        <div><span>Afetados</span><strong><?= e($numero($resumo['total_afetados'] ?? 0)); ?></strong></div>
        <div class="decree-print-highlight decree-print-highlight--success"><span>Com COMPDEC</span><strong><?= e($numero($compdecsComTotal)); ?></strong></div>
        <div class="decree-print-highlight decree-print-highlight--warning"><span>Sem COMPDEC</span><strong><?= e($numero($compdecsSemTotal)); ?></strong></div>
        <div><span>UBMs</span><strong><?= e($numero(count($mapa['ubms']))); ?></strong></div>
    </section>

    <section class="decree-print-section">
        <h3><span><?= e(str_pad((string) $sectionNumber++, 2, '0', STR_PAD_LEFT)); ?></span>Filtros aplicados</h3>
        <div class="decree-print-grid">
            <?php foreach ($filtrosAplicados as $label => $value): ?>
                <div<?= isset($filterHighlightClasses[$label]) ? ' class="' . e($filterHighlightClasses[$label]) . '"' : ''; ?>>
                    <span><?= e($label); ?></span>
                    <strong><?= e($value); ?></strong>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="decree-print-section">
        <h3><span><?= e(str_pad((string) $sectionNumber++, 2, '0', STR_PAD_LEFT)); ?></span>Camadas territoriais</h3>
        <div class="decree-print-grid">
            <div><span>Desastres no mapa</span><strong><?= e($numero(count($mapa['desastres']))); ?></strong></div>
            <div><span>COMPDECs no mapa</span><strong><?= e($numero(count($mapa['compdecs']))); ?></strong></div>
            <div class="decree-print-highlight decree-print-highlight--success"><span>Com COMPDEC</span><strong><?= e($numero($compdecsComTotal)); ?></strong></div>
            <div class="decree-print-highlight decree-print-highlight--warning"><span>Sem COMPDEC</span><strong><?= e($numero($compdecsSemTotal)); ?></strong></div>
            <div><span>UBMs atribuídas</span><strong><?= e($numero(count($mapa['ubms']))); ?></strong></div>
            <div><span>Total de pontos</span><strong><?= e($numero($totalPontos)); ?></strong></div>
        </div>
    </section>

    <section class="decree-print-section">
        <h3><span><?= e(str_pad((string) $sectionNumber++, 2, '0', STR_PAD_LEFT)); ?></span>Municípios críticos</h3>
        <?php if ($indicadores !== []): ?>
            <div class="decree-print-table">
                <div class="decree-print-row panel-print-ranking-row decree-print-row-head">
                    <span>Município</span>
                    <span>Registros</span>
                    <span>Afetados</span>
                </div>
                <?php foreach ($indicadores as $item): ?>
                    <div class="decree-print-row panel-print-ranking-row">
                        <span><?= e($valor($item['municipio'] ?? null)); ?></span>
                        <span><?= e($numero($item['total'] ?? 0)); ?></span>
                        <span><?= e($numero($item['afetados'] ?? 0)); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="decree-print-empty">Nenhum indicador para o recorte atual.</p>
        <?php endif; ?>
    </section>

    <section class="decree-print-section">
        <h3><span><?= e(str_pad((string) $sectionNumber++, 2, '0', STR_PAD_LEFT)); ?></span>Registros recentes</h3>
        <?php if ($recentes !== []): ?>
            <div class="decree-print-table">
                <div class="decree-print-row panel-print-recent-row decree-print-row-head">
                    <span>Protocolo</span>
                    <span>Município</span>
                    <span>Desastre</span>
                    <span>Status</span>
                </div>
                <?php foreach ($recentes as $registro): ?>
                    <div class="decree-print-row panel-print-recent-row">
                        <span><?= e($valor($registro['protocolo_dgd'] ?? null)); ?></span>
                        <span><?= e($valor($registro['municipio'] ?? null)); ?></span>
                        <span><?= e($valor($registro['cobrade_tipo'] ?? 'Desastre')); ?> • <?= e($data($registro['data_desastre'] ?? null)); ?></span>
                        <span><?= e($valor($registro['homologacao'] ?? null)); ?> / <?= e($valor($registro['status_envio_pge'] ?? null)); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="decree-print-empty">Nenhum registro recente para o recorte atual.</p>
        <?php endif; ?>
    </section>

    <section class="decree-print-section">
        <h3><span><?= e(str_pad((string) $sectionNumber++, 2, '0', STR_PAD_LEFT)); ?></span>Decretos do recorte</h3>
        <?php if ($registros !== []): ?>
            <div class="decree-print-table">
                <div class="decree-print-row panel-print-register-row decree-print-row-head">
                    <span>Protocolo</span>
                    <span>Município</span>
                    <span>Desastre</span>
                    <span>Institucional</span>
                    <span>PGE</span>
                    <span>Afetados</span>
                </div>
                <?php foreach ($registros as $registro): ?>
                    <div class="decree-print-row panel-print-register-row">
                        <span><?= e($valor($registro['protocolo_dgd'] ?? null)); ?></span>
                        <span><?= e($valor($registro['municipio'] ?? null)); ?><br><small><?= e($valor($registro['compdec_regiao_integracao'] ?? null)); ?></small></span>
                        <span><?= e($valor($registro['cobrade_codigo'] ?? null)); ?> - <?= e($valor($registro['cobrade_subtipo'] ?? null)); ?><br><small><?= e($data($registro['data_desastre'] ?? null)); ?></small></span>
                        <span><?= e($valor($registro['homologacao'] ?? null)); ?><br><small><?= e($valor($registro['reconhecimento'] ?? null)); ?></small></span>
                        <span><?= e($valor($registro['status_prazo_pge_calculado'] ?? null)); ?><br><small><?= e($valor($registro['duracao_pge_dias'] ?? null)); ?> dia(s)</small></span>
                        <span class="panel-print-affected">
                            <strong><?= e($numero($registro['total_afetados'] ?? 0)); ?></strong>
                            <small>pessoas afetadas</small>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if (($resumo['total_desastres'] ?? 0) > count($registros)): ?>
                <p class="decree-print-empty">Exibindo os 200 registros mais recentes do recorte. Total filtrado: <?= e($numero($resumo['total_desastres'] ?? 0)); ?>.</p>
            <?php endif; ?>
        <?php else: ?>
            <p class="decree-print-empty">Nenhum decreto encontrado para o recorte atual.</p>
        <?php endif; ?>
    </section>

    <footer class="decree-print-footer">
        <span>DGD - Relatório gerencial do painel</span>
        <span>Recorte <?= e($valor($filters['ano'] ?? null)); ?></span>
        <span class="decree-print-page-number" data-decree-print-page-number>Página 1 de 1</span>
    </footer>
</article>
