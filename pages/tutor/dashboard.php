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

// Datos de ejemplo de alumnos a cargo
$alumnos = [
    [
        'nombre' => 'Juan Pérez',
        'escuela' => 'Escuela N° 123 "San Martín"',
        'bici_estado' => 'activa',
        'modulos_completados' => 3,
        'total_modulos' => 5
    ],
    [
        'nombre' => 'María Pérez',
        'escuela' => 'Escuela N° 45 "Belgrano"',
        'bici_estado' => 'en_proceso',
        'modulos_completados' => 0,
        'total_modulos' => 5
    ]
];

include __DIR__ . '/../../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Panel del Tutor</h1>
        <p>Bienvenido/a, <?= e($user['nombre']) ?></p>
    </div>

    <!-- Resumen -->
    <div class="grid grid-3">
        <div class="card metric-card">
            <div class="metric-icon">👨‍👩‍👧‍👦</div>
            <div class="metric-info">
                <span class="metric-value"><?= count($alumnos) ?></span>
                <span class="metric-label">Alumnos a cargo</span>
            </div>
        </div>
        <div class="card metric-card">
            <div class="metric-icon">🚲</div>
            <div class="metric-info">
                <span class="metric-value">1</span>
                <span class="metric-label">Bicicletas activas</span>
            </div>
        </div>
        <div class="card metric-card">
            <div class="metric-icon">⏳</div>
            <div class="metric-info">
                <span class="metric-value">1</span>
                <span class="metric-label">En proceso</span>
            </div>
        </div>
    </div>

    <!-- Lista de Alumnos -->
    <div class="card" style="margin-top: 2rem;">
        <div class="card-header">
            <h3>👨‍👩‍👧‍👦 Mis Representados</h3>
        </div>
        <div class="card-body">
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
                    <tr>
                        <td><?= e($alumno['nombre']) ?></td>
                        <td><?= e($alumno['escuela']) ?></td>
                        <td>
                            <?php if ($alumno['bici_estado'] === 'activa'): ?>
                                <span class="badge badge-success">Activa</span>
                            <?php elseif ($alumno['bici_estado'] === 'en_proceso'): ?>
                                <span class="badge badge-warning">En Proceso</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Pendiente</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="progress-bar" style="width: 100px;">
                                <div class="progress-fill" style="width: <?= ($alumno['modulos_completados'] / $alumno['total_modulos']) * 100 ?>%"></div>
                            </div>
                            <small><?= $alumno['modulos_completados'] ?>/<?= $alumno['total_modulos'] ?></small>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Timeline de Estado -->
    <div class="card" style="margin-top: 2rem;">
        <div class="card-header">
            <h3>📋 Estado de Solicitudes</h3>
        </div>
        <div class="card-body">
            <div class="timeline">
                <div class="timeline-item completed">
                    <div class="timeline-marker">✓</div>
                    <div class="timeline-content">
                        <strong>Documentación presentada</strong>
                        <p>Juan Pérez - 10/03/2026</p>
                    </div>
                </div>
                <div class="timeline-item completed">
                    <div class="timeline-marker">✓</div>
                    <div class="timeline-content">
                        <strong>Documentación aprobada</strong>
                        <p>Juan Pérez - 12/03/2026</p>
                    </div>
                </div>
                <div class="timeline-item completed">
                    <div class="timeline-marker">✓</div>
                    <div class="timeline-content">
                        <strong>Bicicleta entregada</strong>
                        <p>Juan Pérez - 15/03/2026</p>
                    </div>
                </div>
                <div class="timeline-item active">
                    <div class="timeline-marker">●</div>
                    <div class="timeline-content">
                        <strong>En proceso de asignación</strong>
                        <p>María Pérez - Esperando disponibilidad</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
