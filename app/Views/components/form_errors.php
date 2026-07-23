<?php if (!empty($errors) && is_array($errors)): ?>
    <div class="alert alert-error form-error-summary" role="alert">
        <strong>Não foi possível salvar ainda.</strong>
        <p>Corrija os itens abaixo e tente novamente.</p>
        <ul>
            <?php foreach ($errors as $messages): ?>
                <?php foreach ((array) $messages as $message): ?>
                    <li><?= e($message); ?></li>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
