<div class="page-header">
    <div>
        <span class="breadcrumb">COMPDECs</span>
        <h1>Gestao das COMPDECs</h1>
    </div>
</div>

<form method="get" action="<?= e(url('/compdecs')); ?>" class="filters compdec-filters">
    <input name="busca" value="<?= e($filtros['busca'] ?? ''); ?>" placeholder="Buscar por municipio, prefeito, coordenador ou UBM">

    <select name="regiao_integracao">
        <option value="">Todas as regioes</option>
        <?php foreach ($regioes as $regiao): ?>
            <option value="<?= e($regiao); ?>" <?= (string) ($filtros['regiao_integracao'] ?? '') === (string) $regiao ? 'selected' : ''; ?>>
                <?= e($regiao); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="tem_compdec">
        <option value="">Todas</option>
        <option value="1" <?= (string) ($filtros['tem_compdec'] ?? '') === '1' ? 'selected' : ''; ?>>Com COMPDEC</option>
        <option value="0" <?= (string) ($filtros['tem_compdec'] ?? '') === '0' ? 'selected' : ''; ?>>Sem COMPDEC</option>
    </select>

    <button type="submit" class="button button-secondary">Filtrar</button>
    <a class="button button-light" href="<?= e(url('/compdecs')); ?>">Limpar</a>
</form>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Foto</th>
                <th>Municipio</th>
                <th>Regiao</th>
                <th>COMPDEC</th>
                <th>UBM atuante</th>
                <th>Prefeito</th>
                <th>Coordenador</th>
                <th>Telefone</th>
                <th>E-mail</th>
                <th>Acoes</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($compdecs as $compdec): ?>
                <tr>
                    <td><?= compdec_photo_thumb($compdec['foto_coordenador'] ?? null, $compdec['coordenador'] ?? 'Coordenador COMPDEC'); ?></td>
                    <td><?= e($compdec['municipio']); ?></td>
                    <td><?= e($compdec['regiao_integracao'] ?? '-'); ?></td>
                    <td><?= (int) $compdec['tem_compdec'] === 1 ? 'Sim' : 'Nao'; ?></td>
                    <td><?= e($compdec['ubm_nome'] ?? '-'); ?></td>
                    <td><?= e($compdec['prefeito'] ?? '-'); ?></td>
                    <td><?= e($compdec['coordenador'] ?? '-'); ?></td>
                    <td><?= e($compdec['telefone'] ?? '-'); ?></td>
                    <td><?= e($compdec['email'] ?? '-'); ?></td>
                    <td class="actions">
                        <a href="<?= e(url('/compdecs/' . $compdec['id'])); ?>">Ver</a>
                        <?php if (can('compdecs.editar')): ?>
                            <a href="<?= e(url('/compdecs/' . $compdec['id'] . '/editar')); ?>">Editar</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if ($compdecs === []): ?>
                <tr><td colspan="10">Nenhuma COMPDEC encontrada.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$baseUrl = url('/compdecs');
$query = $filtros;
require view_path('compdecs/partials/paginacao');
?>
