<?php
/**
 * TUBI 2026 - Inventario de Bicicletas (Proveedor)
 */
require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn() || !hasRole('proveedor')) {
    redirect('login.php');
}

$user = getCurrentUser();
$pageTitle = 'Inventario';

// Datos de ejemplo
$inventario = array(
    array('codigo' => 'TUBI-2026-00150', 'estado' => 'lista', 'fecha_armado' => '2026-03-15', 'destino' => 'Escuela N° 123'),
    array('codigo' => 'TUBI-2026-00151', 'estado' => 'lista', 'fecha_armado' => '2026-03-15', 'destino' => 'Escuela N° 123'),
    array('codigo' => 'TUBI-2026-00152', 'estado' => 'en_armado', 'fecha_armado' => '-', 'destino' => 'Escuela N° 45'),
    array('codigo' => 'TUBI-2026-00153', 'estado' => 'en_armado', 'fecha_armado' => '-', 'destino' => 'Escuela N° 45'),
    array('codigo' => 'TUBI-2026-00154', 'estado' => 'componentes', 'fecha_armado' => '-', 'destino' => 'Sin asignar'),
);

include __DIR__ . '/../../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Inventario de Bicicletas</h1>
        <button class="btn btn-primary">+ Registrar Bicicleta</button>
    </div>

    <!-- Filtros -->
    <div class="card" style="margin-bottom: 1rem;">
        <div class="card-body">
            <div class="filter-row">
                <select class="form-input">
                    <option value="">Todos los estados</option>
                    <option value="lista">Listas para entrega</option>
                    <option value="en_armado">En armado</option>
                    <option value="componentes">Solo componentes</option>
                </select>
                <input type="text" placeholder="Buscar por código..." class="form-input">
                <button class="btn btn-secondary">Filtrar</button>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Código QR</th>
                        <th>Estado</th>
                        <th>Fecha Armado</th>
                        <th>Destino</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inventario as $bici): ?>
                    <tr>
                        <td><code><?= e($bici['codigo']) ?></code></td>
                        <td>
                            <?php if ($bici['estado'] === 'lista'): ?>
                                <span class="badge badge-success">Lista</span>
                            <?php elseif ($bici['estado'] === 'en_armado'): ?>
                                <span class="badge badge-warning">En Armado</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Componentes</span>
                            <?php endif; ?>
                        </td>
                        <td><?= e($bici['fecha_armado']) ?></td>
                        <td><?= e($bici['destino']) ?></td>
                        <td>
                            <button class="btn btn-secondary btn-sm">Ver</button>
                            <button class="btn btn-primary btn-sm">QR</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
