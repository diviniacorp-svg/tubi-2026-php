<?php
/**
 * TUBI 2026 - Dashboard Escuela
 * Gestión de entregas con datos en tiempo real
 */
require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn() || !hasRole('escuela')) {
    redirect('login.php?role=escuela');
}

$user = getCurrentUser();
$pageTitle = 'Panel Escuela';

// Obtener la escuela real por CUE del usuario
$userCue = isset($user['cue']) ? $user['cue'] : '';
$escuelaRow = $userCue ? dbFetchOne('SELECT id FROM escuelas WHERE cue = ?', array($userCue)) : null;
$escuelaId = $escuelaRow ? (int)$escuelaRow['id'] : 1;
$escuela = getEscuela($escuelaId);
$stats = getEstadisticasEscuela($escuelaId);
$bicicletas = getBicicletasParaEscuela($escuelaId, 15);
$alumnos = getAlumnos(array('escuela_id' => $escuelaId));

// Datos de la escuela
$escuelaData = array(
    'nombre' => isset($escuela['nombre']) ? $escuela['nombre'] : (isset($user['nombre']) ? $user['nombre'] : 'Escuela N° 123 "Gral. San Martín"'),
    'cue' => isset($escuela['cue']) ? $escuela['cue'] : (isset($user['cue']) ? $user['cue'] : '740001234'),
    'localidad' => isset($escuela['localidad']) ? $escuela['localidad'] : 'Ciudad de San Luis',
);

// Progreso de entregas
$totalBicis = max($stats['total_bicicletas'], 1);
$progresoEntregas = round(($stats['entregadas'] / $totalBicis) * 100);

// Procesar acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
              strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    $result = array('success' => false, 'message' => 'Acción no válida');

    if ($action === 'asignar') {
        $biciId = isset($_POST['bici_id']) ? (int)$_POST['bici_id'] : 0;
        $alumnoId = isset($_POST['alumno_id']) ? (int)$_POST['alumno_id'] : 0;
        $dni = isset($_POST['dni']) ? $_POST['dni'] : '';
        $nombre = isset($_POST['nombre']) ? $_POST['nombre'] : '';

        if ($biciId) {
            // Buscar alumno por DNI si no se selecciono de la lista
            if (!$alumnoId && $dni) {
                $alumnoRow = dbFetchOne('SELECT id FROM alumnos WHERE dni = ?', array($dni));
                if ($alumnoRow) $alumnoId = (int)$alumnoRow['id'];
            }
            $datos = array('estado' => 'entregada', 'fecha_entrega' => date('Y-m-d H:i:s'));
            if ($alumnoId) $datos['alumno_id'] = $alumnoId;
            if (dbUpdate('bicicletas', $datos, 'id = ? AND escuela_id = ?', array($biciId, $escuelaId))) {
                $newStats = getEstadisticasEscuela($escuelaId);
                $result = array(
                    'success' => true,
                    'message' => 'Bicicleta asignada y entregada exitosamente',
                    'action' => 'asignar',
                    'bici_id' => $biciId,
                    'alumno_nombre' => $nombre ? $nombre : 'Alumno asignado',
                    'alumno_dni' => $dni ? $dni : '',
                    'stats' => $newStats
                );
            } else {
                $result = array('success' => false, 'message' => 'No se pudo asignar la bicicleta');
            }
        }
    }

    if ($action === 'reasignar') {
        $biciId = isset($_POST['bici_id']) ? (int)$_POST['bici_id'] : 0;
        if ($biciId) {
            $datos = array('estado' => 'en_escuela', 'alumno_id' => null, 'fecha_entrega' => null);
            if (dbUpdate('bicicletas', $datos, 'id = ? AND escuela_id = ?', array($biciId, $escuelaId))) {
                $newStats = getEstadisticasEscuela($escuelaId);
                $result = array(
                    'success' => true,
                    'message' => 'Bicicleta liberada para reasignacion',
                    'action' => 'reasignar',
                    'bici_id' => $biciId,
                    'stats' => $newStats
                );
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
    redirect('pages/escuela/dashboard.php');
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
        .inline-notif {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1rem;
            border-radius: 8px;
            font-size: 0.8125rem;
            font-weight: 500;
            animation: slideInNotif 0.3s ease;
            margin: 0.5rem 0;
        }
        .inline-notif.success {
            background: rgba(34, 197, 94, 0.12);
            color: #16a34a;
            border: 1px solid rgba(34, 197, 94, 0.25);
        }
        .inline-notif.error {
            background: rgba(239, 68, 68, 0.12);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.25);
        }
        .inline-notif .notif-icon {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            flex-shrink: 0;
        }
        .inline-notif.success .notif-icon { background: #22c55e; color: white; }
        .inline-notif.error .notif-icon { background: #ef4444; color: white; }
        @keyframes slideInNotif {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        tr.row-success { animation: rowFlash 2s ease; }
        @keyframes rowFlash {
            0% { background: rgba(34, 197, 94, 0.25); }
            100% { background: transparent; }
        }
        .notif-row td { padding: 0 !important; border: none !important; }
        .btn-processing { opacity: 0.7; pointer-events: none; }
        .num-updated { animation: numPop 0.5s ease; }
        @keyframes numPop {
            0% { transform: scale(1); }
            50% { transform: scale(1.3); color: #22c55e; }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body class="tubi-bg-pattern" data-base-url="<?= BASE_URL ?>">
    <?php include __DIR__ . '/../../includes/zocalo-header.php'; ?>

    <div class="app-container">
        <!-- Header -->
        <header class="app-header">
            <div class="header-left">
                <span class="header-badge escuela">Escuela</span>
            </div>
            <div class="header-right">
                <button class="btn-icon" onclick="location.reload()" title="Actualizar datos">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                        <path d="M23 4v6h-6M1 20v-6h6"/>
                        <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>
                    </svg>
                </button>
                <button class="btn-icon" id="themeToggle" title="Cambiar tema">
                    <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                    </svg>
                    <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20" style="display:none;">
                        <circle cx="12" cy="12" r="5"/>
                        <path d="M12 1v2m0 18v2M4.22 4.22l1.42 1.42m12.72 12.72l1.42 1.42M1 12h2m18 0h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
                    </svg>
                </button>
                <div class="header-user">
                    <div class="user-avatar-sm escuela"><?= strtoupper(substr($user['nombre'], 0, 1)) ?></div>
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
            <div class="dashboard-escuela">
                <!-- Mensaje de Bienvenida -->
                <div class="welcome-banner" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); padding: 1.5rem 2rem; border-radius: 12px; color: white; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 1rem;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="32" height="32" style="flex-shrink: 0;">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                    <div>
                        <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700;">Bienvenidos Directores de Escuelas</h2>
                        <p style="margin: 0; opacity: 0.9; font-size: 0.95rem;">Panel de gestión de entregas y asignaciones</p>
                    </div>
                </div>

                <!-- Info de la Escuela -->
                <div class="card">
                    <div class="escuela-header-card">
                        <div class="escuela-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="28" height="28">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                                <polyline points="9 22 9 12 15 12 15 22"/>
                            </svg>
                        </div>
                        <div class="escuela-info">
                            <strong><?= e($escuelaData['nombre']) ?></strong>
                            <span>CUE: <?= e($escuelaData['cue']) ?> · <?= e($escuelaData['localidad']) ?></span>
                        </div>
                        <span class="badge badge-online">ACTIVA</span>
                    </div>
                </div>

                <!-- Métricas -->
                <div class="stats-grid-4">
                    <div class="stat-card">
                        <div class="stat-icon azul">🚲</div>
                        <div class="stat-info">
                            <span class="stat-value"><?= $stats['total_bicicletas'] ?></span>
                            <span class="stat-label">Total Bicicletas</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon verde">✅</div>
                        <div class="stat-info">
                            <span class="stat-value"><?= $stats['entregadas'] ?></span>
                            <span class="stat-label">Entregadas</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon naranja">⏳</div>
                        <div class="stat-info">
                            <span class="stat-value"><?= $stats['pendientes'] ?></span>
                            <span class="stat-label">Pendientes</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon morado">👨‍🎓</div>
                        <div class="stat-info">
                            <span class="stat-value"><?= $stats['total_alumnos'] ?></span>
                            <span class="stat-label">Alumnos</span>
                        </div>
                    </div>
                </div>

                <!-- Progreso de Entregas -->
                <div class="card">
                    <div class="card-header">
                        <h3>Progreso de entregas</h3>
                        <span class="progress-percent"><?= $progresoEntregas ?>%</span>
                    </div>
                    <div class="card-body">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width:<?= $progresoEntregas ?>%"></div>
                        </div>
                        <p class="progress-text"><?= $stats['entregadas'] ?> de <?= $stats['total_bicicletas'] ?> bicicletas entregadas</p>
                    </div>
                </div>

                <!-- Panel de Gestión Escolar -->
                <div class="card">
                    <div class="card-header">
                        <h3>Panel de Gestión Escolar</h3>
                        <div class="header-actions">
                            <button class="btn btn-secondary btn-sm" onclick="exportarExcel()">
                                <span>📊</span> Exportar Excel
                            </button>
                            <button class="btn btn-success btn-sm" onclick="generarPlanilla()">
                                <span>📋</span> Generar Planilla
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="search-bar">
                            <input type="text" class="form-control" placeholder="🔍 Buscar por QR, Estado, Alumno o DNI..." id="searchInput" onkeyup="filtrarTabla()">
                            <button class="btn btn-primary">Buscar</button>
                        </div>
                        <div class="table-wrapper">
                            <table class="table" id="tablaGestion">
                                <thead>
                                    <tr>
                                        <th>QR</th>
                                        <th>ALUMNO / DNI</th>
                                        <th>ESTADO</th>
                                        <th>LINK</th>
                                        <th>ACCIÓN</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bicicletas as $bici): ?>
                                    <tr data-bici-id="<?= $bici['id'] ?>">
                                        <td><strong><?= e($bici['codigo']) ?></strong></td>
                                        <td class="col-alumno">
                                            <?php if ($bici['alumno']): ?>
                                                <span>👤 <?= e($bici['alumno']['nombre']) ?></span><br>
                                                <small class="text-muted">DNI: <?= e($bici['alumno']['dni']) ?></small>
                                            <?php else: ?>
                                                <em class="text-muted">Sin asignar</em>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($bici['estado'] === 'entregada'): ?>
                                                <span class="badge badge-success">Entregada</span>
                                            <?php else: ?>
                                                <span class="badge badge-info">En Escuela</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-secondary btn-sm" onclick="verFicha('<?= e($bici['codigo']) ?>')">
                                                Ficha
                                            </button>
                                        </td>
                                        <td>
                                            <?php if ($bici['estado'] === 'entregada'): ?>
                                                <form method="POST" style="display:inline">
                                                    <input type="hidden" name="action" value="reasignar">
                                                    <input type="hidden" name="bici_id" value="<?= $bici['id'] ?>">
                                                    <button type="submit" class="btn btn-warning btn-sm">↺ Reasignar</button>
                                                </form>
                                            <?php else: ?>
                                                <button class="btn btn-primary btn-sm" onclick="mostrarModalAsignar(<?= $bici['id'] ?>, '<?= e($bici['codigo']) ?>')">
                                                    + Asignar
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Asignar -->
    <div id="modalAsignar" class="modal" style="display:none">
        <div class="modal-backdrop" onclick="cerrarModal()"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3>Asignar Bicicleta</h3>
                <button onclick="cerrarModal()" class="modal-close">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="asignar">
                <input type="hidden" name="bici_id" id="asignarBiciId">
                <div class="modal-body">
                    <p>Bicicleta: <strong id="asignarBiciCodigo"></strong></p>
                    <div class="form-group">
                        <label>DNI del Alumno *</label>
                        <input type="text" class="form-control" name="dni" placeholder="Ingrese DNI" required>
                    </div>
                    <div class="form-group">
                        <label>Nombre del Alumno *</label>
                        <input type="text" class="form-control" name="nombre" placeholder="Nombre completo" required>
                    </div>
                    <div class="form-group">
                        <label>Seleccionar de la lista (opcional)</label>
                        <select class="form-control" name="alumno_id">
                            <option value="">-- Escribir datos manualmente --</option>
                            <?php foreach ($alumnos as $alumno): ?>
                            <option value="<?= $alumno['id'] ?>"><?= e($alumno['nombre']) ?> - DNI: <?= e($alumno['dni']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="cerrarModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Asignar Bicicleta</button>
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
    </style>

    <script>
    // === Theme Toggle ===
    var themeToggle = document.getElementById('themeToggle');
    var moonIcon = themeToggle.querySelector('.icon-moon');
    var sunIcon = themeToggle.querySelector('.icon-sun');

    var savedTheme = localStorage.getItem('tubi-theme') || 'light';
    if (savedTheme === 'dark') {
        document.body.setAttribute('data-theme', 'dark');
        moonIcon.style.display = 'none';
        sunIcon.style.display = 'block';
    } else {
        document.body.removeAttribute('data-theme');
        moonIcon.style.display = 'block';
        sunIcon.style.display = 'none';
    }

    themeToggle.addEventListener('click', function() {
        var isDark = document.body.getAttribute('data-theme') === 'dark';
        if (isDark) {
            document.body.removeAttribute('data-theme');
            moonIcon.style.display = 'block';
            sunIcon.style.display = 'none';
            localStorage.setItem('tubi-theme', 'light');
        } else {
            document.body.setAttribute('data-theme', 'dark');
            moonIcon.style.display = 'none';
            sunIcon.style.display = 'block';
            localStorage.setItem('tubi-theme', 'dark');
        }
    });

    // === Funciones de tabla ===
    function filtrarTabla() {
        var searchEl = document.getElementById('searchInput');
        var filter = searchEl ? searchEl.value.toLowerCase() : '';
        var rows = document.querySelectorAll('#tablaGestion tbody tr');
        for (var i = 0; i < rows.length; i++) {
            if (rows[i].className.indexOf('notif-row') >= 0) continue;
            rows[i].style.display = rows[i].textContent.toLowerCase().indexOf(filter) >= 0 ? '' : 'none';
        }
    }

    function exportarExcel() {
        var table = document.getElementById('tablaGestion');
        if (!table) return;
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
        link.download = 'tubi_escuela_' + new Date().toISOString().slice(0,10) + '.csv';
        link.click();
    }

    function generarPlanilla() {
        alert('Generando planilla de entregas...\nDocumento PDF listo para imprimir.');
    }

    function verFicha(qr) {
        alert('Ficha de Bicicleta: ' + qr + '\n\nEscuela: <?php echo e($escuelaData['nombre']); ?>\nFecha: ' + new Date().toLocaleDateString());
    }

    function mostrarModalAsignar(biciId, biciCodigo) {
        document.getElementById('asignarBiciId').value = biciId;
        document.getElementById('asignarBiciCodigo').textContent = biciCodigo;
        document.getElementById('modalAsignar').style.display = 'flex';
    }

    function cerrarModal() {
        document.getElementById('modalAsignar').style.display = 'none';
    }

    // === SISTEMA DE CONFIRMACION ===
    var _confirmResolve = null;

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

    document.getElementById('confirmBtn').addEventListener('click', function() {
        document.getElementById('modalConfirm').style.display = 'none';
        if (_confirmResolve) { _confirmResolve(true); _confirmResolve = null; }
    });

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

    // === SISTEMA DE ACCIONES EN TIEMPO REAL ===

    function getEscuelaConfirmMessage(action, biciId, formEl) {
        if (action === 'asignar') {
            var nombreInput = formEl.querySelector('[name="nombre"]');
            var nombre = nombreInput ? nombreInput.value : 'alumno seleccionado';
            var codeEl = document.getElementById('asignarBiciCodigo');
            var code = codeEl ? codeEl.textContent : '';
            return { title: 'Confirmar Asignacion', msg: 'Asignar ' + code + ' al alumno ' + nombre + '?', icon: '🎓' };
        }
        if (action === 'reasignar') {
            var row = document.querySelector('tr[data-bici-id="' + biciId + '"]');
            var codeEl2 = row ? row.querySelector('td:first-child strong') : null;
            var code2 = codeEl2 ? codeEl2.textContent : '';
            return { title: 'Confirmar Reasignacion', msg: 'Liberar bicicleta ' + code2 + ' para reasignacion?', icon: '↺' };
        }
        return null;
    }

    function enviarAccionEscuela(form) {
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

        var xhr = new XMLHttpRequest();
        xhr.open('POST', window.location.href, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    try {
                        var data = JSON.parse(xhr.responseText);
                        if (data.success) {
                            showToast(data.message);
                            if (action === 'asignar') {
                                cerrarModal();
                                handleAsignarSuccess(biciId, data);
                            } else if (action === 'reasignar') {
                                handleReasignarSuccess(biciId, data);
                            }
                            if (data.stats) updateEscuelaStats(data.stats);
                        } else {
                            var row = null;
                            var parent = form.parentNode;
                            while (parent) {
                                if (parent.nodeName === 'TR') { row = parent; break; }
                                parent = parent.parentNode;
                            }
                            if (row) showRowNotif(row, 'error', data.message);
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

    // Interceptar TODOS los form submits con confirmacion
    document.addEventListener('submit', function(e) {
        var form = e.target;
        var actionInput = form.querySelector('[name="action"]');
        if (!actionInput) return;

        e.preventDefault();
        var action = actionInput.value;
        var biciIdInput = form.querySelector('[name="bici_id"]');
        var biciId = biciIdInput ? biciIdInput.value : '';

        var confirmInfo = getEscuelaConfirmMessage(action, biciId, form);
        if (confirmInfo) {
            mostrarConfirm(confirmInfo.title, confirmInfo.msg, confirmInfo.icon, function(confirmed) {
                if (confirmed) enviarAccionEscuela(form);
            });
        } else {
            enviarAccionEscuela(form);
        }
    });

    function restoreBtn(btn) {
        if (btn) {
            btn.className = btn.className.replace(' btn-processing', '');
            btn.innerHTML = btn.getAttribute('data-orig') || 'Accion';
        }
    }

    // === Handlers por accion ===

    function handleAsignarSuccess(biciId, data) {
        var row = document.querySelector('tr[data-bici-id="' + biciId + '"]');
        if (!row) return;

        var badge = row.querySelector('.badge');
        badge.className = 'badge badge-success';
        badge.textContent = 'Entregada';

        var alumnoCell = row.querySelector('.col-alumno');
        if (alumnoCell && data.alumno_nombre) {
            alumnoCell.innerHTML = '<span>👤 ' + data.alumno_nombre + '</span><br><small class="text-muted">DNI: ' + data.alumno_dni + '</small>';
        }

        var actionCell = row.querySelector('td:last-child');
        actionCell.innerHTML = '<form method="POST" style="display:inline"><input type="hidden" name="action" value="reasignar"><input type="hidden" name="bici_id" value="' + biciId + '"><button type="submit" class="btn btn-warning btn-sm">↺ Reasignar</button></form>';

        row.className += ' row-success';
        setTimeout(function() { row.className = row.className.replace(' row-success', ''); }, 2100);
        showRowNotif(row, 'success', '✓ ' + data.message);
    }

    function handleReasignarSuccess(biciId, data) {
        var row = document.querySelector('tr[data-bici-id="' + biciId + '"]');
        if (!row) return;

        var badge = row.querySelector('.badge');
        badge.className = 'badge badge-info';
        badge.textContent = 'En Escuela';

        var alumnoCell = row.querySelector('.col-alumno');
        if (alumnoCell) {
            alumnoCell.innerHTML = '<em class="text-muted">Sin asignar</em>';
        }

        var codeEl = row.querySelector('td:first-child strong');
        var code = codeEl ? codeEl.textContent : '';
        var actionCell = row.querySelector('td:last-child');
        actionCell.innerHTML = '<button class="btn btn-primary btn-sm" onclick="mostrarModalAsignar(' + biciId + ', \'' + code + '\')">+ Asignar</button>';

        row.className += ' row-success';
        setTimeout(function() { row.className = row.className.replace(' row-success', ''); }, 2100);
        showRowNotif(row, 'success', '✓ ' + data.message);
    }

    // === Notificaciones inline ===

    function showRowNotif(row, type, message) {
        var bId = row.getAttribute('data-bici-id');
        var prev = row.parentNode.querySelector('.notif-row[data-for="' + bId + '"]');
        if (prev) prev.parentNode.removeChild(prev);
        var notifRow = document.createElement('tr');
        notifRow.className = 'notif-row';
        notifRow.setAttribute('data-for', bId);
        var colCount = row.querySelectorAll('td').length;
        var icon = type === 'success' ? '✓' : '✕';
        notifRow.innerHTML = '<td colspan="' + colCount + '"><div class="inline-notif ' + type + '"><span class="notif-icon">' + icon + '</span><span>' + message + '</span></div></td>';
        row.parentNode.insertBefore(notifRow, row.nextSibling);
        setTimeout(function() { notifRow.style.transition = 'opacity 0.5s'; notifRow.style.opacity = '0'; setTimeout(function() { if (notifRow.parentNode) notifRow.parentNode.removeChild(notifRow); }, 500); }, 4000);
    }

    // === Actualizar stats en tiempo real ===

    function updateEscuelaStats(stats) {
        var statVals = document.querySelectorAll('.stat-value');
        var keys = ['total_bicicletas', 'entregadas', 'pendientes', 'total_alumnos'];
        for (var i = 0; i < statVals.length; i++) {
            if (i < keys.length && stats[keys[i]] !== undefined) {
                var old = parseInt(statVals[i].textContent) || 0;
                var newVal = stats[keys[i]];
                if (old !== newVal) {
                    statVals[i].textContent = newVal;
                    statVals[i].className += ' num-updated';
                    (function(el) {
                        setTimeout(function() { el.className = el.className.replace(' num-updated', ''); }, 600);
                    })(statVals[i]);
                }
            }
        }
        // Actualizar barra de progreso
        var progressBar = document.querySelector('.progress-fill');
        if (progressBar && stats.total_bicicletas > 0) {
            var pct = Math.round((stats.entregadas / stats.total_bicicletas) * 100);
            progressBar.style.width = pct + '%';
            var pctLabel = document.querySelector('.progress-percent');
            if (pctLabel) pctLabel.textContent = pct + '%';
            var pctText = document.querySelector('.progress-text');
            if (pctText) pctText.textContent = stats.entregadas + ' de ' + stats.total_bicicletas + ' bicicletas entregadas';
        }
    }

    // Auto-refresh cada 90 segundos
    setTimeout(function() { location.reload(); }, 90000);
    </script>

    <!-- Tutorial -->
    <?php include __DIR__ . '/../../includes/tutorial.php'; ?>

    <script src="<?= BASE_URL ?>assets/js/toast.js"></script>

    <?php include __DIR__ . '/../../includes/zocalo-footer.php'; ?>
</body>
</html>
