<?php
/**
 * TUBI 2026 - Gestión de Alumnos (Escuela)
 */
require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn() || !hasRole('escuela')) {
    redirect('login.php');
}

$user = getCurrentUser();
$pageTitle = 'Alumnos';

// Datos de ejemplo
$alumnos = [
    ['dni' => '45123456', 'nombre' => 'Juan Pérez', 'curso' => '4° A', 'estado' => 'activa', 'codigo' => 'TUBI-2026-00123'],
    ['dni' => '45234567', 'nombre' => 'Ana García', 'curso' => '3° B', 'estado' => 'activa', 'codigo' => 'TUBI-2026-00122'],
    ['dni' => '45345678', 'nombre' => 'Carlos López', 'curso' => '5° A', 'estado' => 'activa', 'codigo' => 'TUBI-2026-00121'],
    ['dni' => '45456789', 'nombre' => 'María Rodríguez', 'curso' => '4° B', 'estado' => 'en_proceso', 'codigo' => '-'],
    ['dni' => '45567890', 'nombre' => 'Pedro Sánchez', 'curso' => '3° A', 'estado' => 'pendiente', 'codigo' => '-'],
];

include __DIR__ . '/../../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Gestión de Alumnos</h1>
        <a href="?action=new" class="btn btn-primary">+ Registrar Alumno</a>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>📋 Lista de Alumnos</h3>
            <div class="header-actions">
                <input type="text" placeholder="Buscar..." class="form-input" style="width: 200px;">
            </div>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>DNI</th>
                        <th>Nombre</th>
                        <th>Curso</th>
                        <th>Estado Bici</th>
                        <th>Código</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alumnos as $alumno): ?>
                    <tr>
                        <td><?= e($alumno['dni']) ?></td>
                        <td><?= e($alumno['nombre']) ?></td>
                        <td><?= e($alumno['curso']) ?></td>
                        <td>
                            <?php if ($alumno['estado'] === 'activa'): ?>
                                <span class="badge badge-success">Activa</span>
                            <?php elseif ($alumno['estado'] === 'en_proceso'): ?>
                                <span class="badge badge-warning">En Proceso</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Pendiente</span>
                            <?php endif; ?>
                        </td>
                        <td><code><?= e($alumno['codigo']) ?></code></td>
                        <td>
                            <button class="btn btn-secondary btn-sm">Ver</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
