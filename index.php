<?php
/**
 * TUBI 2026 - Interfaz 0 (Pantalla de Intro)
 * Colores institucionales: Azul #354393, Turquesa #2EC4C6, Oscuro #0B1220
 */
require_once __DIR__ . '/config/config.php';

// Si ya está logueado, redirigir al dashboard
if (isLoggedIn()) {
    $user = getCurrentUser();
    redirect('pages/' . $user['role'] . '/dashboard.php');
}
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
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/intro.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🚲</text></svg>">
</head>
<body class="intro-page">
    <div class="intro-container" id="introContainer">
        <button class="intro-skip" id="introSkip">Saltar</button>

        <div class="intro-background"></div>
        <div class="intro-split-left"></div>
        <div class="intro-split-right"></div>

        <div class="intro-logo">
            <svg viewBox="0 0 200 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                <text x="20" y="60" font-family="Ubuntu, sans-serif" font-size="48" font-weight="700" fill="#354393">Tu</text>
                <text x="100" y="60" font-family="Ubuntu, sans-serif" font-size="48" font-weight="700" fill="#2EC4C6">Bi</text>
                <circle cx="165" cy="45" r="8" stroke="#354393" stroke-width="2" fill="none"/>
                <circle cx="185" cy="45" r="8" stroke="#2EC4C6" stroke-width="2" fill="none"/>
                <path d="M175 30l5 10 10 0M170 45l5-10" stroke="#354393" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </div>

        <div class="intro-content">
            <div class="intro-loading-bar">
                <div class="intro-loading-fill"></div>
            </div>

            <div class="intro-icons">
                <div class="intro-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="5.5" cy="17.5" r="3.5"/>
                        <circle cx="18.5" cy="17.5" r="3.5"/>
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
                        <path d="M12 2C6.5 2 2 6.5 2 12c0 2.5 1 4.8 2.5 6.5L6 17h12l1.5 1.5C21 16.8 22 14.5 22 12c0-5.5-4.5-10-10-10z"/>
                        <path d="M8 12h8"/>
                    </svg>
                </div>
                <div class="intro-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>
                </div>
                <div class="intro-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- URL de redirección -->
    <script>
        window.introRedirectUrl = '<?= BASE_URL ?>selector.php';
    </script>
    <script src="<?= BASE_URL ?>assets/js/intro.js"></script>
</body>
</html>
