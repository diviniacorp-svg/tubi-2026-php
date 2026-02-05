<?php
/**
 * TUBI 2026 - Dashboard Escuela
 * Gestión de entregas con datos en tiempo real
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/data.php';

if (!isLoggedIn() || !hasRole('escuela')) {
    redirect('login.php?role=escuela');
}

$user = getCurrentUser();
$pageTitle = 'Panel Escuela';

// Obtener datos en tiempo real
$escuelaId = $user['id'] ?? 1;
$escuela = getEscuela($escuelaId);
$stats = getEstadisticasEscuela($escuelaId);
$bicicletas = getBicicletasParaEscuela($escuelaId, 15);
$alumnos = getAlumnos(['escuela_id' => $escuelaId]);

// Datos de la escuela
$escuelaData = [
    'nombre' => $escuela['nombre'] ?? $user['nombre'] ?? 'Escuela N° 123 "Gral. San Martín"',
    'cue' => $escuela['cue'] ?? $user['cue'] ?? '740001234',
    'localidad' => $escuela['localidad'] ?? 'Ciudad de San Luis',
];

// Progreso de entregas
$totalBicis = max($stats['total_bicicletas'], 1);
$progresoEntregas = round(($stats['entregadas'] / $totalBicis) * 100);

// Procesar acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'asignar') {
        $biciId = $_POST['bici_id'] ?? 0;
        $alumnoId = $_POST['alumno_id'] ?? 0;
        $dni = $_POST['dni'] ?? '';
        $nombre = $_POST['nombre'] ?? '';

        if ($biciId) {
            // Marcar bicicleta como entregada y asignar alumno
            $data = &$_SESSION['tubi_data'];
            foreach ($data['bicicletas'] as &$b) {
                if ($b['id'] == $biciId) {
                    $b['estado'] = 'entregada';
                    $b['alumno_id'] = $alumnoId ?: rand(1, 50);
                    $b['fecha_entrega'] = date('Y-m-d H:i:s');
                    break;
                }
            }
            setFlash('success', 'Bicicleta asignada y entregada exitosamente');
        }
        redirect('pages/escuela/dashboard.php');
    }

    if ($action === 'reasignar') {
        $biciId = $_POST['bici_id'] ?? 0;
        if ($biciId) {
            $data = &$_SESSION['tubi_data'];
            foreach ($data['bicicletas'] as &$b) {
                if ($b['id'] == $biciId) {
                    $b['estado'] = 'en_escuela';
                    $b['alumno_id'] = null;
                    $b['fecha_entrega'] = null;
                    break;
                }
            }
            setFlash('success', 'Bicicleta liberada para reasignación');
        }
        redirect('pages/escuela/dashboard.php');
    }
}

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> - TuBi 2026</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🚲</text></svg>">
</head>
<body class="dark-theme tubi-bg-pattern" data-base-url="<?= BASE_URL ?>">
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
                <span class="header-badge escuela">Escuela</span>
            </div>
            <div class="header-right">
                <button class="btn-icon" onclick="location.reload()" title="Actualizar datos">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                        <path d="M23 4v6h-6M1 20v-6h6"/>
                        <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>
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
                <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?>">
                    <?= e($flash['message']) ?>
                </div>
                <?php endif; ?>

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
                                    <tr>
                                        <td><strong><?= e($bici['codigo']) ?></strong></td>
                                        <td>
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
                                                <form method="POST" style="display:inline" onsubmit="return confirm('¿Está seguro de reasignar esta bicicleta?')">
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

    <script>
    function filtrarTabla() {
        const filter = document.getElementById('searchInput').value.toLowerCase();
        document.querySelectorAll('#tablaGestion tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
        });
    }

    function exportarExcel() {
        alert('📊 Exportando datos a Excel...\nEl archivo se descargará en breve.');
    }

    function generarPlanilla() {
        alert('📋 Generando planilla de entregas...\nDocumento PDF listo para imprimir.');
    }

    function verFicha(qr) {
        alert('📋 Ficha de Bicicleta: ' + qr + '\n\nEscuela: <?= e($escuelaData['nombre']) ?>\nFecha: ' + new Date().toLocaleDateString());
    }

    function mostrarModalAsignar(biciId, biciCodigo) {
        document.getElementById('asignarBiciId').value = biciId;
        document.getElementById('asignarBiciCodigo').textContent = biciCodigo;
        document.getElementById('modalAsignar').style.display = 'flex';
    }

    function cerrarModal() {
        document.getElementById('modalAsignar').style.display = 'none';
    }

    // Auto-refresh cada 60 segundos
    setTimeout(() => location.reload(), 60000);
    </script>

    <!-- Tutorial y Chat -->
    <?php include __DIR__ . '/../../includes/tutorial.php'; ?>

    <!-- Chat TuBi Flotante -->
    <div class="chat-box" id="chatBox">
        <button class="chat-toggle" id="chatToggle" title="Chat TuBi">💬</button>
        <div class="chat-window" id="chatWindow" style="display: none;">
            <div class="chat-header">
                <span>🚲 TuBi Chat</span>
                <button class="chat-close" id="chatClose">×</button>
            </div>
            <div class="chat-messages" id="chatMessages"></div>
            <div class="chat-input-container">
                <input type="text" class="chat-input" id="chatInput" placeholder="Escribí tu mensaje..." autocomplete="off">
                <button class="chat-send" id="chatSend">➤</button>
            </div>
        </div>
    </div>

    <script src="<?= BASE_URL ?>assets/js/toast.js"></script>
    <script src="<?= BASE_URL ?>assets/js/chat.js"></script>
</body>
</html>
