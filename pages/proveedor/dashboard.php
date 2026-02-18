<?php
/**
 * TUBI 2026 - Dashboard Proveedor
 * Flujo de trabajo completo con datos en tiempo real
 */
require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn() || !hasRole('proveedor')) {
    redirect('login.php?role=proveedor');
}

$user = getCurrentUser();
$pageTitle = 'Panel Proveedor';

// Obtener datos en tiempo real (sin filtrar por proveedor para ver todas las bicis)
$proveedorId = null;
$stats = getEstadisticasProveedor($proveedorId);
$bicicletas = getBicicletasParaProveedor(15);
$escuelas = getEscuelas();

// Datos del proveedor
$proveedorData = array(
    'nombre' => isset($user['nombre']) ? $user['nombre'] : 'Logistica San Luis S.A.',
    'cuit' => isset($user['cuit']) ? $user['cuit'] : '30-12345678-9',
    'localidad' => 'Villa Mercedes, San Luis',
);

// Procesar acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
              strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    $result = array('success' => false, 'message' => 'Acción no válida');

    if ($action === 'registrar_bici') {
        $numero = isset($_POST['numero']) ? $_POST['numero'] : '';
        $serie = isset($_POST['serie']) ? $_POST['serie'] : '';
        if ($numero && $serie) {
            $newId = addBicicleta(array(
                'serie' => $serie,
                'rodado' => 26,
                'color' => 'Azul',
                'estado' => 'deposito',
                'proveedor_id' => 1,
            ));
            $codigo = 'TUBI-2026-' . str_pad($newId, 5, '0', STR_PAD_LEFT);
            $newStats = getEstadisticasProveedor($proveedorId);
            $result = array('success' => true, 'message' => "Bicicleta registrada: $codigo", 'action' => 'registrar', 'stats' => $newStats);
        } else {
            $result = array('success' => false, 'message' => 'Completá todos los campos');
        }
    }

    if ($action === 'armar') {
        $biciId = isset($_POST['bici_id']) ? $_POST['bici_id'] : 0;
        if ($biciId && cambiarEstadoBicicleta($biciId, 'armada')) {
            $newStats = getEstadisticasProveedor($proveedorId);
            $result = array(
                'success' => true,
                'message' => 'Bicicleta marcada como ARMADA',
                'action' => 'armar',
                'bici_id' => (int)$biciId,
                'stats' => $newStats
            );
        } else {
            $result = array('success' => false, 'message' => 'No se pudo armar la bicicleta');
        }
    }

    if ($action === 'suministrar') {
        $biciId = isset($_POST['bici_id']) ? $_POST['bici_id'] : 0;
        $escuelaId = isset($_POST['escuela_id']) ? $_POST['escuela_id'] : 0;
        if ($biciId && $escuelaId && cambiarEstadoBicicleta($biciId, 'en_escuela', $escuelaId)) {
            $escuelaNombre = '';
            foreach ($escuelas as $esc) {
                if ($esc['id'] == $escuelaId) {
                    $escuelaNombre = $esc['nombre'];
                    break;
                }
            }
            $newStats = getEstadisticasProveedor($proveedorId);
            $result = array(
                'success' => true,
                'message' => "Bicicleta SUMINISTRADA a $escuelaNombre",
                'action' => 'suministrar',
                'bici_id' => (int)$biciId,
                'escuela_nombre' => $escuelaNombre,
                'stats' => $newStats
            );
        } else {
            $result = array('success' => false, 'message' => 'Seleccioná una escuela válida');
        }
    }

    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    setFlash($result['success'] ? 'success' : 'error', $result['message']);
    redirect('pages/proveedor/dashboard.php');
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
        /* === Inline Notifications (junto a la acción) === */
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

        .inline-notif.success .notif-icon {
            background: #22c55e;
            color: white;
        }

        .inline-notif.error .notif-icon {
            background: #ef4444;
            color: white;
        }

        @keyframes slideInNotif {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Row highlight animation */
        tr.row-success {
            animation: rowFlash 2s ease;
        }

        @keyframes rowFlash {
            0% { background: rgba(34, 197, 94, 0.25); }
            100% { background: transparent; }
        }

        tr.row-error {
            animation: rowFlashError 2s ease;
        }

        @keyframes rowFlashError {
            0% { background: rgba(239, 68, 68, 0.2); }
            100% { background: transparent; }
        }

        /* Number update animation */
        .num-updated {
            animation: numPop 0.5s ease;
        }

        @keyframes numPop {
            0% { transform: scale(1); }
            50% { transform: scale(1.3); color: #22c55e; }
            100% { transform: scale(1); }
        }

        /* Processing button state */
        .btn-processing {
            opacity: 0.7;
            pointer-events: none;
        }

        /* Notification row in table */
        .notif-row td {
            padding: 0 !important;
            border: none !important;
        }
    </style>
</head>
<body class="tubi-bg-pattern" data-base-url="<?= BASE_URL ?>">
    <!-- Zócalo Institucional Superior -->
    <?php include __DIR__ . '/../../includes/zocalo-header.php'; ?>

    <div class="app-container">
        <!-- Header -->
        <header class="app-header">
            <div class="header-left">
                <span class="header-badge proveedor">Proveedor</span>
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
                    <div class="user-avatar-sm proveedor"><?= strtoupper(substr($user['nombre'], 0, 1)) ?></div>
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
            <div class="dashboard-proveedor">
                <!-- Mensaje de Bienvenida -->
                <div class="welcome-banner" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); padding: 1.5rem 2rem; border-radius: 12px; color: white; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 1rem;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="32" height="32" style="flex-shrink: 0;">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    <div>
                        <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700;">Bienvenidos Proveedores</h2>
                        <p style="margin: 0; opacity: 0.9; font-size: 0.95rem;">Panel de gestión de inventario y distribución</p>
                    </div>
                </div>

                <!-- Info del Proveedor -->
                <div class="card">
                    <div class="proveedor-header-card">
                        <div class="proveedor-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="28" height="28">
                                <rect x="1" y="3" width="15" height="13"/>
                                <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                                <circle cx="5.5" cy="18.5" r="2.5"/>
                                <circle cx="18.5" cy="18.5" r="2.5"/>
                            </svg>
                        </div>
                        <div class="proveedor-info">
                            <strong><?= e($proveedorData['nombre']) ?></strong>
                            <span>CUIT: <?= e($proveedorData['cuit']) ?> · <?= e($proveedorData['localidad']) ?></span>
                        </div>
                        <span class="badge badge-online">● ACTIVO</span>
                    </div>
                </div>

                <!-- Flujo de Producción Visual -->
                <div class="card">
                    <div class="card-header">
                        <h3>Flujo de Producción</h3>
                        <span class="badge badge-info">Tiempo real</span>
                    </div>
                    <div class="card-body">
                        <div class="flow-diagram">
                            <div class="flow-step">
                                <div class="flow-icon azul">
                                    <span class="flow-badge"><?= $stats['en_deposito'] ?></span>
                                    <span class="flow-emoji">📦</span>
                                </div>
                                <span class="flow-label">En Depósito</span>
                            </div>
                            <div class="flow-arrow">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
                                    <path d="M5 12h14M12 5l7 7-7 7"/>
                                </svg>
                            </div>
                            <div class="flow-step">
                                <div class="flow-icon naranja">
                                    <span class="flow-badge"><?= $stats['armadas'] ?></span>
                                    <span class="flow-emoji">🔧</span>
                                </div>
                                <span class="flow-label">Armadas</span>
                            </div>
                            <div class="flow-arrow">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
                                    <path d="M5 12h14M12 5l7 7-7 7"/>
                                </svg>
                            </div>
                            <div class="flow-step">
                                <div class="flow-icon morado">
                                    <span class="flow-badge"><?= $stats['suministradas'] ?></span>
                                    <span class="flow-emoji">🚚</span>
                                </div>
                                <span class="flow-label">En Escuelas</span>
                            </div>
                            <div class="flow-arrow">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
                                    <path d="M5 12h14M12 5l7 7-7 7"/>
                                </svg>
                            </div>
                            <div class="flow-step">
                                <div class="flow-icon verde">
                                    <span class="flow-badge"><?= $stats['en_escuelas'] ?></span>
                                    <span class="flow-emoji">✅</span>
                                </div>
                                <span class="flow-label">Entregadas</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Registrador de Bicicleta -->
                <div class="card">
                    <div class="card-header">
                        <h3>Registrar Nueva Bicicleta</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="form-register">
                            <input type="hidden" name="action" value="registrar_bici">
                            <div class="form-grid-2">
                                <div class="form-group">
                                    <label>Número de Bicicleta *</label>
                                    <input type="text" class="form-control" name="numero" placeholder="Ej: 00901" required>
                                </div>
                                <div class="form-group">
                                    <label>Ciclo</label>
                                    <input type="text" class="form-control" value="2026" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Serie n.° *</label>
                                    <input type="text" class="form-control" name="serie" placeholder="Número de serie" required>
                                </div>
                                <div class="form-group">
                                    <label>Proveedor</label>
                                    <select class="form-control" name="proveedor">
                                        <option><?= e($proveedorData['nombre']) ?></option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success">
                                <span>+</span> Registrar Bicicleta
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Métricas de Producción -->
                <div class="stats-grid-4">
                    <div class="stat-card">
                        <div class="stat-icon azul">📦</div>
                        <div class="stat-info">
                            <span class="stat-value"><?= $stats['en_deposito'] ?></span>
                            <span class="stat-label">En Depósito</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon naranja">🔧</div>
                        <div class="stat-info">
                            <span class="stat-value"><?= $stats['armadas'] ?></span>
                            <span class="stat-label">Armadas</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon morado">🚚</div>
                        <div class="stat-info">
                            <span class="stat-value"><?= $stats['suministradas'] ?></span>
                            <span class="stat-label">En Escuelas</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon verde">✅</div>
                        <div class="stat-info">
                            <span class="stat-value"><?= $stats['en_escuelas'] ?></span>
                            <span class="stat-label">Entregadas</span>
                        </div>
                    </div>
                </div>

                <!-- Producción Diaria -->
                <div class="card">
                    <div class="card-header">
                        <h3>Producción</h3>
                    </div>
                    <div class="card-body">
                        <div class="production-grid">
                            <div class="production-item">
                                <span class="prod-icon">⏱️</span>
                                <div class="prod-info">
                                    <span class="prod-value"><?= $stats['armadas_hoy'] ?></span>
                                    <span class="prod-label">Armadas hoy</span>
                                </div>
                            </div>
                            <div class="production-item">
                                <span class="prod-icon">📅</span>
                                <div class="prod-info">
                                    <span class="prod-value"><?= $stats['esta_semana'] ?></span>
                                    <span class="prod-label">Esta semana</span>
                                </div>
                            </div>
                            <div class="production-item">
                                <span class="prod-icon">✅</span>
                                <div class="prod-info">
                                    <span class="prod-value"><?= $stats['promedio_dia'] ?></span>
                                    <span class="prod-label">Promedio/día</span>
                                </div>
                            </div>
                            <div class="production-item">
                                <span class="prod-icon">⚠️</span>
                                <div class="prod-info">
                                    <span class="prod-value"><?= $stats['pendientes'] ?></span>
                                    <span class="prod-label">Pendientes</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel de Gestión -->
                <div class="card">
                    <div class="card-header">
                        <h3>Panel de Gestión</h3>
                        <div class="header-actions">
                            <button class="btn btn-secondary btn-sm" onclick="location.reload()">
                                <span>🔄</span> Actualizar
                            </button>
                            <button class="btn btn-secondary btn-sm" onclick="exportarExcel()">
                                <span>📊</span> Exportar Excel
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="search-bar">
                            <input type="text" class="form-control" placeholder="🔍 Buscar por QR, Estado, Estudiante o DNI..." id="searchInput" onkeyup="filtrarTabla()">
                            <button class="btn btn-primary">Buscar</button>
                        </div>
                        <div class="table-wrapper">
                            <table class="table" id="tablaGestion">
                                <thead>
                                    <tr>
                                        <th>QR</th>
                                        <th>ESTUDIANTE / DNI</th>
                                        <th>ESTADO</th>
                                        <th>ESCUELA</th>
                                        <th>OPTIMIZACIÓN</th>
                                        <th>ACCIÓN</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bicicletas as $bici): ?>
                                    <tr data-bici-id="<?= $bici['id'] ?>">
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
                                            <?php if ($bici['escuela']): ?>
                                                <small class="text-success">📍 <?= e($bici['escuela']['nombre']) ?></small>
                                            <?php else: ?>
                                                <em class="text-muted">-</em>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            // Sugerencias de optimización según estado
                                            $optimizaciones = array(
                                                'deposito' => array(
                                                    'icon' => '⚡',
                                                    'texto' => 'Armar en lote para ahorrar tiempo',
                                                    'color' => '#f59e0b'
                                                ),
                                                'armada' => array(
                                                    'icon' => '📦',
                                                    'texto' => 'Agrupar envíos por zona geográfica',
                                                    'color' => '#06b6d4'
                                                ),
                                                'en_escuela' => array(
                                                    'icon' => '✓',
                                                    'texto' => 'Listo para asignación a alumno',
                                                    'color' => '#22c55e'
                                                ),
                                                'entregada' => array(
                                                    'icon' => '🎯',
                                                    'texto' => 'Proceso completado exitosamente',
                                                    'color' => '#10b981'
                                                ),
                                            );
                                            $opt = isset($optimizaciones[$bici['estado']]) ? $optimizaciones[$bici['estado']] : array('icon' => 'ℹ️', 'texto' => '-', 'color' => '#64748b');
                                            ?>
                                            <div style="display: flex; align-items: center; gap: 0.5rem; cursor: help;" title="Click para más detalles">
                                                <span style="font-size: 1.2rem;"><?= $opt['icon'] ?></span>
                                                <small style="color: <?= $opt['color'] ?>; font-weight: 500; line-height: 1.3;">
                                                    <?= $opt['texto'] ?>
                                                </small>
                                            </div>
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
                                                <span class="text-info">📦 En tránsito</span>
                                            <?php else: ?>
                                                <span class="text-success">✓ Completado</span>
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

    <!-- Modal Suministrar -->
    <div id="modalSuministrar" class="modal" style="display:none">
        <div class="modal-backdrop" onclick="cerrarModal()"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3>Suministrar Bicicleta</h3>
                <button onclick="cerrarModal()" class="modal-close">&times;</button>
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
                            <?php foreach ($escuelas as $escuela): ?>
                            <option value="<?= $escuela['id'] ?>"><?= e($escuela['nombre']) ?> - <?= e($escuela['localidad']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="cerrarModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Confirmar Suministro</button>
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
        link.download = 'tubi_proveedor_' + new Date().toISOString().slice(0,10) + '.csv';
        link.click();
    }

    function mostrarModalSuministrar(biciId, biciCodigo) {
        document.getElementById('suministrarBiciId').value = biciId;
        document.getElementById('suministrarBiciCodigo').textContent = biciCodigo;
        document.getElementById('modalSuministrar').style.display = 'flex';
    }

    function cerrarModal() {
        document.getElementById('modalSuministrar').style.display = 'none';
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

    function getConfirmMessage(action, biciId, formEl) {
        var biciRow = document.querySelector('tr[data-bici-id="' + biciId + '"]');
        var codeEl = biciRow ? biciRow.querySelector('td:first-child strong') : null;
        var code = codeEl ? codeEl.textContent : '';
        if (action === 'armar') return { title: 'Confirmar Armado', msg: 'Confirmar armado de bicicleta ' + code + '?', icon: '🔧' };
        if (action === 'suministrar') {
            var sel = document.querySelector('#modalSuministrar select[name="escuela_id"]');
            var escNombre = (sel && sel.options[sel.selectedIndex]) ? sel.options[sel.selectedIndex].text : '';
            return { title: 'Confirmar Suministro', msg: 'Suministrar ' + code + ' a ' + escNombre + '?', icon: '🚚' };
        }
        if (action === 'registrar_bici') return { title: 'Registrar Bicicleta', msg: 'Registrar nueva bicicleta en el sistema?', icon: '📋' };
        return null;
    }

    function enviarAccion(form) {
        var actionInput = form.querySelector('[name="action"]');
        if (!actionInput) return;
        var action = actionInput.value;
        var biciIdInput = form.querySelector('[name="bici_id"]');
        var biciId = biciIdInput ? biciIdInput.value : '';

        var btn = form.querySelector('button[type="submit"]');
        if (btn) {
            btn.className += ' btn-processing';
            btn.setAttribute('data-orig', btn.innerHTML);
            btn.innerHTML = '⏳ Procesando...';
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
                            if (action === 'armar') {
                                handleArmarSuccess(biciId, data);
                            } else if (action === 'suministrar') {
                                cerrarModal();
                                handleSuministrarSuccess(biciId, data);
                            } else if (action === 'registrar_bici') {
                                handleRegistrarSuccess(form, data);
                            }
                            // Actualizar contadores en tiempo real
                            if (data.stats) {
                                updateFlowDiagram(data.stats);
                                updateStatCards(data.stats);
                            }
                        } else {
                            var row = null;
                            var parent = form.parentNode;
                            while (parent) {
                                if (parent.nodeName === 'TR') { row = parent; break; }
                                parent = parent.parentNode;
                            }
                            if (row) showRowNotif(row, 'error', data.message);
                            else showElementNotif(form, 'error', data.message);
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

        var confirmInfo = getConfirmMessage(action, biciId, form);
        if (confirmInfo) {
            mostrarConfirm(confirmInfo.title, confirmInfo.msg, confirmInfo.icon, function(confirmed) {
                if (confirmed) enviarAccion(form);
            });
        } else {
            enviarAccion(form);
        }
    });

    function restoreBtn(btn) {
        if (btn) {
            btn.className = btn.className.replace(' btn-processing', '');
            btn.innerHTML = btn.getAttribute('data-orig') || 'Accion';
        }
    }

    // === Handlers por accion ===

    function handleArmarSuccess(biciId, data) {
        var row = document.querySelector('tr[data-bici-id="' + biciId + '"]');
        if (!row) return;
        row.setAttribute('data-estado', 'armada');
        var badge = row.querySelector('.badge');
        badge.className = 'badge badge-warning';
        badge.textContent = 'Armada';
        var code = row.querySelector('td:first-child strong').textContent;
        row.querySelector('td:last-child').innerHTML = '<button class="btn btn-primary btn-sm" onclick="mostrarModalSuministrar(' + biciId + ', \'' + code + '\')">🚚 Suministrar</button>';
        var cells = row.querySelectorAll('td');
        if (cells[4]) {
            cells[4].innerHTML = '<div style="display:flex;align-items:center;gap:0.5rem"><span style="font-size:1.2rem">📦</span><small style="color:#06b6d4;font-weight:500">Agrupar envios por zona</small></div>';
        }
        row.className += ' row-success';
        setTimeout(function() { row.className = row.className.replace(' row-success', ''); }, 2100);
        showRowNotif(row, 'success', '✓ ' + data.message);
    }

    function handleSuministrarSuccess(biciId, data) {
        var row = document.querySelector('tr[data-bici-id="' + biciId + '"]');
        if (!row) return;
        row.setAttribute('data-estado', 'en_escuela');
        var badge = row.querySelector('.badge');
        badge.className = 'badge badge-info';
        badge.textContent = 'En Escuela';
        var cells = row.querySelectorAll('td');
        if (cells[3]) {
            cells[3].innerHTML = '<small class="text-success">📍 ' + data.escuela_nombre + '</small>';
        }
        row.querySelector('td:last-child').innerHTML = '<span class="text-info">📦 En transito</span>';
        if (cells[4]) {
            cells[4].innerHTML = '<div style="display:flex;align-items:center;gap:0.5rem"><span style="font-size:1.2rem">✓</span><small style="color:#22c55e;font-weight:500">Listo para asignacion</small></div>';
        }
        row.className += ' row-success';
        setTimeout(function() { row.className = row.className.replace(' row-success', ''); }, 2100);
        showRowNotif(row, 'success', '✓ ' + data.message);
    }

    function handleRegistrarSuccess(form, data) {
        showElementNotif(form, 'success', '✓ ' + data.message);
        form.reset();
        setTimeout(function() { location.reload(); }, 2000);
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

    function showElementNotif(element, type, message) {
        var prev = element.parentNode.querySelector('.inline-notif');
        if (prev) prev.parentNode.removeChild(prev);
        var notif = document.createElement('div');
        notif.className = 'inline-notif ' + type;
        var icon = type === 'success' ? '✓' : '✕';
        notif.innerHTML = '<span class="notif-icon">' + icon + '</span><span>' + message + '</span>';
        element.parentNode.insertBefore(notif, element.nextSibling);
        setTimeout(function() { notif.style.transition = 'opacity 0.5s'; notif.style.opacity = '0'; setTimeout(function() { if (notif.parentNode) notif.parentNode.removeChild(notif); }, 500); }, 4000);
    }

    // === Actualizar contadores en tiempo real ===

    function updateFlowDiagram(stats) {
        var badges = document.querySelectorAll('.flow-badge');
        if (badges.length >= 4) {
            animateNum(badges[0], stats.en_deposito);
            animateNum(badges[1], stats.armadas);
            animateNum(badges[2], stats.suministradas);
            animateNum(badges[3], stats.en_escuelas);
        }
    }

    function updateStatCards(stats) {
        var statVals = document.querySelectorAll('.stats-grid-4 .stat-value');
        if (statVals.length >= 4) {
            animateNum(statVals[0], stats.en_deposito);
            animateNum(statVals[1], stats.armadas);
            animateNum(statVals[2], stats.suministradas);
            animateNum(statVals[3], stats.en_escuelas);
        }
        var prodVals = document.querySelectorAll('.prod-value');
        if (prodVals.length >= 4) {
            animateNum(prodVals[0], stats.armadas_hoy);
            animateNum(prodVals[1], stats.esta_semana);
            animateNum(prodVals[2], stats.promedio_dia);
            animateNum(prodVals[3], stats.pendientes);
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

    // Auto-refresh cada 90 segundos
    setTimeout(function() { location.reload(); }, 90000);
    </script>

    <!-- Tutorial -->
    <?php include __DIR__ . '/../../includes/tutorial.php'; ?>

    <script src="<?= BASE_URL ?>assets/js/toast.js"></script>

    <?php include __DIR__ . '/../../includes/zocalo-footer.php'; ?>
</body>
</html>
