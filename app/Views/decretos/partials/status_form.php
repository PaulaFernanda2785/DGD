<form method="post" action="<?= e(url('/decretos/' . $registro['id'] . '/status')); ?>" class="inline-status-form" data-history-modal data-history-summary="Atualização de <?= e(match ($campo) {
    'homologacao_status_id' => 'homologação',
    'reconhecimento_status_id' => 'reconhecimento',
    'status_envio_pge_id' => 'envio à PGE',
    default => 'status',
}); ?> do decreto <?= e($registro['protocolo_dgd']); ?>">
    <?= csrf_input(); ?>
    <input type="hidden" name="campo" value="<?= e($campo); ?>">
    <input type="hidden" name="historico_observacao" data-history-observation>
    <select name="valor">
        <?php foreach ($opcoes as $opcao): ?>
            <option value="<?= e($opcao['id']); ?>" <?= (string) $valorAtual === (string) $opcao['id'] ? 'selected' : ''; ?>>
                <?= e($opcao['nome']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit">Salvar</button>
</form>
