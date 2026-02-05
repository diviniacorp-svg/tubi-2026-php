<?php
/**
 * TUBI 2026 - Configuración del Sistema (Admin)
 */
require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn() || !hasRole('admin')) {
    redirect('login.php');
}

$pageTitle = 'Configuración';

// Configuración de ejemplo
$configItems = [
    'general' => [
        'nombre_programa' => 'Tu Bicicleta San Luis',
        'año_programa' => '2026',
        'email_contacto' => 'tubi@sanluis.gob.ar'
    ],
    'api' => [
        'gemini_status' => defined('GEMINI_API_KEY') && GEMINI_API_KEY !== 'TU_API_KEY_AQUI'
    ]
];

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
                        <input type="text" class="form-input" value="<?= e($configItems['general']['año_programa']) ?>">
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
                            <span class="integration-icon">🤖</span>
                            <div>
                                <strong>Google Gemini AI</strong>
                                <p>Chat inteligente para usuarios</p>
                            </div>
                        </div>
                        <?php if ($configItems['api']['gemini_status']): ?>
                            <span class="badge badge-success">Conectado</span>
                        <?php else: ?>
                            <span class="badge badge-error">Sin Configurar</span>
                        <?php endif; ?>
                    </div>
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
                                <p>MySQL / MariaDB</p>
                            </div>
                        </div>
                        <span class="badge badge-warning">Demo Mode</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Configuración de API -->
    <div class="card" style="margin-top: 2rem;">
        <div class="card-header">
            <h3>🔑 Configuración de API</h3>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <strong>Nota:</strong> Las API keys se configuran en el archivo <code>config/config.php</code>
            </div>
            <form class="config-form">
                <div class="form-group">
                    <label>Google Gemini API Key</label>
                    <input type="password" class="form-input" value="<?= $configItems['api']['gemini_status'] ? '••••••••••••••••' : '' ?>" placeholder="Ingrese su API key de Gemini">
                    <small>Obtenga su API key en <a href="https://aistudio.google.com/app/apikey" target="_blank">Google AI Studio</a></small>
                </div>
                <button type="button" class="btn btn-primary">Actualizar API Key</button>
            </form>
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
                    <span class="label">Servidor:</span>
                    <span class="value"><?= $_SERVER['SERVER_SOFTWARE'] ?? 'N/A' ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Zona Horaria:</span>
                    <span class="value"><?= date_default_timezone_get() ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
