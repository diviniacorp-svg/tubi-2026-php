<?php
/**
 * TUBI 2026 - Dashboard Proveedor
 * Flujo de trabajo completo con datos en tiempo real
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/data.php';

if (!isLoggedIn() || !hasRole('proveedor')) {
    redirect('login.php?role=proveedor');
}

$user = getCurrentUser();
$pageTitle = 'Panel Proveedor';

// Obtener datos en tiempo real
$proveedorId = $user['id'] ?? 1;
$stats = getEstadisticasProveedor($proveedorId);
$bicicletas = getBicicletasParaProveedor(15);
$escuelas = getEscuelas();

// Datos del proveedor
$proveedorData = [
    'nombre' => $user['nombre'] ?? 'Logística San Luis S.A.',
    'cuit' => $user['cuit'] ?? '30-12345678-9',
    'localidad' => 'Villa Mercedes, San Luis',
];

// Procesar acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'registrar_bici') {
        $numero = $_POST['numero'] ?? '';
        $serie = $_POST['serie'] ?? '';

        if ($numero && $serie) {
            $newId = addBicicleta([
                'serie' => $serie,
                'rodado' => 26,
                'color' => 'Azul',
                'estado' => 'deposito',
                'proveedor_id' => $proveedorId,
            ]);
            setFlash('success', 'Bicicleta registrada exitosamente: TUBI-2026-' . str_pad($newId, 5, '0', STR_PAD_LEFT));
        }
        redirect('pages/proveedor/dashboard.php');
    }

    if ($action === 'armar') {
        $biciId = $_POST['bici_id'] ?? 0;
        if ($biciId) {
            cambiarEstadoBicicleta($biciId, 'armada');
            setFlash('success', 'Bicicleta marcada como ARMADA');
        }
        redirect('pages/proveedor/dashboard.php');
    }

    if ($action === 'suministrar') {
        $biciId = $_POST['bici_id'] ?? 0;
        $escuelaId = $_POST['escuela_id'] ?? 0;
        if ($biciId && $escuelaId) {
            cambiarEstadoBicicleta($biciId, 'en_escuela', $escuelaId);
            setFlash('success', 'Bicicleta SUMINISTRADA a escuela exitosamente');
        }
        redirect('pages/proveedor/dashboard.php');
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
    <style>
        /* Panel de Chat Integrado */
        .chat-panel-integrated {
            background: var(--bg-card);
            border: 2px solid var(--border-color);
            border-radius: 16px;
            margin: 2rem 1rem;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
        }

        .chat-panel-header {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            color: white;
            padding: 1.5rem 2rem;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
        }

        .chat-panel-title {
            display: flex;
            align-items: center;
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .chat-panel-subtitle {
            font-size: 0.875rem;
            opacity: 0.9;
            font-weight: 400;
        }

        .chat-panel-body {
            height: 500px;
            overflow-y: auto;
            background: var(--bg-dark);
        }

        .chat-messages-panel {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            min-height: 100%;
        }

        .chat-panel-footer {
            background: var(--bg-card);
            padding: 1.5rem 2rem;
            border-top: 2px solid var(--border-color);
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .chat-input-panel {
            flex: 1;
            padding: 0.75rem 1rem;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            background: var(--bg-dark);
            color: var(--text-primary);
            font-size: 0.95rem;
            transition: all var(--transition);
        }

        .chat-input-panel:focus {
            outline: none;
            border-color: #06b6d4;
            box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.1);
        }

        .chat-send-panel {
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all var(--transition);
            min-width: 50px;
        }

        .chat-send-panel:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(6, 182, 212, 0.4);
        }

        .chat-send-panel:active {
            transform: translateY(0);
        }

        /* Mensajes del chat */
        .chat-message {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            max-width: 85%;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .chat-message.user {
            align-self: flex-end;
        }

        .chat-message.assistant {
            align-self: flex-start;
        }

        .chat-message-content {
            padding: 0.875rem 1.25rem;
            border-radius: 12px;
            line-height: 1.5;
            word-wrap: break-word;
        }

        .chat-message.user .chat-message-content {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            color: white;
            border-bottom-right-radius: 4px;
        }

        .chat-message.assistant .chat-message-content {
            background: var(--bg-card);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
            border-bottom-left-radius: 4px;
        }

        .chat-message-time {
            font-size: 0.75rem;
            color: var(--text-muted);
            padding: 0 0.5rem;
        }

        .chat-loading {
            display: flex;
            gap: 0.5rem;
            padding: 1rem;
        }

        .chat-loading-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #06b6d4;
            animation: bounce 1.4s infinite ease-in-out;
        }

        .chat-loading-dot:nth-child(1) {
            animation-delay: -0.32s;
        }

        .chat-loading-dot:nth-child(2) {
            animation-delay: -0.16s;
        }

        @keyframes bounce {
            0%, 80%, 100% {
                transform: scale(0);
            }
            40% {
                transform: scale(1);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .chat-panel-body {
                height: 400px;
            }

            .chat-message {
                max-width: 95%;
            }
        }
    </style>
</head>
<body class="dark-theme tubi-bg-pattern" data-base-url="<?= BASE_URL ?>">
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
                <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?>">
                    <?= e($flash['message']) ?>
                </div>
                <?php endif; ?>

                <!-- Mensaje de Bienvenida -->
                <div class="welcome-banner" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); padding: 1.5rem 2rem; border-radius: 12px; color: white; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 1rem;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="32" height="32" style="flex-shrink: 0;">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    <div>
                        <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700;">¡Bienvenido, Proveedor!</h2>
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
                                            <?php
                                            $estadoClass = [
                                                'deposito' => 'badge-secondary',
                                                'armada' => 'badge-warning',
                                                'en_escuela' => 'badge-info',
                                                'entregada' => 'badge-success',
                                            ];
                                            $estadoLabel = [
                                                'deposito' => 'En Depósito',
                                                'armada' => 'Armada',
                                                'en_escuela' => 'En Escuela',
                                                'entregada' => 'Entregada',
                                            ];
                                            ?>
                                            <span class="badge <?= $estadoClass[$bici['estado']] ?? 'badge-secondary' ?>">
                                                <?= $estadoLabel[$bici['estado']] ?? $bici['estado'] ?>
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
                                            $optimizaciones = [
                                                'deposito' => [
                                                    'icon' => '⚡',
                                                    'texto' => 'Armar en lote para ahorrar tiempo',
                                                    'color' => '#f59e0b'
                                                ],
                                                'armada' => [
                                                    'icon' => '📦',
                                                    'texto' => 'Agrupar envíos por zona geográfica',
                                                    'color' => '#06b6d4'
                                                ],
                                                'en_escuela' => [
                                                    'icon' => '✓',
                                                    'texto' => 'Listo para asignación a alumno',
                                                    'color' => '#22c55e'
                                                ],
                                                'entregada' => [
                                                    'icon' => '🎯',
                                                    'texto' => 'Proceso completado exitosamente',
                                                    'color' => '#10b981'
                                                ],
                                            ];
                                            $opt = $optimizaciones[$bici['estado']] ?? ['icon' => 'ℹ️', 'texto' => '-', 'color' => '#64748b'];
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

    function mostrarModalSuministrar(biciId, biciCodigo) {
        document.getElementById('suministrarBiciId').value = biciId;
        document.getElementById('suministrarBiciCodigo').textContent = biciCodigo;
        document.getElementById('modalSuministrar').style.display = 'flex';
    }

    function cerrarModal() {
        document.getElementById('modalSuministrar').style.display = 'none';
    }

    // Auto-refresh cada 60 segundos para datos en tiempo real
    setTimeout(() => location.reload(), 60000);
    </script>

    <!-- Panel de Chat IA TuBi - Integrado -->
    <div class="chat-panel-integrated">
        <div class="chat-panel-header">
            <div class="chat-panel-title">
                <svg viewBox="0 0 24 24" fill="currentColor" width="24" height="24" style="margin-right: 0.5rem;">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
                <span>🤖 Asistente IA TuBi</span>
            </div>
            <div class="chat-panel-subtitle">Consultas sobre gestión, optimización y procesos del sistema</div>
        </div>
        <div class="chat-panel-body">
            <div class="chat-messages-panel" id="chatMessages">
                <!-- Mensajes se cargan dinámicamente -->
            </div>
        </div>
        <div class="chat-panel-footer">
            <input type="text" class="chat-input-panel" id="chatInput" placeholder="Escribí tu consulta al asistente IA..." autocomplete="off">
            <button class="chat-send-panel" id="chatSend" title="Enviar mensaje">
                <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
                    <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Tutorial -->
    <?php include __DIR__ . '/../../includes/tutorial.php'; ?>

    <script src="<?= BASE_URL ?>assets/js/toast.js"></script>
    <script>
    // Chat IA Integrado - Panel completo
    (function() {
        const chatMessages = document.getElementById('chatMessages');
        const chatInput = document.getElementById('chatInput');
        const chatSend = document.getElementById('chatSend');

        if (!chatMessages || !chatInput || !chatSend) return;

        let conversationHistory = [];
        let welcomeShown = false;

        // Cargar mensaje de bienvenida al cargar la página
        window.addEventListener('load', function() {
            if (!welcomeShown) {
                loadWelcomeMessage();
                welcomeShown = true;
            }
        });

        // Enviar mensaje al hacer click
        chatSend.addEventListener('click', sendMessage);

        // Enviar mensaje con Enter
        chatInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        // Cargar mensaje de bienvenida
        function loadWelcomeMessage() {
            const baseUrl = '<?= BASE_URL ?>';
            fetch(baseUrl + 'api/chat.php?action=welcome')
                .then(response => response.json())
                .then(data => {
                    if (data.content) {
                        addMessage(data.content, 'assistant');
                    }
                })
                .catch(error => {
                    addMessage('¡Hola! Soy el Asistente TuBi. Estoy aquí para ayudarte con consultas sobre:\n\n• Gestión de inventario\n• Procesos de armado y suministro\n• Optimización de flujos de trabajo\n• Estadísticas y reportes\n\n¿En qué puedo ayudarte?', 'assistant');
                });
        }

        // Enviar mensaje
        function sendMessage() {
            const message = chatInput.value.trim();
            if (!message) return;

            // Agregar mensaje del usuario
            addMessage(message, 'user');
            chatInput.value = '';
            chatInput.disabled = true;
            chatSend.disabled = true;

            // Mostrar indicador de escritura
            const loadingDiv = showLoading();

            const baseUrl = '<?= BASE_URL ?>';
            fetch(baseUrl + 'api/chat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    message: message,
                    history: conversationHistory.slice(-10)
                })
            })
            .then(response => response.json())
            .then(data => {
                removeLoading(loadingDiv);
                chatInput.disabled = false;
                chatSend.disabled = false;
                chatInput.focus();

                if (data.content) {
                    addMessage(data.content, 'assistant');
                    conversationHistory.push({ role: 'user', content: message });
                    conversationHistory.push({ role: 'assistant', content: data.content });
                } else if (data.error) {
                    addMessage('Disculpá, no pude procesar tu mensaje. ¿Podés reformularlo?', 'assistant');
                }
            })
            .catch(error => {
                removeLoading(loadingDiv);
                chatInput.disabled = false;
                chatSend.disabled = false;
                chatInput.focus();
                addMessage('Error de conexión. Por favor, intentá de nuevo.', 'assistant');
            });
        }

        // Agregar mensaje al chat
        function addMessage(content, role) {
            const messageDiv = document.createElement('div');
            messageDiv.className = 'chat-message ' + role;

            const contentDiv = document.createElement('div');
            contentDiv.className = 'chat-message-content';
            contentDiv.innerHTML = formatMessage(content);

            const timeDiv = document.createElement('div');
            timeDiv.className = 'chat-message-time';
            const now = new Date();
            timeDiv.textContent = now.getHours().toString().padStart(2, '0') + ':' +
                                 now.getMinutes().toString().padStart(2, '0');

            messageDiv.appendChild(contentDiv);
            messageDiv.appendChild(timeDiv);

            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Formatear mensaje
        function formatMessage(text) {
            text = text.replace(/\n/g, '<br>');
            text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
            text = text.replace(/• /g, '&bull; ');
            text = text.replace(/- /g, '&bull; ');
            return text;
        }

        // Mostrar indicador de carga
        function showLoading() {
            const loadingDiv = document.createElement('div');
            loadingDiv.className = 'chat-message assistant';
            loadingDiv.innerHTML = `
                <div class="chat-message-content">
                    <div class="chat-loading">
                        <div class="chat-loading-dot"></div>
                        <div class="chat-loading-dot"></div>
                        <div class="chat-loading-dot"></div>
                    </div>
                </div>
            `;
            chatMessages.appendChild(loadingDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
            return loadingDiv;
        }

        // Eliminar indicador de carga
        function removeLoading(loadingDiv) {
            if (loadingDiv && loadingDiv.parentNode) {
                loadingDiv.remove();
            }
        }
    })();
    </script>
</body>
</html>
