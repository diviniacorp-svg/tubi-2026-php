<?php
/**
 * TUBI 2026 - Aprendé Jugando
 * Sistema de gamificación con videos y trivia
 */
require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn() || !hasRole('alumno')) {
    redirect('login.php?role=alumno');
}

$user = getCurrentUser();
$pageTitle = 'Aprendé Jugando';

// Videos completados (tracking en sesión)
if (!isset($_SESSION['tubi_videos_vistos'])) {
    $_SESSION['tubi_videos_vistos'] = 0;
}
$videosVistos = $_SESSION['tubi_videos_vistos'];
$totalVideos = 3;
$quizDesbloqueado = $videosVistos >= $totalVideos;

// Progreso del estudiante (desde BD)
$alumnoData = getAlumnoByUsuario($user['id']);
$studentProgress = array(
    'total_points' => $alumnoData ? (int)$alumnoData['puntos'] : 0,
    'modules_completed' => $alumnoData ? (int)$alumnoData['modulos_completados'] : 0,
    'badges' => array(),
    'streak_days' => $alumnoData ? (int)$alumnoData['racha'] : 0
);

// Módulos de aprendizaje (desde BD)
$modulosDB = dbFetchAll('SELECT * FROM modulos ORDER BY id');
$modulos = array();
$colorIdx = 0;
$colores = array('#354393', '#4aacc4');
foreach ($modulosDB as $mod) {
    $modulos[] = array(
        'id' => (int)$mod['id'],
        'titulo' => $mod['titulo'],
        'descripcion' => $mod['descripcion'],
        'icono' => $mod['icono'],
        'color' => $colores[$colorIdx % 2],
        'puntos' => (int)$mod['puntos'],
        'completado' => ((int)$mod['id'] <= $studentProgress['modules_completed']),
        'preguntas' => 5
    );
    $colorIdx++;
}

// Insignias ganadas (desde BD)
$logrosGanados = array();
if ($alumnoData) {
    $allLogros = dbFetchAll('SELECT * FROM logros ORDER BY id');
    foreach ($allLogros as $idx => $logro) {
        if ($idx < $studentProgress['modules_completed']) {
            $logrosGanados[] = $logro;
        }
    }
}
$studentProgress['badges'] = $logrosGanados;

$totalModules = count($modulos);
$completedModules = 0;
foreach ($modulos as $m) { if ($m['completado']) $completedModules++; }
$progressPercent = ($totalModules > 0) ? ($completedModules / $totalModules) * 100 : 0;
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
        /* === RESET & BASE === */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        * { font-family: 'Ubuntu', -apple-system, sans-serif; }

        body {
            background: #e4f1f7;
            color: #414242;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
            position: relative;
        }
        body::before {
            content: '';
            position: fixed;
            top: 50%;
            left: 50%;
            width: 600px;
            height: 600px;
            transform: translate(-50%, -50%);
            background: url('<?= BASE_URL ?>assets/img/intro-central.png') no-repeat center;
            background-size: contain;
            opacity: 0.06;
            z-index: 0;
            pointer-events: none;
        }

        /* === ZÓCALO SUPERIOR === */
        .zocalo-institucional, .app-header, .nav-tabs, .app-content, .zocalo-pie {
            position: relative;
            z-index: 1;
        }
        .zocalo-institucional {
            background: linear-gradient(135deg, #354393 0%, #4aacc4 100%);
            padding: 0.5rem 1.5rem;
            color: white;
        }
        .zocalo-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .zocalo-left, .zocalo-right { display: flex; align-items: center; }
        .zocalo-tubi-logo { height: 54px; width: auto; }
        .zocalo-edu-img { height: 58px; width: auto; }

        /* === APP HEADER === */
        .app-header {
            background: #dce9f2;
            border-bottom: 1px solid #c8dfe9;
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 1200px;
            margin: 0 auto;
        }
        .header-left { display: flex; align-items: center; gap: 0.75rem; }
        .header-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 100px;
            font-size: 0.75rem;
            font-weight: 600;
            color: white;
            background: linear-gradient(135deg, #354393, #4a5aab);
        }
        .header-right { display: flex; align-items: center; gap: 0.75rem; }
        .header-user { display: flex; align-items: center; gap: 0.5rem; }
        .user-avatar-sm {
            width: 32px; height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #354393, #4aacc4);
            color: white;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 0.85rem;
        }
        .user-name { font-size: 0.85rem; font-weight: 500; color: #414242; }
        .btn-icon {
            background: none; border: none; cursor: pointer;
            color: #414242; padding: 0.25rem;
            text-decoration: none; display: flex;
        }

        /* === NAV TABS === */
        .nav-tabs {
            display: flex;
            background: #dce9f2;
            border-bottom: 2px solid #c8dfe9;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }
        .nav-tab {
            padding: 0.75rem 1.25rem;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            color: #354393;
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            transition: 0.2s;
        }
        .nav-tab:hover { border-bottom-color: #c8dfe9; }
        .nav-tab.active { color: #354393; border-bottom-color: #4aacc4; font-weight: 600; }

        /* === CONTENIDO PRINCIPAL === */
        .app-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1.5rem;
        }

        /* === BANNER APRENDÉ === */
        .learn-banner {
            background: linear-gradient(135deg, #354393 0%, #4aacc4 100%);
            border-radius: 16px;
            padding: 2rem;
            color: white;
            margin-bottom: 1.5rem;
        }
        .learn-banner h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .learn-banner p {
            font-size: 0.95rem;
            opacity: 0.9;
        }

        /* === STATS ROW === */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .stat-card {
            background: #eef6fa;
            border: 1px solid #c8dfe9;
            border-radius: 14px;
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .stat-card-icon {
            width: 44px; height: 44px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }
        .stat-card-icon.azul { background: rgba(53, 67, 147, 0.12); }
        .stat-card-icon.turquesa { background: rgba(74, 172, 196, 0.12); }
        .stat-card-icon.verde { background: rgba(34, 197, 94, 0.12); }
        .stat-card-icon.naranja { background: rgba(249, 115, 22, 0.12); }
        .stat-card-value { font-size: 1.4rem; font-weight: 700; color: #354393; line-height: 1; }
        .stat-card-label { font-size: 0.75rem; color: #6b7b8a; }

        /* === PROGRESS CARD === */
        .progress-card {
            background: linear-gradient(135deg, #354393 0%, #4aacc4 100%);
            border-radius: 14px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.5rem;
            color: white;
        }
        .progress-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }
        .progress-card-title { font-weight: 600; font-size: 0.95rem; }
        .progress-card-percent { font-size: 1.25rem; font-weight: 700; }
        .progress-bar-large {
            height: 10px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 5px;
            overflow: hidden;
        }
        .progress-bar-fill {
            height: 100%;
            background: white;
            border-radius: 5px;
            transition: width 0.5s ease;
        }

        /* === INSIGNIAS === */
        .badges-section {
            background: #eef6fa;
            border: 1px solid #c8dfe9;
            border-radius: 16px;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
        }
        .badges-title {
            font-weight: 600; font-size: 1rem;
            color: #354393; margin-bottom: 0.75rem;
        }
        .badges-row { display: flex; gap: 0.75rem; flex-wrap: wrap; }
        .badge-item {
            width: 56px; height: 56px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.6rem;
            position: relative;
        }
        .badge-item.earned {
            background: linear-gradient(135deg, #354393, #4aacc4);
            box-shadow: 0 4px 12px rgba(53, 67, 147, 0.25);
        }
        .badge-item.locked {
            background: #d0e3ed;
            opacity: 0.55;
        }
        .badge-item.locked::after {
            content: '🔒';
            position: absolute;
            font-size: 0.75rem;
            bottom: -4px; right: -4px;
        }

        /* === SECCIÓN TÍTULO === */
        .section-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #354393;
            margin: 1.5rem 0 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* === MÓDULOS GRID === */
        .modules-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .module-card {
            background: #eef6fa;
            border: 1px solid #c8dfe9;
            border-radius: 16px;
            padding: 1.25rem;
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative;
            overflow: hidden;
        }
        .module-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(53, 67, 147, 0.1);
        }
        .module-card.completed { border-color: #22c55e; }
        .module-card.completed::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, #22c55e, #16a34a);
        }
        .module-card.locked { opacity: 0.6; pointer-events: none; }

        .module-header {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
        }
        .module-icon {
            width: 52px; height: 52px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.6rem;
            flex-shrink: 0;
        }
        .module-info h3 {
            font-size: 0.95rem;
            font-weight: 600;
            color: #414242;
            margin-bottom: 0.25rem;
        }
        .module-info p {
            font-size: 0.8rem;
            color: #6b7b8a;
            margin: 0;
            line-height: 1.4;
        }

        .module-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 0.75rem;
            font-size: 0.8rem;
        }
        .module-meta-item {
            display: flex; align-items: center; gap: 0.25rem;
            color: #6b7b8a;
        }

        .module-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .module-points {
            display: flex; align-items: center; gap: 0.25rem;
            font-weight: 600; color: #4aacc4; font-size: 0.875rem;
        }

        .btn-module {
            padding: 0.5rem 1rem;
            border-radius: 100px;
            font-weight: 600;
            font-size: 0.8rem;
            cursor: pointer;
            transition: 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            border: none;
        }
        .btn-module.primary {
            background: linear-gradient(135deg, #354393, #4aacc4);
            color: white;
        }
        .btn-module.primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(53, 67, 147, 0.3);
        }
        .btn-module.completed-btn {
            background: rgba(34, 197, 94, 0.12);
            color: #22c55e;
            border: 1px solid #22c55e;
        }
        .btn-module.locked-btn {
            background: #d0e3ed;
            color: #8a9aaa;
            cursor: not-allowed;
        }

        /* === ZÓCALO PIE === */
        .zocalo-pie {
            background: linear-gradient(135deg, #354393 0%, #4aacc4 100%);
            padding: 1rem 1.5rem;
            color: white;
            margin-top: 2rem;
        }
        .zocalo-pie-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .zocalo-pie-left { display: flex; align-items: center; gap: 0.75rem; }
        .zocalo-pie-left span { font-size: 0.7rem; opacity: 0.85; display: block; }
        .zocalo-pie-sep { opacity: 0.3; font-size: 1.5rem; font-weight: 300; }
        .zocalo-pie-logo { height: 22px; width: auto; }
        .zocalo-pie-right { font-size: 0.75rem; opacity: 0.7; }

        /* === RESPONSIVE === */
        @media (max-width: 1024px) {
            .modules-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 768px) {
            .stats-row { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 640px) {
            .zocalo-inner, .zocalo-pie-inner { flex-direction: column; gap: 0.5rem; text-align: center; }
            .zocalo-tubi-logo { height: 38px; }
            .zocalo-edu-img { height: 44px; }
            .stats-row { grid-template-columns: 1fr 1fr; }
            .modules-grid { grid-template-columns: 1fr; }
            .app-content { padding: 1rem; }
            .zocalo-pie-left { flex-wrap: wrap; justify-content: center; }
        }
    </style>
</head>
<body>
    <!-- Zócalo Institucional Superior -->
    <div class="zocalo-institucional">
        <div class="zocalo-inner">
            <div class="zocalo-left">
                <img src="<?= BASE_URL ?>assets/img/tubi-logo-blanco.png" alt="TuBi" class="zocalo-tubi-logo">
            </div>
            <div class="zocalo-right">
                <img src="<?= BASE_URL ?>assets/img/edu-logo-blanco.png" alt="2026 Año de la Educación" class="zocalo-edu-img">
            </div>
        </div>
    </div>

    <!-- Header -->
    <header class="app-header">
        <div class="header-left">
            <span class="header-badge">Estudiante</span>
        </div>
        <div class="header-right">
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

    <!-- Nav Tabs -->
    <nav class="nav-tabs">
        <a href="<?= BASE_URL ?>pages/alumno/dashboard.php" class="nav-tab">Mi TuBi</a>
        <a href="<?= BASE_URL ?>pages/alumno/aprender.php" class="nav-tab active">Aprendé</a>
        <a href="<?= BASE_URL ?>pages/alumno/logros.php" class="nav-tab">Logros</a>
    </nav>

    <!-- Contenido -->
    <main class="app-content">
        <!-- Banner -->
        <div class="learn-banner">
            <h1>Aprendé Jugando</h1>
            <p>Mirá los videos, respondé las preguntas y ganá puntos</p>
        </div>

        <!-- Stats -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-card-icon azul">⭐</div>
                <div>
                    <div class="stat-card-value"><?= number_format($studentProgress['total_points']) ?></div>
                    <div class="stat-card-label">Puntos Totales</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-icon turquesa">📚</div>
                <div>
                    <div class="stat-card-value"><?= $completedModules ?>/<?= $totalModules ?></div>
                    <div class="stat-card-label">Módulos</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-icon verde">🏆</div>
                <div>
                    <div class="stat-card-value"><?= count($studentProgress['badges']) ?></div>
                    <div class="stat-card-label">Insignias</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-icon naranja">🔥</div>
                <div>
                    <div class="stat-card-value"><?= $studentProgress['streak_days'] ?></div>
                    <div class="stat-card-label">Días seguidos</div>
                </div>
            </div>
        </div>

        <!-- Progreso general -->
        <div class="progress-card">
            <div class="progress-card-header">
                <span class="progress-card-title">Tu progreso general</span>
                <span class="progress-card-percent"><?= round($progressPercent) ?>%</span>
            </div>
            <div class="progress-bar-large">
                <div class="progress-bar-fill" style="width: <?= $progressPercent ?>%"></div>
            </div>
        </div>

        <!-- Insignias -->
        <div class="badges-section">
            <h2 class="badges-title">Tus Insignias</h2>
            <div class="badges-row">
                <?php
                $allLogrosForBadges = dbFetchAll('SELECT * FROM logros ORDER BY id');
                foreach ($allLogrosForBadges as $idx => $logro):
                    $earned = ($idx < $studentProgress['modules_completed']);
                ?>
                <div class="badge-item <?= $earned ? 'earned' : 'locked' ?>" title="<?= e($logro['titulo']) ?>"><?= $logro['icono'] ?></div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Módulos de aprendizaje -->
        <h2 class="section-title"><span>📖</span> Módulos de Aprendizaje</h2>
        <div class="modules-grid">
            <?php foreach ($modulos as $index => $modulo):
                $isLocked = !$modulo['completado'] && $index > 0 && !$modulos[$index - 1]['completado'];
            ?>
            <div class="module-card <?= $modulo['completado'] ? 'completed' : '' ?> <?= $isLocked ? 'locked' : '' ?>">
                <div class="module-header">
                    <div class="module-icon" style="background: <?= $modulo['color'] ?>15;">
                        <?= $modulo['icono'] ?>
                    </div>
                    <div class="module-info">
                        <h3><?= e($modulo['titulo']) ?></h3>
                        <p><?= e($modulo['descripcion']) ?></p>
                    </div>
                </div>
                <div class="module-meta">
                    <span class="module-meta-item">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                        1 video
                    </span>
                    <span class="module-meta-item">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        <?= $modulo['preguntas'] ?> preguntas
                    </span>
                </div>
                <div class="module-footer">
                    <span class="module-points">⭐ <?= $modulo['puntos'] ?> pts</span>
                    <?php if ($modulo['completado']): ?>
                        <a href="<?= BASE_URL ?>pages/alumno/modulo.php?id=<?= $modulo['id'] ?>" class="btn-module completed-btn">✓ Completado</a>
                    <?php elseif ($isLocked): ?>
                        <span class="btn-module locked-btn">🔒 Bloqueado</span>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>pages/alumno/modulo.php?id=<?= $modulo['id'] ?>" class="btn-module primary">
                            Comenzar
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- Zócalo Pie -->
    <div class="zocalo-pie">
        <div class="zocalo-pie-inner">
            <div class="zocalo-pie-left">
                <img src="<?= BASE_URL ?>assets/img/tubi-logo-blanco.png" alt="TuBi" class="zocalo-pie-logo">
                <span class="zocalo-pie-sep">|</span>
                <div><span>Secretaría<br>de Transporte</span></div>
            </div>
            <div class="zocalo-pie-right">
                <span>Mi provincia en bicicleta - San Luis 2026</span>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../../includes/tutorial.php'; ?>
</body>
</html>
