<?php if (!empty($errors) && is_array($errors)): ?>
    <div class="alert alert-error">
        <strong>Verifique os campos informados.</strong>
        <ul>
            <?php foreach ($errors as $messages): ?>
                <?php foreach ((array) $messages as $message): ?>
                    <li><?= e($message); ?></li>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
