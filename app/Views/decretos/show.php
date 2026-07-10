<?php
    $valueOrDash = static fn (mixed $value): string => trim((string) $value) !== '' ? (string) $value : '-';
    $formatDate = static fn (mixed $value): string => !empty($value) ? date('d/m/Y', strtotime((string) $value)) : '-';
    $pgeResultadoCodigo = (string) ($registro['status_envio_pge_codigo'] ?? '');
    $pgeResultadoLabel = match ($pgeResultadoCodigo) {
        'APROVADO' => 'Aprovado PGE',
        'REPROVADO' => 'Reprovado PGE',
        default => 'Data de conclusão PGE',
    };
    $pgeResultadoData = in_array($pgeResultadoCodigo, ['APROVADO', 'REPROVADO'], true)
        ? ($registro['data_decreto_homologacao'] ?? $registro['data_conclusao_pge'] ?? null)
        : ($registro['data_conclusao_pge'] ?? null);
    $homologacaoDataLabel = (string) ($registro['homologacao_codigo'] ?? '') === 'NAO_HOMOLOGADO'
        ? 'Data da não homologação'
        : 'Data de homologação';
?>

<div class="page-header page-header-modern decree-detail-header">
    <div>
        <span class="breadcrumb">Decretos &gt; Detalhe</span>
        <h1><?= e($registro['protocolo_dgd']); ?></h1>
        <p><?= e($registro['municipio']); ?> · <?= e($formatDate($registro['data_desastre'])); ?> · <?= e($registro['tipo_decreto']); ?></p>
    </div>

    <div class="actions">
        <a class="button button-light" href="<?= e(url('/decretos')); ?>">Voltar</a>
        <?php if (can('decretos.editar')): ?>
            <a class="button button-primary" href="<?= e(url('/decretos/' . $registro['id'] . '/editar')); ?>">Editar</a>
        <?php endif; ?>
    </div>
</div>

<section class="detail-hero detail-hero-modern">
    <div>
        <span>Município</span>
        <strong><?= e($registro['municipio']); ?></strong>
    </div>
    <div>
        <span>COBRADE</span>
        <strong><?= e($registro['cobrade_codigo'] . ' - ' . $registro['cobrade_subtipo']); ?></strong>
    </div>
    <div>
        <span>Total de afetados</span>
        <strong><?= e($registro['total_afetados']); ?></strong>
    </div>
    <div>
        <span>Status PGE</span>
        <?= status_badge($registro['status_prazo_pge_calculado']); ?>
    </div>
</section>

<section class="detail-section detail-overview-section">
    <div class="detail-section-heading">
        <div>
            <span>01</span>
            <h2>Dados gerais e COMPDEC</h2>
        </div>
        <p>Informações principais do município, COMPDEC, UBM atuante e classificação do decreto.</p>
    </div>

    <div class="detail-card-grid">
        <div><strong>Município</strong><span><?= e($registro['municipio']); ?></span></div>
        <div><strong>UBM atuante</strong><span><?= e($valueOrDash($registro['ubm_atuante'] ?? null)); ?></span></div>
        <div><strong>Região de integração</strong><span><?= e($valueOrDash($registro['compdec_regiao_integracao'] ?? null)); ?></span></div>
        <div><strong>Prefeito</strong><span><?= e($valueOrDash($registro['compdec_prefeito'] ?? null)); ?></span></div>
        <div><strong>Coordenador COMPDEC</strong><span><?= e($valueOrDash($registro['compdec_coordenador'] ?? null)); ?></span></div>
        <div><strong>Telefone COMPDEC</strong><span><?= e($valueOrDash($registro['compdec_telefone'] ?? null)); ?></span></div>
        <div><strong>E-mail COMPDEC</strong><span><?= e($valueOrDash($registro['compdec_email'] ?? null)); ?></span></div>
        <div><strong>Tipo de decreto</strong><span><?= e($registro['tipo_decreto']); ?></span></div>
        <div><strong>Data do desastre</strong><span><?= e($formatDate($registro['data_desastre'])); ?></span></div>
        <div><strong>Grupo COBRADE</strong><span><?= e($registro['cobrade_grupo']); ?></span></div>
    </div>
</section>

<section class="detail-section detail-institutional-section">
    <div class="detail-section-heading">
        <div>
            <span>02</span>
            <h2>Atos institucionais</h2>
        </div>
        <p>Protocolos, decretos, homologação, reconhecimento federal e acompanhamento da PGE.</p>
    </div>

    <div class="detail-card-grid">
        <div><strong>Protocolo S2ID</strong><span><?= e($valueOrDash($registro['protocolo_s2id'] ?? null)); ?></span></div>
        <div><strong>Decreto municipal</strong><span><?= e($valueOrDash($registro['numero_decreto_municipal'] ?? null)); ?></span></div>
        <div><strong>Data do decreto municipal</strong><span><?= e($formatDate($registro['data_decreto_municipal'] ?? null)); ?></span></div>
        <div><strong>Homologação</strong><span><?= status_badge($registro['homologacao']); ?></span></div>
        <div><strong><?= e($homologacaoDataLabel); ?></strong><span><?= e($formatDate($registro['data_decreto_homologacao'] ?? null)); ?></span></div>
        <div><strong>Reconhecimento</strong><span><?= status_badge($registro['reconhecimento']); ?></span></div>
        <div><strong>Protocolo PAE/PGE</strong><span><?= e($valueOrDash($registro['protocolo_pae_pge'] ?? null)); ?></span></div>
        <div><strong>Envio à PGE</strong><span><?= status_badge($registro['status_envio_pge']); ?></span></div>
        <div><strong>Data de envio à PGE</strong><span><?= e($formatDate($registro['data_envio_pge'] ?? null)); ?></span></div>
        <div><strong><?= e($pgeResultadoLabel); ?></strong><span><?= e($formatDate($pgeResultadoData)); ?></span></div>
        <div><strong>Dias PGE</strong><span><?= e($valueOrDash($registro['duracao_pge_dias'] ?? null)); ?></span></div>
        <div><strong>Status PGE</strong><span><?= status_badge($registro['status_prazo_pge_calculado'] ?? null); ?></span></div>
        <div><strong>Analista</strong><span><?= e($valueOrDash($registro['analista'] ?? null)); ?></span></div>
    </div>
</section>

<section class="detail-section detail-damage-section">
    <div class="detail-section-heading">
        <div>
            <span>03</span>
            <h2>Danos humanos</h2>
        </div>
        <p>Quantitativos registrados para compor o total de pessoas afetadas.</p>
    </div>

    <div class="human-damage-grid">
        <?php foreach ([
            'numero_obitos' => 'Óbitos',
            'numero_feridos' => 'Feridos',
            'numero_enfermos' => 'Enfermos',
            'numero_desabrigados' => 'Desabrigados',
            'numero_desalojados' => 'Desalojados',
            'numero_outros_afetados' => 'Outros afetados',
        ] as $field => $label): ?>
            <div>
                <span><?= e($label); ?></span>
                <strong><?= e($registro[$field]); ?></strong>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<?php if (!empty($registro['observacoes'])): ?>
    <section class="detail-section observation-detail-section">
        <div class="detail-section-heading">
            <div>
                <span>04</span>
                <h2>Observações</h2>
            </div>
        </div>
        <p class="detail-observation"><?= nl2br(e($registro['observacoes'])); ?></p>
    </section>
<?php endif; ?>

<section class="detail-section evidence-detail-section">
    <div class="detail-section-heading">
        <div>
            <span>05</span>
            <h2>Anexos</h2>
        </div>
        <p>Documentos vinculados ao registro. É possível selecionar, arrastar ou colar um arquivo para envio.</p>
    </div>

    <?php if (can('anexos.upload')): ?>
        <form method="post" action="<?= e(url('/decretos/' . $registro['id'] . '/anexos')); ?>" enctype="multipart/form-data" class="detail-upload-form" data-history-modal data-history-summary="Inclusão de anexo no decreto <?= e($registro['protocolo_dgd']); ?>">
            <?= csrf_input(); ?>
            <input type="hidden" name="historico_observacao" data-history-observation>

            <div class="field">
                <label for="tipo_anexo_id">Tipo de anexo</label>
                <select id="tipo_anexo_id" name="tipo_anexo_id" required>
                    <option value="">Selecione</option>
                    <?php foreach ($dominios['tiposAnexo'] as $tipo): ?>
                        <option value="<?= e($tipo['id']); ?>"><?= e($tipo['nome']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="attachment-dropzone detail-attachment-dropzone" data-attachment-zone tabindex="0">
                <div class="attachment-head">
                    <strong>Novo anexo</strong>
                    <span>PDF, DOC, DOCX, JPG ou PNG</span>
                </div>
                <p>Selecione, arraste ou cole o arquivo neste bloco.</p>
                <input id="arquivo_anexo" class="attachment-input" type="file" name="arquivo" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required data-attachment-input>
                <label class="button button-light" for="arquivo_anexo">Selecionar arquivo</label>
                <ul class="attachment-list" data-attachment-list>
                    <li>Nenhum arquivo selecionado.</li>
                </ul>
            </div>

            <div class="field">
                <label for="descricao_anexo">Descrição</label>
                <input id="descricao_anexo" name="descricao" placeholder="Descrição opcional">
            </div>

            <button type="submit" class="button button-secondary">Enviar anexo</button>
        </form>
    <?php endif; ?>

    <div class="attachment-card-list">
        <?php foreach ($registro['anexos'] as $anexo): ?>
            <article class="attachment-card">
                <div>
                    <span><?= e($anexo['tipo_anexo']); ?></span>
                    <strong><?= e($anexo['nome_original']); ?></strong>
                    <small><?= e(number_format((int) $anexo['tamanho_bytes'] / 1024, 1, ',', '.')); ?> KB · <?= e($anexo['enviado_em']); ?></small>
                </div>
                <div class="attachment-actions">
                    <a class="button button-light" href="<?= e(url('/anexos/' . $anexo['id'] . '/ver')); ?>" target="_blank" rel="noopener noreferrer">Ver</a>
                    <a class="button button-light" href="<?= e(url('/anexos/' . $anexo['id'] . '/download')); ?>">Baixar</a>
                    <?php if (can('anexos.excluir')): ?>
                        <form method="post" action="<?= e(url('/anexos/' . $anexo['id'] . '/excluir')); ?>">
                            <?= csrf_input(); ?>
                            <button class="button button-danger" type="submit" data-confirm="Deseja remover este anexo?">Excluir</button>
                        </form>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>

        <?php if ($registro['anexos'] === []): ?>
            <div class="panel-empty">Nenhum anexo cadastrado.</div>
        <?php endif; ?>
    </div>
</section>

<section class="detail-section history-section">
    <div class="detail-section-heading">
        <div>
            <span>06</span>
            <h2>Histórico de edição</h2>
        </div>
        <p>Registro cronológico das alterações, anexos incluídos, usuário responsável e observações informadas.</p>
    </div>

    <div class="history-timeline">
        <?php foreach (($registro['historico'] ?? []) as $historico): ?>
            <article class="history-item">
                <div class="history-marker"></div>
                <div class="history-content">
                    <div class="history-head">
                        <div>
                            <strong><?= e($historico['campo']); ?></strong>
                            <span><?= e($historico['usuario_nome'] ?? 'Usuário não identificado'); ?></span>
                        </div>
                        <time><?= e(date('d/m/Y H:i', strtotime((string) $historico['criado_em']))); ?></time>
                    </div>

                    <?php if (($historico['valor_anterior'] ?? null) !== null || ($historico['valor_novo'] ?? null) !== null): ?>
                        <div class="history-values">
                            <div>
                                <span>Anterior</span>
                                <strong><?= e($valueOrDash($historico['valor_anterior'] ?? null)); ?></strong>
                            </div>
                            <div>
                                <span>Novo</span>
                                <strong><?= e($valueOrDash($historico['valor_novo'] ?? null)); ?></strong>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($historico['justificativa'])): ?>
                        <p><?= nl2br(e($historico['justificativa'])); ?></p>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>

        <?php if (($registro['historico'] ?? []) === []): ?>
            <div class="panel-empty">Nenhum histórico registrado para este decreto.</div>
        <?php endif; ?>
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
