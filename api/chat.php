<?php
/**
 * TUBI 2026 - API de Chat con Google Gemini
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../services/GeminiService.php';

header('Content-Type: application/json');

// Verificar autenticación
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

$user = getCurrentUser();
$role = $user['role'];

// Manejar acción de bienvenida
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'welcome') {
    $gemini = new GeminiService();
    echo json_encode([
        'success' => true,
        'content' => $gemini->getWelcomeMessage($role)
    ]);
    exit;
}

// Manejar mensajes de chat
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['message']) || empty(trim($input['message']))) {
        echo json_encode(['error' => 'Mensaje vacío']);
        exit;
    }

    $message = trim($input['message']);
    $history = $input['history'] ?? [];

    // Validar longitud del mensaje
    if (strlen($message) > 2000) {
        echo json_encode(['error' => 'Mensaje demasiado largo']);
        exit;
    }

    $gemini = new GeminiService();
    $response = $gemini->chat($message, $role, $history);

    echo json_encode($response);
    exit;
}

// Método no permitido
http_response_code(405);
echo json_encode(['error' => 'Método no permitido']);
