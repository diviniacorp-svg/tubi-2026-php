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
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🚲</text></svg>">
    <style>
        .selector-page {
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            background: var(--bg-dark);
            font-family: 'Ubuntu', sans-serif;
            position: relative;
            overflow: hidden;
        }


        .selector-header {
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            z-index: 10;
        }

        .selector-logo {
            color: var(--text-primary);
            cursor: pointer;
            user-select: none;
        }

        .selector-logo svg {
            width: 100px;
            height: 34px;
        }

        .selector-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            text-align: center;
            position: relative;
            z-index: 10;
        }

        .selector-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .selector-subtitle {
            font-size: 1rem;
            color: var(--text-secondary);
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
            background: var(--bg-card);
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius-xl);
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
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .selector-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
        }

        .selector-card:hover::before {
            opacity: 1;
        }

        /* Línea superior con color del rol */
        .selector-card:has(.alumno)::before {
            background: linear-gradient(90deg, #2563eb, #3b82f6);
        }

        .selector-card:has(.proveedor)::before {
            background: linear-gradient(90deg, #06b6d4, #22d3ee);
        }

        .selector-card:has(.escuela)::before {
            background: linear-gradient(90deg, #2563eb, #06b6d4);
        }

        .selector-card:has(.admin)::before {
            background: linear-gradient(90deg, #1e3a5f, #134e4a);
        }

        /* Iconos simples con colores institucionales */
        .selector-card-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 1rem;
            border-radius: var(--border-radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .selector-card-icon svg {
            width: 32px;
            height: 32px;
            stroke-width: 1.5;
        }

        /* Estudiante - Azul institucional */
        .selector-card-icon.alumno {
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            color: white;
        }

        .selector-card:has(.alumno):hover {
            border-color: #2563eb;
            box-shadow: 0 10px 30px rgba(37, 99, 235, 0.3);
        }

        /* Proveedor - Turquesa */
        .selector-card-icon.proveedor {
            background: linear-gradient(135deg, #06b6d4 0%, #22d3ee 100%);
            color: white;
        }

        .selector-card:has(.proveedor):hover {
            border-color: #06b6d4;
            box-shadow: 0 10px 30px rgba(6, 182, 212, 0.3);
        }

        /* Escuela - Degradé azul a turquesa */
        .selector-card-icon.escuela {
            background: linear-gradient(135deg, #2563eb 0%, #06b6d4 100%);
            color: white;
        }

        .selector-card:has(.escuela):hover {
            border-color: #0891b2;
            box-shadow: 0 10px 30px rgba(8, 145, 178, 0.3);
        }

        /* Admin - Oculto por defecto */
        .selector-card.admin-card {
            display: none;
            background: linear-gradient(135deg, var(--bg-card) 0%, var(--bg-card-hover) 100%);
        }

        .selector-card.admin-card.visible {
            display: block;
        }

        .selector-card-icon.admin {
            background: linear-gradient(135deg, #1e3a5f 0%, #134e4a 100%);
            color: white;
        }

        .selector-card-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.375rem;
        }

        .selector-card-desc {
            font-size: 0.8125rem;
            color: var(--text-secondary);
            margin: 0;
        }

        .selector-footer {
            padding: 1.5rem;
            text-align: center;
        }

        .selector-footer p {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        /* Responsive Desktop */
        @media (min-width: 1024px) {
            .selector-grid {
                max-width: 800px;
            }

            .selector-card {
                padding: 2rem;
            }

            .selector-card-icon {
                width: 72px;
                height: 72px;
            }

            .selector-card-icon svg {
                width: 36px;
                height: 36px;
            }

            .selector-title {
                font-size: 2rem;
            }
        }

        /* Responsive Tablet */
        @media (max-width: 768px) {
            .selector-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 0.75rem;
            }

            .selector-card {
                padding: 1.25rem;
            }
        }

        /* Responsive Mobile */
        @media (max-width: 640px) {
            .selector-grid {
                grid-template-columns: 1fr;
                max-width: 320px;
            }

            .selector-card {
                display: flex;
                align-items: center;
                text-align: left;
                gap: 1rem;
                padding: 1rem 1.25rem;
            }

            .selector-card-icon {
                margin: 0;
                width: 52px;
                height: 52px;
                flex-shrink: 0;
            }

            .selector-card-icon svg {
                width: 26px;
                height: 26px;
            }

            .selector-card-info {
                flex: 1;
            }
        }
    </style>
</head>
<body>
    <div class="selector-page">
        <!-- Header -->
        <header class="selector-header">
            <button class="theme-toggle" id="themeToggle" title="Cambiar tema">
                <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                </svg>
                <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="5"/>
                    <line x1="12" y1="1" x2="12" y2="3"/>
                    <line x1="12" y1="21" x2="12" y2="23"/>
                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
                    <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                    <line x1="1" y1="12" x2="3" y2="12"/>
                    <line x1="21" y1="12" x2="23" y2="12"/>
                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
                    <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                </svg>
            </button>
        </header>

        <!-- Content -->
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
            <p>SAN LUIS | La Provincia · Secretaría de Transporte · 2026</p>
        </footer>
    </div>

    <script>
    // Sistema de tema claro/oscuro
    const themeToggle = document.getElementById('themeToggle');
    const html = document.documentElement;

    const savedTheme = localStorage.getItem('tubi-theme') || 'dark';
    if (savedTheme === 'light') {
        html.setAttribute('data-theme', 'light');
    }

    themeToggle.addEventListener('click', () => {
        const currentTheme = html.getAttribute('data-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        if (newTheme === 'light') {
            html.setAttribute('data-theme', 'light');
        } else {
            html.removeAttribute('data-theme');
        }
        localStorage.setItem('tubi-theme', newTheme);
    });

    // Admin oculto - 5 clicks en logo TuBi para revelar
    let clickCount = 0;
    let clickTimer = null;
    const tubiLogo = document.getElementById('tubiLogo');
    const adminCard = document.getElementById('adminCard');

    tubiLogo.addEventListener('click', () => {
        clickCount++;

        if (clickTimer) clearTimeout(clickTimer);

        clickTimer = setTimeout(() => {
            clickCount = 0;
        }, 2000);

        if (clickCount >= 5) {
            adminCard.classList.add('visible');
            clickCount = 0;
        }
    });
    </script>
</body>
</html>
