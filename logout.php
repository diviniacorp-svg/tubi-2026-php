<?php
/**
 * TUBI 2026 - Logout
 */
require_once __DIR__ . '/config/config.php';

// Destruir sesión
session_destroy();

// Redirigir al login
header("Location: " . BASE_URL . "login.php");
exit;
