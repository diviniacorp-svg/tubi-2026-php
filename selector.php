<?php
/**
 * TUBI 2026 - Selector de Login
 * Colores institucionales: Azul #2563eb, Turquesa #06b6d4
 */
require_once __DIR__ . '/config/config.php';

if (isLoggedIn()) {
    $user = getCurrentUser();
    redirect('pages/' . $user['role'] . '/dashboard.php');
}

$pageTitle = 'Seleccionar Usuario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> - TuBi 2026</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🚲</text></svg>">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        * { font-family: 'Ubuntu', -apple-system, sans-serif; }

        .selector-page {
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            background: #e4f1f7;
            position: relative;
            overflow: hidden;
        }

        /* Zócalo superior institucional */
        .selector-zocalo {
            background: linear-gradient(135deg, #354393 0%, #4aacc4 100%);
            padding: 0.5rem 1.5rem;
        }
        .selector-zocalo-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .selector-zocalo-inner img.tubi-logo { height: 70px; width: auto; cursor: pointer; }
        .selector-zocalo-inner img.edu-logo { height: 58px; width: auto; }

        .selector-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            text-align: center;
        }

        .selector-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #354393;
            margin-bottom: 0.5rem;
        }

        .selector-subtitle {
            font-size: 1rem;
            color: #6b7b8a;
            margin-bottom: 2rem;
        }

        .selector-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            width: 100%;
            max-width: 700px;
        }

        .selector-card {
            background: #eef6fa;
            border: 2px solid #c8dfe9;
            border-radius: 16px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }

        .selector-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .selector-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 30px rgba(53, 67, 147, 0.15);
        }

        .selector-card:hover::before { opacity: 1; }

        .selector-card:has(.alumno)::before { background: linear-gradient(90deg, #354393, #4a5aab); }
        .selector-card:has(.proveedor)::before { background: linear-gradient(90deg, #4aacc4, #3d95ab); }
        .selector-card:has(.escuela)::before { background: linear-gradient(90deg, #354393, #4aacc4); }
        .selector-card:has(.admin)::before { background: linear-gradient(90deg, #354393, #4aacc4); }

        .selector-card-icon {
            width: 64px; height: 64px;
            margin: 0 auto 1rem;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
        }

        .selector-card-icon svg { width: 32px; height: 32px; stroke-width: 1.5; }

        .selector-card-icon.alumno {
            background: linear-gradient(135deg, #354393, #4a5aab);
            color: white;
        }
        .selector-card:has(.alumno):hover {
            border-color: #354393;
            box-shadow: 0 10px 30px rgba(53, 67, 147, 0.2);
        }

        .selector-card-icon.proveedor {
            background: linear-gradient(135deg, #4aacc4, #3d95ab);
            color: white;
        }
        .selector-card:has(.proveedor):hover {
            border-color: #4aacc4;
            box-shadow: 0 10px 30px rgba(74, 172, 196, 0.2);
        }

        .selector-card-icon.escuela {
            background: linear-gradient(135deg, #354393, #4aacc4);
            color: white;
        }
        .selector-card:has(.escuela):hover {
            border-color: #4aacc4;
            box-shadow: 0 10px 30px rgba(74, 172, 196, 0.2);
        }

        .selector-card.admin-card { display: none; }
        .selector-card.admin-card.visible { display: block; }
        .selector-card-icon.admin {
            background: linear-gradient(135deg, #354393, #4aacc4);
            color: white;
        }

        .selector-card-title {
            font-size: 1rem; font-weight: 600;
            color: #414242; margin-bottom: 0.375rem;
        }

        .selector-card-desc {
            font-size: 0.8125rem;
            color: #6b7b8a;
            margin: 0;
        }

        .selector-footer {
            background: linear-gradient(135deg, #354393 0%, #4aacc4 100%);
            padding: 1rem 1.5rem;
            text-align: center;
        }
        .selector-footer p { font-size: 0.75rem; color: rgba(255,255,255,0.8); margin: 0; }

        @media (min-width: 1024px) {
            .selector-grid { max-width: 800px; }
            .selector-card { padding: 2rem; }
            .selector-card-icon { width: 72px; height: 72px; }
            .selector-card-icon svg { width: 36px; height: 36px; }
            .selector-title { font-size: 2rem; }
        }

        @media (max-width: 768px) {
            .selector-grid { grid-template-columns: repeat(3, 1fr); gap: 0.75rem; }
            .selector-card { padding: 1.25rem; }
        }

        @media (max-width: 640px) {
            .selector-zocalo-inner { flex-direction: column; gap: 0.5rem; }
            .selector-zocalo-inner img.tubi-logo { height: 50px; }
            .selector-zocalo-inner img.edu-logo { height: 44px; }
            .selector-grid { grid-template-columns: 1fr; max-width: 320px; }
            .selector-card {
                display: flex; align-items: center; text-align: left;
                gap: 1rem; padding: 1rem 1.25rem;
            }
            .selector-card-icon { margin: 0; width: 52px; height: 52px; flex-shrink: 0; }
            .selector-card-icon svg { width: 26px; height: 26px; }
            .selector-card-info { flex: 1; }
        }
    </style>
</head>
<body>
    <div class="selector-page">
        <!-- Zócalo Institucional -->
        <div class="selector-zocalo">
            <div class="selector-zocalo-inner">
                <img src="<?= BASE_URL ?>assets/img/tubi-logo-blanco.png" alt="TuBi" class="tubi-logo" id="tubiLogo">
                <img src="<?= BASE_URL ?>assets/img/edu-logo-blanco.png" alt="2026 Año de la Educación" class="edu-logo">
            </div>
        </div>

        <!-- Content (logo TuBi en header = 5 clicks para admin) -->
        <main class="selector-content">
            <h1 class="selector-title">¿Cómo querés ingresar?</h1>
            <p class="selector-subtitle">Seleccioná tu tipo de usuario</p>

            <div class="selector-grid" id="selectorGrid">
                <!-- Estudiante - Azul -->
                <a href="<?= BASE_URL ?>login.php?role=alumno" class="selector-card">
                    <div class="selector-card-icon alumno">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </div>
                    <div class="selector-card-info">
                        <h2 class="selector-card-title">Soy Estudiante</h2>
                        <p class="selector-card-desc">Ingresá con tu DNI</p>
                    </div>
                </a>

                <!-- Proveedor - Turquesa -->
                <a href="<?= BASE_URL ?>login.php?role=proveedor" class="selector-card">
                    <div class="selector-card-icon proveedor">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                        </svg>
                    </div>
                    <div class="selector-card-info">
                        <h2 class="selector-card-title">Soy Proveedor</h2>
                        <p class="selector-card-desc">Panel de armado</p>
                    </div>
                </a>

                <!-- Escuela - Degradé -->
                <a href="<?= BASE_URL ?>login.php?role=escuela" class="selector-card">
                    <div class="selector-card-icon escuela">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                            <polyline points="9 22 9 12 15 12 15 22"/>
                        </svg>
                    </div>
                    <div class="selector-card-info">
                        <h2 class="selector-card-title">Soy Escuela</h2>
                        <p class="selector-card-desc">Gestión de entregas</p>
                    </div>
                </a>

                <!-- Admin - Oculto, se revela con 5 clicks en logo -->
                <a href="<?= BASE_URL ?>login.php?role=admin" class="selector-card admin-card" id="adminCard">
                    <div class="selector-card-icon admin">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                    </div>
                    <div class="selector-card-info">
                        <h2 class="selector-card-title">Administración</h2>
                        <p class="selector-card-desc">Centro de control</p>
                    </div>
                </a>
            </div>
        </main>

        <!-- Footer -->
        <footer class="selector-footer">
            <p>SAN LUIS | La Provincia &middot; Secretaría de Transporte &middot; 2026</p>
        </footer>
    </div>

    <script>
    // Admin oculto - 5 clicks en logo TuBi para revelar
    var clickCount = 0;
    var clickTimer = null;
    var tubiLogo = document.getElementById('tubiLogo');
    var adminCard = document.getElementById('adminCard');

    if (tubiLogo) {
        tubiLogo.addEventListener('click', function() {
            clickCount++;

            if (clickTimer) clearTimeout(clickTimer);

            clickTimer = setTimeout(function() {
                clickCount = 0;
            }, 2000);

            if (clickCount >= 5) {
                adminCard.className = adminCard.className + ' visible';
                clickCount = 0;
            }
        });
    }
    </script>
</body>
</html>
