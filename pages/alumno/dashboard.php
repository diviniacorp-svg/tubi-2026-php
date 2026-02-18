<?php
/**
 * TUBI 2026 - Dashboard Estudiante
 * Panel principal con carnet, bicicleta, gamificacion y retos diarios
 * Compatible con PHP 5.4+ y MySQL
 */
require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn() || !hasRole('alumno')) {
    redirect('login.php?role=alumno');
}

$user = getCurrentUser();
$pageTitle = 'Mi TuBi';

// Obtener datos del alumno desde BD
$userId = isset($user['id']) ? $user['id'] : 1;
$alumnoData = getAlumnoByUsuario($userId);

if (!$alumnoData) {
    $alumnoData = getAlumno(1);
}
if (!$alumnoData) {
    $alumnoData = array(
        'id' => 1,
        'nombre' => $user['nombre'],
        'dni' => isset($user['dni']) ? $user['dni'] : '40123456',
        'escuela_id' => 1,
        'curso' => '5 B',
        'estado' => 'asignado',
        'modulos_completados' => 3,
        'puntos' => 150,
        'racha' => 7,
        'fecha_registro' => '2026-01-15',
    );
}

$alumnoId = $alumnoData['id'];

// Escuela del alumno
$escuelaData = null;
if (!empty($alumnoData['escuela_id'])) {
    $escuelaData = getEscuela($alumnoData['escuela_id']);
}
$escuelaNombre = $escuelaData ? $escuelaData['nombre'] : 'Escuela N 123';

// Buscar bicicleta asignada
$biciData = dbFetchOne('SELECT * FROM bicicletas WHERE alumno_id = ?', array($alumnoId));
if (!$biciData) {
    $biciData = array(
        'codigo' => 'TUBI-SL-00123',
        'serie' => 'SN-2026-00123',
        'rodado' => 26,
        'color' => 'Azul TuBi',
        'estado' => 'entregada',
        'fecha_entrega' => '2026-02-01',
        'garantia_hasta' => '2028-02-01',
    );
}

// Datos del alumno
$modulosCompletados = isset($alumnoData['modulos_completados']) ? (int)$alumnoData['modulos_completados'] : 3;
$totalModulos = 8;
$puntos = isset($alumnoData['puntos']) ? (int)$alumnoData['puntos'] : 150;
$racha = isset($alumnoData['racha']) ? (int)$alumnoData['racha'] : 7;

// Logros
$logros = getData('logros');
$logrosObtenidos = array_slice($logros, 0, 3);

// Retos completados hoy
$retoMatutinoHecho = retoCompletadoHoy($alumnoId, 'checklist');
$retoNocturnoHecho = retoCompletadoHoy($alumnoId, 'trivia');

// Videos completados (para desbloquear quiz)
if (!isset($_SESSION['tubi_videos_vistos'])) {
    $_SESSION['tubi_videos_vistos'] = 0;
}
$videosVistos = $_SESSION['tubi_videos_vistos'];
$totalVideos = 3;
$quizDesbloqueado = $videosVistos >= $totalVideos;

// Sistema de retos
$horaActual = (int)date('H');
$esManana = $horaActual >= 6 && $horaActual < 12;
$esNoche = $horaActual >= 18 || $horaActual < 6;

$retoMatutino = array(
    'id' => 'check_bici',
    'nombre' => 'Chequeo de tu TuBi',
    'descripcion' => 'Revisa frenos, luces y cubiertas antes de salir',
    'duracion' => 2,
    'puntos' => 30,
    'icono' => '&#128295;',
    'horario' => '6:00 a 12:00',
    'activo' => $esManana,
);

$retoNocturno = array(
    'id' => 'trivia_vial',
    'nombre' => 'Trivia Vial Nocturna',
    'descripcion' => '5 preguntas rapidas sobre seguridad vial',
    'duracion' => 3,
    'puntos' => 50,
    'icono' => '&#127769;',
    'horario' => '18:00 a 6:00',
    'activo' => $esNoche,
    'requiere_videos' => true,
    'desbloqueado' => $quizDesbloqueado,
);

// Datos para checklist y trivia
$checklistItems = array(
    array('id' => 'frenos', 'label' => 'Frenos funcionando correctamente', 'icono' => '&#128721;'),
    array('id' => 'luces', 'label' => 'Luces delantera y trasera', 'icono' => '&#128161;'),
    array('id' => 'cubiertas', 'label' => 'Cubiertas infladas y sin cortes', 'icono' => '&#11093;'),
    array('id' => 'cadena', 'label' => 'Cadena limpia y lubricada', 'icono' => '&#9939;'),
    array('id' => 'asiento', 'label' => 'Asiento firme y a tu altura', 'icono' => '&#128186;'),
    array('id' => 'casco', 'label' => 'Casco en buen estado', 'icono' => '&#9937;'),
);

$triviaPreguntas = array(
    array('pregunta' => 'Por donde debe circular un ciclista en la ciudad?',
        'opciones' => array('Por la vereda', 'Por la ciclovia o borde derecho', 'Por el centro de la calle', 'Por cualquier lado'),
        'correcta' => 1, 'puntos' => 10),
    array('pregunta' => 'Que elemento es OBLIGATORIO para circular de noche?',
        'opciones' => array('Radio', 'Luces y reflectantes', 'Bocina', 'Espejo retrovisor'),
        'correcta' => 1, 'puntos' => 10),
    array('pregunta' => 'Que significa una luz amarilla del semaforo?',
        'opciones' => array('Acelerar para pasar', 'Precaucion, va a cambiar a rojo', 'Que los peatones crucen', 'Nada importante'),
        'correcta' => 1, 'puntos' => 10),
    array('pregunta' => 'Cual es la distancia segura al adelantar un ciclista?',
        'opciones' => array('50 cm', '1 metro', '1.5 metros minimo', 'No importa la distancia'),
        'correcta' => 2, 'puntos' => 10),
    array('pregunta' => 'Que debes hacer antes de girar en bicicleta?',
        'opciones' => array('Girar rapido', 'Senalizar con el brazo y mirar', 'Frenar de golpe', 'Tocar bocina'),
        'correcta' => 1, 'puntos' => 10),
);

$rutasDisponibles = array(
    array('nombre' => 'Circuito del Centro', 'distancia' => '3.2 km', 'dificultad' => 'Facil', 'icono' => '&#127961;',
        'descripcion' => 'Recorrido por el microcentro puntano con ciclovia protegida'),
    array('nombre' => 'Costanera del Rio', 'distancia' => '5.8 km', 'dificultad' => 'Moderado', 'icono' => '&#127754;',
        'descripcion' => 'Paseo por la costanera del Rio San Luis hasta el puente'),
    array('nombre' => 'Circuito Potrero de los Funes', 'distancia' => '14 km', 'dificultad' => 'Dificil', 'icono' => '&#9968;',
        'descripcion' => 'Vuelta completa al circuito internacional, ideal para entrenamiento'),
    array('nombre' => 'Parque de las Naciones', 'distancia' => '4.5 km', 'dificultad' => 'Facil', 'icono' => '&#127795;',
        'descripcion' => 'Recorrido recreativo por el parque con paradas de descanso'),
);

// ============================================
// AJAX POST HANDLER
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
              strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($isAjax && $action === 'complete_checklist') {
        $items = isset($_POST['items']) ? $_POST['items'] : '';
        $itemsArr = $items ? explode(',', $items) : array();
        $totalItems = count($checklistItems);
        $marcados = count($itemsArr);
        $puntosGanados = ($marcados >= $totalItems) ? 30 : round(($marcados / $totalItems) * 30);

        // Registrar reto en BD
        registrarReto($alumnoId, 'checklist', $puntosGanados, $items);

        // Sumar puntos al alumno
        $nuevosPuntos = $puntos + $puntosGanados;
        $nuevaRacha = $racha + 1;
        updateAlumno($alumnoId, array('puntos' => $nuevosPuntos, 'racha' => $nuevaRacha));

        header('Content-Type: application/json');
        echo json_encode(array(
            'success' => true,
            'message' => 'Checklist completado! +' . $puntosGanados . ' pts',
            'puntos_ganados' => $puntosGanados,
            'puntos_total' => $nuevosPuntos,
            'racha' => $nuevaRacha,
            'items_marcados' => $marcados,
            'items_total' => $totalItems
        ));
        exit;
    }

    if ($isAjax && $action === 'submit_trivia') {
        $respuestas = isset($_POST['respuestas']) ? $_POST['respuestas'] : '';
        $respArr = $respuestas ? explode(',', $respuestas) : array();
        $puntosGanados = 0;
        $correctas = 0;

        for ($i = 0; $i < count($triviaPreguntas); $i++) {
            if (isset($respArr[$i]) && (int)$respArr[$i] === $triviaPreguntas[$i]['correcta']) {
                $puntosGanados += $triviaPreguntas[$i]['puntos'];
                $correctas++;
            }
        }

        // Registrar reto en BD
        registrarReto($alumnoId, 'trivia', $puntosGanados, $respuestas);

        // Sumar puntos
        $nuevosPuntos = $puntos + $puntosGanados;
        $nuevaRacha = $racha + 1;
        updateAlumno($alumnoId, array('puntos' => $nuevosPuntos, 'racha' => $nuevaRacha));

        header('Content-Type: application/json');
        echo json_encode(array(
            'success' => true,
            'message' => 'Trivia completada! +' . $puntosGanados . ' pts',
            'puntos_ganados' => $puntosGanados,
            'puntos_total' => $nuevosPuntos,
            'racha' => $nuevaRacha,
            'correctas' => $correctas,
            'total_preguntas' => count($triviaPreguntas)
        ));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle); ?> - TuBi 2026</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/tubi-institucional.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>&#128690;</text></svg>">
    <style>
        /* Base components */
        .app-container { max-width: 1200px; margin: 0 auto; }
        .app-content { padding: 0 1.5rem 1.5rem; }
        .badge { padding: 0.2rem 0.6rem; border-radius: 100px; font-size: 0.7rem; font-weight: 600; }
        .badge-success { background: #22c55e; color: white; }
        .btn { padding: 0.5rem 1rem; border-radius: 8px; font-weight: 600; font-size: 0.85rem; cursor: pointer; border: none; transition: 0.2s; display: inline-flex; align-items: center; gap: 0.375rem; }
        .btn-secondary { background: #354393; color: white; }
        .btn-secondary:hover { background: #2a3678; }
        .btn-block { width: 100%; justify-content: center; }
        .btn-icon { background: none; border: none; cursor: pointer; color: #414242; padding: 0.25rem; text-decoration: none; display: flex; }
        .btn-ghost { background: #e5e7eb; color: #414242; }
        .btn-ghost:hover { background: #d1d5db; }
        .btn-success { background: #22c55e; color: white; }
        .btn-success:hover { background: #16a34a; }
        .progress-bar { height: 8px; background: #c8dfe9; border-radius: 4px; overflow: hidden; }
        .progress-fill { height: 100%; background: linear-gradient(90deg, #354393, #4aacc4); border-radius: 4px; transition: width 0.5s; }

        /* === HEADER === */
        .student-header { background: linear-gradient(135deg, #354393 0%, #4aacc4 100%); padding: 0.6rem 1.5rem 0; color: white; }
        .student-header-inner { max-width: 900px; margin: 0 auto; }
        .student-header-top { display: flex; align-items: center; justify-content: space-between; padding-bottom: 0.5rem; }
        .student-header-left { display: flex; align-items: center; gap: 0.6rem; }
        .student-header-left .tubi-logo { height: 32px; width: auto; }
        .student-header-left .header-sep { opacity: 0.3; font-size: 1.2rem; font-weight: 200; }
        .student-header-left .header-badge { padding: 0.15rem 0.6rem; border-radius: 100px; font-size: 0.7rem; font-weight: 600; background: rgba(255,255,255,0.2); color: white; }
        .student-header-right { display: flex; align-items: center; gap: 0.6rem; }
        .student-header-right .user-avatar-sm { width: 28px; height: 28px; border-radius: 50%; background: rgba(255,255,255,0.25); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.75rem; }
        .student-header-right .user-name { font-size: 0.8rem; font-weight: 500; color: white; }
        .student-header-right .btn-icon { color: rgba(255,255,255,0.7); }
        .student-header-right .btn-icon:hover { color: white; }
        .student-nav { display: flex; gap: 0; }
        .student-nav a { padding: 0.5rem 1rem; text-decoration: none; font-size: 0.8rem; font-weight: 500; color: rgba(255,255,255,0.65); border-bottom: 2px solid transparent; transition: 0.2s; }
        .student-nav a:hover { color: white; border-bottom-color: rgba(255,255,255,0.3); }
        .student-nav a.active { color: white; border-bottom-color: white; font-weight: 600; }

        /* === CARDS === */
        .data-card-header.estudiante { background: linear-gradient(135deg, #35439315 0%, #35439325 100%); }
        .data-card-header.bicicleta { background: linear-gradient(135deg, #4aacc415 0%, #4aacc425 100%); }
        .data-card-icon.estudiante { background: linear-gradient(135deg, #354393, #4a5aab); box-shadow: 0 4px 12px rgba(53, 67, 147, 0.3); }
        .data-card-icon.bicicleta { background: linear-gradient(135deg, #4aacc4, #3d95ab); box-shadow: 0 4px 12px rgba(74, 172, 196, 0.3); }
        .section-title { font-size: 1.1rem; font-weight: 700; color: #354393; margin: 1.5rem 0 0.75rem; display: flex; align-items: center; gap: 0.5rem; }
        .section-title:first-child { margin-top: 0; }
        .section-title .section-icon { font-size: 1.25rem; }
        .data-card { background: linear-gradient(135deg, #eef6fa 0%, #e4f1f7 100%); border: 1px solid #c8dfe9; border-radius: 16px; overflow: hidden; }
        .data-card-header { padding: 1rem 1.25rem; display: flex; align-items: center; gap: 0.75rem; border-bottom: 1px solid #c8dfe9; }
        .data-card-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
        .data-card-title { flex: 1; }
        .data-card-title h3 { font-size: 1rem; font-weight: 600; color: #414242; margin: 0; }
        .data-card-body { padding: 1.25rem; }
        .data-row { display: flex; justify-content: space-between; padding: 0.625rem 0; border-bottom: 1px dashed #c8dfe9; }
        .data-row:last-child { border-bottom: none; }
        .data-label { font-size: 0.8125rem; color: #8a9aaa; display: flex; align-items: center; gap: 0.5rem; }
        .data-value { font-size: 0.875rem; font-weight: 500; color: #414242; text-align: right; }
        .data-card-qr { display: flex; justify-content: center; padding: 1rem; background: #dce9f1; margin: 0 1.25rem 1.25rem; border-radius: 12px; }
        .qr-code-display { width: 100px; height: 100px; background: white; border-radius: 8px; padding: 8px; display: flex; align-items: center; justify-content: center; }

        /* Grid */
        .top-cards-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; align-items: start; }
        .top-cards-grid > .data-card { margin-bottom: 0; }

        /* Gamification */
        .gamification-card { background: #eef6fa; border: 1px solid #c8dfe9; border-radius: 16px; overflow: hidden; }
        .gamification-card-header { background: linear-gradient(135deg, #354393 0%, #4aacc4 100%); padding: 1rem 1.25rem; color: white; display: flex; align-items: center; gap: 0.75rem; }
        .gamification-card-header h3 { margin: 0; font-size: 1rem; font-weight: 600; }
        .gamification-card-body { padding: 1.25rem; }
        .gamification-points { text-align: center; padding: 0.75rem 0; border-bottom: 1px solid #c8dfe9; margin-bottom: 1rem; }
        .gamification-points .points-value { font-size: 2rem; font-weight: 700; color: #4aacc4; }
        .gamification-points .points-label { font-size: 0.8125rem; color: #6b7b8a; }
        .gamification-racha { background: linear-gradient(135deg, #354393 0%, #4aacc4 100%); border-radius: 12px; padding: 1rem; color: white; text-align: center; margin-bottom: 1rem; }
        .gamification-racha .racha-value { font-size: 1.75rem; font-weight: 700; line-height: 1; }
        .gamification-racha .racha-label { font-size: 0.8125rem; opacity: 0.9; margin-top: 0.25rem; }
        .gamification-racha .racha-days { display: flex; justify-content: center; gap: 0.375rem; margin-top: 0.75rem; }
        .gamification-racha .racha-day { width: 26px; height: 26px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; font-size: 0.7rem; }
        .gamification-racha .racha-day.active { background: white; color: #354393; }
        .gamification-progress { margin-bottom: 1rem; }
        .gamification-progress .gami-progress-label { font-size: 0.875rem; font-weight: 600; color: #414242; margin-bottom: 0.5rem; }
        .gamification-progress .progress-text { font-size: 0.8125rem; color: #6b7b8a; margin-top: 0.5rem; }
        .gamification-logros { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; padding-top: 0.75rem; border-top: 1px solid #c8dfe9; }
        .gamification-logros .logro-badge { font-size: 1.5rem; }
        .gamification-logros a { font-size: 0.8125rem; color: #4aacc4; text-decoration: none; margin-left: auto; }

        /* Retos */
        .retos-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .reto-card { background: #eef6fa; border: 1px solid #c8dfe9; border-radius: 16px; overflow: hidden; transition: transform 0.2s, box-shadow 0.2s; }
        .reto-card:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(53,67,147,0.1); }
        .reto-card.inactivo { opacity: 0.5; pointer-events: none; }
        .reto-card.bloqueado { opacity: 0.6; }
        .reto-card-header { padding: 1rem 1.25rem; display: flex; align-items: center; gap: 0.75rem; border-bottom: 1px solid #d0e3ed; }
        .reto-card-header.matutino { background: linear-gradient(135deg, #4aacc420 0%, #4aacc410 100%); }
        .reto-card-header.nocturno { background: linear-gradient(135deg, #35439320 0%, #35439310 100%); }
        .reto-card-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
        .reto-card-icon.matutino { background: linear-gradient(135deg, #4aacc4, #3d95ab); color: white; }
        .reto-card-icon.nocturno { background: linear-gradient(135deg, #354393, #4a5aab); color: white; }
        .reto-card-info h4 { margin: 0; font-size: 0.95rem; font-weight: 600; color: #414242; }
        .reto-card-info span { font-size: 0.75rem; color: #888; }
        .reto-card-body { padding: 1rem 1.25rem; }
        .reto-card-body p { font-size: 0.85rem; color: #414242; margin: 0 0 0.75rem; }
        .reto-card-meta { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
        .reto-card-meta span { font-size: 0.8rem; color: #888; display: flex; align-items: center; gap: 0.25rem; }
        .reto-card-meta .reto-pts { color: #4aacc4; font-weight: 600; }
        .reto-estado { display: inline-block; padding: 0.35rem 0.75rem; border-radius: 100px; font-size: 0.75rem; font-weight: 600; text-align: center; }
        .reto-estado.activo { background: #354393; color: white; cursor: pointer; border: none; }
        .reto-estado.activo:hover { background: #2a3678; }
        .reto-estado.inactivo { background: #d0e3ed; color: #888; }
        .reto-estado.bloqueado { background: #ffecd2; color: #c77d2e; }
        .reto-estado.completado { background: #22c55e; color: white; }

        /* Accesos */
        .accesos-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.75rem; }
        .acceso-item { display: flex; flex-direction: column; align-items: center; padding: 1.25rem 0.75rem; background: #eef6fa; border: 1px solid #c8dfe9; border-radius: 16px; text-decoration: none; color: #414242; transition: transform 0.2s, box-shadow 0.2s; text-align: center; cursor: pointer; }
        .acceso-item:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(53,67,147,0.1); }
        .acceso-icon { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 0.5rem; }
        .acceso-icon.azul { background: linear-gradient(135deg, #354393, #4a5aab); }
        .acceso-icon.turquesa { background: linear-gradient(135deg, #4aacc4, #3d95ab); }
        .acceso-icon.verde { background: linear-gradient(135deg, #22c55e, #10b981); }
        .acceso-icon.naranja { background: linear-gradient(135deg, #f59e0b, #f97316); }
        .acceso-item span { font-size: 0.8rem; font-weight: 500; }

        /* === MODALES === */
        .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); z-index: 1000; display: none; align-items: center; justify-content: center; }
        .modal-overlay.visible { display: flex; }
        .modal-box { background: white; border-radius: 16px; max-width: 500px; width: 90%; max-height: 85vh; overflow-y: auto; animation: modalIn 0.2s ease; }
        @keyframes modalIn { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .modal-header { padding: 1.25rem; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
        .modal-header h3 { margin: 0; font-size: 1.1rem; font-weight: 700; color: #354393; }
        .modal-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #888; padding: 0; line-height: 1; }
        .modal-close:hover { color: #414242; }
        .modal-body { padding: 1.25rem; }
        .modal-footer { padding: 1rem 1.25rem; border-top: 1px solid #e5e7eb; display: flex; gap: 0.5rem; justify-content: flex-end; }

        /* Checklist */
        .checklist-item { display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; border-radius: 10px; border: 1px solid #e5e7eb; margin-bottom: 0.5rem; cursor: pointer; transition: 0.2s; }
        .checklist-item:hover { background: #f0f7fa; }
        .checklist-item.checked { background: #ecfdf5; border-color: #22c55e; }
        .checklist-check { width: 22px; height: 22px; border-radius: 6px; border: 2px solid #d1d5db; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; flex-shrink: 0; }
        .checklist-item.checked .checklist-check { background: #22c55e; border-color: #22c55e; color: white; }
        .checklist-label { font-size: 0.9rem; color: #414242; }
        .checklist-icon { font-size: 1.2rem; }

        /* Trivia */
        .trivia-progress { text-align: center; margin-bottom: 1rem; font-size: 0.85rem; color: #888; }
        .trivia-progress-bar { height: 6px; background: #e5e7eb; border-radius: 3px; overflow: hidden; margin-top: 0.5rem; }
        .trivia-progress-fill { height: 100%; background: linear-gradient(90deg, #354393, #4aacc4); border-radius: 3px; transition: width 0.3s; }
        .trivia-question { font-size: 1rem; font-weight: 600; color: #354393; margin-bottom: 1rem; text-align: center; }
        .trivia-option { width: 100%; padding: 0.75rem 1rem; border: 2px solid #e5e7eb; border-radius: 10px; background: white; cursor: pointer; text-align: left; margin-bottom: 0.5rem; font-size: 0.9rem; font-family: 'Ubuntu', sans-serif; transition: 0.2s; }
        .trivia-option:hover { border-color: #4aacc4; background: #f0f7fa; }
        .trivia-option.correct { border-color: #22c55e; background: #ecfdf5; }
        .trivia-option.incorrect { border-color: #ef4444; background: #fef2f2; }
        .trivia-option.disabled { pointer-events: none; opacity: 0.7; }
        .trivia-result { text-align: center; padding: 1.5rem 0; }
        .trivia-result .result-score { font-size: 2.5rem; font-weight: 700; color: #4aacc4; }
        .trivia-result .result-label { font-size: 0.9rem; color: #888; margin-top: 0.25rem; }
        .trivia-result .result-detail { font-size: 1rem; color: #414242; margin-top: 1rem; }

        /* Rutas */
        .ruta-card { padding: 1rem; border: 1px solid #e5e7eb; border-radius: 12px; margin-bottom: 0.75rem; transition: 0.2s; }
        .ruta-card:hover { border-color: #4aacc4; background: #f0f7fa; }
        .ruta-header { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem; }
        .ruta-icon { font-size: 1.5rem; }
        .ruta-name { font-weight: 600; color: #354393; font-size: 0.95rem; }
        .ruta-meta { display: flex; gap: 0.75rem; margin-bottom: 0.5rem; }
        .ruta-meta span { font-size: 0.8rem; color: #888; }
        .ruta-badge { padding: 0.15rem 0.5rem; border-radius: 100px; font-size: 0.7rem; font-weight: 600; }
        .ruta-badge.facil { background: #ecfdf5; color: #22c55e; }
        .ruta-badge.moderado { background: #fef3c7; color: #f59e0b; }
        .ruta-badge.dificil { background: #fef2f2; color: #ef4444; }
        .ruta-desc { font-size: 0.85rem; color: #666; }

        /* QR Modal */
        .qr-modal-display { text-align: center; padding: 1rem; background: #f8fafc; border-radius: 12px; margin-bottom: 1rem; }
        .qr-modal-display svg { width: 180px; height: 180px; }

        /* Toast */
        .toast-container { position: fixed; top: 1rem; right: 1rem; z-index: 2000; }
        .toast { padding: 0.75rem 1.25rem; border-radius: 10px; color: white; font-weight: 600; font-size: 0.85rem; margin-bottom: 0.5rem; transform: translateX(120%); transition: transform 0.3s; box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .toast.show { transform: translateX(0); }
        .toast.success { background: #22c55e; }
        .toast.info { background: #354393; }
        .toast.error { background: #ef4444; }

        /* Responsive */
        @media (max-width: 1024px) {
            .top-cards-grid { grid-template-columns: 1fr 1fr; }
            .top-cards-grid > :nth-child(3) { grid-column: 1 / -1; }
        }
        @media (max-width: 640px) {
            .student-header-top { flex-direction: column; gap: 0.4rem; text-align: center; }
            .student-header-left { justify-content: center; }
            .student-header-left .tubi-logo { height: 24px; }
            .student-header-right { justify-content: center; }
            .student-nav { justify-content: center; }
            .student-nav a { padding: 0.5rem 0.6rem; font-size: 0.75rem; }
            .top-cards-grid { grid-template-columns: 1fr; }
            .retos-grid { grid-template-columns: 1fr; }
            .accesos-grid { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body data-base-url="<?php echo BASE_URL; ?>">
    <div class="student-header">
        <div class="student-header-inner">
            <div class="student-header-top">
                <div class="student-header-left">
                    <img src="<?php echo BASE_URL; ?>assets/img/tubi-logo-blanco.png" alt="TuBi" class="tubi-logo">
                    <span class="header-sep">|</span>
                    <span class="header-badge">Estudiante</span>
                </div>
                <div class="student-header-right">
                    <button class="btn-icon" id="themeToggle" title="Cambiar tema" style="color: white;">
                        <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                        </svg>
                        <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18" style="display:none;">
                            <circle cx="12" cy="12" r="5"/>
                            <path d="M12 1v2m0 18v2M4.22 4.22l1.42 1.42m12.72 12.72l1.42 1.42M1 12h2m18 0h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
                        </svg>
                    </button>
                    <div class="user-avatar-sm"><?php echo strtoupper(substr($user['nombre'], 0, 1)); ?></div>
                    <span class="user-name"><?php echo e($user['nombre']); ?></span>
                    <a href="<?php echo BASE_URL; ?>logout.php" class="btn-icon" title="Salir">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>
                        </svg>
                    </a>
                </div>
            </div>
            <nav class="student-nav">
                <a href="<?php echo BASE_URL; ?>pages/alumno/dashboard.php" class="active">Mi TuBi</a>
                <a href="<?php echo BASE_URL; ?>pages/alumno/aprender.php">Aprende</a>
                <a href="<?php echo BASE_URL; ?>pages/alumno/logros.php">Logros</a>
            </nav>
        </div>
    </div>

    <div class="app-container">
        <main class="app-content" style="padding-top: 1.5rem;">
            <div class="dashboard-estudiante">

                <!-- Mensaje de Bienvenida -->
                <div class="welcome-banner" style="background: linear-gradient(135deg, #354393 0%, #4aacc4 100%); padding: 1.25rem 1.5rem; border-radius: 12px; color: white; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 1rem;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="32" height="32" style="flex-shrink: 0;">
                        <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                    </svg>
                    <div>
                        <h2 style="margin: 0; font-size: 1.4rem; font-weight: 700;">Bienvenido Estudiante</h2>
                        <p style="margin: 0; opacity: 0.9; font-size: 0.9rem;">Tu panel de control TuBi &mdash; <?php echo e($user['nombre']); ?></p>
                    </div>
                </div>

                <h2 class="section-title"><span class="section-icon">&#128203;</span> Mis Datos</h2>
                <div class="top-cards-grid">
                    <!-- Carnet -->
                    <div class="data-card">
                        <div class="data-card-header estudiante">
                            <div class="data-card-icon estudiante">&#128100;</div>
                            <div class="data-card-title"><h3>Mi carnet TuBi</h3></div>
                            <span class="badge badge-success">ACTIVO</span>
                        </div>
                        <div class="data-card-body">
                            <div class="data-row"><span class="data-label">Nombre</span><span class="data-value"><?php echo e($user['nombre']); ?></span></div>
                            <div class="data-row"><span class="data-label">DNI</span><span class="data-value"><?php echo e(isset($alumnoData['dni']) ? $alumnoData['dni'] : '40.123.456'); ?></span></div>
                            <div class="data-row"><span class="data-label">Escuela</span><span class="data-value"><?php echo e($escuelaNombre); ?></span></div>
                            <div class="data-row"><span class="data-label">Curso</span><span class="data-value"><?php echo e(isset($alumnoData['curso']) ? $alumnoData['curso'] : '5 B'); ?></span></div>
                            <div class="data-row"><span class="data-label">Inscripcion</span><span class="data-value"><?php echo date('d/m/Y', strtotime(isset($alumnoData['fecha_registro']) ? $alumnoData['fecha_registro'] : '2026-01-15')); ?></span></div>
                        </div>
                    </div>

                    <!-- Bicicleta -->
                    <div class="data-card">
                        <div class="data-card-header bicicleta">
                            <div class="data-card-icon bicicleta">&#128690;</div>
                            <div class="data-card-title"><h3>Mi TuBi</h3></div>
                            <span class="badge badge-success">ENTREGADA</span>
                        </div>
                        <div class="data-card-body">
                            <div class="data-row"><span class="data-label">Codigo</span><span class="data-value" style="color: #354393; font-weight: 700;"><?php echo e($biciData['codigo']); ?></span></div>
                            <div class="data-row"><span class="data-label">Serie</span><span class="data-value"><?php echo e(isset($biciData['serie']) ? $biciData['serie'] : 'SN-2026-00123'); ?></span></div>
                            <div class="data-row"><span class="data-label">Rodado</span><span class="data-value">R<?php echo e($biciData['rodado']); ?></span></div>
                            <div class="data-row"><span class="data-label">Color</span><span class="data-value"><?php echo e(isset($biciData['color']) ? $biciData['color'] : 'Azul TuBi'); ?></span></div>
                            <div class="data-row"><span class="data-label">Entrega</span><span class="data-value"><?php echo date('d/m/Y', strtotime(isset($biciData['fecha_entrega']) ? $biciData['fecha_entrega'] : '2026-02-01')); ?></span></div>
                        </div>
                        <div class="data-card-qr">
                            <div class="qr-code-display">
                                <svg viewBox="0 0 100 100" width="84" height="84">
                                    <rect x="10" y="10" width="25" height="25" fill="#000"/><rect x="65" y="10" width="25" height="25" fill="#000"/>
                                    <rect x="10" y="65" width="25" height="25" fill="#000"/><rect x="45" y="45" width="10" height="10" fill="#000"/>
                                    <rect x="65" y="65" width="10" height="10" fill="#000"/><rect x="80" y="80" width="10" height="10" fill="#000"/>
                                    <rect x="15" y="15" width="15" height="15" fill="#fff"/><rect x="70" y="15" width="15" height="15" fill="#fff"/>
                                    <rect x="15" y="70" width="15" height="15" fill="#fff"/><rect x="18" y="18" width="9" height="9" fill="#000"/>
                                    <rect x="73" y="18" width="9" height="9" fill="#000"/><rect x="18" y="73" width="9" height="9" fill="#000"/>
                                </svg>
                            </div>
                        </div>
                        <div style="padding: 0 1.25rem 1.25rem;">
                            <button class="btn btn-secondary btn-block" onclick="mostrarQR()">
                                <span>&#128241;</span> Ver QR Completo
                            </button>
                        </div>
                    </div>

                    <!-- Gamificacion -->
                    <div class="gamification-card">
                        <div class="gamification-card-header">
                            <span style="font-size: 1.5rem;">&#127942;</span>
                            <div><h3>Aprende Jugando</h3></div>
                        </div>
                        <div class="gamification-card-body">
                            <div class="gamification-points">
                                <div class="points-value" id="dashPuntos"><?php echo $puntos; ?> pts</div>
                                <div class="points-label">Puntos totales</div>
                            </div>
                            <div class="gamification-racha">
                                <div style="font-size: 1.5rem;">&#128293;</div>
                                <div class="racha-value" id="dashRacha"><?php echo $racha; ?></div>
                                <div class="racha-label">dias de racha</div>
                                <div class="racha-days">
                                    <?php for ($i = 1; $i <= 7; $i++): ?>
                                    <div class="racha-day <?php echo $i <= $racha ? 'active' : ''; ?>"><?php echo $i <= $racha ? '&#10003;' : $i; ?></div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="gamification-progress">
                                <div class="gami-progress-label">Aprendizaje</div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo ($modulosCompletados / $totalModulos) * 100; ?>%"></div>
                                </div>
                                <p class="progress-text">Modulo <?php echo $modulosCompletados; ?> de <?php echo $totalModulos; ?></p>
                            </div>
                            <div class="gamification-logros">
                                <?php foreach ($logrosObtenidos as $logro): ?>
                                <span class="logro-badge"><?php echo $logro['icono']; ?></span>
                                <?php endforeach; ?>
                                <a href="<?php echo BASE_URL; ?>pages/alumno/logros.php"><?php echo count($logrosObtenidos); ?> logros &rarr;</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Retos Diarios -->
                <h2 class="section-title"><span class="section-icon">&#127918;</span> Retos Diarios</h2>
                <div class="retos-grid">
                    <!-- Reto Matutino -->
                    <div class="reto-card <?php echo !$retoMatutino['activo'] && !$retoMatutinoHecho ? 'inactivo' : ''; ?>">
                        <div class="reto-card-header matutino">
                            <div class="reto-card-icon matutino"><?php echo $retoMatutino['icono']; ?></div>
                            <div class="reto-card-info">
                                <h4><?php echo e($retoMatutino['nombre']); ?></h4>
                                <span>&#9728; Horario: <?php echo $retoMatutino['horario']; ?></span>
                            </div>
                        </div>
                        <div class="reto-card-body">
                            <p><?php echo e($retoMatutino['descripcion']); ?></p>
                            <div class="reto-card-meta">
                                <span>&#9201; <?php echo $retoMatutino['duracion']; ?> min</span>
                                <span class="reto-pts">+<?php echo $retoMatutino['puntos']; ?> pts</span>
                            </div>
                            <?php if ($retoMatutinoHecho): ?>
                            <span class="reto-estado completado" id="btnMatutino">&#10003; Completado hoy</span>
                            <?php elseif ($retoMatutino['activo']): ?>
                            <button class="reto-estado activo" id="btnMatutino" onclick="iniciarChecklist()">&#9654; Iniciar Reto</button>
                            <?php else: ?>
                            <span class="reto-estado inactivo" id="btnMatutino">&#128274; Disponible de 6:00 a 12:00</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Reto Nocturno -->
                    <div class="reto-card <?php echo !$retoNocturno['activo'] && !$retoNocturnoHecho ? 'inactivo' : ''; ?> <?php echo ($retoNocturno['activo'] && !$retoNocturno['desbloqueado'] && !$retoNocturnoHecho) ? 'bloqueado' : ''; ?>">
                        <div class="reto-card-header nocturno">
                            <div class="reto-card-icon nocturno"><?php echo $retoNocturno['icono']; ?></div>
                            <div class="reto-card-info">
                                <h4><?php echo e($retoNocturno['nombre']); ?></h4>
                                <span>&#127769; Horario: <?php echo $retoNocturno['horario']; ?></span>
                            </div>
                        </div>
                        <div class="reto-card-body">
                            <p><?php echo e($retoNocturno['descripcion']); ?></p>
                            <div class="reto-card-meta">
                                <span>&#9201; <?php echo $retoNocturno['duracion']; ?> min</span>
                                <span class="reto-pts">+<?php echo $retoNocturno['puntos']; ?> pts</span>
                            </div>
                            <?php if ($retoNocturnoHecho): ?>
                            <span class="reto-estado completado" id="btnNocturno">&#10003; Completado hoy</span>
                            <?php elseif (!$retoNocturno['activo']): ?>
                            <span class="reto-estado inactivo" id="btnNocturno">&#128274; Disponible de 18:00 a 6:00</span>
                            <?php elseif (!$retoNocturno['desbloqueado']): ?>
                            <span class="reto-estado bloqueado" id="btnNocturno">&#128250; Mira los <?php echo $totalVideos; ?> videos para desbloquear (<?php echo $videosVistos; ?>/<?php echo $totalVideos; ?>)</span>
                            <?php else: ?>
                            <button class="reto-estado activo" id="btnNocturno" onclick="iniciarTrivia()">&#9654; Iniciar Trivia</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Accesos Rapidos -->
                <h2 class="section-title"><span class="section-icon">&#9889;</span> Accesos Rapidos</h2>
                <div class="accesos-grid">
                    <a href="<?php echo BASE_URL; ?>pages/alumno/aprender.php" class="acceso-item">
                        <div class="acceso-icon turquesa">&#127918;</div>
                        <span>Aprende</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>pages/alumno/logros.php" class="acceso-item">
                        <div class="acceso-icon azul">&#127942;</div>
                        <span>Mis Logros</span>
                    </a>
                    <a href="#" class="acceso-item" onclick="mostrarQR(); return false;">
                        <div class="acceso-icon verde">&#128241;</div>
                        <span>Mi QR</span>
                    </a>
                    <a href="#" class="acceso-item" onclick="mostrarRutas(); return false;">
                        <div class="acceso-icon naranja">&#128205;</div>
                        <span>Rutas</span>
                    </a>
                </div>
            </div>
        </main>
    </div>

    <!-- MODAL QR -->
    <div class="modal-overlay" id="modalQR">
        <div class="modal-box">
            <div class="modal-header">
                <h3>&#128241; QR de tu TuBi</h3>
                <button class="modal-close" onclick="cerrarModal('modalQR')">&times;</button>
            </div>
            <div class="modal-body">
                <div class="qr-modal-display">
                    <svg viewBox="0 0 100 100" width="180" height="180">
                        <rect x="10" y="10" width="25" height="25" fill="#000"/><rect x="65" y="10" width="25" height="25" fill="#000"/>
                        <rect x="10" y="65" width="25" height="25" fill="#000"/><rect x="45" y="45" width="10" height="10" fill="#000"/>
                        <rect x="65" y="65" width="10" height="10" fill="#000"/><rect x="80" y="80" width="10" height="10" fill="#000"/>
                        <rect x="15" y="15" width="15" height="15" fill="#fff"/><rect x="70" y="15" width="15" height="15" fill="#fff"/>
                        <rect x="15" y="70" width="15" height="15" fill="#fff"/><rect x="18" y="18" width="9" height="9" fill="#000"/>
                        <rect x="73" y="18" width="9" height="9" fill="#000"/><rect x="18" y="73" width="9" height="9" fill="#000"/>
                        <rect x="40" y="10" width="5" height="5" fill="#000"/><rect x="50" y="10" width="5" height="5" fill="#000"/>
                        <rect x="40" y="20" width="5" height="5" fill="#000"/><rect x="55" y="40" width="5" height="5" fill="#000"/>
                        <rect x="40" y="55" width="5" height="5" fill="#000"/><rect x="55" y="55" width="5" height="5" fill="#000"/>
                        <rect x="70" y="45" width="5" height="5" fill="#000"/><rect x="80" y="55" width="5" height="5" fill="#000"/>
                    </svg>
                </div>
                <div class="data-row"><span class="data-label">Codigo</span><span class="data-value" style="color:#354393;font-weight:700"><?php echo e($biciData['codigo']); ?></span></div>
                <div class="data-row"><span class="data-label">Serie</span><span class="data-value"><?php echo e(isset($biciData['serie']) ? $biciData['serie'] : 'SN-2026-00123'); ?></span></div>
                <div class="data-row"><span class="data-label">Rodado</span><span class="data-value">R<?php echo e($biciData['rodado']); ?></span></div>
                <div class="data-row"><span class="data-label">Color</span><span class="data-value"><?php echo e(isset($biciData['color']) ? $biciData['color'] : 'Azul'); ?></span></div>
                <div class="data-row"><span class="data-label">Entrega</span><span class="data-value"><?php echo date('d/m/Y', strtotime(isset($biciData['fecha_entrega']) ? $biciData['fecha_entrega'] : '2026-02-01')); ?></span></div>
                <div class="data-row"><span class="data-label">Garantia</span><span class="data-value"><?php echo date('d/m/Y', strtotime(isset($biciData['garantia_hasta']) ? $biciData['garantia_hasta'] : '2028-02-01')); ?></span></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-ghost" onclick="cerrarModal('modalQR')">Cerrar</button>
            </div>
        </div>
    </div>

    <!-- MODAL CHECKLIST -->
    <div class="modal-overlay" id="modalChecklist">
        <div class="modal-box">
            <div class="modal-header">
                <h3>&#128295; Chequeo de tu TuBi</h3>
                <button class="modal-close" onclick="cerrarModal('modalChecklist')">&times;</button>
            </div>
            <div class="modal-body">
                <p style="font-size:0.85rem;color:#888;margin:0 0 1rem;">Marca cada item que hayas revisado antes de salir:</p>
                <?php foreach ($checklistItems as $idx => $item): ?>
                <div class="checklist-item" onclick="toggleCheck(this)" data-id="<?php echo $item['id']; ?>">
                    <span class="checklist-icon"><?php echo $item['icono']; ?></span>
                    <span class="checklist-label"><?php echo e($item['label']); ?></span>
                    <span class="checklist-check"></span>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="modal-footer">
                <button class="btn btn-ghost" onclick="cerrarModal('modalChecklist')">Cancelar</button>
                <button class="btn btn-success" id="btnEnviarChecklist" onclick="enviarChecklist()">Completar Revision</button>
            </div>
        </div>
    </div>

    <!-- MODAL TRIVIA -->
    <div class="modal-overlay" id="modalTrivia">
        <div class="modal-box">
            <div class="modal-header">
                <h3>&#127769; Trivia Vial</h3>
                <button class="modal-close" onclick="cerrarModal('modalTrivia')">&times;</button>
            </div>
            <div class="modal-body" id="triviaBody">
                <!-- Generado por JS -->
            </div>
        </div>
    </div>

    <!-- MODAL RUTAS -->
    <div class="modal-overlay" id="modalRutas">
        <div class="modal-box">
            <div class="modal-header">
                <h3>&#128205; Rutas Seguras en San Luis</h3>
                <button class="modal-close" onclick="cerrarModal('modalRutas')">&times;</button>
            </div>
            <div class="modal-body">
                <?php foreach ($rutasDisponibles as $ruta): ?>
                <?php
                    $badgeClass = 'facil';
                    if ($ruta['dificultad'] === 'Moderado') $badgeClass = 'moderado';
                    if ($ruta['dificultad'] === 'Dificil') $badgeClass = 'dificil';
                ?>
                <div class="ruta-card">
                    <div class="ruta-header">
                        <span class="ruta-icon"><?php echo $ruta['icono']; ?></span>
                        <span class="ruta-name"><?php echo e($ruta['nombre']); ?></span>
                    </div>
                    <div class="ruta-meta">
                        <span>&#128207; <?php echo $ruta['distancia']; ?></span>
                        <span class="ruta-badge <?php echo $badgeClass; ?>"><?php echo $ruta['dificultad']; ?></span>
                    </div>
                    <div class="ruta-desc"><?php echo e($ruta['descripcion']); ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="modal-footer">
                <button class="btn btn-ghost" onclick="cerrarModal('modalRutas')">Cerrar</button>
            </div>
        </div>
    </div>

    <!-- Toast container -->
    <div class="toast-container" id="toastContainer"></div>

    <?php include __DIR__ . '/../../includes/zocalo-footer.php'; ?>

    <script>
    // === TRIVIA DATA (from PHP) ===
    var triviaData = <?php echo json_encode($triviaPreguntas); ?>;
    var triviaRespuestas = [];
    var triviaActual = 0;

    // === MODALES ===
    function mostrarQR() {
        document.getElementById('modalQR').className = 'modal-overlay visible';
    }
    function mostrarRutas() {
        document.getElementById('modalRutas').className = 'modal-overlay visible';
    }
    function cerrarModal(id) {
        document.getElementById(id).className = 'modal-overlay';
    }

    // Cerrar modal al click fuera
    var overlays = document.querySelectorAll('.modal-overlay');
    for (var i = 0; i < overlays.length; i++) {
        overlays[i].addEventListener('click', function(e) {
            if (e.target === this) {
                this.className = 'modal-overlay';
            }
        });
    }

    // === CHECKLIST ===
    function iniciarChecklist() {
        // Reset checkmarks
        var items = document.querySelectorAll('#modalChecklist .checklist-item');
        for (var i = 0; i < items.length; i++) {
            items[i].className = 'checklist-item';
        }
        document.getElementById('modalChecklist').className = 'modal-overlay visible';
    }

    function toggleCheck(el) {
        if (el.className.indexOf('checked') >= 0) {
            el.className = 'checklist-item';
        } else {
            el.className = 'checklist-item checked';
        }
    }

    function enviarChecklist() {
        var checked = document.querySelectorAll('#modalChecklist .checklist-item.checked');
        var items = [];
        for (var i = 0; i < checked.length; i++) {
            items.push(checked[i].getAttribute('data-id'));
        }

        if (items.length === 0) {
            showToast('Marca al menos un item', 'error');
            return;
        }

        var btn = document.getElementById('btnEnviarChecklist');
        btn.disabled = true;
        btn.innerHTML = 'Enviando...';

        var xhr = new XMLHttpRequest();
        xhr.open('POST', window.location.href, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                btn.disabled = false;
                btn.innerHTML = 'Completar Revision';
                if (xhr.status === 200) {
                    try {
                        var data = JSON.parse(xhr.responseText);
                        if (data.success) {
                            cerrarModal('modalChecklist');
                            showToast(data.message, 'success');
                            updateDashboard(data);
                            marcarRetoCompleto('btnMatutino');
                        } else {
                            showToast(data.message, 'error');
                        }
                    } catch(e) {
                        showToast('Error procesando respuesta', 'error');
                    }
                } else {
                    showToast('Error de conexion', 'error');
                }
            }
        };
        xhr.send('action=complete_checklist&items=' + encodeURIComponent(items.join(',')));
    }

    // === TRIVIA ===
    function iniciarTrivia() {
        triviaRespuestas = [];
        triviaActual = 0;
        mostrarPregunta(0);
        document.getElementById('modalTrivia').className = 'modal-overlay visible';
    }

    function mostrarPregunta(idx) {
        var body = document.getElementById('triviaBody');
        var q = triviaData[idx];
        var total = triviaData.length;

        var html = '<div class="trivia-progress">';
        html += 'Pregunta ' + (idx + 1) + ' de ' + total;
        html += '<div class="trivia-progress-bar"><div class="trivia-progress-fill" style="width:' + ((idx + 1) / total * 100) + '%"></div></div>';
        html += '</div>';
        html += '<div class="trivia-question">' + q.pregunta + '</div>';

        for (var i = 0; i < q.opciones.length; i++) {
            html += '<button class="trivia-option" onclick="seleccionarOpcion(' + idx + ',' + i + ',this)">' + q.opciones[i] + '</button>';
        }

        body.innerHTML = html;
    }

    function seleccionarOpcion(qIdx, opcion, btn) {
        var q = triviaData[qIdx];
        var correcta = q.correcta;
        triviaRespuestas.push(opcion);

        // Marcar todas como disabled
        var opciones = btn.parentNode.querySelectorAll('.trivia-option');
        for (var i = 0; i < opciones.length; i++) {
            opciones[i].className = 'trivia-option disabled';
            if (i === correcta) {
                opciones[i].className = 'trivia-option correct disabled';
            }
        }
        if (opcion !== correcta) {
            btn.className = 'trivia-option incorrect disabled';
        }

        // Siguiente pregunta despues de 1 segundo
        setTimeout(function() {
            triviaActual++;
            if (triviaActual < triviaData.length) {
                mostrarPregunta(triviaActual);
            } else {
                enviarTrivia();
            }
        }, 1000);
    }

    function enviarTrivia() {
        var body = document.getElementById('triviaBody');
        body.innerHTML = '<div style="text-align:center;padding:2rem 0"><p>Enviando resultados...</p></div>';

        var xhr = new XMLHttpRequest();
        xhr.open('POST', window.location.href, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                try {
                    var data = JSON.parse(xhr.responseText);
                    if (data.success) {
                        // Mostrar resultados
                        var html = '<div class="trivia-result">';
                        html += '<div class="result-score">+' + data.puntos_ganados + ' pts</div>';
                        html += '<div class="result-label">Puntos ganados</div>';
                        html += '<div class="result-detail">' + data.correctas + ' de ' + data.total_preguntas + ' correctas</div>';
                        html += '</div>';
                        html += '<div style="text-align:center;padding:1rem 0">';
                        html += '<button class="btn btn-secondary" onclick="cerrarModal(\'modalTrivia\')">Cerrar</button>';
                        html += '</div>';
                        body.innerHTML = html;

                        showToast(data.message, 'success');
                        updateDashboard(data);
                        marcarRetoCompleto('btnNocturno');
                    }
                } catch(e) {
                    showToast('Error procesando resultados', 'error');
                }
            }
        };
        xhr.send('action=submit_trivia&respuestas=' + encodeURIComponent(triviaRespuestas.join(',')));
    }

    // === DASHBOARD UPDATE ===
    function updateDashboard(data) {
        if (data.puntos_total !== undefined) {
            var el = document.getElementById('dashPuntos');
            if (el) el.innerHTML = data.puntos_total + ' pts';
        }
        if (data.racha !== undefined) {
            var el2 = document.getElementById('dashRacha');
            if (el2) el2.innerHTML = data.racha;
        }
    }

    function marcarRetoCompleto(btnId) {
        var btn = document.getElementById(btnId);
        if (btn) {
            btn.className = 'reto-estado completado';
            btn.innerHTML = '&#10003; Completado hoy';
            btn.onclick = null;
            btn.disabled = true;
        }
    }

    // === TOAST ===
    function showToast(msg, type) {
        var container = document.getElementById('toastContainer');
        var toast = document.createElement('div');
        toast.className = 'toast ' + (type || 'info');
        toast.innerHTML = msg;
        container.appendChild(toast);

        setTimeout(function() { toast.className += ' show'; }, 50);
        setTimeout(function() {
            toast.className = toast.className.replace(' show', '');
            setTimeout(function() { container.removeChild(toast); }, 300);
        }, 3000);
    }

    // === THEME TOGGLE ===
    (function() {
        var themeToggle = document.getElementById('themeToggle');
        var moonIcon = themeToggle ? themeToggle.querySelector('.icon-moon') : null;
        var sunIcon = themeToggle ? themeToggle.querySelector('.icon-sun') : null;
        var body = document.body;

        var savedTheme = localStorage.getItem('tubi-theme') || 'light';
        if (savedTheme === 'dark') {
            body.setAttribute('data-theme', 'dark');
            if (moonIcon) moonIcon.style.display = 'none';
            if (sunIcon) sunIcon.style.display = 'block';
        } else {
            body.removeAttribute('data-theme');
            if (moonIcon) moonIcon.style.display = 'block';
            if (sunIcon) sunIcon.style.display = 'none';
        }

        if (themeToggle) {
            themeToggle.addEventListener('click', function() {
                var isDark = body.getAttribute('data-theme') === 'dark';
                if (isDark) {
                    body.removeAttribute('data-theme');
                    if (moonIcon) moonIcon.style.display = 'block';
                    if (sunIcon) sunIcon.style.display = 'none';
                    localStorage.setItem('tubi-theme', 'light');
                } else {
                    body.setAttribute('data-theme', 'dark');
                    if (moonIcon) moonIcon.style.display = 'none';
                    if (sunIcon) sunIcon.style.display = 'block';
                    localStorage.setItem('tubi-theme', 'dark');
                }
            });
        }
    })();
    </script>

    <?php include __DIR__ . '/../../includes/tutorial.php'; ?>
</body>
</html>
