<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($statusCode ?? 'Erro'); ?> - DGD</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            color: #1f2933;
        }

        main {
            width: min(560px, calc(100% - 32px));
            padding: 24px;
            border: 1px solid #d8dee6;
            border-radius: 8px;
            background: #fff;
        }

        h1 {
            margin: 0 0 8px;
            font-size: 28px;
        }

        p {
            margin: 0;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <main>
        <?= $content; ?>
    </main>
</body>
</html>
