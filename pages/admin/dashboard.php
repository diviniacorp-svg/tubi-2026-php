<?php
/**
 * TUBI 2026 - Dashboard Administrador
 * Centro de Control con visualización en tiempo real y gestión de IA
 */
require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn() || !hasRole('admin')) {
    redirect('login.php?role=admin');
}

$user = getCurrentUser();
$pageTitle = 'Centro de Control';

// Obtener estadísticas en tiempo real
$stats = getEstadisticasAdmin();
$bicicletas = getBicicletasParaAdmin(20);
$escuelas = getEscuelas();
$proveedores = getProveedores();

// Meta del programa: 7000 bicicletas
$metaTotal = 7000;
$progresoMeta = round(($stats['entregadas'] / max($metaTotal, 1)) * 100, 1);

// Progreso anual (sobre las existentes)
$progresoAnual = round(($stats['entregadas'] / max($stats['total_bicicletas'], 1)) * 100, 1);

// Estadisticas cruzadas para resumen ejecutivo
$totalAlumnos = dbCount('alumnos');
$totalEscuelas = dbCount('escuelas');
$totalProveedores = dbCount('proveedores');
$totalOrdenes = dbCount('ordenes');
$totalModulos = dbCount('modulos');
$totalUsuarios = dbCount('usuarios');

// Alumnos con bici entregada
$alumnosConBici = dbFetchOne('SELECT COUNT(DISTINCT alumno_id) AS total FROM bicicletas WHERE estado = ? AND alumno_id > 0', array('entregada'));
$alumnosConBiciTotal = $alumnosConBici ? (int)$alumnosConBici['total'] : 0;

// Progreso educativo promedio
$progresoEducativo = dbFetchOne('SELECT AVG(modulos_completados) AS promedio FROM alumnos');
$promedioModulos = $progresoEducativo ? round((float)$progresoEducativo['promedio'], 1) : 0;
$porcEducativo = $totalModulos > 0 ? round(($promedioModulos / $totalModulos) * 100) : 0;

// Bicis por escuela (top 5)
$bicisPorEscuela = dbFetchAll(
    'SELECT e.nombre, COUNT(b.id) AS total_bicis, ' .
    'SUM(CASE WHEN b.estado = \'entregada\' THEN 1 ELSE 0 END) AS entregadas ' .
    'FROM escuelas e LEFT JOIN bicicletas b ON b.escuela_id = e.id ' .
    'GROUP BY e.id, e.nombre ORDER BY total_bicis DESC LIMIT 5'
);

// Alertas recientes (simuladas)
$alertas = array(
    array('mensaje' => $stats['armadas'] . ' bicicletas listas para suministrar', 'tiempo' => 'Hace 5 min', 'tipo' => 'info'),
    array('mensaje' => $stats['en_escuelas'] . ' bicicletas en escuelas pendientes de entrega', 'tiempo' => 'Hace 15 min', 'tipo' => 'warning'),
    array('mensaje' => 'Proveedor "Logística San Luis" activo', 'tiempo' => 'Hace 1 hora', 'tipo' => 'success'),
);

// Distribución por estado
$distribucion = array(
    array('label' => 'Entregadas', 'valor' => $stats['entregadas'], 'color' => '#22c55e', 'icono' => '✅'),
    array('label' => 'En Escuelas', 'valor' => $stats['en_escuelas'], 'color' => '#f59e0b', 'icono' => '🏫'),
    array('label' => 'Armadas', 'valor' => $stats['armadas'], 'color' => '#3b82f6', 'icono' => '🔧'),
    array('label' => 'En Depósito', 'valor' => $stats['en_deposito'], 'color' => '#6b7280', 'icono' => '📦'),
);

// Tab activa
$tabActiva = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';

// Procesar acciones de bicicletas (admin puede hacer todo)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($tabActiva, array('bicicletas', 'flujo', 'dashboard'))) {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
              strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    $result = array('success' => false, 'message' => 'Acción no válida');

    if ($action === 'armar') {
        $biciId = (int)(isset($_POST['bici_id']) ? $_POST['bici_id'] : 0);
        if ($biciId && cambiarEstadoBicicleta($biciId, 'armada')) {
            $newStats = getEstadisticasAdmin();
            $result = array('success' => true, 'message' => 'Bicicleta marcada como ARMADA', 'action' => 'armar', 'bici_id' => $biciId, 'stats' => $newStats);
        } else {
            $result = array('success' => false, 'message' => 'No se pudo armar la bicicleta');
        }
    }

    if ($action === 'suministrar') {
        $biciId = (int)(isset($_POST['bici_id']) ? $_POST['bici_id'] : 0);
        $escuelaId = (int)(isset($_POST['escuela_id']) ? $_POST['escuela_id'] : 0);
        if ($biciId && $escuelaId && cambiarEstadoBicicleta($biciId, 'en_escuela', $escuelaId)) {
            $escuelaNombre = '';
            foreach ($escuelas as $esc) { if ($esc['id'] == $escuelaId) { $escuelaNombre = $esc['nombre']; break; } }
            $newStats = getEstadisticasAdmin();
            $result = array('success' => true, 'message' => "Bicicleta suministrada a $escuelaNombre", 'action' => 'suministrar', 'bici_id' => $biciId, 'escuela_nombre' => $escuelaNombre, 'stats' => $newStats);
        } else {
            $result = array('success' => false, 'message' => 'Seleccioná una escuela válida');
        }
    }

    if ($action === 'entregar') {
        $biciId = (int)(isset($_POST['bici_id']) ? $_POST['bici_id'] : 0);
        $dni = sanitize(isset($_POST['dni']) ? $_POST['dni'] : '');
        $nombre = sanitize(isset($_POST['nombre']) ? $_POST['nombre'] : '');
        if ($biciId) {
            // Buscar alumno por DNI
            $alumno = dbFetchOne('SELECT id FROM alumnos WHERE dni = ?', array($dni));
            $alumnoId = $alumno ? $alumno['id'] : null;
            $datos = array('estado' => 'entregada', 'fecha_entrega' => date('Y-m-d H:i:s'));
            if ($alumnoId) {
                $datos['alumno_id'] = (int)$alumnoId;
            }
            if (dbUpdate('bicicletas', $datos, 'id = ?', array($biciId))) {
                $newStats = getEstadisticasAdmin();
                $result = array('success' => true, 'message' => "Bicicleta entregada a $nombre", 'action' => 'entregar', 'bici_id' => $biciId, 'alumno_nombre' => $nombre ? $nombre : 'Alumno', 'alumno_dni' => $dni, 'stats' => $newStats);
            } else {
                $result = array('success' => false, 'message' => 'No se pudo entregar la bicicleta');
            }
        }
    }

    if ($action === 'reasignar') {
        $biciId = (int)(isset($_POST['bici_id']) ? $_POST['bici_id'] : 0);
        if ($biciId) {
            $datos = array('estado' => 'en_escuela', 'alumno_id' => null, 'fecha_entrega' => null);
            if (dbUpdate('bicicletas', $datos, 'id = ?', array($biciId))) {
                $newStats = getEstadisticasAdmin();
                $result = array('success' => true, 'message' => 'Bicicleta liberada para reasignacion', 'action' => 'reasignar', 'bici_id' => $biciId, 'stats' => $newStats);
            } else {
                $result = array('success' => false, 'message' => 'No se pudo reasignar');
            }
        }
    }

    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    setFlash($result['success'] ? 'success' : 'error', $result['message']);
    redirect('pages/admin/dashboard.php?tab=' . $tabActiva);
}

$flash = getFlash();
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
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/tubi-institucional.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🚲</text></svg>">
    <style>
        * { font-family: 'Ubuntu', -apple-system, sans-serif !important; }
    </style>
</head>
<body class="tubi-bg-pattern" data-base-url="<?= BASE_URL ?>">
    <?php include __DIR__ . '/../../includes/zocalo-header.php'; ?>

    <div class="app-container wide">
        <!-- Header -->
        <header class="app-header">
            <div class="header-left">
                <span class="header-badge admin">Administración</span>
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
                <span class="live-indicator">
                    <span class="live-dot"></span> EN VIVO
                </span>
                <button class="btn-icon" onclick="location.reload()" title="Actualizar datos">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                        <path d="M23 4v6h-6M1 20v-6h6"/>
                        <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>
                    </svg>
                </button>
                <div class="header-user">
                    <div class="user-avatar-sm admin"><?= strtoupper(substr($user['nombre'], 0, 1)) ?></div>
                    <span class="user-name"><?= e($user['nombre']) ?></span>
                </div>
                <a href="<?= BASE_URL ?>logout.php" class="btn-icon" title="Salir">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>
                    </svg>
                </a>
            </div>
        </header>

        <!-- Main Content -->
        <main class="app-content">
            <div class="dashboard-admin">
                <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?>">
                    <?= e($flash['message']) ?>
                </div>
                <?php endif; ?>

                <!-- Header del Panel -->
                <div class="card">
                    <div class="admin-header-card">
                        <div class="admin-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="28" height="28">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </div>
                        <div class="admin-info">
                            <strong>Centro de Control TuBi</strong>
                            <span>Gestión Integral del Programa · Gobierno de San Luis</span>
                        </div>
                        <span class="badge badge-live">● EN VIVO</span>
                    </div>
                </div>

                <!-- Tabs de Navegación -->
                <div class="card tabs-card">
                    <div class="tabs-nav">
                        <a href="?tab=dashboard" class="tab-item <?= $tabActiva === 'dashboard' ? 'active' : '' ?>">📊 Dashboard</a>
                        <a href="?tab=flujo" class="tab-item <?= $tabActiva === 'flujo' ? 'active' : '' ?>">🔄 Flujo de Trabajo</a>
                        <a href="?tab=bicicletas" class="tab-item <?= $tabActiva === 'bicicletas' ? 'active' : '' ?>">🚲 Bicicletas</a>
                        <a href="?tab=escuelas" class="tab-item <?= $tabActiva === 'escuelas' ? 'active' : '' ?>">🏫 Escuelas</a>
                        <a href="?tab=reportes" class="tab-item <?= $tabActiva === 'reportes' ? 'active' : '' ?>">📋 Reportes</a>
                    </div>
                </div>

                <?php if ($tabActiva === 'dashboard'): ?>
                <!-- Meta del Programa TuBi -->
                <div class="card" style="background: linear-gradient(135deg, #354393 0%, #4aacc4 100%); color: white; border: none;">
                    <div class="card-body" style="padding: 1.5rem 2rem;">
                        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                            <div>
                                <h3 style="margin: 0 0 0.25rem 0; font-size: 1.2rem; font-weight: 700; color: white;">Meta del Programa TuBi 2026</h3>
                                <p style="margin: 0; opacity: 0.9; font-size: 0.85rem;"><?php echo number_format($stats['entregadas']); ?> de <?php echo number_format($metaTotal); ?> bicicletas entregadas a estudiantes</p>
                            </div>
                            <div style="text-align: right;">
                                <span style="font-size: 2rem; font-weight: 700;"><?php echo $progresoMeta; ?>%</span>
                            </div>
                        </div>
                        <div style="margin-top: 1rem; background: rgba(255,255,255,0.2); border-radius: 10px; height: 16px; overflow: hidden;">
                            <div style="background: #22c55e; height: 100%; border-radius: 10px; width: <?php echo $progresoMeta; ?>%; transition: width 0.5s ease; min-width: 2%;"></div>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-top: 0.5rem; font-size: 0.8rem; opacity: 0.85;">
                            <span><?php echo number_format($totalAlumnos); ?> alumnos registrados</span>
                            <span><?php echo number_format($totalEscuelas); ?> escuelas · <?php echo number_format($totalProveedores); ?> proveedores</span>
                        </div>
                    </div>
                </div>

                <!-- Métricas Principales -->
                <div class="stats-grid-4">
                    <div class="stat-card highlight">
                        <div class="stat-icon azul">🚲</div>
                        <div class="stat-info">
                            <span class="stat-value"><?= number_format($stats['total_bicicletas']) ?></span>
                            <span class="stat-label">Total Bicicletas</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon verde">✅</div>
                        <div class="stat-info">
                            <span class="stat-value"><?= number_format($stats['entregadas']) ?></span>
                            <span class="stat-label">Entregadas</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon naranja">🏫</div>
                        <div class="stat-info">
                            <span class="stat-value"><?= number_format($stats['en_escuelas']) ?></span>
                            <span class="stat-label">En Escuelas</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon morado">📦</div>
                        <div class="stat-info">
                            <span class="stat-value"><?= number_format($stats['en_deposito'] + $stats['armadas']) ?></span>
                            <span class="stat-label">En Producción</span>
                        </div>
                    </div>
                </div>

                <div class="grid-2">
                    <!-- Métricas de Rendimiento -->
                    <div class="card">
                        <div class="card-header">
                            <h3>Métricas de Rendimiento</h3>
                            <span class="badge badge-info">Tiempo real</span>
                        </div>
                        <div class="card-body">
                            <div class="metrics-grid">
                                <div class="metric-item">
                                    <span class="metric-value color-primary"><?= $stats['entregas_hoy'] ?></span>
                                    <span class="metric-label">Entregas hoy</span>
                                </div>
                                <div class="metric-item">
                                    <span class="metric-value color-accent"><?= $stats['entregas_semana'] ?></span>
                                    <span class="metric-label">Esta semana</span>
                                </div>
                                <div class="metric-item">
                                    <span class="metric-value color-success"><?= $stats['entregas_mes'] ?></span>
                                    <span class="metric-label">Este mes</span>
                                </div>
                                <div class="metric-item">
                                    <span class="metric-value color-warning"><?= $stats['tasa_entrega'] ?>%</span>
                                    <span class="metric-label">Tasa de entrega</span>
                                </div>
                            </div>
                            <div class="progress-section">
                                <div class="progress-header">
                                    <span>Progreso anual</span>
                                    <span class="progress-percent"><?= $progresoAnual ?>%</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width:<?= $progresoAnual ?>%"></div>
                                </div>
                                <p class="progress-text"><?= number_format($stats['entregadas']) ?> de <?= number_format($stats['total_bicicletas']) ?> bicicletas entregadas</p>
                            </div>
                        </div>
                    </div>

                    <!-- Distribución por Estado -->
                    <div class="card">
                        <div class="card-header">
                            <h3>Distribución por Estado</h3>
                        </div>
                        <div class="card-body">
                            <?php foreach ($distribucion as $item): ?>
                            <div class="distribution-item">
                                <div class="dist-header">
                                    <span class="dist-label">
                                        <span class="dist-dot" style="background:<?= $item['color'] ?>"></span>
                                        <?= $item['icono'] ?> <?= e($item['label']) ?>
                                    </span>
                                    <span class="dist-value"><?= number_format($item['valor']) ?></span>
                                </div>
                                <div class="progress-bar thin">
                                    <div class="progress-fill" style="width:<?= ($item['valor'] / max($stats['total_bicicletas'], 1)) * 100 ?>%;background:<?= $item['color'] ?>"></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="grid-2">
                    <!-- Alertas Recientes -->
                    <div class="card">
                        <div class="card-header">
                            <h3>Actividad Reciente</h3>
                            <span class="badge badge-info"><?= count($alertas) ?></span>
                        </div>
                        <div class="card-body">
                            <?php foreach ($alertas as $alerta): ?>
                            <div class="alert-item <?= $alerta['tipo'] ?>">
                                <span class="alert-icon">
                                    <?php if ($alerta['tipo'] === 'warning'): ?>⚠️
                                    <?php elseif ($alerta['tipo'] === 'success'): ?>✅
                                    <?php else: ?>ℹ️<?php endif; ?>
                                </span>
                                <div class="alert-content">
                                    <p><?= e($alerta['mensaje']) ?></p>
                                    <small>⏱️ <?= e($alerta['tiempo']) ?></small>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Acciones Rápidas -->
                    <div class="card">
                        <div class="card-header">
                            <h3>Acciones Rápidas</h3>
                        </div>
                        <div class="card-body">
                            <div class="quick-actions-grid">
                                <a href="?tab=flujo" class="quick-action-btn">
                                    <div class="qa-icon azul">🔄</div>
                                    <span>Ver Flujo</span>
                                </a>
                                <a href="?tab=bicicletas" class="quick-action-btn">
                                    <div class="qa-icon verde">🚲</div>
                                    <span>Gestión Bicis</span>
                                </a>
                                <a href="?tab=reportes" class="quick-action-btn">
                                    <div class="qa-icon naranja">📊</div>
                                    <span>Reportes</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <?php elseif ($tabActiva === 'flujo'): ?>
                <!-- Visualización del Flujo de Trabajo -->
                <div class="card">
                    <div class="card-header">
                        <h3>Flujo de Trabajo en Tiempo Real</h3>
                        <span class="badge badge-live">● Actualizando</span>
                    </div>
                    <div class="card-body">
                        <div class="workflow-diagram">
                            <div class="workflow-stage">
                                <div class="stage-header deposito">
                                    <span class="stage-icon">📦</span>
                                    <span class="stage-title">DEPÓSITO</span>
                                </div>
                                <div class="stage-count"><?= $stats['en_deposito'] ?></div>
                                <div class="stage-label">Bicicletas en stock</div>
                            </div>

                            <div class="workflow-arrow">
                                <div class="arrow-line"></div>
                                <span class="arrow-label">Armado</span>
                            </div>

                            <div class="workflow-stage">
                                <div class="stage-header proveedor">
                                    <span class="stage-icon">🔧</span>
                                    <span class="stage-title">PROVEEDOR</span>
                                </div>
                                <div class="stage-count"><?= $stats['armadas'] ?></div>
                                <div class="stage-label">Armadas y listas</div>
                            </div>

                            <div class="workflow-arrow">
                                <div class="arrow-line"></div>
                                <span class="arrow-label">Suministro</span>
                            </div>

                            <div class="workflow-stage">
                                <div class="stage-header escuela">
                                    <span class="stage-icon">🏫</span>
                                    <span class="stage-title">ESCUELA</span>
                                </div>
                                <div class="stage-count"><?= $stats['en_escuelas'] ?></div>
                                <div class="stage-label">Pendientes de entrega</div>
                            </div>

                            <div class="workflow-arrow">
                                <div class="arrow-line"></div>
                                <span class="arrow-label">Entrega</span>
                            </div>

                            <div class="workflow-stage">
                                <div class="stage-header estudiante">
                                    <span class="stage-icon">🎓</span>
                                    <span class="stage-title">ESTUDIANTE</span>
                                </div>
                                <div class="stage-count"><?= $stats['entregadas'] ?></div>
                                <div class="stage-label">Entregadas</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Proveedores Activos -->
                <div class="card">
                    <div class="card-header">
                        <h3>Proveedores Activos</h3>
                    </div>
                    <div class="card-body">
                        <div class="providers-grid">
                            <?php foreach ($proveedores as $prov): ?>
                            <div class="provider-card">
                                <div class="provider-header">
                                    <span class="provider-icon">🚚</span>
                                    <div class="provider-info">
                                        <strong><?= e($prov['nombre']) ?></strong>
                                        <span><?= e($prov['localidad']) ?></span>
                                    </div>
                                    <span class="badge badge-online">● Activo</span>
                                </div>
                                <div class="provider-stats">
                                    <div class="prov-stat">
                                        <span class="prov-value"><?= $prov['bicicletas_armadas'] ?></span>
                                        <span class="prov-label">Armadas total</span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Escuelas con Bicicletas -->
                <div class="card">
                    <div class="card-header">
                        <h3>Escuelas en el Programa</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-wrapper">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ESCUELA</th>
                                        <th>LOCALIDAD</th>
                                        <th>BICIS ASIGNADAS</th>
                                        <th>ALUMNOS</th>
                                        <th>ESTADO</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($escuelas, 0, 5) as $esc): ?>
                                    <tr>
                                        <td><strong><?= e($esc['nombre']) ?></strong></td>
                                        <td><?= e($esc['localidad']) ?></td>
                                        <td><span class="badge badge-info"><?= $esc['bicicletas_asignadas'] ?></span></td>
                                        <td><?= $esc['total_alumnos'] ?></td>
                                        <td><span class="badge badge-success">Activa</span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <?php elseif ($tabActiva === 'bicicletas'): ?>
                <!-- Gestión de Bicicletas -->
                <div class="card">
                    <div class="card-header">
                        <h3>Gestión de Bicicletas</h3>
                        <div class="header-actions">
                            <button class="btn btn-secondary btn-sm" onclick="location.reload()">🔄 Actualizar</button>
                            <button class="btn btn-secondary btn-sm" onclick="exportarCSV()">📊 Exportar CSV</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="search-bar">
                            <input type="text" class="form-control" placeholder="🔍 Buscar por QR, Alumno, DNI o Escuela..." id="searchBici" onkeyup="filtrarTabla()">
                            <select class="form-control" style="width:auto" onchange="filtrarPorEstado(this.value)">
                                <option value="">Todos los estados</option>
                                <option value="deposito">En Depósito</option>
                                <option value="armada">Armada</option>
                                <option value="en_escuela">En Escuela</option>
                                <option value="entregada">Entregada</option>
                            </select>
                            <button class="btn btn-primary">Buscar</button>
                        </div>
                        <div class="table-wrapper">
                            <table class="table" id="tablaGestion">
                                <thead>
                                    <tr>
                                        <th>QR</th>
                                        <th>ALUMNO / DNI</th>
                                        <th>ESCUELA</th>
                                        <th>ESTADO</th>
                                        <th>FECHA</th>
                                        <th>ACCIÓN</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bicicletas as $bici): ?>
                                    <tr data-estado="<?= $bici['estado'] ?>" data-bici-id="<?= $bici['id'] ?>">
                                        <td><strong><?= e($bici['codigo']) ?></strong></td>
                                        <td class="col-alumno">
                                            <?php if ($bici['alumno']): ?>
                                                <?= e($bici['alumno']['nombre']) ?><br>
                                                <small class="text-muted">DNI: <?= e($bici['alumno']['dni']) ?></small>
                                            <?php else: ?>
                                                <em class="text-muted">Sin asignar</em>
                                            <?php endif; ?>
                                        </td>
                                        <td class="col-escuela">
                                            <?php if ($bici['escuela']): ?>
                                                <?= e($bici['escuela']['nombre']) ?>
                                            <?php else: ?>
                                                <em class="text-muted">-</em>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $estadoClass = array(
                                                'deposito' => 'badge-secondary',
                                                'armada' => 'badge-warning',
                                                'en_escuela' => 'badge-info',
                                                'entregada' => 'badge-success',
                                            );
                                            $estadoLabel = array(
                                                'deposito' => 'En Depósito',
                                                'armada' => 'Armada',
                                                'en_escuela' => 'En Escuela',
                                                'entregada' => 'Entregada',
                                            );
                                            ?>
                                            <span class="badge <?= isset($estadoClass[$bici['estado']]) ? $estadoClass[$bici['estado']] : 'badge-secondary' ?>">
                                                <?= isset($estadoLabel[$bici['estado']]) ? $estadoLabel[$bici['estado']] : $bici['estado'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($bici['fecha_entrega']): ?>
                                                <?= date('d/m/Y', strtotime($bici['fecha_entrega'])) ?>
                                            <?php elseif ($bici['fecha_armado']): ?>
                                                <?= date('d/m/Y', strtotime($bici['fecha_armado'])) ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($bici['estado'] === 'deposito'): ?>
                                                <form method="POST" style="display:inline">
                                                    <input type="hidden" name="action" value="armar">
                                                    <input type="hidden" name="bici_id" value="<?= $bici['id'] ?>">
                                                    <button type="submit" class="btn btn-warning btn-sm">🔧 Armar</button>
                                                </form>
                                            <?php elseif ($bici['estado'] === 'armada'): ?>
                                                <button class="btn btn-primary btn-sm" onclick="mostrarModalSuministrar(<?= $bici['id'] ?>, '<?= e($bici['codigo']) ?>')">🚚 Suministrar</button>
                                            <?php elseif ($bici['estado'] === 'en_escuela'): ?>
                                                <button class="btn btn-success btn-sm" onclick="mostrarModalEntregar(<?= $bici['id'] ?>, '<?= e($bici['codigo']) ?>')">🎓 Entregar</button>
                                            <?php elseif ($bici['estado'] === 'entregada'): ?>
                                                <form method="POST" style="display:inline">
                                                    <input type="hidden" name="action" value="reasignar">
                                                    <input type="hidden" name="bici_id" value="<?= $bici['id'] ?>">
                                                    <button type="submit" class="btn btn-secondary btn-sm">↺ Reasignar</button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <?php elseif ($tabActiva === 'escuelas'): ?>
                <!-- Gestión de Escuelas -->
                <div class="card">
                    <div class="card-header">
                        <h3>Escuelas Registradas</h3>
                        <span class="badge badge-info"><?= count($escuelas) ?> escuelas</span>
                    </div>
                    <div class="card-body">
                        <div class="search-bar" style="margin-bottom: 1.5rem;">
                            <input type="text" id="searchEscuela" class="input" placeholder="🔍 Buscar escuela por nombre, CUE o localidad..." style="width: 100%; max-width: 500px;">
                        </div>
                        <div class="table-wrapper">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ESCUELA</th>
                                        <th>CUE</th>
                                        <th>LOCALIDAD</th>
                                        <th>BICIS ASIGNADAS</th>
                                        <th>ALUMNOS</th>
                                        <th>ESTADO</th>
                                    </tr>
                                </thead>
                                <tbody id="escuelasTableBody">
                                    <?php foreach ($escuelas as $esc): ?>
                                    <tr>
                                        <td><strong><?= e($esc['nombre']) ?></strong></td>
                                        <td><?= e($esc['cue']) ?></td>
                                        <td><?= e($esc['localidad']) ?></td>
                                        <td><span class="badge badge-info"><?= $esc['bicicletas_asignadas'] ?></span></td>
                                        <td><?= $esc['total_alumnos'] ?></td>
                                        <td><span class="badge badge-success">Activa</span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <script>
                // Buscador de escuelas en tiempo real
                var searchEscuelaEl = document.getElementById('searchEscuela');
                if (searchEscuelaEl) {
                    searchEscuelaEl.addEventListener('input', function(e) {
                        var searchTerm = e.target.value.toLowerCase();
                        var rows = document.querySelectorAll('#escuelasTableBody tr');
                        for (var i = 0; i < rows.length; i++) {
                            var text = rows[i].textContent.toLowerCase();
                            rows[i].style.display = text.indexOf(searchTerm) >= 0 ? '' : 'none';
                        }
                    });
                }
                </script>

                <?php elseif ($tabActiva === 'reportes'): ?>
                <!-- Resumen Ejecutivo -->
                <div class="card">
                    <div class="card-header">
                        <h3>Resumen Ejecutivo del Programa</h3>
                        <span class="badge badge-live">Datos en tiempo real</span>
                    </div>
                    <div class="card-body">
                        <div class="stats-grid-4">
                            <div class="stat-card">
                                <div class="stat-icon azul">👥</div>
                                <div class="stat-info">
                                    <span class="stat-value"><?php echo number_format($totalAlumnos); ?></span>
                                    <span class="stat-label">Alumnos inscriptos</span>
                                </div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon verde">🎓</div>
                                <div class="stat-info">
                                    <span class="stat-value"><?php echo number_format($alumnosConBiciTotal); ?></span>
                                    <span class="stat-label">Alumnos con TuBi</span>
                                </div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon naranja">🏫</div>
                                <div class="stat-info">
                                    <span class="stat-value"><?php echo number_format($totalEscuelas); ?></span>
                                    <span class="stat-label">Escuelas activas</span>
                                </div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon morado">📋</div>
                                <div class="stat-info">
                                    <span class="stat-value"><?php echo number_format($totalOrdenes); ?></span>
                                    <span class="stat-label">Ordenes generadas</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid-2">
                    <!-- Progreso Educativo -->
                    <div class="card">
                        <div class="card-header">
                            <h3>Progreso Educativo</h3>
                        </div>
                        <div class="card-body">
                            <div class="metric-item" style="text-align: center; padding: 1rem 0;">
                                <span style="font-size: 2.5rem; font-weight: 700; color: #354393;"><?php echo $porcEducativo; ?>%</span>
                                <p style="margin: 0.5rem 0 0; color: var(--text-secondary);">Promedio de avance en modulos educativos</p>
                            </div>
                            <div class="progress-section" style="margin-top: 1rem;">
                                <div class="progress-header">
                                    <span>Promedio modulos completados</span>
                                    <span class="progress-percent"><?php echo $promedioModulos; ?>/<?php echo $totalModulos; ?></span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width:<?php echo $porcEducativo; ?>%;background:#354393"></div>
                                </div>
                            </div>
                            <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                                <div style="display:flex;justify-content:space-between;margin-bottom:0.5rem;">
                                    <span style="color:var(--text-secondary);font-size:0.85rem;">Total modulos disponibles</span>
                                    <strong><?php echo $totalModulos; ?></strong>
                                </div>
                                <div style="display:flex;justify-content:space-between;">
                                    <span style="color:var(--text-secondary);font-size:0.85rem;">Usuarios del sistema</span>
                                    <strong><?php echo $totalUsuarios; ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bicis por Escuela (Top 5) -->
                    <div class="card">
                        <div class="card-header">
                            <h3>Bicicletas por Escuela (Top 5)</h3>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($bicisPorEscuela)): ?>
                            <?php foreach ($bicisPorEscuela as $escItem): ?>
                            <?php
                                $escTotal = max((int)$escItem['total_bicis'], 1);
                                $escEntregadas = (int)$escItem['entregadas'];
                                $escPorc = round(($escEntregadas / $escTotal) * 100);
                            ?>
                            <div class="distribution-item">
                                <div class="dist-header">
                                    <span class="dist-label" style="font-size: 0.85rem;">
                                        🏫 <?php echo e($escItem['nombre']); ?>
                                    </span>
                                    <span class="dist-value"><?php echo $escEntregadas; ?>/<?php echo $escTotal; ?></span>
                                </div>
                                <div class="progress-bar thin">
                                    <div class="progress-fill" style="width:<?php echo $escPorc; ?>%;background:<?php echo $escPorc >= 75 ? '#22c55e' : ($escPorc >= 40 ? '#f59e0b' : '#3b82f6'); ?>"></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <p style="text-align:center;color:#888;padding:1rem 0;">Sin datos de escuelas</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Generador de Reportes -->
                <div class="card">
                    <div class="card-header">
                        <h3>Generador de Reportes</h3>
                    </div>
                    <div class="card-body">
                        <div class="reports-grid">
                            <div class="report-card" onclick="generarReporte('general')">
                                <div class="report-icon">📊</div>
                                <h4>Reporte General</h4>
                                <p>Estadisticas completas del programa</p>
                            </div>
                            <div class="report-card" onclick="generarReporte('entregas')">
                                <div class="report-icon">📋</div>
                                <h4>Reporte de Entregas</h4>
                                <p>Listado de todas las entregas realizadas</p>
                            </div>
                            <div class="report-card" onclick="generarReporte('escuelas')">
                                <div class="report-icon">🏫</div>
                                <h4>Reporte por Escuela</h4>
                                <p>Detalle por institucion educativa</p>
                            </div>
                            <div class="report-card" onclick="generarReporte('proveedores')">
                                <div class="report-icon">🚚</div>
                                <h4>Reporte de Proveedores</h4>
                                <p>Produccion por proveedor</p>
                            </div>
                        </div>
                    </div>
                </div>

                <?php else: ?>
                <div class="card">
                    <div class="card-body" style="text-align:center;padding:3rem">
                        <h3>🚧 Sección en desarrollo</h3>
                        <p>Esta funcionalidad estará disponible próximamente.</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Modal Suministrar (Admin) -->
    <div id="modalSuministrar" class="modal" style="display:none">
        <div class="modal-backdrop" onclick="cerrarModalSuministrar()"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3>Suministrar Bicicleta a Escuela</h3>
                <button onclick="cerrarModalSuministrar()" class="modal-close">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="suministrar">
                <input type="hidden" name="bici_id" id="suministrarBiciId">
                <div class="modal-body">
                    <p>Bicicleta: <strong id="suministrarBiciCodigo"></strong></p>
                    <div class="form-group">
                        <label>Seleccionar Escuela *</label>
                        <select class="form-control" name="escuela_id" required>
                            <option value="">-- Seleccionar escuela --</option>
                            <?php foreach ($escuelas as $esc): ?>
                            <option value="<?= $esc['id'] ?>"><?= e($esc['nombre']) ?> - <?= e($esc['localidad']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="cerrarModalSuministrar()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Confirmar Suministro</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Entregar (Admin) -->
    <div id="modalEntregar" class="modal" style="display:none">
        <div class="modal-backdrop" onclick="cerrarModalEntregar()"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3>Entregar Bicicleta a Alumno</h3>
                <button onclick="cerrarModalEntregar()" class="modal-close">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="entregar">
                <input type="hidden" name="bici_id" id="entregarBiciId">
                <div class="modal-body">
                    <p>Bicicleta: <strong id="entregarBiciCodigo"></strong></p>
                    <div class="form-group">
                        <label>DNI del Alumno *</label>
                        <input type="text" class="form-control" name="dni" placeholder="Ingrese DNI" required>
                    </div>
                    <div class="form-group">
                        <label>Nombre del Alumno *</label>
                        <input type="text" class="form-control" name="nombre" placeholder="Nombre completo" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="cerrarModalEntregar()">Cancelar</button>
                    <button type="submit" class="btn btn-success">Confirmar Entrega</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de Confirmación -->
    <div id="modalConfirm" class="modal" style="display:none">
        <div class="modal-backdrop" onclick="cerrarConfirm()"></div>
        <div class="modal-content" style="max-width:420px">
            <div class="modal-header">
                <h3 id="confirmTitle">Confirmar acción</h3>
                <button onclick="cerrarConfirm()" class="modal-close">&times;</button>
            </div>
            <div class="modal-body" style="text-align:center;padding:1.5rem">
                <div id="confirmIcon" style="font-size:3rem;margin-bottom:1rem">⚠️</div>
                <p id="confirmMessage" style="font-size:1rem;color:var(--text-primary);margin:0"></p>
            </div>
            <div class="modal-footer" style="justify-content:center;gap:1rem">
                <button type="button" class="btn btn-secondary" onclick="cerrarConfirm()">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmBtn">Confirmar</button>
            </div>
        </div>
    </div>

    <!-- Toast de Éxito Global -->
    <div id="toastGlobal" style="position:fixed;top:1.5rem;right:1.5rem;z-index:10000;display:none">
        <div style="background:linear-gradient(135deg,#22c55e,#16a34a);color:#fff;padding:1rem 1.5rem;border-radius:12px;box-shadow:0 8px 32px rgba(0,0,0,0.3);display:flex;align-items:center;gap:0.75rem;font-size:0.9375rem;font-weight:500;animation:toastSlideIn 0.4s ease">
            <span style="font-size:1.25rem">✓</span>
            <span id="toastMessage">Acción completada</span>
        </div>
    </div>
    <style>
    @keyframes toastSlideIn { from{transform:translateX(100%);opacity:0} to{transform:translateX(0);opacity:1} }
    @keyframes toastSlideOut { from{transform:translateX(0);opacity:1} to{transform:translateX(100%);opacity:0} }
    .inline-notif { display:flex; align-items:center; gap:0.5rem; padding:0.625rem 1rem; border-radius:8px; font-size:0.8125rem; animation:slideIn 0.3s ease; }
    .inline-notif.success { background:rgba(34,197,94,0.1); color:#22c55e; border:1px solid rgba(34,197,94,0.2); }
    .inline-notif.error { background:rgba(239,68,68,0.1); color:#ef4444; border:1px solid rgba(239,68,68,0.2); }
    @keyframes slideIn { from{opacity:0;transform:translateY(-5px)} to{opacity:1;transform:translateY(0)} }
    .row-success { animation:rowFlash 2s ease; }
    @keyframes rowFlash { 0%{background:transparent} 30%{background:rgba(34,197,94,0.15)} 100%{background:transparent} }
    .num-updated { animation:numPop 0.5s ease; color:#22c55e !important; }
    @keyframes numPop { 0%{transform:scale(1)} 50%{transform:scale(1.3)} 100%{transform:scale(1)} }
    .btn-processing { opacity:0.7; pointer-events:none; }
    </style>

    <script>
    // === Theme Toggle ===
    var themeToggle = document.getElementById('themeToggle');
    var moonIcon = themeToggle ? themeToggle.querySelector('.icon-moon') : null;
    var sunIcon = themeToggle ? themeToggle.querySelector('.icon-sun') : null;

    var savedTheme = localStorage.getItem('tubi-theme') || 'light';
    if (savedTheme === 'dark') {
        document.body.setAttribute('data-theme', 'dark');
        if (moonIcon) moonIcon.style.display = 'none';
        if (sunIcon) sunIcon.style.display = 'block';
    } else {
        document.body.removeAttribute('data-theme');
        if (moonIcon) moonIcon.style.display = 'block';
        if (sunIcon) sunIcon.style.display = 'none';
    }

    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            var isDark = document.body.getAttribute('data-theme') === 'dark';
            if (isDark) {
                document.body.removeAttribute('data-theme');
                if (moonIcon) moonIcon.style.display = 'block';
                if (sunIcon) sunIcon.style.display = 'none';
                localStorage.setItem('tubi-theme', 'light');
            } else {
                document.body.setAttribute('data-theme', 'dark');
                if (moonIcon) moonIcon.style.display = 'none';
                if (sunIcon) sunIcon.style.display = 'block';
                localStorage.setItem('tubi-theme', 'dark');
            }
        });
    }

    // === MODALES ===
    function mostrarModalSuministrar(biciId, biciCodigo) {
        document.getElementById('suministrarBiciId').value = biciId;
        document.getElementById('suministrarBiciCodigo').textContent = biciCodigo;
        document.getElementById('modalSuministrar').style.display = 'flex';
    }
    function cerrarModalSuministrar() {
        document.getElementById('modalSuministrar').style.display = 'none';
    }

    function mostrarModalEntregar(biciId, biciCodigo) {
        document.getElementById('entregarBiciId').value = biciId;
        document.getElementById('entregarBiciCodigo').textContent = biciCodigo;
        document.getElementById('modalEntregar').style.display = 'flex';
    }
    function cerrarModalEntregar() {
        document.getElementById('modalEntregar').style.display = 'none';
    }

    // === SISTEMA DE CONFIRMACION ===
    var _confirmResolve = null;
    var _confirmPendingForm = null;

    function mostrarConfirm(titulo, mensaje, icono, callback) {
        _confirmResolve = callback;
        document.getElementById('confirmTitle').textContent = titulo;
        document.getElementById('confirmMessage').textContent = mensaje;
        document.getElementById('confirmIcon').textContent = icono || '⚠️';
        document.getElementById('modalConfirm').style.display = 'flex';
    }

    function cerrarConfirm() {
        document.getElementById('modalConfirm').style.display = 'none';
        if (_confirmResolve) { _confirmResolve(false); _confirmResolve = null; }
    }

    var confirmBtnEl = document.getElementById('confirmBtn');
    if (confirmBtnEl) {
        confirmBtnEl.addEventListener('click', function() {
            document.getElementById('modalConfirm').style.display = 'none';
            if (_confirmResolve) { _confirmResolve(true); _confirmResolve = null; }
        });
    }

    function showToast(message) {
        var toast = document.getElementById('toastGlobal');
        document.getElementById('toastMessage').textContent = message;
        toast.style.display = 'block';
        toast.querySelector('div').style.animation = 'toastSlideIn 0.4s ease';
        setTimeout(function() {
            toast.querySelector('div').style.animation = 'toastSlideOut 0.4s ease forwards';
            setTimeout(function() { toast.style.display = 'none'; }, 400);
        }, 3500);
    }

    // === TABLA Y FILTROS ===
    function filtrarTabla() {
        var searchEl = document.getElementById('searchBici');
        var filter = searchEl ? searchEl.value.toLowerCase() : '';
        var rows = document.querySelectorAll('#tablaGestion tbody tr');
        for (var i = 0; i < rows.length; i++) {
            if (rows[i].className.indexOf('notif-row') >= 0) continue;
            rows[i].style.display = rows[i].textContent.toLowerCase().indexOf(filter) >= 0 ? '' : 'none';
        }
    }

    function filtrarPorEstado(estado) {
        var rows = document.querySelectorAll('#tablaGestion tbody tr');
        for (var i = 0; i < rows.length; i++) {
            if (rows[i].className.indexOf('notif-row') >= 0) continue;
            if (!estado || rows[i].getAttribute('data-estado') === estado) {
                rows[i].style.display = '';
            } else {
                rows[i].style.display = 'none';
            }
        }
    }

    function exportarCSV() {
        var table = document.getElementById('tablaGestion');
        if (!table) { alert('No hay tabla para exportar'); return; }
        var csv = [];
        var allRows = table.querySelectorAll('tr');
        for (var r = 0; r < allRows.length; r++) {
            if (allRows[r].className.indexOf('notif-row') >= 0) continue;
            var cols = allRows[r].querySelectorAll('td, th');
            var rowData = [];
            for (var c = 0; c < cols.length; c++) {
                rowData.push('"' + cols[c].textContent.trim().replace(/"/g, '""') + '"');
            }
            csv.push(rowData.join(','));
        }
        var blob = new Blob(['\uFEFF' + csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
        var link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'tubi_admin_' + new Date().toISOString().slice(0,10) + '.csv';
        link.click();
    }

    function generarReporte(tipo) {
        var tipos = {
            'general': 'Reporte General del Programa TuBi 2026',
            'entregas': 'Reporte de Entregas Realizadas',
            'escuelas': 'Reporte por Escuela',
            'proveedores': 'Reporte de Produccion por Proveedor'
        };
        alert('Generando: ' + tipos[tipo] + '\n\nEl documento PDF se descargara en breve.');
    }

    // === SISTEMA DE ACCIONES EN TIEMPO REAL (ADMIN) ===

    function getAdminConfirmMessage(action, biciId, formEl) {
        var biciRow = document.querySelector('tr[data-bici-id="' + biciId + '"]');
        var codeEl = biciRow ? biciRow.querySelector('td:first-child strong') : null;
        var code = codeEl ? codeEl.textContent : '';

        if (action === 'armar') return { title: 'Confirmar Armado', msg: 'Confirmar armado de bicicleta ' + code + '?', icon: '🔧' };
        if (action === 'suministrar') {
            var sel = document.querySelector('#modalSuministrar select[name="escuela_id"]');
            var escNombre = (sel && sel.options[sel.selectedIndex]) ? sel.options[sel.selectedIndex].text : '';
            return { title: 'Confirmar Suministro', msg: 'Suministrar ' + code + ' a ' + escNombre + '?', icon: '🚚' };
        }
        if (action === 'entregar') {
            var nombreInput = formEl.querySelector('[name="nombre"]');
            var nombre = nombreInput ? nombreInput.value : 'alumno';
            return { title: 'Confirmar Entrega', msg: 'Entregar ' + code + ' al alumno ' + nombre + '?', icon: '🎓' };
        }
        if (action === 'reasignar') return { title: 'Confirmar Reasignacion', msg: 'Liberar bicicleta ' + code + ' para reasignacion?', icon: '↺' };
        return null;
    }

    function enviarAccionAdmin(form) {
        var actionInput = form.querySelector('[name="action"]');
        if (!actionInput) return;
        var action = actionInput.value;
        var biciIdInput = form.querySelector('[name="bici_id"]');
        var biciId = biciIdInput ? biciIdInput.value : '';

        var btn = form.querySelector('button[type="submit"]');
        if (btn) {
            btn.className += ' btn-processing';
            btn.setAttribute('data-orig', btn.innerHTML);
            btn.innerHTML = '⏳...';
        }

        var url = window.location.pathname + '?tab=bicicletas';
        var xhr = new XMLHttpRequest();
        xhr.open('POST', url, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    try {
                        var data = JSON.parse(xhr.responseText);
                        if (data.success) {
                            showToast(data.message);
                            if (action === 'armar') handleAdminArmarSuccess(biciId, data);
                            else if (action === 'suministrar') { cerrarModalSuministrar(); handleAdminSuministrarSuccess(biciId, data); }
                            else if (action === 'entregar') { cerrarModalEntregar(); handleAdminEntregarSuccess(biciId, data); }
                            else if (action === 'reasignar') handleAdminReasignarSuccess(biciId, data);
                            if (data.stats) updateAdminStats(data.stats);
                        } else {
                            var row = null;
                            var parent = form.parentNode;
                            while (parent) {
                                if (parent.nodeName === 'TR') { row = parent; break; }
                                parent = parent.parentNode;
                            }
                            if (row) showRowNotif(row, 'error', data.message);
                            else alert(data.message);
                            restoreBtn(btn);
                        }
                    } catch(ex) {
                        alert('Error procesando respuesta');
                        restoreBtn(btn);
                    }
                } else {
                    alert('Error de conexion');
                    restoreBtn(btn);
                }
            }
        };
        var fd = new FormData(form);
        xhr.send(fd);
    }

    document.addEventListener('submit', function(e) {
        var form = e.target;
        var actionInput = form.querySelector('[name="action"]');
        if (!actionInput) return;

        e.preventDefault();
        var action = actionInput.value;
        var biciIdInput = form.querySelector('[name="bici_id"]');
        var biciId = biciIdInput ? biciIdInput.value : '';

        var confirmInfo = getAdminConfirmMessage(action, biciId, form);
        if (confirmInfo) {
            mostrarConfirm(confirmInfo.title, confirmInfo.msg, confirmInfo.icon, function(confirmed) {
                if (confirmed) enviarAccionAdmin(form);
            });
        } else {
            enviarAccionAdmin(form);
        }
    });

    function restoreBtn(btn) {
        if (btn) {
            btn.className = btn.className.replace(' btn-processing', '');
            btn.innerHTML = btn.getAttribute('data-orig') || 'Accion';
        }
    }

    // === HANDLERS DE EXITO POR ACCION ===

    function handleAdminArmarSuccess(biciId, data) {
        var row = document.querySelector('tr[data-bici-id="' + biciId + '"]');
        if (!row) return;
        row.setAttribute('data-estado', 'armada');
        row.querySelector('.badge').className = 'badge badge-warning';
        row.querySelector('.badge').textContent = 'Armada';
        var code = row.querySelector('td:first-child strong').textContent;
        row.querySelector('td:last-child').innerHTML = '<button class="btn btn-primary btn-sm" onclick="mostrarModalSuministrar(' + biciId + ', \'' + code + '\')">🚚 Suministrar</button>';
        flashRow(row);
        showRowNotif(row, 'success', '✓ ' + data.message);
    }

    function handleAdminSuministrarSuccess(biciId, data) {
        var row = document.querySelector('tr[data-bici-id="' + biciId + '"]');
        if (!row) return;
        row.setAttribute('data-estado', 'en_escuela');
        row.querySelector('.badge').className = 'badge badge-info';
        row.querySelector('.badge').textContent = 'En Escuela';
        var escCell = row.querySelector('.col-escuela');
        if (escCell) escCell.innerHTML = data.escuela_nombre;
        var code = row.querySelector('td:first-child strong').textContent;
        row.querySelector('td:last-child').innerHTML = '<button class="btn btn-success btn-sm" onclick="mostrarModalEntregar(' + biciId + ', \'' + code + '\')">🎓 Entregar</button>';
        flashRow(row);
        showRowNotif(row, 'success', '✓ ' + data.message);
    }

    function handleAdminEntregarSuccess(biciId, data) {
        var row = document.querySelector('tr[data-bici-id="' + biciId + '"]');
        if (!row) return;
        row.setAttribute('data-estado', 'entregada');
        row.querySelector('.badge').className = 'badge badge-success';
        row.querySelector('.badge').textContent = 'Entregada';
        var alumnoCell = row.querySelector('.col-alumno');
        if (alumnoCell) alumnoCell.innerHTML = data.alumno_nombre + '<br><small class="text-muted">DNI: ' + data.alumno_dni + '</small>';
        row.querySelector('td:last-child').innerHTML = '<form method="POST" style="display:inline"><input type="hidden" name="action" value="reasignar"><input type="hidden" name="bici_id" value="' + biciId + '"><button type="submit" class="btn btn-secondary btn-sm">↺ Reasignar</button></form>';
        var cells = row.querySelectorAll('td');
        if (cells[4]) cells[4].textContent = new Date().toLocaleDateString('es-AR');
        flashRow(row);
        showRowNotif(row, 'success', '✓ ' + data.message);
    }

    function handleAdminReasignarSuccess(biciId, data) {
        var row = document.querySelector('tr[data-bici-id="' + biciId + '"]');
        if (!row) return;
        row.setAttribute('data-estado', 'en_escuela');
        row.querySelector('.badge').className = 'badge badge-info';
        row.querySelector('.badge').textContent = 'En Escuela';
        var alumnoCell = row.querySelector('.col-alumno');
        if (alumnoCell) alumnoCell.innerHTML = '<em class="text-muted">Sin asignar</em>';
        var code = row.querySelector('td:first-child strong').textContent;
        row.querySelector('td:last-child').innerHTML = '<button class="btn btn-success btn-sm" onclick="mostrarModalEntregar(' + biciId + ', \'' + code + '\')">🎓 Entregar</button>';
        flashRow(row);
        showRowNotif(row, 'success', '✓ ' + data.message);
    }

    function flashRow(row) {
        row.className += ' row-success';
        setTimeout(function() { row.className = row.className.replace(' row-success', ''); }, 2100);
    }

    function showRowNotif(row, type, message) {
        var bId = row.getAttribute('data-bici-id');
        var prev = row.parentNode.querySelector('.notif-row[data-for="' + bId + '"]');
        if (prev) prev.parentNode.removeChild(prev);
        var notifRow = document.createElement('tr');
        notifRow.className = 'notif-row';
        notifRow.setAttribute('data-for', bId);
        var colCount = row.querySelectorAll('td').length;
        var icon = type === 'success' ? '✓' : '✕';
        notifRow.innerHTML = '<td colspan="' + colCount + '"><div class="inline-notif ' + type + '"><span>' + icon + '</span><span>' + message + '</span></div></td>';
        row.parentNode.insertBefore(notifRow, row.nextSibling);
        setTimeout(function() { notifRow.style.transition = 'opacity 0.5s'; notifRow.style.opacity = '0'; setTimeout(function() { if (notifRow.parentNode) notifRow.parentNode.removeChild(notifRow); }, 500); }, 4000);
    }

    // === ACTUALIZAR STATS DEL ADMIN ===
    function updateAdminStats(stats) {
        var stageCounts = document.querySelectorAll('.stage-count');
        if (stageCounts.length >= 4) {
            animateNum(stageCounts[0], stats.en_deposito);
            animateNum(stageCounts[1], stats.armadas);
            animateNum(stageCounts[2], stats.en_escuelas);
            animateNum(stageCounts[3], stats.entregadas);
        }

        var statVals = document.querySelectorAll('.stat-value');
        if (statVals.length >= 4) {
            animateNum(statVals[0], stats.total_bicicletas);
            animateNum(statVals[1], stats.entregadas);
            animateNum(statVals[2], stats.en_escuelas);
            animateNum(statVals[3], stats.armadas);
        }

        var progressBar = document.querySelector('.progress-fill');
        if (progressBar && stats.total_bicicletas > 0) {
            var pct = Math.round((stats.entregadas / stats.total_bicicletas) * 100 * 10) / 10;
            progressBar.style.width = pct + '%';
            var pctLabel = document.querySelector('.progress-percent');
            if (pctLabel) pctLabel.textContent = pct + '%';
        }
    }

    function animateNum(el, newVal) {
        if (!el) return;
        var old = parseInt(el.textContent) || 0;
        if (old === newVal) return;
        el.textContent = newVal;
        el.className += ' num-updated';
        setTimeout(function() { el.className = el.className.replace(' num-updated', ''); }, 600);
    }

    // Auto-refresh cada 90 segundos para admin
    setTimeout(function() { location.reload(); }, 90000);
    </script>

    <!-- Tutorial -->
    <?php include __DIR__ . '/../../includes/tutorial.php'; ?>

    <script src="<?= BASE_URL ?>assets/js/toast.js"></script>

    <?php include __DIR__ . '/../../includes/zocalo-footer.php'; ?>
</body>
</html>
