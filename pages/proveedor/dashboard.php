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

                <!-- Banner de Instrucciones para Pruebas -->
                <div class="card" style="border-left: 4px solid #2EC4C6; background: linear-gradient(135deg, rgba(46, 196, 198, 0.1) 0%, rgba(53, 67, 147, 0.05) 100%);">
                    <div class="card-header">
                        <h3 style="display: flex; align-items: center; gap: 0.5rem;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                                <line x1="16" y1="13" x2="8" y2="13"/>
                                <line x1="16" y1="17" x2="8" y2="17"/>
                                <polyline points="10 9 9 9 8 9"/>
                            </svg>
                            Instrucciones para Pruebas del Sistema
                        </h3>
                        <span class="badge" style="background: #2EC4C6; color: white;">Demo</span>
                    </div>
                    <div class="card-body">
                        <div style="display: grid; gap: 1rem;">
                            <div style="display: flex; gap: 1rem; align-items: flex-start;">
                                <span style="flex-shrink: 0; width: 32px; height: 32px; background: #354393; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700;">1</span>
                                <div>
                                    <strong style="display: block; margin-bottom: 0.25rem;">Registrar Bicicletas</strong>
                                    <p style="margin: 0; opacity: 0.8;">Usá el formulario "Registrar Nueva Bicicleta" para crear bicicletas. Se generarán automáticamente en estado "Depósito".</p>
                                </div>
                            </div>
                            <div style="display: flex; gap: 1rem; align-items: flex-start;">
                                <span style="flex-shrink: 0; width: 32px; height: 32px; background: #354393; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700;">2</span>
                                <div>
                                    <strong style="display: block; margin-bottom: 0.25rem;">Armar Bicicletas</strong>
                                    <p style="margin: 0; opacity: 0.8;">En la tabla "Gestión de Bicicletas", hacé click en "Armar" para cambiar el estado a "Armada". Observá cómo se actualizan las estadísticas en tiempo real.</p>
                                </div>
                            </div>
                            <div style="display: flex; gap: 1rem; align-items: flex-start;">
                                <span style="flex-shrink: 0; width: 32px; height: 32px; background: #354393; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700;">3</span>
                                <div>
                                    <strong style="display: block; margin-bottom: 0.25rem;">Suministrar a Escuelas</strong>
                                    <p style="margin: 0; opacity: 0.8;">Seleccioná una escuela del desplegable y hacé click en "Suministrar" para enviar bicicletas armadas. Esto las transferirá al panel de la Escuela.</p>
                                </div>
                            </div>
                            <div style="display: flex; gap: 1rem; align-items: flex-start;">
                                <span style="flex-shrink: 0; width: 32px; height: 32px; background: #2EC4C6; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700;">💬</span>
                                <div>
                                    <strong style="display: block; margin-bottom: 0.25rem;">Probar Chat IA</strong>
                                    <p style="margin: 0; opacity: 0.8;">Click en el botón de chat 💬 (esquina inferior derecha) para consultar al asistente IA contextualizado para proveedores. Probá preguntas como: "¿Cómo registro una bicicleta?" o "¿Cuál es el proceso de suministro?"</p>
                                </div>
                            </div>
                            <div style="display: flex; gap: 1rem; align-items: flex-start; padding: 1rem; background: rgba(46, 196, 198, 0.1); border-radius: 8px; margin-top: 0.5rem;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20" style="flex-shrink: 0; color: #2EC4C6;">
                                    <circle cx="12" cy="12" r="10"/>
                                    <line x1="12" y1="16" x2="12" y2="12"/>
                                    <line x1="12" y1="8" x2="12.01" y2="8"/>
                                </svg>
                                <div style="font-size: 0.9rem;">
                                    <strong>Nota:</strong> Este es un sistema de demostración. Los datos se almacenan en sesión PHP y se reinician al cerrar el navegador. Para probar el flujo completo, cambiá de rol usando el selector en el login.
                                </div>
                            </div>
                        </div>
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
                                        <th>ESCUELA</th>
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
