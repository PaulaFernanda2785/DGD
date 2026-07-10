<form method="post" action="<?= e(url('/decretos/' . $registro['id'] . '/status')); ?>" class="inline-status-form" data-history-modal data-history-summary="Atualização de <?= e(match ($campo) {
    'homologacao_status_id' => 'homologação',
    'reconhecimento_status_id' => 'reconhecimento',
    'status_envio_pge_id' => 'envio à PGE',
    default => 'status',
}); ?> do decreto <?= e($registro['protocolo_dgd']); ?>">
    <?= csrf_input(); ?>
    <input type="hidden" name="campo" value="<?= e($campo); ?>">
    <input type="hidden" name="historico_observacao" data-history-observation>
    <?php if ($campo === 'homologacao_status_id'): ?>
        <input type="hidden" name="data_envio_pge" value="<?= e($registro['data_envio_pge'] ?? ''); ?>" data-pge-date-target>
        <input type="hidden" name="data_decreto_homologacao" value="<?= e($registro['data_decreto_homologacao'] ?? ''); ?>" data-homologacao-date-target>
    <?php endif; ?>
    <select name="valor">
        <?php foreach ($opcoes as $opcao): ?>
            <option value="<?= e($opcao['id']); ?>" data-codigo="<?= e($opcao['codigo'] ?? ''); ?>" <?= (string) $valorAtual === (string) $opcao['id'] ? 'selected' : ''; ?>>
                <?= e($opcao['nome']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit" aria-label="Salvar status">Salvar</button>
</form>
