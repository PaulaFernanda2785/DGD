<form method="post" action="<?= e(url('/decretos/' . $registro['id'] . '/status')); ?>" class="inline-status-form">
    <?= csrf_input(); ?>
    <input type="hidden" name="campo" value="<?= e($campo); ?>">
    <select name="valor">
        <?php foreach ($opcoes as $opcao): ?>
            <option value="<?= e($opcao['id']); ?>" <?= (string) $valorAtual === (string) $opcao['id'] ? 'selected' : ''; ?>>
                <?= e($opcao['nome']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit">Salvar</button>
</form>
