<?php
    $totalRegistros = (int) ($paginacao['total'] ?? count($compdecs));
    $comCompdecTotal = (int) ($resumo['com_compdec'] ?? 0);
    $semCompdecTotal = (int) ($resumo['sem_compdec'] ?? 0);
    $dash = static function (mixed $value): string {
        $text = trim((string) $value);

        return $text !== '' && $text !== 'Nao foi registrado' ? $text : 'Não foi registrado';
    };
    $isSelected = static fn (string $key, string $value): string => (string) ($filtros[$key] ?? '') === $value ? 'selected' : '';
?>

<div class="page-header page-header-modern compdecs-page-header">
    <div>
        <span class="breadcrumb">COMPDECs &gt; Gestão</span>
        <h1>Gestão das COMPDECs</h1>
        <p>Consulta operacional dos municípios, coordenadores, UBM atuante e contatos cadastrados.</p>
    </div>
</div>

<section class="compdec-overview-grid" aria-label="Resumo das COMPDECs">
    <div>
        <span>Registros filtrados</span>
        <strong><?= e($totalRegistros); ?></strong>
    </div>
    <div>
        <span>Com COMPDEC</span>
        <strong><?= e($comCompdecTotal); ?></strong>
    </div>
    <div>
        <span>Sem COMPDEC</span>
        <strong><?= e($semCompdecTotal); ?></strong>
    </div>
</section>

<form method="get" action="<?= e(url('/compdecs')); ?>" class="compdec-filter-panel" aria-label="Filtros de COMPDEC">
    <div class="field compdec-filter-search">
        <label for="busca">Busca inteligente</label>
        <input id="busca" name="busca" value="<?= e($filtros['busca'] ?? ''); ?>" placeholder="Município, prefeito, coordenador, telefone, e-mail ou UBM">
    </div>

    <div class="field">
        <label for="regiao_integracao">Região</label>
        <select id="regiao_integracao" name="regiao_integracao">
            <option value="">Todas as regiões</option>
            <?php foreach ($regioes as $regiao): ?>
                <option value="<?= e($regiao); ?>" <?= (string) ($filtros['regiao_integracao'] ?? '') === (string) $regiao ? 'selected' : ''; ?>>
                    <?= e($regiao); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="field">
        <label for="tem_compdec">Situação</label>
        <select id="tem_compdec" name="tem_compdec">
            <option value="">Todas</option>
            <option value="1" <?= $isSelected('tem_compdec', '1'); ?>>Com COMPDEC</option>
            <option value="0" <?= $isSelected('tem_compdec', '0'); ?>>Sem COMPDEC</option>
        </select>
    </div>

    <div class="field">
        <label for="ubm">UBM atuante</label>
        <select id="ubm" name="ubm">
            <option value="">Todas as UBM</option>
            <?php foreach (($ubms ?? []) as $ubm): ?>
                <option value="<?= e($ubm); ?>" <?= (string) ($filtros['ubm'] ?? '') === (string) $ubm ? 'selected' : ''; ?>>
                    <?= e($ubm); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="compdec-filter-actions">
        <button type="submit" class="button button-primary">Filtrar</button>
        <a class="button button-light" href="<?= e(url('/compdecs')); ?>">Limpar</a>
    </div>
</form>

<section class="compdec-card-list" aria-label="Listagem de COMPDECs">
    <?php foreach ($compdecs as $compdec): ?>
        <?php
            $possuiCompdec = (int) ($compdec['tem_compdec'] ?? 0) === 1;
            $contatoResumo = trim((string) ($compdec['telefone'] ?? '')) !== '' || trim((string) ($compdec['email'] ?? '')) !== '';
        ?>
        <article class="compdec-card">
            <header class="compdec-card-header">
                <div class="compdec-card-person">
                    <?= compdec_photo_thumb($compdec['foto_coordenador'] ?? null, $compdec['coordenador'] ?? 'Coordenador COMPDEC'); ?>
                    <div>
                        <span><?= e($compdec['municipio']); ?></span>
                        <h2><?= e($dash($compdec['coordenador'] ?? null)); ?></h2>
                        <p><?= e($dash($compdec['regiao_integracao'] ?? null)); ?></p>
                    </div>
                </div>

                <div class="compdec-card-actions">
                    <a class="button button-light" href="<?= e(url('/compdecs/' . $compdec['id'])); ?>">Ver</a>
                    <?php if (can('compdecs.editar')): ?>
                        <a class="button button-secondary" href="<?= e(url('/compdecs/' . $compdec['id'] . '/editar')); ?>">Editar</a>
                    <?php endif; ?>
                </div>
            </header>

            <div class="compdec-status-row">
                <span class="status-badge <?= $possuiCompdec ? 'badge-success' : 'badge-warning'; ?>">
                    <?= $possuiCompdec ? 'Possui COMPDEC' : 'Não possui COMPDEC'; ?>
                </span>
                <span class="status-badge <?= $contatoResumo ? 'badge-info' : 'badge-muted'; ?>">
                    <?= $contatoResumo ? 'Contato registrado' : 'Contato pendente'; ?>
                </span>
            </div>

            <div class="compdec-card-grid">
                <div>
                    <span>UBM atuante</span>
                    <strong><?= e($dash($compdec['ubm_nome'] ?? null)); ?></strong>
                </div>
                <div>
                    <span>Prefeito</span>
                    <strong><?= e($dash($compdec['prefeito'] ?? null)); ?></strong>
                </div>
                <div>
                    <span>Telefone</span>
                    <strong><?= e($dash($compdec['telefone'] ?? null)); ?></strong>
                </div>
                <div>
                    <span>E-mail</span>
                    <strong><?= e($dash($compdec['email'] ?? null)); ?></strong>
                </div>
            </div>
        </article>
    <?php endforeach; ?>

    <?php if ($compdecs === []): ?>
        <div class="compdec-empty-state" role="status" aria-live="polite">
            <div class="compdec-empty-icon" aria-hidden="true">C</div>
            <div>
                <strong>Nenhuma COMPDEC encontrada</strong>
                <p>Revise os filtros aplicados ou limpe a busca para visualizar todos os municípios cadastrados.</p>
            </div>
            <a class="button button-light" href="<?= e(url('/compdecs')); ?>">Limpar filtros</a>
        </div>
    <?php endif; ?>
</section>

<?php
$baseUrl = url('/compdecs');
$query = $filtros;
require view_path('compdecs/partials/paginacao');
?>
