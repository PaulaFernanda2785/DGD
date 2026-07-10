<?php

use App\Core\Auth;

$usuario = Auth::user();
$appJsVersion = is_file(PUBLIC_PATH . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'app.js')
    ? (string) filemtime(PUBLIC_PATH . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'app.js')
    : '1';
$stylesheets = $stylesheets ?? [];
$scripts = $scripts ?? [];
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$runtimeBasePath = rtrim(str_replace('/index.php', '', $scriptName), '/');
$runtimeBaseUrl = $scheme . '://' . $host . $runtimeBasePath;
$assetBaseUrl = rtrim($runtimeBaseUrl, '/');
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$basePath = $runtimeBasePath !== '' ? $runtimeBasePath : '';
$currentPath = '/' . ltrim((string) preg_replace('#^' . preg_quote($basePath, '#') . '#', '', $requestPath), '/');
$isActive = static function (string $path) use ($currentPath): string {
    return $currentPath === $path || str_starts_with($currentPath, rtrim($path, '/') . '/') ? ' aria-current="page" class="is-active"' : '';
};
?>
<!doctype html>
<html lang="pt-BR" data-app-base-url="<?= e(rtrim($runtimeBaseUrl, '/')); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'DGD'); ?> - DGD</title>
    <link rel="icon" href="<?= e($assetBaseUrl . '/assets/img/app-icon-192.png'); ?>">
    <?php foreach ($stylesheets as $stylesheet): ?>
        <link rel="stylesheet" href="<?= e(url((string) $stylesheet)); ?>">
    <?php endforeach; ?>
    <link rel="stylesheet" href="<?= e($assetBaseUrl . '/assets/css/app.css'); ?>">
    <?php foreach ($scripts as $script): ?>
        <script src="<?= e(url((string) $script)); ?>" defer></script>
    <?php endforeach; ?>
    <script src="<?= e($assetBaseUrl . '/assets/js/app.js?v=' . $appJsVersion); ?>" defer></script>
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar" id="app-sidebar" aria-label="Menu principal">
            <div class="brand">
                <img src="<?= e(url('/assets/img/logo-cedec.png')); ?>" alt="CEDEC-PA">
                <div>
                    <strong>DGD</strong>
                    <span>CEDEC-PA</span>
                </div>
            </div>

            <nav class="nav">
                <?php if (can('painel.visualizar')): ?>
                    <a href="<?= e(url('/painel')); ?>" data-initial="P" title="Painel"<?= $isActive('/painel'); ?>><span>Painel</span></a>
                <?php endif; ?>

                <?php if (can('decretos.visualizar')): ?>
                    <a href="<?= e(url('/decretos')); ?>" data-initial="D" title="Decretos"<?= $isActive('/decretos'); ?>><span>Decretos</span></a>
                <?php endif; ?>

                <?php if (can('compdecs.visualizar')): ?>
                    <a href="<?= e(url('/compdecs')); ?>" data-initial="C" title="COMPDECs"<?= $isActive('/compdecs'); ?>><span>COMPDECs</span></a>
                <?php endif; ?>

                <?php if (can('usuarios.visualizar')): ?>
                    <a href="<?= e(url('/usuarios')); ?>" data-initial="U" title="Usuários"<?= $isActive('/usuarios'); ?>><span>Usuários</span></a>
                <?php endif; ?>

                <?php if (can('senha.alterar_propria')): ?>
                    <a href="<?= e(url('/alterar-senha')); ?>" data-initial="S" title="Alterar senha"<?= $isActive('/alterar-senha'); ?>><span>Alterar senha</span></a>
                <?php endif; ?>
            </nav>

            <button type="button" class="sidebar-collapse" data-sidebar-collapse aria-label="Recolher menu" aria-pressed="false">
                <span></span>
            </button>
        </aside>

        <div class="content-shell">
            <header class="topbar">
                <div class="topbar-left">
                    <button type="button" class="menu-toggle" data-menu-toggle aria-controls="app-sidebar" aria-expanded="false" aria-label="Abrir menu">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>

                    <div class="topbar-title">
                        <span>Sistema DGD</span>
                        <strong><?= e($title ?? 'Painel operacional'); ?></strong>
                    </div>
                </div>

                <div class="topbar-session">
                    <div class="topbar-user">
                        <div class="user-avatar" aria-hidden="true"><?= e(strtoupper(substr((string) ($usuario['nome'] ?? 'U'), 0, 1))); ?></div>
                        <div>
                            <strong><?= e($usuario['nome'] ?? 'Usuário'); ?></strong>
                            <span><?= e($usuario['perfil_codigo'] ?? ''); ?></span>
                        </div>
                    </div>

                    <form method="post" action="<?= e(url('/logout')); ?>">
                        <?= csrf_input(); ?>
                        <button type="submit" class="button button-light">Sair</button>
                    </form>
                </div>
            </header>

            <main class="content">
                <?php require view_path('components/flash'); ?>
                <?= $content; ?>
            </main>
        </div>
    </div>

    <div class="sidebar-backdrop" data-sidebar-backdrop hidden></div>

    <div class="modal-backdrop" data-confirm-backdrop hidden>
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="confirm-title">
            <h2 id="confirm-title">Confirmar ação</h2>
            <p data-confirm-message>Deseja continuar?</p>
            <div class="modal-actions">
                <button type="button" class="button button-light" data-confirm-cancel>Cancelar</button>
                <button type="button" class="button button-danger" data-confirm-ok>Confirmar</button>
            </div>
        </div>
    </div>

    <div class="modal-backdrop history-modal-backdrop" data-history-backdrop hidden>
        <div class="modal history-modal" role="dialog" aria-modal="true" aria-labelledby="history-title">
            <h2 id="history-title">Registrar histórico</h2>
            <div class="history-modal-summary" data-history-modal-summary></div>
            <div class="field history-pge-date-field" data-history-pge-date-field hidden>
                <label for="history-pge-date">Data de envio à PGE</label>
                <input id="history-pge-date" type="date" data-history-pge-date>
            </div>
            <label for="history-observation">Observação</label>
            <textarea id="history-observation" rows="4" data-history-textarea placeholder="Descreva o motivo ou contexto desta alteração."></textarea>
            <div class="modal-actions">
                <button type="button" class="button button-light" data-history-cancel>Cancelar</button>
                <button type="button" class="button button-primary" data-history-confirm>Confirmar e salvar</button>
            </div>
        </div>
    </div>

    <button type="button" class="back-to-top" data-back-to-top aria-label="Voltar para o topo" hidden>
        <img src="<?= e(url('/assets/icons/icon-arrow-up.svg')); ?>" alt="">
    </button>
</body>
</html>
