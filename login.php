<?php
/**
 * TUBI 2026 - Login Page
 * Diseño con logo oficial TuBi
 */
require_once __DIR__ . '/config/config.php';

if (isLoggedIn()) {
    $user = getCurrentUser();
    redirect('pages/' . $user['role'] . '/dashboard.php');
}

// Validar rol desde URL
$allowedRoles = ['alumno', 'tutor', 'escuela', 'proveedor', 'admin'];
$role = isset($_GET['role']) ? $_GET['role'] : 'alumno';
if (!in_array($role, $allowedRoles)) {
    $role = 'alumno';
}

$roleConfig = [
    'alumno' => [
        'title' => 'Ingreso Estudiantes',
        'subtitle' => 'Accedé a Mi TuBi App',
        'field' => 'D.N.I. Estudiante',
        'placeholder' => 'Ej: 12345678',
        'icon' => 'student',
        'color' => '#2563eb',
        'gradient' => 'linear-gradient(135deg, #2563eb 0%, #3b82f6 100%)'
    ],
    'tutor' => [
        'title' => 'Ingreso Tutores',
        'subtitle' => 'Panel de Tutor Responsable',
        'field' => 'D.N.I. Tutor',
        'placeholder' => 'Ingresá tu DNI',
        'icon' => 'users',
        'color' => '#06b6d4',
        'gradient' => 'linear-gradient(135deg, #06b6d4 0%, #22d3ee 100%)'
    ],
    'proveedor' => [
        'title' => 'Ingreso Proveedores',
        'subtitle' => 'Panel de Gestión',
        'field' => 'CUIT',
        'placeholder' => 'Ingresá tu CUIT',
        'icon' => 'truck',
        'color' => '#06b6d4',
        'gradient' => 'linear-gradient(135deg, #06b6d4 0%, #22d3ee 100%)'
    ],
    'escuela' => [
        'title' => 'Ingreso Escuelas',
        'subtitle' => 'Panel de Gestión Escolar',
        'field' => 'CUE / Usuario',
        'placeholder' => 'Ingresá tu usuario',
        'icon' => 'school',
        'color' => '#0891b2',
        'gradient' => 'linear-gradient(135deg, #2563eb 0%, #06b6d4 100%)'
    ],
    'admin' => [
        'title' => 'Administración',
        'subtitle' => 'Centro de Control TuBi',
        'field' => 'Usuario',
        'placeholder' => 'Ingresá tu usuario',
        'icon' => 'lock',
        'color' => '#1e3a5f',
        'gradient' => 'linear-gradient(135deg, #1e3a5f 0%, #134e4a 100%)'
    ]
];

$config = isset($roleConfig[$role]) ? $roleConfig[$role] : $roleConfig['alumno'];
$error = '';

// Procesar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $DEMO_USERS = $GLOBALS['DEMO_USERS'];

    $identifier = isset($_POST['identifier']) ? trim($_POST['identifier']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Login demo universal
    if (strtolower($identifier) === 'tubi' && $password === 'tubi2026') {
        $demoUser = isset($DEMO_USERS[$role]) ? $DEMO_USERS[$role] : $DEMO_USERS['alumno'];
        // Buscar ID real en BD
        $dbUser = dbFetchOne('SELECT id FROM usuarios WHERE role = ?', array($role));
        if ($dbUser) {
            $demoUser['id'] = $dbUser['id'];
        }
        $_SESSION['user'] = $demoUser;
        redirect('pages/' . $role . '/dashboard.php');
    }

    // Buscar en base de datos
    $dbUser = dbFetchOne('SELECT * FROM usuarios WHERE email = ? AND password = ?', array($identifier, $password));

    if ($dbUser) {
        $_SESSION['user'] = array(
            'id' => $dbUser['id'],
            'email' => $dbUser['email'],
            'nombre' => $dbUser['nombre'],
            'dni' => $dbUser['dni'],
            'cuit' => $dbUser['cuit'],
            'cue' => $dbUser['cue'],
            'role' => $dbUser['role']
        );
        redirect('pages/' . $dbUser['role'] . '/dashboard.php');
    }

    // Fallback: buscar en demo users
    $foundUser = null;
    foreach ($DEMO_USERS as $user) {
        if ($user['email'] === $identifier && $user['password'] === $password) {
            $foundUser = $user;
            break;
        }
    }

    if ($foundUser) {
        $_SESSION['user'] = $foundUser;
        redirect('pages/' . $foundUser['role'] . '/dashboard.php');
    } else {
        $error = 'Credenciales incorrectas';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($config['title']) ?> - TuBi 2026</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🚲</text></svg>">
    <style>
        .login-page {
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            background: var(--bg-dark);
            font-family: 'Ubuntu', sans-serif;
            position: relative;
        }

        .login-header {
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            z-index: 10;
        }

        .btn-back {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            color: var(--text-secondary);
            font-size: 0.875rem;
            transition: all var(--transition);
        }

        .btn-back:hover {
            background: var(--bg-card-hover);
            color: var(--text-primary);
        }

        .login-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
            z-index: 10;
        }

        .login-card {
            width: 100%;
            max-width: 400px;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius-xl);
            padding: 2rem;
            text-align: center;
        }

        .login-logo {
            margin-bottom: 1.5rem;
        }

        .login-logo svg {
            width: 180px;
            height: auto;
            color: <?= $config['color'] ?>;
        }

        .login-icon-wrapper {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #354393 0%, #2EC4C6 100%);
            color: white;
            box-shadow: 0 10px 30px rgba(53, 67, 147, 0.3);
            position: relative;
            overflow: hidden;
        }

        .login-icon-wrapper::before {
            content: '';
            position: absolute;
            inset: -2px;
            background: linear-gradient(135deg, #2EC4C6, #354393);
            border-radius: 20px;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .login-icon-wrapper:hover::before {
            opacity: 0.2;
        }

        .login-icon-wrapper svg {
            width: 40px;
            height: 40px;
            position: relative;
            z-index: 1;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
        }

        .login-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .login-subtitle {
            font-size: 0.9375rem;
            color: var(--text-secondary);
            margin-bottom: 1.5rem;
        }

        .login-error {
            padding: 0.75rem;
            background: rgba(239,68,68,0.15);
            border-radius: var(--border-radius);
            border-left: 3px solid var(--color-error);
            color: #f87171;
            text-align: left;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }

        .login-form {
            text-align: left;
        }

        .login-form .form-group {
            margin-bottom: 1rem;
        }

        .login-form label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
        }

        .input-group {
            position: relative;
            display: flex;
            align-items: center;
            background: var(--bg-input);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            transition: border-color var(--transition);
        }

        .input-group:focus-within {
            border-color: <?= $config['color'] ?>;
        }

        .input-group > svg {
            position: absolute;
            left: 1rem;
            width: 20px;
            height: 20px;
            color: var(--text-muted);
            pointer-events: none;
            flex-shrink: 0;
        }

        .input-group input {
            width: 100%;
            padding: 0.875rem 3rem 0.875rem 3rem;
            background: transparent;
            border: none;
            font-size: 1rem;
            color: var(--text-primary);
            font-family: 'Ubuntu', sans-serif;
        }

        .input-group input:focus {
            outline: none;
        }

        .toggle-password {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 0.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
        }

        .toggle-password svg {
            width: 20px;
            height: 20px;
        }

        .toggle-password:hover {
            color: var(--text-secondary);
        }

        .login-form .btn {
            width: 100%;
            padding: 1rem;
            margin-top: 0.5rem;
            background: <?= $config['gradient'] ?>;
            border: none;
            color: white;
            font-weight: 600;
            font-size: 1rem;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: all var(--transition);
            box-shadow: 0 4px 15px <?= $config['color'] ?>40;
        }

        .login-form .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px <?= $config['color'] ?>50;
        }

        .login-demo {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        .login-demo code {
            background: var(--bg-card-hover);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-family: monospace;
            color: <?= $config['color'] ?>;
        }

        .login-help {
            margin-top: 1rem;
        }

        .login-help a {
            font-size: 0.875rem;
            color: var(--text-muted);
        }

        .login-help a:hover {
            color: <?= $config['color'] ?>;
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 1.5rem;
                border-radius: var(--border-radius-lg);
            }

            .login-logo svg {
                width: 150px;
            }
        }
    </style>
</head>
<body>
    <div class="login-page">
        <!-- Header -->
        <header class="login-header">
            <a href="<?= BASE_URL ?>selector.php" class="btn-back">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Volver
            </a>
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
        </header>

        <!-- Content -->
        <main class="login-content">
            <div class="login-card">
                <!-- Icon -->
                <div class="login-icon-wrapper">
                    <?php if ($config['icon'] === 'student'): ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                        <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                    </svg>
                    <?php elseif ($config['icon'] === 'users'): ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    <?php elseif ($config['icon'] === 'truck'): ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="1" y="3" width="15" height="13"/>
                        <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                        <circle cx="5.5" cy="18.5" r="2.5"/>
                        <circle cx="18.5" cy="18.5" r="2.5"/>
                    </svg>
                    <?php elseif ($config['icon'] === 'school'): ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                    <?php else: ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    <?php endif; ?>
                </div>

                <!-- Title -->
                <h1 class="login-title"><?= e($config['title']) ?></h1>
                <p class="login-subtitle"><?= e($config['subtitle']) ?></p>

                <?php if ($error): ?>
                <div class="login-error">
                    <?= e($error) ?>
                </div>
                <?php endif; ?>

                <!-- Form -->
                <form method="POST" class="login-form">
                    <div class="form-group">
                        <label><?= e($config['field']) ?></label>
                        <div class="input-group">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            <input type="text" name="identifier" required
                                   placeholder="<?= e($config['placeholder']) ?>"
                                   value="<?= e(isset($_POST['identifier']) ? $_POST['identifier'] : '') ?>"
                                   autocomplete="username">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Contraseña</label>
                        <div class="input-group">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            <input type="password" name="password" required
                                   placeholder="Ingresá tu contraseña" id="password"
                                   autocomplete="current-password">
                            <button type="button" class="toggle-password" onclick="togglePassword()">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        Ingresar
                    </button>
                </form>

                <!-- Demo hint -->
                <div class="login-demo">
                    <span>Demo: </span>
                    <code>tubi</code> / <code>tubi2026</code>
                </div>

                <!-- Help -->
                <div class="login-help">
                    <a href="#">¿Necesitás ayuda para ingresar?</a>
                </div>
            </div>
        </main>
    </div>

    <script>
    function togglePassword() {
        var input = document.getElementById('password');
        input.type = input.type === 'password' ? 'text' : 'password';
    }

    // Sistema de tema
    var themeToggle = document.getElementById('themeToggle');
    var savedTheme = localStorage.getItem('tubi-theme') || 'light';
    if (savedTheme === 'dark') {
        document.body.setAttribute('data-theme', 'dark');
    }

    themeToggle.addEventListener('click', function() {
        var isDark = document.body.getAttribute('data-theme') === 'dark';
        if (isDark) {
            document.body.removeAttribute('data-theme');
            localStorage.setItem('tubi-theme', 'light');
        } else {
            document.body.setAttribute('data-theme', 'dark');
            localStorage.setItem('tubi-theme', 'dark');
        }
    });
    </script>
</body>
</html>
