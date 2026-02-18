<?php
/**
 * TUBI 2026 - Configuración del Sistema (Admin)
 */
require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn() || !hasRole('admin')) {
    redirect('login.php');
}

$pageTitle = 'Configuración';

// Configuración del programa
$configItems = array(
    'general' => array(
        'nombre_programa' => 'Tu Bicicleta San Luis',
        'anio_programa' => '2026',
        'email_contacto' => 'tubi@sanluis.gob.ar'
    )
);

// Estadísticas reales de la BD
$dbStats = array(
    'usuarios' => dbCount('usuarios'),
    'alumnos' => dbCount('alumnos'),
    'bicicletas' => dbCount('bicicletas'),
    'escuelas' => dbCount('escuelas'),
    'proveedores' => dbCount('proveedores'),
    'ordenes' => dbCount('ordenes'),
    'modulos' => dbCount('modulos'),
    'logros' => dbCount('logros')
);

// Verificar conexión BD
$dbConectada = false;
$dbVersion = '';
try {
    $conn = db();
    $dbConectada = true;
    $versionRow = dbFetchOne('SELECT VERSION() as v');
    $dbVersion = $versionRow ? $versionRow['v'] : 'N/A';
} catch (Exception $ex) {
    $dbConectada = false;
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Configuración del Sistema</h1>
    </div>

    <div class="grid grid-2">
        <!-- Configuración General -->
        <div class="card">
            <div class="card-header">
                <h3>⚙️ Configuración General</h3>
            </div>
            <div class="card-body">
                <form class="config-form">
                    <div class="form-group">
                        <label>Nombre del Programa</label>
                        <input type="text" class="form-input" value="<?= e($configItems['general']['nombre_programa']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Año del Programa</label>
                        <input type="text" class="form-input" value="<?= e($configItems['general']['anio_programa']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Email de Contacto</label>
                        <input type="email" class="form-input" value="<?= e($configItems['general']['email_contacto']) ?>">
                    </div>
                    <button type="button" class="btn btn-primary">Guardar Cambios</button>
                </form>
            </div>
        </div>

        <!-- Estado de APIs -->
        <div class="card">
            <div class="card-header">
                <h3>🔌 Estado de Integraciones</h3>
            </div>
            <div class="card-body">
                <div class="integration-status">
                    <div class="integration-item">
                        <div class="integration-info">
                            <span class="integration-icon">📧</span>
                            <div>
                                <strong>Servicio de Email</strong>
                                <p>Notificaciones por email</p>
                            </div>
                        </div>
                        <span class="badge badge-success">Conectado</span>
                    </div>
                    <div class="integration-item">
                        <div class="integration-info">
                            <span class="integration-icon">🗄️</span>
                            <div>
                                <strong>Base de Datos</strong>
                                <p>MySQL <?= e($dbVersion) ?> - <?= e(DB_NAME) ?></p>
                            </div>
                        </div>
                        <?php if ($dbConectada): ?>
                        <span class="badge badge-success">Conectada</span>
                        <?php else: ?>
                        <span class="badge badge-danger">Desconectada</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas de la Base de Datos -->
    <div class="card" style="margin-top: 2rem;">
        <div class="card-header">
            <h3>📊 Base de Datos (<?= e(DB_NAME) ?>)</h3>
        </div>
        <div class="card-body">
            <div class="info-grid">
                <?php foreach ($dbStats as $tabla => $total): ?>
                <div class="info-item">
                    <span class="label"><?= ucfirst(e($tabla)) ?>:</span>
                    <span class="value"><?= $total ?> registros</span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Información del Sistema -->
    <div class="card" style="margin-top: 2rem;">
        <div class="card-header">
            <h3>ℹ️ Información del Sistema</h3>
        </div>
        <div class="card-body">
            <div class="info-grid">
                <div class="info-item">
                    <span class="label">Versión:</span>
                    <span class="value">TuBi 2026 v1.0.0</span>
                </div>
                <div class="info-item">
                    <span class="label">PHP Version:</span>
                    <span class="value"><?= phpversion() ?></span>
                </div>
                <div class="info-item">
                    <span class="label">MySQL Version:</span>
                    <span class="value"><?= e($dbVersion) ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Servidor:</span>
                    <span class="value"><?= isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'N/A' ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Zona Horaria:</span>
                    <span class="value"><?= date_default_timezone_get() ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Base de Datos:</span>
                    <span class="value"><?= e(DB_HOST) ?> / <?= e(DB_NAME) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
