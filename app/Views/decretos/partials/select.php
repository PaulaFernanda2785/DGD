<select name="<?= e($name); ?>">
    <?php foreach ($options as $option): ?>
        <?php $selected = (string) old($name, $registro[$name] ?? '1') === (string) $option['id']; ?>
        <option value="<?= e($option['id']); ?>" data-codigo="<?= e($option['codigo'] ?? ''); ?>" <?= $selected ? 'selected' : ''; ?>><?= e($option['nome']); ?></option>
    <?php endforeach; ?>
</select>
