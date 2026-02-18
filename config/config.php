<?php
/**
 * TUBI 2026 - Configuracion Principal
 * Sistema de Gestion de Bicicletas - San Luis
 * Compatible con PHP 5.4+
 */

// Configuracion de sesion segura
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 0);
    ini_set('session.use_strict_mode', 1);
    session_start();
}

// Zona horaria
date_default_timezone_set('America/Argentina/San_Luis');

// ============================================
// CONFIGURACION PARA DEPLOY
// ============================================
define('ENVIRONMENT', 'development');

if (ENVIRONMENT === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Configuracion de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'tubi_2026');
define('DB_USER', 'root');
define('DB_PASS', '');

// ============================================
// RUTAS BASE - AUTO-DETECCION
// ============================================
$_projectRoot = str_replace('\\', '/', realpath(__DIR__ . '/../'));
$_docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ? $_SERVER['DOCUMENT_ROOT'] : __DIR__ . '/../'));
$_baseUrl = '/';
if ($_docRoot && strpos($_projectRoot, $_docRoot) === 0 && $_projectRoot !== $_docRoot) {
    $_baseUrl = substr($_projectRoot, strlen($_docRoot));
    $_baseUrl = '/' . trim($_baseUrl, '/') . '/';
}
define('BASE_URL', $_baseUrl);
define('BASE_PATH', __DIR__ . '/../');

// Roles disponibles (como string separado, compatible PHP 5.4)
$GLOBALS['TUBI_ROLES'] = array('alumno', 'tutor', 'escuela', 'proveedor', 'admin');

// Incluir sistema de base de datos
require_once __DIR__ . '/db.php';

// Incluir sistema de datos (funciones helper)
require_once __DIR__ . '/data.php';

// Auto-instalar BD si no existe
if (!dbIsInstalled()) {
    $installResult = dbInstall();
    if ($installResult !== true) {
        die('<h2>Error instalando base de datos</h2><p>' . $installResult . '</p><p>Asegurate de tener MySQL corriendo y el usuario configurado en config.php</p>');
    }
}

// Usuarios demo (fallback si BD no tiene datos)
$GLOBALS['DEMO_USERS'] = array(
    'alumno' => array(
        'email' => 'alumno@tubi.com',
        'password' => 'demo123',
        'nombre' => 'Juan Perez',
        'dni' => '45123456',
        'role' => 'alumno',
        'escuela' => 'Escuela N 123 "San Martin"'
    ),
    'tutor' => array(
        'email' => 'tutor@tubi.com',
        'password' => 'demo123',
        'nombre' => 'Maria Gonzalez',
        'dni' => '20123456',
        'role' => 'tutor'
    ),
    'escuela' => array(
        'email' => 'escuela@tubi.com',
        'password' => 'demo123',
        'nombre' => 'Escuela N 123',
        'cue' => '740001234',
        'role' => 'escuela'
    ),
    'proveedor' => array(
        'email' => 'proveedor@tubi.com',
        'password' => 'demo123',
        'nombre' => 'Logistica San Luis S.A.',
        'cuit' => '30-12345678-9',
        'role' => 'proveedor'
    ),
    'admin' => array(
        'email' => 'admin@tubi.com',
        'password' => 'admin123',
        'nombre' => 'Administrador TuBi',
        'role' => 'admin'
    )
);

// ============================================
// FUNCIONES HELPER
// ============================================

function isLoggedIn() {
    return isset($_SESSION['user']) && !empty($_SESSION['user']);
}

function getCurrentUser() {
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

function hasRole($requiredRole) {
    $user = getCurrentUser();
    return $user && $user['role'] === $requiredRole;
}

function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit;
}

function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function setFlash($type, $message) {
    $_SESSION['flash'] = array('type' => $type, 'message' => $message);
}

function getFlash() {
    $flash = isset($_SESSION['flash']) ? $_SESSION['flash'] : null;
    unset($_SESSION['flash']);
    return $flash;
}

function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        // Compatible PHP 5.4 (sin random_bytes)
        $_SESSION['csrf_token'] = md5(uniqid(mt_rand(), true) . time());
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && $_SESSION['csrf_token'] === $token;
}

function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function validateInt($value, $default = 0) {
    return filter_var($value, FILTER_VALIDATE_INT) !== false ? (int)$value : $default;
}
