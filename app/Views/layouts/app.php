<?php

use App\Core\Auth;

$usuario = Auth::user();
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'DGD'); ?> - DGD</title>
    <link rel="icon" href="<?= e(url('/assets/img/app-icon-192.png')); ?>">
    <link rel="stylesheet" href="<?= e(url('/assets/css/app.css')); ?>">
    <script src="<?= e(url('/assets/js/app.js')); ?>" defer></script>
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar" id="app-sidebar">
            <div class="brand">
                <img src="<?= e(url('/assets/img/logo-cedec.png')); ?>" alt="CEDEC-PA">
                <div>
                    <strong>DGD</strong>
                    <span>CEDEC-PA</span>
                </div>
            </div>

            <nav class="nav">
                <?php if (can('painel.visualizar')): ?>
                    <a href="<?= e(url('/painel')); ?>">Painel</a>
                <?php endif; ?>

                <?php if (can('decretos.visualizar')): ?>
                    <a href="<?= e(url('/decretos')); ?>">Decretos</a>
                <?php endif; ?>

                <?php if (can('usuarios.visualizar')): ?>
                    <a href="<?= e(url('/usuarios')); ?>">Usuarios</a>
                <?php endif; ?>

                <?php if (can('senha.alterar_propria')): ?>
                    <a href="<?= e(url('/alterar-senha')); ?>">Alterar senha</a>
                <?php endif; ?>
            </nav>
        </aside>

        <div class="content-shell">
            <header class="topbar">
                <button type="button" class="menu-toggle" data-menu-toggle aria-controls="app-sidebar" aria-expanded="false">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>

                <div>
                    <strong><?= e($usuario['nome'] ?? 'Usuario'); ?></strong>
                    <span><?= e($usuario['perfil_codigo'] ?? ''); ?></span>
                </div>

                <form method="post" action="<?= e(url('/logout')); ?>">
                    <?= csrf_input(); ?>
                    <button type="submit" class="button button-light">Sair</button>
                </form>
            </header>

            <main class="content">
                <?php require view_path('components/flash'); ?>
                <?= $content; ?>
            </main>
        </div>
    </div>

    <div class="modal-backdrop" data-confirm-backdrop hidden>
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="confirm-title">
            <h2 id="confirm-title">Confirmar acao</h2>
            <p data-confirm-message>Deseja continuar?</p>
            <div class="modal-actions">
                <button type="button" class="button button-light" data-confirm-cancel>Cancelar</button>
                <button type="button" class="button button-danger" data-confirm-ok>Confirmar</button>
            </div>
        </div>
    </div>
</body>
</html>
