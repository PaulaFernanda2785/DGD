<?php

$success = flash('success');
$error = flash('error');
$warning = flash('warning');
?>

<?php if ($success): ?>
    <div class="alert alert-success"><?= e($success); ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error"><?= e($error); ?></div>
<?php endif; ?>

<?php if ($warning): ?>
    <div class="alert alert-warning"><?= e($warning); ?></div>
<?php endif; ?>
