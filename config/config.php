<?php
/**
 * TUBI 2026 - Configuración Principal
 * Sistema de Gestión de Bicicletas - San Luis
 */

// Configuración de sesión segura
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 0); // Cambiar a 1 si usa HTTPS en producción
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.use_strict_mode', 1);
    session_start();
}

// Incluir sistema de datos
require_once __DIR__ . '/data.php';

// Zona horaria
date_default_timezone_set('America/Argentina/San_Luis');

// ============================================
// CONFIGURACIÓN PARA DEPLOY
// ============================================
// Cambiar 'development' a 'production' en el servidor
define('ENVIRONMENT', 'development');

// Configuración de errores según ambiente
if (ENVIRONMENT === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Configuración de la base de datos (para futuro uso)
define('DB_HOST', 'localhost');
define('DB_NAME', 'tubi_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configuración de Google Gemini API
define('GEMINI_API_KEY', getenv('GEMINI_API_KEY') ?: '');
define('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent');

// ============================================
// RUTAS BASE - CAMBIAR SEGÚN TU HOSTING
// ============================================
// Para localhost/XAMPP: '/tubi-php/'
// Para dominio raíz: '/'
// Para subdirectorio: '/mi-carpeta/'
define('BASE_URL', '/');
define('BASE_PATH', __DIR__ . '/../');

// Roles disponibles
define('ROLES', ['alumno', 'tutor', 'escuela', 'proveedor', 'admin']);

// Usuarios de demo (en producción usar base de datos)
$DEMO_USERS = [
    'alumno' => [
        'email' => 'alumno@tubi.com',
        'password' => 'demo123',
        'nombre' => 'Juan Pérez',
        'dni' => '45123456',
        'role' => 'alumno',
        'escuela' => 'Escuela N° 123 "San Martín"'
    ],
    'tutor' => [
        'email' => 'tutor@tubi.com',
        'password' => 'demo123',
        'nombre' => 'María González',
        'dni' => '20123456',
        'role' => 'tutor'
    ],
    'escuela' => [
        'email' => 'escuela@tubi.com',
        'password' => 'demo123',
        'nombre' => 'Escuela N° 123',
        'cue' => '740001234',
        'role' => 'escuela'
    ],
    'proveedor' => [
        'email' => 'proveedor@tubi.com',
        'password' => 'demo123',
        'nombre' => 'Logística San Luis S.A.',
        'cuit' => '30-12345678-9',
        'role' => 'proveedor'
    ],
    'admin' => [
        'email' => 'admin@tubi.com',
        'password' => 'admin123',
        'nombre' => 'Administrador TuBi',
        'role' => 'admin'
    ]
];

// Función helper para verificar autenticación
function isLoggedIn() {
    return isset($_SESSION['user']) && !empty($_SESSION['user']);
}

// Función para obtener usuario actual
function getCurrentUser() {
    return $_SESSION['user'] ?? null;
}

// Función para verificar rol
function hasRole($requiredRole) {
    $user = getCurrentUser();
    return $user && $user['role'] === $requiredRole;
}

// Función para redirigir
function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit;
}

// Función para escapar HTML
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Función para mostrar mensajes flash
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

/**
 * Generar token CSRF
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validar token CSRF
 */
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Sanitizar input
 */
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validar entero
 */
function validateInt($value, $default = 0) {
    return filter_var($value, FILTER_VALIDATE_INT) !== false ? (int)$value : $default;
}
