<?php
    $valor = static fn (mixed $value): string => trim((string) $value) !== '' ? (string) $value : 'Não informado';
    $data = static fn (mixed $value): string => !empty($value) ? date('d/m/Y', strtotime((string) $value)) : 'Não informado';
    $numero = static fn (mixed $value): string => number_format((float) ($value ?? 0), 0, ',', '.');
    $usuario = \App\Core\Auth::user();
    $geradoEm ??= new DateTimeImmutable('now');
    $pgeResultadoCodigo = (string) ($registro['status_envio_pge_codigo'] ?? '');
    $pgeResultadoLabel = match ($pgeResultadoCodigo) {
        'APROVADO' => 'Data de aprovação PGE',
        'REPROVADO' => 'Data de reprovação PGE',
        default => 'Data de conclusão PGE',
    };
    $pgeResultadoData = in_array($pgeResultadoCodigo, ['APROVADO', 'REPROVADO'], true)
        ? ($registro['data_decreto_homologacao'] ?? $registro['data_conclusao_pge'] ?? null)
        : ($registro['data_conclusao_pge'] ?? null);
    $homologacaoDataLabel = (string) ($registro['homologacao_codigo'] ?? '') === 'NAO_HOMOLOGADO'
        ? 'Data da não homologação'
        : 'Data de homologação';
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

        return $codigo !== '' ? url('/assets/images/cobrade_simbologia/simbologia_cobrade_' . str_replace('.', '_', $codigo) . '.png') : null;
    };
    $simbologiaUrl = $cobradeSymbolUrl($registro['cobrade_simbologia'] ?? null, $registro['cobrade_codigo'] ?? null);
    $descricaoDesastre = $valor($registro['cobrade_descricao'] ?? null);
    $normalizarStatus = static function (mixed $value): string {
        $text = mb_strtoupper(trim((string) $value), 'UTF-8');
        $text = strtr($text, [
            'Á' => 'A',
            'À' => 'A',
            'Â' => 'A',
            'Ã' => 'A',
            'Ä' => 'A',
            'É' => 'E',
            'È' => 'E',
            'Ê' => 'E',
            'Ë' => 'E',
            'Í' => 'I',
            'Ì' => 'I',
            'Î' => 'I',
            'Ï' => 'I',
            'Ó' => 'O',
            'Ò' => 'O',
            'Ô' => 'O',
            'Õ' => 'O',
            'Ö' => 'O',
            'Ú' => 'U',
            'Ù' => 'U',
            'Û' => 'U',
            'Ü' => 'U',
            'Ç' => 'C',
        ]);

        return preg_replace('/[^A-Z0-9]+/', '_', $text) ?? '';
    };
    $classeDestaque = static function (string $campo, mixed $codigo, mixed $value) use ($normalizarStatus): string {
        $status = $normalizarStatus(trim((string) $codigo) . ' ' . trim((string) $value));

        if ($campo === 'tipo_decreto') {
            if (str_contains($status, 'CALAMIDADE')) {
                return 'decree-print-highlight decree-print-highlight--danger';
            }

            if (str_contains($status, 'EMERGENCIA')) {
                return 'decree-print-highlight decree-print-highlight--warning';
            }

            return 'decree-print-highlight decree-print-highlight--muted';
        }

        if (
            str_contains($status, 'NAO_HOMOLOGADO')
            || str_contains($status, 'NAO_RECONHECIDO')
            || str_contains($status, 'REPROVADO')
            || str_contains($status, 'INDEFERIDO')
        ) {
            return 'decree-print-highlight decree-print-highlight--danger';
        }

        if (
            str_contains($status, 'HOMOLOGADO')
            || str_contains($status, 'RECONHECIDO')
            || str_contains($status, 'APROVADO')
            || str_contains($status, 'CONCLUIDO')
        ) {
            return 'decree-print-highlight decree-print-highlight--success';
        }

        if (
            str_contains($status, 'ENVIADO')
            || str_contains($status, 'ANALISE')
            || str_contains($status, 'NO_PRAZO')
        ) {
            return 'decree-print-highlight decree-print-highlight--info';
        }

        if (
            str_contains($status, 'PENDENTE')
            || str_contains($status, 'SOLICITADO')
            || str_contains($status, 'PREPARACAO')
            || str_contains($status, 'AGUARDANDO')
        ) {
            return 'decree-print-highlight decree-print-highlight--warning';
        }

        return 'decree-print-highlight decree-print-highlight--muted';
    };
    $destaquesRelatorio = [
        'Tipo de decreto' => $classeDestaque('tipo_decreto', $registro['tipo_decreto_codigo'] ?? null, $registro['tipo_decreto'] ?? null),
        'Homologação' => $classeDestaque('homologacao', $registro['homologacao_codigo'] ?? null, $registro['homologacao'] ?? null),
        'Reconhecimento federal' => $classeDestaque('reconhecimento', $registro['reconhecimento_codigo'] ?? null, $registro['reconhecimento'] ?? null),
        'Envio à PGE' => $classeDestaque('envio_pge', $registro['status_envio_pge_codigo'] ?? null, $registro['status_envio_pge'] ?? null),
        'Status PGE' => $classeDestaque('status_pge', $registro['status_prazo_pge_calculado'] ?? null, $registro['status_prazo_pge_calculado'] ?? null),
    ];
    $blocos = [
        'Dados do município e COMPDEC' => [
            'Município' => $valor($registro['municipio'] ?? null),
            'UF' => $valor($registro['uf'] ?? 'PA'),
            'Região de integração' => $valor($registro['compdec_regiao_integracao'] ?? null),
            'Prefeito' => $valor($registro['compdec_prefeito'] ?? null),
            'Coordenador COMPDEC' => $valor($registro['compdec_coordenador'] ?? null),
            'Telefone COMPDEC' => $valor($registro['compdec_telefone'] ?? null),
            'E-mail COMPDEC' => $valor($registro['compdec_email'] ?? null),
            'UBM atuante' => $valor($registro['ubm_atuante'] ?? null),
        ],
        'Classificação oficial COBRADE' => [
            'Grupo' => $valor($registro['cobrade_grupo'] ?? null),
            'Subgrupo' => $valor($registro['cobrade_subgrupo'] ?? null),
            'Tipo' => $valor($registro['cobrade_tipo'] ?? null),
            'Subtipo' => $valor($registro['cobrade_subtipo'] ?? null),
            'Código COBRADE' => $valor($registro['cobrade_codigo'] ?? null),
            'Data do desastre' => $data($registro['data_desastre'] ?? null),
        ],
        'Atos e acompanhamento institucional' => [
            'Tipo de decreto' => $valor($registro['tipo_decreto'] ?? null),
            'Protocolo S2ID' => $valor($registro['protocolo_s2id'] ?? null),
            'Número do decreto municipal' => $valor($registro['numero_decreto_municipal'] ?? null),
            'Data do decreto municipal' => $data($registro['data_decreto_municipal'] ?? null),
            'Dias do decreto' => $valor($registro['total_dias_decreto'] ?? null),
            'Homologação' => $valor($registro['homologacao'] ?? null),
            $homologacaoDataLabel => $data($registro['data_decreto_homologacao'] ?? null),
            'Reconhecimento federal' => $valor($registro['reconhecimento'] ?? null),
            'Protocolo PAE/PGE' => $valor($registro['protocolo_pae_pge'] ?? null),
            'Envio à PGE' => $valor($registro['status_envio_pge'] ?? null),
            'Data de envio à PGE' => $data($registro['data_envio_pge'] ?? null),
            $pgeResultadoLabel => $data($pgeResultadoData),
            'Dias PGE' => $valor($registro['duracao_pge_dias'] ?? null),
            'Status PGE' => $valor($registro['status_prazo_pge_calculado'] ?? null),
            'Analista responsável' => $valor($registro['analista'] ?? null),
        ],
        'Recursos e danos humanos' => [
            'Recurso de resposta' => $valor($registro['recurso_resposta'] ?? null),
            'Recurso de reconstrução' => $valor($registro['recurso_reconstrucao'] ?? null),
            'Óbitos' => $numero($registro['numero_obitos'] ?? 0),
            'Feridos' => $numero($registro['numero_feridos'] ?? 0),
            'Enfermos' => $numero($registro['numero_enfermos'] ?? 0),
            'Desabrigados' => $numero($registro['numero_desabrigados'] ?? 0),
            'Desalojados' => $numero($registro['numero_desalojados'] ?? 0),
            'Outros afetados' => $numero($registro['numero_outros_afetados'] ?? 0),
            'Total de afetados' => $numero($registro['total_afetados'] ?? 0),
        ],
    ];
?>

<article class="decree-print-report" data-decree-print-content>
    <header class="decree-print-header">
        <div class="decree-print-brand">
            <img src="<?= e(url('/assets/img/logo-cedec.png')); ?>" alt="CEDEC-PA">
            <div>
                <strong>Defesa Civil do Estado do Pará</strong>
                <span>Sistema DGD - Gestão de Desastres e Decretos</span>
            </div>
        </div>
        <div class="decree-print-meta">
            <span>Relatório do processo</span>
            <strong><?= e($registro['protocolo_dgd'] ?? 'Não informado'); ?></strong>
            <small>Gerado em <?= e($geradoEm->format('d/m/Y H:i')); ?> por <?= e($usuario['nome'] ?? 'Usuário autenticado'); ?></small>
        </div>
    </header>

    <section class="decree-print-cover">
        <div>
            <span>Município</span>
            <h2><?= e($valor($registro['municipio'] ?? null)); ?></h2>
            <p><?= e($valor($registro['cobrade_codigo'] ?? null)); ?> - <?= e($valor($registro['cobrade_subtipo'] ?? null)); ?></p>
        </div>
        <div class="decree-print-disaster-info">
            <?php if ($simbologiaUrl !== null): ?>
                <img src="<?= e($simbologiaUrl); ?>" alt="Simbologia COBRADE">
            <?php endif; ?>
            <div>
                <span>Descrição do desastre</span>
                <p><?= e($descricaoDesastre); ?></p>
            </div>
        </div>
    </section>

    <section class="decree-print-summary">
        <div><span>Protocolo DGD</span><strong><?= e($registro['protocolo_dgd'] ?? 'Não informado'); ?></strong></div>
        <div class="<?= e($destaquesRelatorio['Tipo de decreto']); ?>"><span>Tipo de decreto</span><strong><?= e($valor($registro['tipo_decreto'] ?? null)); ?></strong></div>
        <div class="<?= e($destaquesRelatorio['Status PGE']); ?>"><span>Status PGE</span><strong><?= e($valor($registro['status_prazo_pge_calculado'] ?? null)); ?></strong></div>
        <div><span>Total de afetados</span><strong><?= e($numero($registro['total_afetados'] ?? 0)); ?></strong></div>
    </section>

    <?php $sectionNumber = 1; ?>
    <?php foreach ($blocos as $titulo => $campos): ?>
        <section class="decree-print-section">
            <h3><span><?= e(str_pad((string) $sectionNumber, 2, '0', STR_PAD_LEFT)); ?></span><?= e($titulo); ?></h3>
            <div class="decree-print-grid">
                <?php foreach ($campos as $label => $value): ?>
                    <div<?= isset($destaquesRelatorio[$label]) ? ' class="' . e($destaquesRelatorio[$label]) . '"' : ''; ?>>
                        <span><?= e($label); ?></span>
                        <strong><?= e($value); ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php $sectionNumber++; ?>
    <?php endforeach; ?>

    <section class="decree-print-section">
        <h3><span><?= e(str_pad((string) $sectionNumber, 2, '0', STR_PAD_LEFT)); ?></span>Observações complementares</h3>
        <p class="decree-print-text"><?= nl2br(e($valor($registro['observacoes'] ?? null))); ?></p>
    </section>
    <?php $sectionNumber++; ?>

    <section class="decree-print-section">
        <h3><span><?= e(str_pad((string) $sectionNumber, 2, '0', STR_PAD_LEFT)); ?></span>Documentos e evidências</h3>
        <?php if (($registro['anexos'] ?? []) !== []): ?>
            <div class="decree-print-table">
                <div class="decree-print-row decree-print-row-head">
                    <span>Tipo</span>
                    <span>Arquivo</span>
                    <span>Enviado em</span>
                    <span>Tamanho</span>
                </div>
                <?php foreach ($registro['anexos'] as $anexo): ?>
                    <div class="decree-print-row">
                        <span><?= e($valor($anexo['tipo_anexo'] ?? null)); ?></span>
                        <span><?= e($valor($anexo['nome_original'] ?? null)); ?></span>
                        <span><?= e($data($anexo['enviado_em'] ?? null)); ?></span>
                        <span><?= e(number_format((int) ($anexo['tamanho_bytes'] ?? 0) / 1024, 1, ',', '.')); ?> KB</span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="decree-print-empty">Nenhum anexo cadastrado.</p>
        <?php endif; ?>
    </section>
    <?php $sectionNumber++; ?>

    <section class="decree-print-section">
        <h3><span><?= e(str_pad((string) $sectionNumber, 2, '0', STR_PAD_LEFT)); ?></span>Histórico de edição</h3>
        <?php if (($registro['historico'] ?? []) !== []): ?>
            <div class="decree-print-history">
                <?php foreach ($registro['historico'] as $historico): ?>
                    <div>
                        <header>
                            <strong><?= e($valor($historico['campo'] ?? null)); ?></strong>
                            <time><?= e(!empty($historico['criado_em']) ? date('d/m/Y H:i', strtotime((string) $historico['criado_em'])) : 'Data não informada'); ?></time>
                        </header>
                        <p>Usuário: <?= e($valor($historico['usuario_nome'] ?? null)); ?></p>
                        <?php if (($historico['valor_anterior'] ?? null) !== null || ($historico['valor_novo'] ?? null) !== null): ?>
                            <p>Anterior: <?= e($valor($historico['valor_anterior'] ?? null)); ?> | Novo: <?= e($valor($historico['valor_novo'] ?? null)); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($historico['justificativa'])): ?>
                            <p>Observação: <?= nl2br(e($historico['justificativa'])); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="decree-print-empty">Nenhum histórico registrado para este decreto.</p>
        <?php endif; ?>
    </section>

    <footer class="decree-print-footer">
        <span>DGD - Relatório administrativo do decreto</span>
        <span><?= e($registro['protocolo_dgd'] ?? ''); ?></span>
        <span class="decree-print-page-number" data-decree-print-page-number>Página 1 de 1</span>
    </footer>
</article>
