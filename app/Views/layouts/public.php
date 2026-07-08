<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'DGD'); ?> - DGD</title>
    <link rel="icon" href="<?= e(url('/assets/img/app-icon-192.png')); ?>">
    <link rel="stylesheet" href="<?= e(url('/assets/css/app.css')); ?>">
    <?= $extraHead ?? ''; ?>
    <script src="<?= e(url('/assets/js/app.js')); ?>" defer></script>
</head>
<body class="public-page is-guest">
    <main class="public-shell">
        <?= $content; ?>
    </main>

    <button type="button" class="back-to-top" data-back-to-top aria-label="Voltar para o topo" hidden>
        <img src="<?= e(url('/assets/icons/icon-arrow-up.svg')); ?>" alt="">
    </button>
</body>
</html>
