<?php
/**
 * TUBI 2026 - Header Component
 */
require_once __DIR__ . '/../config/config.php';

$user = getCurrentUser();
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'TuBi 2026') ?> - Tu Bicicleta San Luis</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🚲</text></svg>">
    <script src="<?= BASE_URL ?>assets/js/toast.js" defer></script>
</head>
<body class="tubi-bg-pattern" data-base-url="<?= BASE_URL ?>" data-theme="dark">
    <div class="page-wrapper">
        <?php if ($user): ?>
        <header class="header">
            <div class="header-inner">
                <a href="<?= BASE_URL ?>" class="logo" style="min-width: 140px; height: 50px; display: flex; align-items: center;">
                    <!-- Espacio para logo - agregar imagen aquí -->
                </a>

                <button class="theme-toggle" id="themeToggle" aria-label="Cambiar tema (Oscuro/Claro/Azul)" title="Click para cambiar tema" style="margin-right: 1rem;">
                    <svg class="theme-icon sun-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="5"/>
                        <path d="M12 1v2m0 18v2M4.22 4.22l1.42 1.42m12.72 12.72l1.42 1.42M1 12h2m18 0h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
                    </svg>
                    <svg class="theme-icon moon-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                    </svg>
                </button>

                <nav class="nav-menu">
                    <?php
                    $role = $user['role'];
                    $currentPage = basename($_SERVER['PHP_SELF'], '.php');

                    $navItems = [
                        'alumno' => [
                            ['url' => 'pages/alumno/dashboard.php', 'label' => '🚲 Mi TuBi', 'page' => 'dashboard'],
                            ['url' => 'pages/alumno/aprender.php', 'label' => '🎮 Aprende Jugando', 'page' => 'aprender'],
                        ],
                        'tutor' => [
                            ['url' => 'pages/tutor/dashboard.php', 'label' => 'Panel', 'page' => 'dashboard'],
                        ],
                        'escuela' => [
                            ['url' => 'pages/escuela/dashboard.php', 'label' => 'Panel', 'page' => 'dashboard'],
                            ['url' => 'pages/escuela/alumnos.php', 'label' => 'Alumnos', 'page' => 'alumnos'],
                        ],
                        'proveedor' => [
                            ['url' => 'pages/proveedor/dashboard.php', 'label' => 'Panel', 'page' => 'dashboard'],
                            ['url' => 'pages/proveedor/inventario.php', 'label' => 'Inventario', 'page' => 'inventario'],
                        ],
                        'admin' => [
                            ['url' => 'pages/admin/dashboard.php', 'label' => 'Dashboard', 'page' => 'dashboard'],
                            ['url' => 'pages/admin/usuarios.php', 'label' => 'Usuarios', 'page' => 'usuarios'],
                            ['url' => 'pages/admin/config.php', 'label' => 'Config', 'page' => 'config'],
                        ],
                    ];

                    if (isset($navItems[$role])):
                        foreach ($navItems[$role] as $item):
                    ?>
                        <a href="<?= BASE_URL . $item['url'] ?>" class="nav-link <?= $currentPage === $item['page'] ? 'active' : '' ?>">
                            <?= e($item['label']) ?>
                        </a>
                    <?php
                        endforeach;
                    endif;
                    ?>
                </nav>

                <div class="user-menu">
                    <div class="user-avatar"><?= strtoupper(substr($user['nombre'], 0, 1)) ?></div>
                    <a href="<?= BASE_URL ?>logout.php" class="btn btn-secondary btn-sm">Salir</a>
                </div>
            </div>
        </header>
        <?php endif; ?>

        <?php if ($flash): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof TubiToast !== 'undefined') {
                    TubiToast.<?= $flash['type'] === 'success' ? 'success' : ($flash['type'] === 'error' ? 'error' : 'info') ?>(
                        '<?= $flash['type'] === 'success' ? 'Éxito' : ($flash['type'] === 'error' ? 'Error' : 'Información') ?>',
                        '<?= addslashes(e($flash['message'])) ?>'
                    );
                }
            });
        </script>
        <noscript>
            <div class="container" style="padding-top: 1rem;">
                <div class="alert alert-<?= e($flash['type']) ?>">
                    <?= e($flash['message']) ?>
                </div>
            </div>
        </noscript>
        <?php endif; ?>

        <main class="main-content">
