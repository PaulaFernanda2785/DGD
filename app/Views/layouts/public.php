<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#3d4098">
    <meta name="application-name" content="DGD">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="DGD">
    <title><?= e($title ?? 'DGD'); ?> - DGD</title>
    <link rel="manifest" href="<?= e(url('/manifest.webmanifest?v=20260714.2')); ?>">
    <link rel="icon" href="<?= e(url('/favicon.ico?v=20260714.2')); ?>" sizes="any">
    <link rel="icon" type="image/png" sizes="192x192" href="<?= e(url('/assets/img/app-icon-192.png?v=20260714.2')); ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= e(url('/assets/img/app-icon-180.png?v=20260714.2')); ?>">
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
