<?php
/**
 * TUBI 2026 - Dashboard Administrador
 * Centro de Control con visualización en tiempo real y gestión de IA
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/data.php';

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

// Progreso anual
$progresoAnual = round(($stats['entregadas'] / max($stats['total_bicicletas'], 1)) * 100, 1);

// Alertas recientes (simuladas)
$alertas = [
    ['mensaje' => $stats['armadas'] . ' bicicletas listas para suministrar', 'tiempo' => 'Hace 5 min', 'tipo' => 'info'],
    ['mensaje' => $stats['en_escuelas'] . ' bicicletas en escuelas pendientes de entrega', 'tiempo' => 'Hace 15 min', 'tipo' => 'warning'],
    ['mensaje' => 'Proveedor "Logística San Luis" activo', 'tiempo' => 'Hace 1 hora', 'tipo' => 'success'],
];

// Distribución por estado
$distribucion = [
    ['label' => 'Entregadas', 'valor' => $stats['entregadas'], 'color' => '#22c55e', 'icono' => '✅'],
    ['label' => 'En Escuelas', 'valor' => $stats['en_escuelas'], 'color' => '#f59e0b', 'icono' => '🏫'],
    ['label' => 'Armadas', 'valor' => $stats['armadas'], 'color' => '#3b82f6', 'icono' => '🔧'],
    ['label' => 'En Depósito', 'valor' => $stats['en_deposito'], 'color' => '#6b7280', 'icono' => '📦'],
];

// Tab activa
$tabActiva = $_GET['tab'] ?? 'dashboard';

// Inicializar documentos de IA si no existen
if (!isset($_SESSION['tubi_ia_docs'])) {
    $_SESSION['tubi_ia_docs'] = [
        [
            'id' => 1,
            'nombre' => 'Manual de Entregas TuBi 2026.pdf',
            'tamaño' => '2.4 MB',
            'fecha' => '2026-01-15',
            'tipo' => 'manual',
            'descripcion' => 'Procedimientos oficiales de entrega de bicicletas',
            'estado' => 'procesado',
        ],
        [
            'id' => 2,
            'nombre' => 'FAQ Estudiantes y Tutores.pdf',
            'tamaño' => '1.1 MB',
            'fecha' => '2026-01-20',
            'tipo' => 'faq',
            'descripcion' => 'Preguntas frecuentes para usuarios del programa',
            'estado' => 'procesado',
        ],
        [
            'id' => 3,
            'nombre' => 'Protocolo Escuelas.pdf',
            'tamaño' => '890 KB',
            'fecha' => '2026-01-25',
            'tipo' => 'protocolo',
            'descripcion' => 'Protocolo de recepción y entrega en escuelas',
            'estado' => 'procesado',
        ],
    ];
}

// Procesar subida de documentos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tabActiva === 'ia') {
    $action = $_POST['action'] ?? '';

    if ($action === 'upload_doc') {
        $nombre = trim($_POST['doc_nombre'] ?? '');
        $tipo = $_POST['doc_tipo'] ?? 'manual';
        $descripcion = trim($_POST['doc_descripcion'] ?? '');

        if ($nombre) {
            $nuevoId = count($_SESSION['tubi_ia_docs']) + 1;
            $_SESSION['tubi_ia_docs'][] = [
                'id' => $nuevoId,
                'nombre' => $nombre . '.pdf',
                'tamaño' => rand(500, 3000) . ' KB',
                'fecha' => date('Y-m-d'),
                'tipo' => $tipo,
                'descripcion' => $descripcion ?: 'Documento cargado por administrador',
                'estado' => 'procesando',
            ];
            setFlash('success', 'Documento "' . $nombre . '.pdf" cargado exitosamente. Procesando para la IA...');
        }
        redirect('pages/admin/dashboard.php?tab=ia');
    }

    if ($action === 'delete_doc') {
        $docId = (int)($_POST['doc_id'] ?? 0);
        foreach ($_SESSION['tubi_ia_docs'] as $key => $doc) {
            if ($doc['id'] == $docId) {
                $docNombre = $doc['nombre'];
                unset($_SESSION['tubi_ia_docs'][$key]);
                $_SESSION['tubi_ia_docs'] = array_values($_SESSION['tubi_ia_docs']);
                setFlash('success', 'Documento "' . $docNombre . '" eliminado correctamente.');
                break;
            }
        }
        redirect('pages/admin/dashboard.php?tab=ia');
    }

    if ($action === 'reprocess_doc') {
        $docId = (int)($_POST['doc_id'] ?? 0);
        foreach ($_SESSION['tubi_ia_docs'] as &$doc) {
            if ($doc['id'] == $docId) {
                $doc['estado'] = 'procesando';
                setFlash('info', 'Reprocesando documento "' . $doc['nombre'] . '"...');
                break;
            }
        }
        redirect('pages/admin/dashboard.php?tab=ia');
    }
}

// Simular procesamiento de documentos
foreach ($_SESSION['tubi_ia_docs'] as &$doc) {
    if ($doc['estado'] === 'procesando' && rand(1, 3) === 1) {
        $doc['estado'] = 'procesado';
    }
}

$documentosIA = $_SESSION['tubi_ia_docs'];
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
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🚲</text></svg>">
    <style>
        /* Estilos para sección de IA */
        .ia-header-card {
            background: linear-gradient(135deg, #8b5cf620 0%, #6366f120 100%);
            border: 1px solid #8b5cf640;
            border-radius: var(--border-radius-xl);
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .ia-icon-wrap {
            width: 56px;
            height: 56px;
            border-radius: var(--border-radius-lg);
            background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
        }

        .ia-info {
            flex: 1;
        }

        .ia-info strong {
            display: block;
            font-size: 1.125rem;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }

        .ia-info span {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        .ia-stats {
            display: flex;
            gap: 1.5rem;
        }

        .ia-stat {
            text-align: center;
        }

        .ia-stat-value {
            display: block;
            font-size: 1.5rem;
            font-weight: 700;
            color: #8b5cf6;
        }

        .ia-stat-label {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        /* Upload zone */
        .upload-zone {
            border: 2px dashed var(--border-color);
            border-radius: var(--border-radius-xl);
            padding: 2rem;
            text-align: center;
            background: var(--bg-card-hover);
            transition: all var(--transition);
            cursor: pointer;
        }

        .upload-zone:hover {
            border-color: #8b5cf6;
            background: #8b5cf610;
        }

        .upload-zone.dragover {
            border-color: #8b5cf6;
            background: #8b5cf620;
        }

        .upload-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .upload-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .upload-subtitle {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-bottom: 1rem;
        }

        .upload-formats {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        /* Document list */
        .doc-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .doc-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.25rem;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius-lg);
            transition: all var(--transition);
        }

        .doc-item:hover {
            border-color: var(--border-color-hover);
            background: var(--bg-card-hover);
        }

        .doc-icon {
            width: 48px;
            height: 48px;
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .doc-icon.manual { background: #ef444420; }
        .doc-icon.faq { background: #3b82f620; }
        .doc-icon.protocolo { background: #22c55e20; }
        .doc-icon.otro { background: #6b728020; }

        .doc-content {
            flex: 1;
            min-width: 0;
        }

        .doc-name {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .doc-desc {
            font-size: 0.8125rem;
            color: var(--text-secondary);
            margin-bottom: 0.375rem;
        }

        .doc-meta {
            display: flex;
            gap: 1rem;
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .doc-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn-doc {
            padding: 0.375rem 0.75rem;
            border-radius: var(--border-radius);
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        /* Modal de carga */
        .upload-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        @media (max-width: 480px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .ia-header-card {
                flex-direction: column;
                text-align: center;
            }

            .ia-stats {
                width: 100%;
                justify-content: center;
            }

            .doc-item {
                flex-wrap: wrap;
            }

            .doc-actions {
                width: 100%;
                margin-top: 0.5rem;
            }
        }
    </style>
</head>
<body class="tubi-bg-pattern" data-base-url="<?= BASE_URL ?>">
    <div class="app-container wide">
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
                        <a href="?tab=ia" class="tab-item <?= $tabActiva === 'ia' ? 'active' : '' ?>">🤖 Base IA</a>
                        <a href="?tab=reportes" class="tab-item <?= $tabActiva === 'reportes' ? 'active' : '' ?>">📋 Reportes</a>
                    </div>
                </div>

                <?php if ($tabActiva === 'dashboard'): ?>
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
                                <a href="?tab=ia" class="quick-action-btn">
                                    <div class="qa-icon morado">🤖</div>
                                    <span>Base IA</span>
                                </a>
                                <a href="?tab=reportes" class="quick-action-btn">
                                    <div class="qa-icon naranja">📊</div>
                                    <span>Reportes</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <?php elseif ($tabActiva === 'ia'): ?>
                <!-- Sección de Base de Conocimiento IA -->

                <!-- Header IA -->
                <div class="ia-header-card">
                    <div class="ia-icon-wrap">🤖</div>
                    <div class="ia-info">
                        <strong>Base de Conocimiento IA</strong>
                        <span>Gestión de documentos para el asistente TuBi Chat</span>
                    </div>
                    <div class="ia-stats">
                        <div class="ia-stat">
                            <span class="ia-stat-value"><?= count($documentosIA) ?></span>
                            <span class="ia-stat-label">Documentos</span>
                        </div>
                        <div class="ia-stat">
                            <span class="ia-stat-value"><?= count(array_filter($documentosIA, fn($d) => $d['estado'] === 'procesado')) ?></span>
                            <span class="ia-stat-label">Procesados</span>
                        </div>
                    </div>
                </div>

                <div class="grid-2">
                    <!-- Zona de carga -->
                    <div class="card">
                        <div class="card-header">
                            <h3>Cargar Documento</h3>
                        </div>
                        <div class="card-body">
                            <div class="upload-zone" onclick="document.getElementById('modalUpload').style.display='flex'" id="uploadZone">
                                <div class="upload-icon">📄</div>
                                <div class="upload-title">Arrastrá o hacé click para cargar</div>
                                <div class="upload-subtitle">Subí documentos PDF para entrenar al asistente IA</div>
                                <div class="upload-formats">Formatos: PDF · Máximo 10MB</div>
                            </div>

                            <div style="margin-top: 1.5rem;">
                                <h4 style="font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 0.75rem;">Tipos de documentos recomendados:</h4>
                                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; color: var(--text-primary);">
                                        <span>📋</span> Manuales de procedimientos
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; color: var(--text-primary);">
                                        <span>❓</span> Preguntas frecuentes (FAQ)
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; color: var(--text-primary);">
                                        <span>📜</span> Protocolos de entrega
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; color: var(--text-primary);">
                                        <span>📖</span> Guías para usuarios
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Estado del sistema IA -->
                    <div class="card">
                        <div class="card-header">
                            <h3>Estado del Sistema IA</h3>
                            <span class="badge badge-success">● Activo</span>
                        </div>
                        <div class="card-body">
                            <div style="display: flex; flex-direction: column; gap: 1rem;">
                                <div class="distribution-item">
                                    <div class="dist-header">
                                        <span class="dist-label">
                                            <span class="dist-dot" style="background:#22c55e"></span>
                                            Chat Estudiantes
                                        </span>
                                        <span class="badge badge-success">Online</span>
                                    </div>
                                </div>
                                <div class="distribution-item">
                                    <div class="dist-header">
                                        <span class="dist-label">
                                            <span class="dist-dot" style="background:#22c55e"></span>
                                            Chat Escuelas
                                        </span>
                                        <span class="badge badge-success">Online</span>
                                    </div>
                                </div>
                                <div class="distribution-item">
                                    <div class="dist-header">
                                        <span class="dist-label">
                                            <span class="dist-dot" style="background:#22c55e"></span>
                                            Chat Proveedores
                                        </span>
                                        <span class="badge badge-success">Online</span>
                                    </div>
                                </div>
                                <div class="distribution-item">
                                    <div class="dist-header">
                                        <span class="dist-label">
                                            <span class="dist-dot" style="background:#3b82f6"></span>
                                            Última actualización
                                        </span>
                                        <span style="font-size: 0.875rem; color: var(--text-secondary);"><?= date('d/m/Y H:i') ?></span>
                                    </div>
                                </div>
                            </div>

                            <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                                <button class="btn btn-primary btn-block" onclick="sincronizarIA()">
                                    🔄 Sincronizar Base de Conocimiento
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lista de documentos -->
                <div class="card">
                    <div class="card-header">
                        <h3>Documentos Cargados</h3>
                        <span class="badge badge-info"><?= count($documentosIA) ?> documentos</span>
                    </div>
                    <div class="card-body">
                        <?php if (empty($documentosIA)): ?>
                        <div style="text-align: center; padding: 2rem; color: var(--text-muted);">
                            <div style="font-size: 3rem; margin-bottom: 1rem;">📭</div>
                            <p>No hay documentos cargados todavía.</p>
                            <p style="font-size: 0.875rem;">Cargá documentos PDF para entrenar al asistente IA.</p>
                        </div>
                        <?php else: ?>
                        <div class="doc-list">
                            <?php foreach ($documentosIA as $doc): ?>
                            <div class="doc-item">
                                <div class="doc-icon <?= $doc['tipo'] ?>">
                                    <?php
                                    $iconos = ['manual' => '📕', 'faq' => '❓', 'protocolo' => '📜', 'otro' => '📄'];
                                    echo $iconos[$doc['tipo']] ?? '📄';
                                    ?>
                                </div>
                                <div class="doc-content">
                                    <div class="doc-name">
                                        <?= e($doc['nombre']) ?>
                                        <?php if ($doc['estado'] === 'procesado'): ?>
                                        <span class="badge badge-success" style="font-size: 0.625rem;">✓ Procesado</span>
                                        <?php else: ?>
                                        <span class="badge badge-warning" style="font-size: 0.625rem;">⏳ Procesando...</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="doc-desc"><?= e($doc['descripcion']) ?></div>
                                    <div class="doc-meta">
                                        <span>📁 <?= e($doc['tamaño']) ?></span>
                                        <span>📅 <?= date('d/m/Y', strtotime($doc['fecha'])) ?></span>
                                        <span>🏷️ <?= ucfirst($doc['tipo']) ?></span>
                                    </div>
                                </div>
                                <div class="doc-actions">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="reprocess_doc">
                                        <input type="hidden" name="doc_id" value="<?= $doc['id'] ?>">
                                        <button type="submit" class="btn btn-secondary btn-doc" title="Reprocesar">
                                            🔄 Reprocesar
                                        </button>
                                    </form>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('¿Eliminar este documento de la base de conocimiento?')">
                                        <input type="hidden" name="action" value="delete_doc">
                                        <input type="hidden" name="doc_id" value="<?= $doc['id'] ?>">
                                        <button type="submit" class="btn btn-secondary btn-doc" style="color: #ef4444;" title="Eliminar">
                                            🗑️
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
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
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bicicletas as $bici): ?>
                                    <tr data-estado="<?= $bici['estado'] ?>">
                                        <td><strong><?= e($bici['codigo']) ?></strong></td>
                                        <td>
                                            <?php if ($bici['alumno']): ?>
                                                <?= e($bici['alumno']['nombre']) ?><br>
                                                <small class="text-muted">DNI: <?= e($bici['alumno']['dni']) ?></small>
                                            <?php else: ?>
                                                <em class="text-muted">Sin asignar</em>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($bici['escuela']): ?>
                                                <?= e($bici['escuela']['nombre']) ?>
                                            <?php else: ?>
                                                <em class="text-muted">-</em>
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
                                            <?php if ($bici['fecha_entrega']): ?>
                                                <?= date('d/m/Y', strtotime($bici['fecha_entrega'])) ?>
                                            <?php elseif ($bici['fecha_armado']): ?>
                                                <?= date('d/m/Y', strtotime($bici['fecha_armado'])) ?>
                                            <?php else: ?>
                                                -
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
                document.getElementById('searchEscuela')?.addEventListener('input', function(e) {
                    const searchTerm = e.target.value.toLowerCase();
                    const rows = document.querySelectorAll('#escuelasTableBody tr');

                    rows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        row.style.display = text.includes(searchTerm) ? '' : 'none';
                    });
                });
                </script>

                <?php elseif ($tabActiva === 'reportes'): ?>
                <!-- Reportes -->
                <div class="card">
                    <div class="card-header">
                        <h3>Generador de Reportes</h3>
                    </div>
                    <div class="card-body">
                        <div class="reports-grid">
                            <div class="report-card" onclick="generarReporte('general')">
                                <div class="report-icon">📊</div>
                                <h4>Reporte General</h4>
                                <p>Estadísticas completas del programa</p>
                            </div>
                            <div class="report-card" onclick="generarReporte('entregas')">
                                <div class="report-icon">📋</div>
                                <h4>Reporte de Entregas</h4>
                                <p>Listado de todas las entregas realizadas</p>
                            </div>
                            <div class="report-card" onclick="generarReporte('escuelas')">
                                <div class="report-icon">🏫</div>
                                <h4>Reporte por Escuela</h4>
                                <p>Detalle por institución educativa</p>
                            </div>
                            <div class="report-card" onclick="generarReporte('proveedores')">
                                <div class="report-icon">🚚</div>
                                <h4>Reporte de Proveedores</h4>
                                <p>Producción por proveedor</p>
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

    <!-- Modal de carga de documento -->
    <div id="modalUpload" class="modal" style="display:none">
        <div class="modal-backdrop" onclick="cerrarModal()"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3>📄 Cargar Documento</h3>
                <button onclick="cerrarModal()" class="modal-close">&times;</button>
            </div>
            <form method="POST" class="upload-form">
                <input type="hidden" name="action" value="upload_doc">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nombre del documento *</label>
                        <input type="text" class="form-control" name="doc_nombre" placeholder="Ej: Manual de Entregas 2026" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Tipo de documento</label>
                            <select class="form-control" name="doc_tipo">
                                <option value="manual">📕 Manual</option>
                                <option value="faq">❓ FAQ / Preguntas frecuentes</option>
                                <option value="protocolo">📜 Protocolo</option>
                                <option value="otro">📄 Otro</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Archivo PDF</label>
                            <input type="file" class="form-control" accept=".pdf" style="padding: 0.5rem;">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea class="form-control" name="doc_descripcion" rows="2" placeholder="Describe brevemente el contenido del documento..."></textarea>
                    </div>
                    <div style="padding: 1rem; background: #8b5cf615; border-radius: var(--border-radius); border-left: 3px solid #8b5cf6;">
                        <p style="font-size: 0.875rem; color: var(--text-secondary); margin: 0;">
                            <strong>💡 Nota:</strong> El documento será procesado automáticamente y su contenido estará disponible para los asistentes de chat IA en todos los paneles.
                        </p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="cerrarModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">📤 Cargar Documento</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Chat Widget -->
    <div class="chat-widget" id="chatBox">
        <button class="chat-fab" id="chatToggle" title="Chat TuBi Admin">
            <span>🤖</span>
        </button>
        <div class="chat-panel" id="chatWindow" style="display: none;">
            <div class="chat-panel-header">
                <span>🔒 TuBi Admin Chat</span>
                <button class="chat-panel-close" id="chatClose">×</button>
            </div>
            <div class="chat-panel-messages" id="chatMessages"></div>
            <div class="chat-panel-input">
                <input type="text" id="chatInput" placeholder="Consultá sobre el programa..." autocomplete="off">
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

    function filtrarTabla() {
        const filter = document.getElementById('searchBici')?.value.toLowerCase() || '';
        document.querySelectorAll('#tablaGestion tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
        });
    }

    function filtrarPorEstado(estado) {
        document.querySelectorAll('#tablaGestion tbody tr').forEach(row => {
            if (!estado || row.dataset.estado === estado) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function exportarCSV() {
        alert('📊 Exportando datos a CSV...\nEl archivo se descargará en breve.');
    }

    function generarReporte(tipo) {
        const tipos = {
            'general': 'Reporte General del Programa TuBi 2026',
            'entregas': 'Reporte de Entregas Realizadas',
            'escuelas': 'Reporte por Escuela',
            'proveedores': 'Reporte de Producción por Proveedor'
        };
        alert('📋 Generando: ' + tipos[tipo] + '\n\nEl documento PDF se descargará en breve.');
    }

    function cerrarModal() {
        document.getElementById('modalUpload').style.display = 'none';
    }

    function sincronizarIA() {
        alert('🔄 Sincronizando base de conocimiento...\n\nTodos los documentos procesados están siendo enviados a los asistentes de chat.\n\n✅ Sincronización completada.');
    }

    // Drag and drop para zona de carga
    const uploadZone = document.getElementById('uploadZone');
    if (uploadZone) {
        uploadZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadZone.classList.add('dragover');
        });

        uploadZone.addEventListener('dragleave', () => {
            uploadZone.classList.remove('dragover');
        });

        uploadZone.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadZone.classList.remove('dragover');
            document.getElementById('modalUpload').style.display = 'flex';
        });
    }

    // Auto-refresh cada 90 segundos para admin
    setTimeout(() => location.reload(), 90000);
    </script>

    <!-- Tutorial y Chat -->
    <?php include __DIR__ . '/../../includes/tutorial.php'; ?>

    <script src="<?= BASE_URL ?>assets/js/toast.js"></script>
</body>
</html>
