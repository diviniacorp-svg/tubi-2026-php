<?php
/**
 * TUBI 2026 - Gestión de Usuarios (Admin)
 */
require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn() || !hasRole('admin')) {
    redirect('login.php');
}

$pageTitle = 'Gestión de Usuarios';

// Datos de ejemplo
$usuarios = [
    ['id' => 1, 'nombre' => 'Juan Pérez', 'email' => 'alumno@tubi.com', 'role' => 'alumno', 'estado' => 'activo'],
    ['id' => 2, 'nombre' => 'María González', 'email' => 'tutor@tubi.com', 'role' => 'tutor', 'estado' => 'activo'],
    ['id' => 3, 'nombre' => 'Escuela N° 123', 'email' => 'escuela@tubi.com', 'role' => 'escuela', 'estado' => 'activo'],
    ['id' => 4, 'nombre' => 'Logística San Luis', 'email' => 'proveedor@tubi.com', 'role' => 'proveedor', 'estado' => 'activo'],
    ['id' => 5, 'nombre' => 'Admin TuBi', 'email' => 'admin@tubi.com', 'role' => 'admin', 'estado' => 'activo'],
];

$roleLabels = [
    'alumno' => ['label' => 'Alumno', 'class' => 'badge-info'],
    'tutor' => ['label' => 'Tutor', 'class' => 'badge-secondary'],
    'escuela' => ['label' => 'Escuela', 'class' => 'badge-primary'],
    'proveedor' => ['label' => 'Proveedor', 'class' => 'badge-warning'],
    'admin' => ['label' => 'Admin', 'class' => 'badge-error'],
];

include __DIR__ . '/../../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Gestión de Usuarios</h1>
        <button class="btn btn-primary">+ Nuevo Usuario</button>
    </div>

    <!-- Filtros -->
    <div class="card" style="margin-bottom: 1rem;">
        <div class="card-body">
            <div class="filter-row">
                <select class="form-input">
                    <option value="">Todos los roles</option>
                    <option value="alumno">Alumnos</option>
                    <option value="tutor">Tutores</option>
                    <option value="escuela">Escuelas</option>
                    <option value="proveedor">Proveedores</option>
                    <option value="admin">Administradores</option>
                </select>
                <select class="form-input">
                    <option value="">Todos los estados</option>
                    <option value="activo">Activos</option>
                    <option value="inactivo">Inactivos</option>
                </select>
                <input type="text" placeholder="Buscar por nombre o email..." class="form-input" style="flex: 1;">
                <button class="btn btn-secondary">Filtrar</button>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u): ?>
                    <tr>
                        <td><?= $u['id'] ?></td>
                        <td><?= e($u['nombre']) ?></td>
                        <td><?= e($u['email']) ?></td>
                        <td>
                            <span class="badge <?= $roleLabels[$u['role']]['class'] ?>">
                                <?= $roleLabels[$u['role']]['label'] ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-success">Activo</span>
                        </td>
                        <td>
                            <button class="btn btn-secondary btn-sm">Editar</button>
                            <button class="btn btn-error btn-sm">Desactivar</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
