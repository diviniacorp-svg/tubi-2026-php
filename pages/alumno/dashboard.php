<?php
/**
 * TUBI 2026 - Dashboard Estudiante
 * Gamificación mejorada con retos matutinos/nocturnos y tarjetas de datos
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/data.php';

if (!isLoggedIn() || !hasRole('alumno')) {
    redirect('login.php?role=alumno');
}

$user = getCurrentUser();
$pageTitle = 'Mi TuBi';

// Obtener datos reales del sistema
$data = initDemoData();
$alumnoId = $user['id'] ?? 1;

// Buscar alumno en datos
$alumnoData = null;
foreach ($data['alumnos'] as $a) {
    if ($a['id'] == $alumnoId) {
        $alumnoData = $a;
        break;
    }
}

// Si no se encuentra, usar datos default
if (!$alumnoData) {
    $alumnoData = [
        'nombre' => $user['nombre'],
        'dni' => $user['dni'] ?? '40123456',
        'escuela' => 'Escuela N° 123 "Gral. San Martín"',
        'curso' => '5° Año B',
        'estado' => 'asignado',
        'modulos_completados' => 3,
        'puntos' => 150,
        'fecha_inscripcion' => '2026-01-15',
    ];
}

// Buscar bicicleta asignada
$biciData = null;
foreach ($data['bicicletas'] as $b) {
    if ($b['alumno_id'] == $alumnoId) {
        $biciData = $b;
        break;
    }
}

// Si no hay bici asignada, usar datos demo
if (!$biciData) {
    $biciData = [
        'codigo' => 'TUBI-SL-00123',
        'serie' => 'SN-2026-00123',
        'rodado' => 26,
        'color' => 'Azul TuBi',
        'estado' => 'entregada',
        'fecha_entrega' => '2026-02-01',
        'garantia_hasta' => '2028-02-01',
    ];
}

// Datos del alumno
$modulosCompletados = $alumnoData['modulos_completados'] ?? 3;
$totalModulos = 8;
$puntos = $alumnoData['puntos'] ?? 150;
$diasPrograma = 45;
$racha = 7; // Días seguidos usando la app

// Estados del proceso
$estadosProceso = ['preinscripto', 'en_revision', 'aprobado', 'asignado', 'entregado'];
$estadoActual = $alumnoData['estado'] ?? 'asignado';
$estadoActualIndex = array_search($estadoActual, $estadosProceso);
if ($estadoActualIndex === false) $estadoActualIndex = 3;

// Logros obtenidos
$logros = getData('logros');
$logrosObtenidos = array_slice($logros, 0, 3);

// Sistema de retos diarios basado en hora
$horaActual = (int)date('H');
$esMañana = $horaActual >= 6 && $horaActual < 12;
$esTarde = $horaActual >= 12 && $horaActual < 18;
$esNoche = $horaActual >= 18 || $horaActual < 6;

// Retos matutinos (6:00 - 12:00)
$retosMatutinos = [
    [
        'id' => 'quiz_vial',
        'nombre' => 'Quiz Vial Express',
        'descripcion' => '10 preguntas sobre señales de tránsito',
        'duracion' => 5,
        'puntos' => 50,
        'icono' => '🚦',
        'completado' => false,
    ],
    [
        'id' => 'ruta_segura',
        'nombre' => 'Planificá tu Ruta',
        'descripcion' => 'Elegí el camino más seguro a tu escuela',
        'duracion' => 8,
        'puntos' => 75,
        'icono' => '🗺️',
        'completado' => true,
    ],
    [
        'id' => 'check_bici',
        'nombre' => 'Chequeo Matutino',
        'descripcion' => 'Verificá los 5 puntos de tu bici',
        'duracion' => 3,
        'puntos' => 30,
        'icono' => '🔧',
        'completado' => false,
    ],
];

// Retos nocturnos (18:00 - 6:00)
$retosNocturnos = [
    [
        'id' => 'reflectantes',
        'nombre' => 'Visibilidad Nocturna',
        'descripcion' => 'Aprendé sobre elementos reflectantes',
        'duracion' => 7,
        'puntos' => 60,
        'icono' => '🌙',
        'completado' => false,
    ],
    [
        'id' => 'trivia_tubi',
        'nombre' => 'Trivia TuBi',
        'descripcion' => 'Respondé 15 preguntas y ganá premios',
        'duracion' => 10,
        'puntos' => 100,
        'icono' => '🎯',
        'completado' => false,
    ],
    [
        'id' => 'desafio_eco',
        'nombre' => 'Desafío Ecológico',
        'descripcion' => 'Calculá cuánto CO2 ahorraste hoy',
        'duracion' => 5,
        'puntos' => 45,
        'icono' => '🌱',
        'completado' => true,
    ],
];

// Retos de la tarde (12:00 - 18:00)
$retosTarde = [
    [
        'id' => 'memoria_señales',
        'nombre' => 'Memoria de Señales',
        'descripcion' => 'Encontrá los pares de señales viales',
        'duracion' => 8,
        'puntos' => 70,
        'icono' => '🧠',
        'completado' => false,
    ],
    [
        'id' => 'circuito_virtual',
        'nombre' => 'Circuito Virtual',
        'descripcion' => 'Completá el recorrido sin errores',
        'duracion' => 12,
        'puntos' => 90,
        'icono' => '🎮',
        'completado' => false,
    ],
];

// Seleccionar retos según hora
if ($esMañana) {
    $retosActuales = $retosMatutinos;
    $tipoReto = 'matutino';
    $iconoTiempo = '☀️';
    $colorReto = '#f59e0b';
} elseif ($esTarde) {
    $retosActuales = $retosTarde;
    $tipoReto = 'de la tarde';
    $iconoTiempo = '🌤️';
    $colorReto = '#3b82f6';
} else {
    $retosActuales = $retosNocturnos;
    $tipoReto = 'nocturno';
    $iconoTiempo = '🌙';
    $colorReto = '#8b5cf6';
}
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
        /* Tarjetas de datos mejoradas */
        .data-card {
            background: linear-gradient(135deg, var(--bg-card) 0%, var(--bg-card-hover) 100%);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius-xl);
            overflow: hidden;
            position: relative;
        }

        .data-card-header {
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-bottom: 1px solid var(--border-color);
        }

        .data-card-header.estudiante {
            background: linear-gradient(135deg, #2563eb15 0%, #3b82f620 100%);
        }

        .data-card-header.bicicleta {
            background: linear-gradient(135deg, #22c55e15 0%, #10b98120 100%);
        }

        .data-card-icon {
            width: 48px;
            height: 48px;
            border-radius: var(--border-radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .data-card-icon.estudiante {
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .data-card-icon.bicicleta {
            background: linear-gradient(135deg, #22c55e 0%, #10b981 100%);
            box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
        }

        .data-card-title {
            flex: 1;
        }

        .data-card-title h3 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
        }

        .data-card-title span {
            font-size: 0.8125rem;
            color: var(--text-secondary);
        }

        .data-card-body {
            padding: 1.25rem;
        }

        .data-row {
            display: flex;
            justify-content: space-between;
            padding: 0.625rem 0;
            border-bottom: 1px dashed var(--border-color);
        }

        .data-row:last-child {
            border-bottom: none;
        }

        .data-label {
            font-size: 0.8125rem;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .data-value {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-primary);
            text-align: right;
        }

        .data-card-qr {
            display: flex;
            justify-content: center;
            padding: 1rem;
            background: var(--bg-dark);
            margin: 0 1.25rem 1.25rem;
            border-radius: var(--border-radius-lg);
        }

        .qr-code-display {
            width: 100px;
            height: 100px;
            background: white;
            border-radius: 8px;
            padding: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Sistema de retos */
        .retos-section {
            margin-bottom: 1rem;
        }

        .retos-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.25rem;
            background: linear-gradient(135deg, <?= $colorReto ?>20 0%, <?= $colorReto ?>10 100%);
            border-radius: var(--border-radius-xl) var(--border-radius-xl) 0 0;
            border: 1px solid <?= $colorReto ?>30;
            border-bottom: none;
        }

        .retos-header-left {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .retos-time-icon {
            font-size: 1.75rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .retos-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .retos-subtitle {
            font-size: 0.8125rem;
            color: var(--text-secondary);
        }

        .retos-timer {
            padding: 0.5rem 1rem;
            background: <?= $colorReto ?>;
            border-radius: 100px;
            color: white;
            font-size: 0.875rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .retos-list {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-top: none;
            border-radius: 0 0 var(--border-radius-xl) var(--border-radius-xl);
            overflow: hidden;
        }

        .reto-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--border-color);
            transition: background var(--transition);
        }

        .reto-item:last-child {
            border-bottom: none;
        }

        .reto-item:hover {
            background: var(--bg-card-hover);
        }

        .reto-item.completado {
            opacity: 0.7;
        }

        .reto-icon {
            width: 50px;
            height: 50px;
            border-radius: var(--border-radius-lg);
            background: var(--bg-card-hover);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .reto-content {
            flex: 1;
            min-width: 0;
        }

        .reto-nombre {
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }

        .reto-desc {
            font-size: 0.8125rem;
            color: var(--text-secondary);
            margin-bottom: 0.375rem;
        }

        .reto-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: 0.75rem;
        }

        .reto-meta span {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            color: var(--text-muted);
        }

        .reto-puntos {
            color: #f59e0b !important;
            font-weight: 600;
        }

        .reto-action {
            flex-shrink: 0;
        }

        .btn-reto {
            padding: 0.5rem 1rem;
            border-radius: 100px;
            font-size: 0.8125rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }

        .btn-reto.completado {
            background: #22c55e20;
            color: #22c55e;
            border: 1px solid #22c55e40;
        }

        /* Racha de días */
        .racha-card {
            background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
            border-radius: var(--border-radius-xl);
            padding: 1.25rem;
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .racha-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
        }

        .racha-icon {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .racha-value {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1;
        }

        .racha-label {
            font-size: 0.875rem;
            opacity: 0.9;
            margin-top: 0.25rem;
        }

        .racha-days {
            display: flex;
            justify-content: center;
            gap: 0.375rem;
            margin-top: 1rem;
        }

        .racha-day {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
        }

        .racha-day.active {
            background: white;
            color: #f59e0b;
        }

        /* Stats mejorados */
        .stats-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .stat-mini {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius-lg);
            padding: 1rem;
            text-align: center;
        }

        .stat-mini-icon {
            font-size: 1.5rem;
            margin-bottom: 0.375rem;
        }

        .stat-mini-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .stat-mini-label {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        /* Responsive mobile */
        @media (max-width: 480px) {
            .retos-header {
                flex-direction: column;
                gap: 0.75rem;
                text-align: center;
            }

            .reto-item {
                flex-wrap: wrap;
            }

            .reto-action {
                width: 100%;
                margin-top: 0.5rem;
            }

            .btn-reto {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body class="tubi-bg-pattern" data-base-url="<?= BASE_URL ?>">
    <div class="app-container">
        <!-- Header -->
        <header class="app-header">
            <div class="header-left">
                <a href="<?= BASE_URL ?>" class="header-logo">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="24" height="24">
                        <circle cx="5.5" cy="17.5" r="3.5"/>
                        <circle cx="18.5" cy="17.5" r="3.5"/>
                        <path d="M15 6a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm-3 11.5V14l-3-3 4-3 2 3h3"/>
                    </svg>
                    <span>TuBi</span>
                </a>
                <span class="header-badge estudiante">Estudiante</span>
            </div>
            <div class="header-right">
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
                <div class="header-user">
                    <div class="user-avatar-sm"><?= strtoupper(substr($user['nombre'], 0, 1)) ?></div>
                    <span class="user-name"><?= e($user['nombre']) ?></span>
                </div>
                <a href="<?= BASE_URL ?>logout.php" class="btn-icon" title="Salir">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>
                    </svg>
                </a>
            </div>
        </header>

        <!-- Navigation Tabs -->
        <nav class="nav-tabs">
            <a href="<?= BASE_URL ?>pages/alumno/dashboard.php" class="nav-tab active">Mi TuBi</a>
            <a href="<?= BASE_URL ?>pages/alumno/aprender.php" class="nav-tab">Aprendé</a>
            <a href="<?= BASE_URL ?>pages/alumno/logros.php" class="nav-tab">Logros</a>
            <a href="#" class="nav-tab" onclick="document.getElementById('chatToggle').click(); return false;">Ayuda</a>
        </nav>

        <!-- Main Content -->
        <main class="app-content">
            <div class="dashboard-estudiante">
                <!-- Mensaje de Bienvenida -->
                <div class="welcome-banner" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); padding: 1.5rem 2rem; border-radius: 12px; color: white; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 1rem;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="32" height="32" style="flex-shrink: 0;">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    <div>
                        <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700;">¡Bienvenido, Estudiante!</h2>
                        <p style="margin: 0; opacity: 0.9; font-size: 0.95rem;">Tu panel de aprendizaje y progreso</p>
                    </div>
                </div>

                <!-- Stats rápidos y racha -->
                <div class="stats-row">
                    <div class="stat-mini">
                        <div class="stat-mini-icon">⭐</div>
                        <div class="stat-mini-value"><?= $puntos ?></div>
                        <div class="stat-mini-label">Puntos</div>
                    </div>
                    <div class="racha-card">
                        <div class="racha-icon">🔥</div>
                        <div class="racha-value"><?= $racha ?></div>
                        <div class="racha-label">días de racha</div>
                        <div class="racha-days">
                            <?php for ($i = 1; $i <= 7; $i++): ?>
                            <div class="racha-day <?= $i <= $racha ? 'active' : '' ?>"><?= $i <= $racha ? '✓' : $i ?></div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>

                <!-- Retos del momento -->
                <div class="retos-section">
                    <div class="retos-header">
                        <div class="retos-header-left">
                            <span class="retos-time-icon"><?= $iconoTiempo ?></span>
                            <div>
                                <div class="retos-title">Retos <?= $tipoReto ?>s</div>
                                <div class="retos-subtitle">Completalos y ganá puntos extra</div>
                            </div>
                        </div>
                        <div class="retos-timer">
                            <span>⏱️</span>
                            <span id="tiempoRestante">--:--</span>
                        </div>
                    </div>
                    <div class="retos-list">
                        <?php foreach ($retosActuales as $reto): ?>
                        <div class="reto-item <?= $reto['completado'] ? 'completado' : '' ?>">
                            <div class="reto-icon"><?= $reto['icono'] ?></div>
                            <div class="reto-content">
                                <div class="reto-nombre"><?= e($reto['nombre']) ?></div>
                                <div class="reto-desc"><?= e($reto['descripcion']) ?></div>
                                <div class="reto-meta">
                                    <span>⏱️ <?= $reto['duracion'] ?> min</span>
                                    <span class="reto-puntos">+<?= $reto['puntos'] ?> pts</span>
                                </div>
                            </div>
                            <div class="reto-action">
                                <?php if ($reto['completado']): ?>
                                <button class="btn-reto completado" disabled>✓ Completado</button>
                                <?php else: ?>
                                <button class="btn btn-primary btn-reto" onclick="iniciarReto('<?= $reto['id'] ?>')">
                                    ▶ Jugar
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Tarjeta de Datos del Estudiante -->
                <div class="data-card">
                    <div class="data-card-header estudiante">
                        <div class="data-card-icon estudiante">👤</div>
                        <div class="data-card-title">
                            <h3>Mi Carnet TuBi</h3>
                            <span>Datos personales del programa</span>
                        </div>
                        <span class="badge badge-success">ACTIVO</span>
                    </div>
                    <div class="data-card-body">
                        <div class="data-row">
                            <span class="data-label">📛 Nombre completo</span>
                            <span class="data-value"><?= e($user['nombre']) ?></span>
                        </div>
                        <div class="data-row">
                            <span class="data-label">🆔 DNI</span>
                            <span class="data-value"><?= e($alumnoData['dni'] ?? '40.123.456') ?></span>
                        </div>
                        <div class="data-row">
                            <span class="data-label">🏫 Escuela</span>
                            <span class="data-value"><?= e($alumnoData['escuela'] ?? 'Esc. N° 123') ?></span>
                        </div>
                        <div class="data-row">
                            <span class="data-label">📚 Curso</span>
                            <span class="data-value"><?= e($alumnoData['curso'] ?? '5° Año B') ?></span>
                        </div>
                        <div class="data-row">
                            <span class="data-label">📅 Inscripción</span>
                            <span class="data-value"><?= date('d/m/Y', strtotime($alumnoData['fecha_inscripcion'] ?? '2026-01-15')) ?></span>
                        </div>
                        <div class="data-row">
                            <span class="data-label">🏆 Nivel</span>
                            <span class="data-value">Ciclista Responsable</span>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta de Datos de la Bicicleta -->
                <div class="data-card">
                    <div class="data-card-header bicicleta">
                        <div class="data-card-icon bicicleta">🚲</div>
                        <div class="data-card-title">
                            <h3>Mi Bicicleta TuBi</h3>
                            <span>Datos del vehículo asignado</span>
                        </div>
                        <span class="badge badge-success">✓ ENTREGADA</span>
                    </div>
                    <div class="data-card-body">
                        <div class="data-row">
                            <span class="data-label">📋 Código TuBi</span>
                            <span class="data-value" style="color: #2563eb; font-weight: 700;"><?= e($biciData['codigo']) ?></span>
                        </div>
                        <div class="data-row">
                            <span class="data-label">🔢 N° de Serie</span>
                            <span class="data-value"><?= e($biciData['serie'] ?? 'SN-2026-00123') ?></span>
                        </div>
                        <div class="data-row">
                            <span class="data-label">⭕ Rodado</span>
                            <span class="data-value">R<?= e($biciData['rodado']) ?></span>
                        </div>
                        <div class="data-row">
                            <span class="data-label">🎨 Color</span>
                            <span class="data-value"><?= e($biciData['color'] ?? 'Azul TuBi') ?></span>
                        </div>
                        <div class="data-row">
                            <span class="data-label">📅 Entrega</span>
                            <span class="data-value"><?= date('d/m/Y', strtotime($biciData['fecha_entrega'] ?? '2026-02-01')) ?></span>
                        </div>
                        <div class="data-row">
                            <span class="data-label">🛡️ Garantía hasta</span>
                            <span class="data-value"><?= date('d/m/Y', strtotime($biciData['garantia_hasta'] ?? '2028-02-01')) ?></span>
                        </div>
                    </div>
                    <div class="data-card-qr">
                        <div class="qr-code-display">
                            <svg viewBox="0 0 100 100" width="84" height="84">
                                <rect x="10" y="10" width="25" height="25" fill="#000"/>
                                <rect x="65" y="10" width="25" height="25" fill="#000"/>
                                <rect x="10" y="65" width="25" height="25" fill="#000"/>
                                <rect x="45" y="45" width="10" height="10" fill="#000"/>
                                <rect x="65" y="65" width="10" height="10" fill="#000"/>
                                <rect x="80" y="80" width="10" height="10" fill="#000"/>
                                <rect x="15" y="15" width="15" height="15" fill="#fff"/>
                                <rect x="70" y="15" width="15" height="15" fill="#fff"/>
                                <rect x="15" y="70" width="15" height="15" fill="#fff"/>
                                <rect x="18" y="18" width="9" height="9" fill="#000"/>
                                <rect x="73" y="18" width="9" height="9" fill="#000"/>
                                <rect x="18" y="73" width="9" height="9" fill="#000"/>
                            </svg>
                        </div>
                    </div>
                    <div style="padding: 0 1.25rem 1.25rem;">
                        <button class="btn btn-secondary btn-block" onclick="mostrarQRCompleto()">
                            <span>📱</span> Ver QR Completo
                        </button>
                    </div>
                </div>

                <!-- Tu Progreso de Aprendizaje -->
                <div class="card">
                    <div class="card-header">
                        <h3>Tu progreso</h3>
                        <span class="progress-percent"><?= round(($modulosCompletados / $totalModulos) * 100) ?>%</span>
                    </div>
                    <div class="card-body">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?= ($modulosCompletados / $totalModulos) * 100 ?>%"></div>
                        </div>
                        <p class="progress-text">Módulo <?= $modulosCompletados ?> de <?= $totalModulos ?> completados</p>
                        <a href="<?= BASE_URL ?>pages/alumno/aprender.php" class="btn btn-primary btn-block">
                            Continuar aprendiendo
                        </a>
                    </div>
                </div>

                <!-- Accesos Rápidos -->
                <div class="card">
                    <div class="card-header">
                        <h3>Accesos rápidos</h3>
                    </div>
                    <div class="card-body">
                        <div class="quick-access-grid">
                            <a href="<?= BASE_URL ?>pages/alumno/aprender.php" class="quick-access-item">
                                <div class="qa-icon verde">🎮</div>
                                <span>Aprendé Jugando</span>
                            </a>
                            <a href="#" class="quick-access-item" onclick="alert('Próximamente'); return false;">
                                <div class="qa-icon naranja">📍</div>
                                <span>Explorá San Luis</span>
                            </a>
                            <a href="#" class="quick-access-item" onclick="document.getElementById('chatToggle').click(); return false;">
                                <div class="qa-icon azul">💬</div>
                                <span>Chat TuBi</span>
                            </a>
                            <a href="<?= BASE_URL ?>pages/alumno/logros.php" class="quick-access-item">
                                <div class="qa-icon morado">🏆</div>
                                <span>Mis Logros</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Asistente TuBi -->
                <div class="card card-gradient">
                    <div class="assistant-header">
                        <div class="assistant-icon">🤖</div>
                        <div class="assistant-info">
                            <strong>Asistente TuBi</strong>
                            <span>IA para ayudarte</span>
                        </div>
                        <span class="badge badge-online">● Online</span>
                    </div>
                    <div class="assistant-body">
                        <p>Preguntame sobre:</p>
                        <div class="assistant-chips">
                            <span class="chip" onclick="abrirChat('¿Cómo cuido mi bici?')">⚡ Cuidado de tu bici</span>
                            <span class="chip" onclick="abrirChat('¿Qué es educación vial?')">⭐ Educación vial</span>
                            <span class="chip" onclick="abrirChat('¿Cómo gano puntos?')">🏆 Cómo ganar puntos</span>
                        </div>
                        <button onclick="document.getElementById('chatToggle').click()" class="btn btn-white btn-block">
                            💬 Abrir Chat TuBi
                        </button>
                    </div>
                </div>

                <!-- Tus Logros -->
                <div class="card">
                    <div class="card-header">
                        <h3>Tus logros</h3>
                        <span class="badge badge-success"><?= count($logrosObtenidos) ?> nuevos</span>
                    </div>
                    <div class="card-body">
                        <?php foreach ($logrosObtenidos as $logro): ?>
                        <div class="logro-item">
                            <div class="logro-icon"><?= $logro['icono'] ?></div>
                            <div class="logro-content">
                                <strong><?= e($logro['titulo']) ?></strong>
                                <p><?= e($logro['descripcion']) ?></p>
                            </div>
                            <span class="logro-check">✓</span>
                        </div>
                        <?php endforeach; ?>
                        <a href="<?= BASE_URL ?>pages/alumno/logros.php" class="btn btn-link btn-block">
                            Ver todos los logros →
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Chat Widget -->
    <div class="chat-widget" id="chatBox">
        <button class="chat-fab" id="chatToggle" title="Chat TuBi">
            <span>💬</span>
        </button>
        <div class="chat-panel" id="chatWindow" style="display: none;">
            <div class="chat-panel-header">
                <span>🚲 TuBi Chat</span>
                <button class="chat-panel-close" id="chatClose">×</button>
            </div>
            <div class="chat-panel-messages" id="chatMessages"></div>
            <div class="chat-panel-input">
                <input type="text" id="chatInput" placeholder="Escribí tu mensaje..." autocomplete="off">
                <button id="chatSend">➤</button>
            </div>
        </div>
    </div>

    <script src="<?= BASE_URL ?>assets/js/chat.js"></script>
    <script>
    // Sistema de tema
    const themeToggle = document.getElementById('themeToggle');
    const html = document.documentElement;
    const savedTheme = localStorage.getItem('tubi-theme') || 'dark';
    if (savedTheme === 'light') {
        html.setAttribute('data-theme', 'light');
    }

    themeToggle?.addEventListener('click', () => {
        const currentTheme = html.getAttribute('data-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        if (newTheme === 'light') {
            html.setAttribute('data-theme', 'light');
        } else {
            html.removeAttribute('data-theme');
        }
        localStorage.setItem('tubi-theme', newTheme);
    });

    // Temporizador de retos
    function actualizarTemporizador() {
        const ahora = new Date();
        const hora = ahora.getHours();
        let proximoCambio;

        if (hora >= 6 && hora < 12) {
            proximoCambio = new Date(ahora);
            proximoCambio.setHours(12, 0, 0, 0);
        } else if (hora >= 12 && hora < 18) {
            proximoCambio = new Date(ahora);
            proximoCambio.setHours(18, 0, 0, 0);
        } else {
            proximoCambio = new Date(ahora);
            if (hora >= 18) {
                proximoCambio.setDate(proximoCambio.getDate() + 1);
            }
            proximoCambio.setHours(6, 0, 0, 0);
        }

        const diff = proximoCambio - ahora;
        const horas = Math.floor(diff / (1000 * 60 * 60));
        const minutos = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));

        const elem = document.getElementById('tiempoRestante');
        if (elem) {
            elem.textContent = `${horas.toString().padStart(2, '0')}:${minutos.toString().padStart(2, '0')}`;
        }
    }

    actualizarTemporizador();
    setInterval(actualizarTemporizador, 60000);

    // Funciones de retos
    function iniciarReto(retoId) {
        const retos = {
            'quiz_vial': { nombre: 'Quiz Vial Express', duracion: 5 },
            'ruta_segura': { nombre: 'Planificá tu Ruta', duracion: 8 },
            'check_bici': { nombre: 'Chequeo Matutino', duracion: 3 },
            'reflectantes': { nombre: 'Visibilidad Nocturna', duracion: 7 },
            'trivia_tubi': { nombre: 'Trivia TuBi', duracion: 10 },
            'desafio_eco': { nombre: 'Desafío Ecológico', duracion: 5 },
            'memoria_señales': { nombre: 'Memoria de Señales', duracion: 8 },
            'circuito_virtual': { nombre: 'Circuito Virtual', duracion: 12 },
        };

        const reto = retos[retoId];
        if (reto) {
            alert(`🎮 ¡Iniciando ${reto.nombre}!\n\nTiempo máximo: ${reto.duracion} minutos\n\n¡Buena suerte!`);
        }
    }

    function mostrarQRCompleto() {
        alert('🔲 Código QR: <?= e($biciData['codigo']) ?>\n\nEscaneá este código para verificar tu bicicleta.\n\nN° Serie: <?= e($biciData['serie'] ?? 'SN-2026-00123') ?>');
    }

    function abrirChat(msg) {
        const toggle = document.getElementById('chatToggle');
        if (toggle) {
            toggle.click();
            setTimeout(() => {
                const input = document.getElementById('chatInput');
                if (input) {
                    input.value = msg;
                    input.focus();
                }
            }, 300);
        }
    }
    </script>

    <!-- Tutorial -->
    <?php include __DIR__ . '/../../includes/tutorial.php'; ?>

    <script src="<?= BASE_URL ?>assets/js/toast.js"></script>
</body>
</html>
