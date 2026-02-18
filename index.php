<?php
/**
 * TUBI 2026 - Interfaz 0 (Pantalla de Intro)
 * Colores institucionales: Azul #354393, Turquesa #2EC4C6, Oscuro #0B1220
 * Compatible PHP 5.4+
 */
require_once __DIR__ . '/config/config.php';

// AJAX: devolver estadísticas en tiempo real (sin login)
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    header('Content-Type: application/json');
    $stats = getEstadisticasAdmin();
    echo json_encode(array('success' => true, 'stats' => $stats));
    exit;
}

// Si ya está logueado, redirigir al dashboard
if (isLoggedIn()) {
    $user = getCurrentUser();
    redirect('pages/' . $user['role'] . '/dashboard.php');
}

// Estadísticas iniciales para render
$stats = getEstadisticasAdmin();
$meta = $stats['tasa_entrega'];
$progreso = $stats['total_bicicletas'] > 0
    ? round(($stats['entregadas'] / $stats['total_bicicletas']) * 100, 1)
    : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>TuBi 2026 - Tu Bicicleta San Luis</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/intro.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🚲</text></svg>">
</head>
<body class="intro-page">
    <div class="intro-container" id="introContainer">
        <button class="intro-skip" id="introSkip">Saltar</button>

        <!-- Fondo degradado -->
        <div class="intro-gradient-bg"></div>

        <!-- Logo TuBi grande como marca de agua -->
        <div class="intro-watermark">
            <img src="<?php echo BASE_URL; ?>assets/img/tubi-logo-blanco.png" alt="TuBi" style="width: 100%; height: auto; opacity: 0.12;">
        </div>

        <!-- Panel de Estadísticas en Vivo - lateral izquierdo -->
        <div class="intro-stats-panel" id="introStats">
            <div class="intro-stats-header">
                <span class="intro-stats-dot"></span>
                <span class="intro-stats-title">Avance del Programa</span>
            </div>
            <div class="intro-stats-grid">
                <div class="intro-stat-item">
                    <span class="intro-stat-value" id="statHoy"><?php echo (int)$stats['entregas_hoy']; ?></span>
                    <span class="intro-stat-label">Entregas Hoy</span>
                </div>
                <div class="intro-stat-item">
                    <span class="intro-stat-value" id="statSemana"><?php echo (int)$stats['entregas_semana']; ?></span>
                    <span class="intro-stat-label">Esta Semana</span>
                </div>
                <div class="intro-stat-item">
                    <span class="intro-stat-value" id="statMes"><?php echo (int)$stats['entregas_mes']; ?></span>
                    <span class="intro-stat-label">Este Mes</span>
                </div>
                <div class="intro-stat-item">
                    <span class="intro-stat-value" id="statTasa"><?php echo $meta; ?>%</span>
                    <span class="intro-stat-label">Tasa Entrega</span>
                </div>
            </div>
            <div class="intro-stats-progress">
                <div class="intro-progress-bar">
                    <div class="intro-progress-fill" id="progresoFill" style="width: <?php echo $progreso; ?>%"></div>
                </div>
                <span class="intro-progress-text" id="statDetalle"><?php echo (int)$stats['entregadas']; ?> / <?php echo (int)$stats['total_bicicletas']; ?> bicicletas entregadas</span>
            </div>
        </div>

        <!-- Contenido central: solo logo TuBi -->
        <div class="intro-content">
        </div>

        <!-- Zona inferior: iconos + barra de carga -->
        <div class="intro-bottom-zone">
            <div class="intro-location">SAN LUIS 2026</div>

            <div class="intro-icons">
                <div class="intro-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="5.5" cy="17.5" r="3.5"/><circle cx="18.5" cy="17.5" r="3.5"/>
                        <path d="M15 6a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm-3 11.5V14l-3-3 4-3 2 3h3"/>
                    </svg>
                </div>
                <div class="intro-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                        <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                    </svg>
                </div>
                <div class="intro-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 18h20v2H2zM4 18c0-5 2-10 8-10s8 5 8 10"/>
                        <path d="M12 8V4"/>
                    </svg>
                </div>
                <div class="intro-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                </div>
                <div class="intro-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                    </svg>
                </div>
            </div>

            <!-- Barra de carga -->
            <div class="intro-loading-bar" id="introLoadingBar">
                <div class="intro-loading-fill"></div>
            </div>
        </div>
    </div>

    <!-- Datos para JS -->
    <script>
        window.introRedirectUrl = '<?php echo BASE_URL; ?>selector.php';
        window.introStatsUrl = '<?php echo BASE_URL; ?>index.php';
    </script>
    <script src="<?php echo BASE_URL; ?>assets/js/intro.js"></script>
</body>
</html>
