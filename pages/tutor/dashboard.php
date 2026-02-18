<?php
/**
 * TUBI 2026 - Dashboard Tutor
 */
require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn() || !hasRole('tutor')) {
    redirect('login.php');
}

$user = getCurrentUser();
$pageTitle = 'Panel del Tutor';

// Obtener alumnos a cargo del tutor desde la BD
$tutorId = isset($user['id']) ? $user['id'] : 0;
$alumnos = dbFetchAll(
    'SELECT a.*, e.nombre AS escuela_nombre, b.codigo AS bici_codigo, b.estado AS bici_estado ' .
    'FROM alumnos a ' .
    'LEFT JOIN escuelas e ON a.escuela_id = e.id ' .
    'LEFT JOIN bicicletas b ON b.alumno_id = a.id ' .
    'ORDER BY a.nombre'
);

// Calcular estadisticas
$totalAlumnos = count($alumnos);
$bicisActivas = 0;
$bicisEnProceso = 0;
$bicisPendientes = 0;
$totalModulos = dbCount('modulos');

foreach ($alumnos as $al) {
    $estado = isset($al['bici_estado']) ? $al['bici_estado'] : '';
    if ($estado === 'entregada') {
        $bicisActivas++;
    } elseif ($estado === 'armada' || $estado === 'en_escuela') {
        $bicisEnProceso++;
    } else {
        $bicisPendientes++;
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="container">
    <!-- Mensaje de Bienvenida -->
    <div style="background: linear-gradient(135deg, #354393 0%, #4aacc4 100%); padding: 1.25rem 1.5rem; border-radius: 12px; color: white; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 1rem;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="32" height="32" style="flex-shrink: 0;">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
        </svg>
        <div>
            <h2 style="margin: 0; font-size: 1.4rem; font-weight: 700;">Bienvenido Tutor</h2>
            <p style="margin: 0; opacity: 0.9; font-size: 0.9rem;">Seguimiento de tus representados &mdash; <?php echo e($user['nombre']); ?></p>
        </div>
    </div>

    <!-- Resumen -->
    <div class="grid grid-3">
        <div class="card metric-card">
            <div class="metric-icon">&#128106;</div>
            <div class="metric-info">
                <span class="metric-value"><?php echo $totalAlumnos; ?></span>
                <span class="metric-label">Alumnos registrados</span>
            </div>
        </div>
        <div class="card metric-card">
            <div class="metric-icon">&#128690;</div>
            <div class="metric-info">
                <span class="metric-value"><?php echo $bicisActivas; ?></span>
                <span class="metric-label">Bicicletas entregadas</span>
            </div>
        </div>
        <div class="card metric-card">
            <div class="metric-icon">&#9203;</div>
            <div class="metric-info">
                <span class="metric-value"><?php echo $bicisEnProceso; ?></span>
                <span class="metric-label">En proceso</span>
            </div>
        </div>
    </div>

    <!-- Lista de Alumnos -->
    <div class="card" style="margin-top: 2rem;">
        <div class="card-header">
            <h3>&#128106; Alumnos del Programa</h3>
        </div>
        <div class="card-body">
            <?php if ($totalAlumnos > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Alumno</th>
                        <th>Escuela</th>
                        <th>Estado Bicicleta</th>
                        <th>Progreso</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alumnos as $alumno): ?>
                    <?php
                        $bEstado = isset($alumno['bici_estado']) ? $alumno['bici_estado'] : 'pendiente';
                        $modCompletos = isset($alumno['modulos_completados']) ? intval($alumno['modulos_completados']) : 0;
                        $porcProgreso = $totalModulos > 0 ? round(($modCompletos / $totalModulos) * 100) : 0;
                    ?>
                    <tr>
                        <td><?php echo e($alumno['nombre']); ?></td>
                        <td><?php echo e(isset($alumno['escuela_nombre']) ? $alumno['escuela_nombre'] : 'Sin escuela'); ?></td>
                        <td>
                            <?php if ($bEstado === 'entregada'): ?>
                                <span class="badge badge-success">Entregada</span>
                            <?php elseif ($bEstado === 'armada' || $bEstado === 'en_escuela'): ?>
                                <span class="badge badge-warning">En Proceso</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Pendiente</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="progress-bar" style="width: 100px;">
                                <div class="progress-fill" style="width: <?php echo $porcProgreso; ?>%"></div>
                            </div>
                            <small><?php echo $modCompletos; ?>/<?php echo $totalModulos; ?></small>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p style="text-align:center; color: #888; padding: 2rem 0;">No hay alumnos registrados aun.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Resumen por escuela -->
    <div class="card" style="margin-top: 2rem;">
        <div class="card-header">
            <h3>&#128202; Resumen del Programa</h3>
        </div>
        <div class="card-body">
            <div class="info-grid">
                <div class="info-item">
                    <span class="label">Total alumnos:</span>
                    <span class="value"><?php echo $totalAlumnos; ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Bicis entregadas:</span>
                    <span class="value" style="color: #22c55e; font-weight: 600;"><?php echo $bicisActivas; ?></span>
                </div>
                <div class="info-item">
                    <span class="label">En proceso:</span>
                    <span class="value" style="color: #f59e0b; font-weight: 600;"><?php echo $bicisEnProceso; ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Pendientes:</span>
                    <span class="value" style="color: #94a3b8; font-weight: 600;"><?php echo $bicisPendientes; ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
