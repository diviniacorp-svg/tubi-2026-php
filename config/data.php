<?php
/**
 * TUBI 2026 - Sistema de Datos Demo
 * Simula una base de datos para la demo
 */

// Inicializar datos en sesión si no existen
function initDemoData() {
    if (!isset($_SESSION['tubi_data'])) {
        $_SESSION['tubi_data'] = [
            'bicicletas' => generateBicicletas(),
            'alumnos' => generateAlumnos(),
            'escuelas' => generateEscuelas(),
            'proveedores' => generateProveedores(),
            'ordenes' => generateOrdenes(),
            'modulos' => generateModulos(),
            'logros' => generateLogros(),
        ];
    }
    return $_SESSION['tubi_data'];
}

// Generar bicicletas demo
function generateBicicletas() {
    $estados = ['deposito', 'armada', 'en_escuela', 'entregada'];
    $bicicletas = [];

    for ($i = 1; $i <= 100; $i++) {
        $estado = $estados[array_rand($estados)];
        $bicicletas[] = [
            'id' => $i,
            'codigo' => 'TUBI-2026-' . str_pad($i, 5, '0', STR_PAD_LEFT),
            'serie' => 'SN-' . strtoupper(substr(md5($i), 0, 8)),
            'rodado' => rand(24, 26),
            'color' => ['Azul', 'Verde', 'Rojo', 'Negro'][array_rand(['Azul', 'Verde', 'Rojo', 'Negro'])],
            'estado' => $estado,
            'alumno_id' => $estado === 'entregada' ? rand(1, 50) : null,
            'escuela_id' => in_array($estado, ['en_escuela', 'entregada']) ? rand(1, 10) : null,
            'proveedor_id' => rand(1, 3),
            'fecha_armado' => $estado !== 'deposito' ? date('Y-m-d', strtotime('-' . rand(1, 60) . ' days')) : null,
            'fecha_entrega' => $estado === 'entregada' ? date('Y-m-d', strtotime('-' . rand(1, 30) . ' days')) : null,
        ];
    }
    return $bicicletas;
}

// Generar alumnos demo
function generateAlumnos() {
    $nombres = ['Juan', 'María', 'Carlos', 'Ana', 'Pedro', 'Laura', 'Diego', 'Sofía', 'Lucas', 'Valentina'];
    $apellidos = ['Pérez', 'García', 'López', 'Martínez', 'González', 'Rodríguez', 'Fernández', 'Sánchez', 'Ramírez', 'Torres'];
    $estados = ['preinscripto', 'en_revision', 'aprobado', 'asignado', 'entregado'];
    $alumnos = [];

    for ($i = 1; $i <= 50; $i++) {
        $estado = $estados[array_rand($estados)];
        $alumnos[] = [
            'id' => $i,
            'nombre' => $nombres[array_rand($nombres)] . ' ' . $apellidos[array_rand($apellidos)],
            'dni' => '4' . rand(0, 9) . rand(100000, 999999),
            'email' => 'alumno' . $i . '@email.com',
            'escuela_id' => rand(1, 10),
            'curso' => rand(1, 6) . '° ' . ['A', 'B', 'C'][array_rand(['A', 'B', 'C'])],
            'estado' => $estado,
            'puntos' => rand(0, 500),
            'modulos_completados' => rand(0, 8),
            'fecha_registro' => date('Y-m-d', strtotime('-' . rand(1, 90) . ' days')),
        ];
    }
    return $alumnos;
}

// Generar escuelas demo
function generateEscuelas() {
    $escuelas = [
        ['id' => 1, 'nombre' => 'Escuela N° 123 "Gral. San Martín"', 'cue' => '740001234', 'localidad' => 'Ciudad de San Luis', 'total_alumnos' => 450, 'bicicletas_asignadas' => 35],
        ['id' => 2, 'nombre' => 'Escuela N° 45 "Juan B. Alberdi"', 'cue' => '740001245', 'localidad' => 'Villa Mercedes', 'total_alumnos' => 380, 'bicicletas_asignadas' => 28],
        ['id' => 3, 'nombre' => 'Escuela N° 78 "Domingo F. Sarmiento"', 'cue' => '740001278', 'localidad' => 'Merlo', 'total_alumnos' => 290, 'bicicletas_asignadas' => 22],
        ['id' => 4, 'nombre' => 'Instituto San Martín', 'cue' => '740001290', 'localidad' => 'Juana Koslay', 'total_alumnos' => 520, 'bicicletas_asignadas' => 40],
        ['id' => 5, 'nombre' => 'Escuela N° 156 "Eva Perón"', 'cue' => '740001356', 'localidad' => 'La Punta', 'total_alumnos' => 310, 'bicicletas_asignadas' => 25],
        ['id' => 6, 'nombre' => 'Colegio Provincial N° 12', 'cue' => '740001412', 'localidad' => 'Potrero de los Funes', 'total_alumnos' => 180, 'bicicletas_asignadas' => 15],
        ['id' => 7, 'nombre' => 'Escuela Técnica N° 8', 'cue' => '740001508', 'localidad' => 'San Luis', 'total_alumnos' => 420, 'bicicletas_asignadas' => 32],
        ['id' => 8, 'nombre' => 'Escuela N° 234 "Manuel Belgrano"', 'cue' => '740001634', 'localidad' => 'Tilisarao', 'total_alumnos' => 250, 'bicicletas_asignadas' => 20],
        ['id' => 9, 'nombre' => 'Instituto Santa Rosa', 'cue' => '740001745', 'localidad' => 'Santa Rosa del Conlara', 'total_alumnos' => 340, 'bicicletas_asignadas' => 26],
        ['id' => 10, 'nombre' => 'Escuela N° 567 "Arturo Illia"', 'cue' => '740001867', 'localidad' => 'Quines', 'total_alumnos' => 200, 'bicicletas_asignadas' => 18],
    ];
    return $escuelas;
}

// Generar proveedores demo
function generateProveedores() {
    return [
        ['id' => 1, 'nombre' => 'Logística San Luis S.A.', 'cuit' => '30-12345678-9', 'localidad' => 'Villa Mercedes', 'estado' => 'activo', 'bicicletas_armadas' => 450],
        ['id' => 2, 'nombre' => 'Bicicletas del Sur SRL', 'cuit' => '30-98765432-1', 'localidad' => 'San Luis', 'estado' => 'activo', 'bicicletas_armadas' => 380],
        ['id' => 3, 'nombre' => 'Transporte Puntano', 'cuit' => '30-55667788-5', 'localidad' => 'Merlo', 'estado' => 'activo', 'bicicletas_armadas' => 290],
    ];
}

// Generar órdenes demo
function generateOrdenes() {
    $estados = ['pendiente', 'en_proceso', 'completada', 'entregada'];
    $ordenes = [];

    for ($i = 1; $i <= 20; $i++) {
        $ordenes[] = [
            'id' => $i,
            'codigo' => 'ORD-2026-' . str_pad($i, 3, '0', STR_PAD_LEFT),
            'escuela_id' => rand(1, 10),
            'proveedor_id' => rand(1, 3),
            'cantidad' => rand(5, 30),
            'estado' => $estados[array_rand($estados)],
            'fecha_creacion' => date('Y-m-d', strtotime('-' . rand(1, 60) . ' days')),
            'fecha_entrega' => rand(0, 1) ? date('Y-m-d', strtotime('+' . rand(1, 30) . ' days')) : null,
        ];
    }
    return $ordenes;
}

// Generar módulos de aprendizaje
function generateModulos() {
    return [
        ['id' => 1, 'titulo' => 'Conociendo tu TuBi', 'descripcion' => 'Aprende las partes de tu bicicleta', 'puntos' => 50, 'duracion' => '15 min', 'icono' => '🚲'],
        ['id' => 2, 'titulo' => 'Seguridad Vial Básica', 'descripcion' => 'Normas de tránsito para ciclistas', 'puntos' => 75, 'duracion' => '20 min', 'icono' => '🛡️'],
        ['id' => 3, 'titulo' => 'Mantenimiento Básico', 'descripcion' => 'Cómo cuidar tu bicicleta', 'puntos' => 100, 'duracion' => '25 min', 'icono' => '🔧'],
        ['id' => 4, 'titulo' => 'Circulación Urbana', 'descripcion' => 'Circular seguro en la ciudad', 'puntos' => 75, 'duracion' => '20 min', 'icono' => '🏙️'],
        ['id' => 5, 'titulo' => 'Primeros Auxilios', 'descripcion' => 'Qué hacer ante un accidente', 'puntos' => 100, 'duracion' => '30 min', 'icono' => '🏥'],
        ['id' => 6, 'titulo' => 'Seguridad Nocturna', 'descripcion' => 'Circular de noche con seguridad', 'puntos' => 75, 'duracion' => '15 min', 'icono' => '🌙'],
        ['id' => 7, 'titulo' => 'Mecánica Avanzada', 'descripcion' => 'Reparaciones que podés hacer', 'puntos' => 150, 'duracion' => '35 min', 'icono' => '⚙️'],
        ['id' => 8, 'titulo' => 'Ciclismo Responsable', 'descripcion' => 'Ser un ciclista ejemplar', 'puntos' => 100, 'duracion' => '20 min', 'icono' => '🏆'],
    ];
}

// Generar logros
function generateLogros() {
    return [
        ['id' => 1, 'titulo' => 'Primera Vuelta', 'descripcion' => 'Completaste tu primer módulo', 'icono' => '🎯', 'puntos' => 25],
        ['id' => 2, 'titulo' => 'Experto Vial', 'descripcion' => 'Aprobaste educación vial', 'icono' => '🛡️', 'puntos' => 50],
        ['id' => 3, 'titulo' => 'Mecánico Básico', 'descripcion' => 'Aprendiste mantenimiento básico', 'icono' => '🔧', 'puntos' => 50],
        ['id' => 4, 'titulo' => 'Ciclista Nocturno', 'descripcion' => 'Dominás la seguridad nocturna', 'icono' => '🌙', 'puntos' => 50],
        ['id' => 5, 'titulo' => 'Campeón TuBi', 'descripcion' => 'Completaste todos los módulos', 'icono' => '🏆', 'puntos' => 200],
        ['id' => 6, 'titulo' => 'Ayudante', 'descripcion' => 'Ayudaste a un compañero', 'icono' => '🤝', 'puntos' => 30],
    ];
}

// Funciones de acceso a datos
function getData($key = null) {
    $data = initDemoData();
    return $key ? ($data[$key] ?? []) : $data;
}

function getBicicletas($filtros = []) {
    $bicicletas = getData('bicicletas');

    if (!empty($filtros['estado'])) {
        $bicicletas = array_filter($bicicletas, fn($b) => $b['estado'] === $filtros['estado']);
    }
    if (!empty($filtros['escuela_id'])) {
        $bicicletas = array_filter($bicicletas, fn($b) => $b['escuela_id'] == $filtros['escuela_id']);
    }
    if (!empty($filtros['proveedor_id'])) {
        $bicicletas = array_filter($bicicletas, fn($b) => $b['proveedor_id'] == $filtros['proveedor_id']);
    }

    return array_values($bicicletas);
}

function getBicicleta($id) {
    $bicicletas = getData('bicicletas');
    foreach ($bicicletas as $b) {
        if ($b['id'] == $id) return $b;
    }
    return null;
}

function getAlumnos($filtros = []) {
    $alumnos = getData('alumnos');

    if (!empty($filtros['escuela_id'])) {
        $alumnos = array_filter($alumnos, fn($a) => $a['escuela_id'] == $filtros['escuela_id']);
    }
    if (!empty($filtros['estado'])) {
        $alumnos = array_filter($alumnos, fn($a) => $a['estado'] === $filtros['estado']);
    }

    return array_values($alumnos);
}

function getAlumno($id) {
    $alumnos = getData('alumnos');
    foreach ($alumnos as $a) {
        if ($a['id'] == $id) return $a;
    }
    return null;
}

function getEscuelas() {
    return getData('escuelas');
}

function getEscuela($id) {
    $escuelas = getData('escuelas');
    foreach ($escuelas as $e) {
        if ($e['id'] == $id) return $e;
    }
    return null;
}

function getProveedores() {
    return getData('proveedores');
}

function getProveedor($id) {
    $proveedores = getData('proveedores');
    foreach ($proveedores as $p) {
        if ($p['id'] == $id) return $p;
    }
    return null;
}

function getOrdenes($filtros = []) {
    $ordenes = getData('ordenes');

    if (!empty($filtros['proveedor_id'])) {
        $ordenes = array_filter($ordenes, fn($o) => $o['proveedor_id'] == $filtros['proveedor_id']);
    }
    if (!empty($filtros['escuela_id'])) {
        $ordenes = array_filter($ordenes, fn($o) => $o['escuela_id'] == $filtros['escuela_id']);
    }
    if (!empty($filtros['estado'])) {
        $ordenes = array_filter($ordenes, fn($o) => $o['estado'] === $filtros['estado']);
    }

    return array_values($ordenes);
}

function getModulos() {
    return getData('modulos');
}

function getLogros() {
    return getData('logros');
}

// Estadísticas generales
function getEstadisticas() {
    $bicicletas = getData('bicicletas');
    $alumnos = getData('alumnos');
    $escuelas = getData('escuelas');

    return [
        'total_bicicletas' => count($bicicletas),
        'bicicletas_entregadas' => count(array_filter($bicicletas, fn($b) => $b['estado'] === 'entregada')),
        'bicicletas_en_escuela' => count(array_filter($bicicletas, fn($b) => $b['estado'] === 'en_escuela')),
        'bicicletas_armadas' => count(array_filter($bicicletas, fn($b) => $b['estado'] === 'armada')),
        'bicicletas_deposito' => count(array_filter($bicicletas, fn($b) => $b['estado'] === 'deposito')),
        'total_alumnos' => count($alumnos),
        'alumnos_con_bici' => count(array_filter($alumnos, fn($a) => $a['estado'] === 'entregado')),
        'total_escuelas' => count($escuelas),
    ];
}

// Actualizar datos (simula UPDATE en BD)
function updateBicicleta($id, $datos) {
    $data = &$_SESSION['tubi_data'];
    foreach ($data['bicicletas'] as &$b) {
        if ($b['id'] == $id) {
            $b = array_merge($b, $datos);
            return true;
        }
    }
    return false;
}

function updateAlumno($id, $datos) {
    $data = &$_SESSION['tubi_data'];
    foreach ($data['alumnos'] as &$a) {
        if ($a['id'] == $id) {
            $a = array_merge($a, $datos);
            return true;
        }
    }
    return false;
}

// Agregar nuevos registros
function addBicicleta($datos) {
    $data = &$_SESSION['tubi_data'];
    $id = max(array_column($data['bicicletas'], 'id')) + 1;
    $datos['id'] = $id;
    $datos['codigo'] = 'TUBI-2026-' . str_pad($id, 5, '0', STR_PAD_LEFT);
    $data['bicicletas'][] = $datos;
    return $id;
}

function addAlumno($datos) {
    $data = &$_SESSION['tubi_data'];
    $id = max(array_column($data['alumnos'], 'id')) + 1;
    $datos['id'] = $id;
    $datos['fecha_registro'] = date('Y-m-d');
    $data['alumnos'][] = $datos;
    return $id;
}

// Estadísticas para Proveedor
function getEstadisticasProveedor($proveedorId = null) {
    $bicicletas = getData('bicicletas');

    if ($proveedorId) {
        $bicicletas = array_filter($bicicletas, fn($b) => $b['proveedor_id'] == $proveedorId);
    }

    return [
        'en_deposito' => count(array_filter($bicicletas, fn($b) => $b['estado'] === 'deposito')),
        'armadas' => count(array_filter($bicicletas, fn($b) => $b['estado'] === 'armada')),
        'suministradas' => count(array_filter($bicicletas, fn($b) => $b['estado'] === 'en_escuela')),
        'en_escuelas' => count(array_filter($bicicletas, fn($b) => $b['estado'] === 'entregada')),
        'total' => count($bicicletas),
        'armadas_hoy' => rand(1, 5),
        'esta_semana' => rand(5, 15),
        'promedio_dia' => rand(3, 8),
        'pendientes' => rand(1, 10),
    ];
}

// Cambiar estado de bicicleta (flujo de trabajo)
function cambiarEstadoBicicleta($id, $nuevoEstado, $escuelaId = null) {
    $data = &$_SESSION['tubi_data'];
    foreach ($data['bicicletas'] as &$b) {
        if ($b['id'] == $id) {
            $b['estado'] = $nuevoEstado;
            if ($escuelaId) {
                $b['escuela_id'] = $escuelaId;
            }
            if ($nuevoEstado === 'armada') {
                $b['fecha_armado'] = date('Y-m-d H:i:s');
            }
            if ($nuevoEstado === 'entregada') {
                $b['fecha_entrega'] = date('Y-m-d H:i:s');
            }
            return true;
        }
    }
    return false;
}

// Estadísticas para Escuela
function getEstadisticasEscuela($escuelaId) {
    $bicicletas = getData('bicicletas');
    $bicicletasEscuela = array_filter($bicicletas, fn($b) => $b['escuela_id'] == $escuelaId);

    $alumnos = getData('alumnos');
    $alumnosEscuela = array_filter($alumnos, fn($a) => $a['escuela_id'] == $escuelaId);

    return [
        'total_bicicletas' => count($bicicletasEscuela),
        'entregadas' => count(array_filter($bicicletasEscuela, fn($b) => $b['estado'] === 'entregada')),
        'pendientes' => count(array_filter($bicicletasEscuela, fn($b) => $b['estado'] !== 'entregada')),
        'total_alumnos' => count($alumnosEscuela),
    ];
}

// Estadísticas para Admin (tiempo real)
function getEstadisticasAdmin() {
    $bicicletas = getData('bicicletas');
    $escuelas = getData('escuelas');

    $stats = [
        'total_bicicletas' => count($bicicletas),
        'entregadas' => count(array_filter($bicicletas, fn($b) => $b['estado'] === 'entregada')),
        'en_escuelas' => count(array_filter($bicicletas, fn($b) => $b['estado'] === 'en_escuela')),
        'en_deposito' => count(array_filter($bicicletas, fn($b) => $b['estado'] === 'deposito')),
        'armadas' => count(array_filter($bicicletas, fn($b) => $b['estado'] === 'armada')),
        'total_escuelas' => count($escuelas),
        'entregas_hoy' => rand(10, 30),
        'entregas_semana' => rand(100, 200),
        'entregas_mes' => rand(500, 800),
        'tasa_entrega' => round((count(array_filter($bicicletas, fn($b) => $b['estado'] === 'entregada')) / count($bicicletas)) * 100, 1),
    ];

    return $stats;
}

// Obtener bicicletas para tabla de proveedor
function getBicicletasParaProveedor($limit = 10) {
    $bicicletas = getData('bicicletas');
    $escuelas = getData('escuelas');
    $alumnos = getData('alumnos');

    // Ordenar por ID descendente (más recientes primero)
    usort($bicicletas, fn($a, $b) => $b['id'] - $a['id']);

    // Tomar solo el límite
    $bicicletas = array_slice($bicicletas, 0, $limit);

    // Añadir info de escuela y alumno
    foreach ($bicicletas as &$b) {
        $b['escuela'] = null;
        $b['alumno'] = null;

        if ($b['escuela_id']) {
            foreach ($escuelas as $e) {
                if ($e['id'] == $b['escuela_id']) {
                    $b['escuela'] = $e;
                    break;
                }
            }
        }

        if ($b['alumno_id']) {
            foreach ($alumnos as $a) {
                if ($a['id'] == $b['alumno_id']) {
                    $b['alumno'] = $a;
                    break;
                }
            }
        }
    }

    return $bicicletas;
}

// Obtener bicicletas para tabla de escuela
function getBicicletasParaEscuela($escuelaId, $limit = 10) {
    $bicicletas = getData('bicicletas');
    $alumnos = getData('alumnos');

    // Filtrar por escuela
    $bicicletas = array_filter($bicicletas, fn($b) => $b['escuela_id'] == $escuelaId);

    // Ordenar por ID descendente
    usort($bicicletas, fn($a, $b) => $b['id'] - $a['id']);

    // Tomar solo el límite
    $bicicletas = array_slice($bicicletas, 0, $limit);

    // Añadir info de alumno
    foreach ($bicicletas as &$b) {
        $b['alumno'] = null;
        if ($b['alumno_id']) {
            foreach ($alumnos as $a) {
                if ($a['id'] == $b['alumno_id']) {
                    $b['alumno'] = $a;
                    break;
                }
            }
        }
    }

    return array_values($bicicletas);
}

// Obtener bicicletas para tabla de admin
function getBicicletasParaAdmin($limit = 10) {
    $bicicletas = getData('bicicletas');
    $escuelas = getData('escuelas');
    $alumnos = getData('alumnos');

    // Ordenar por ID descendente
    usort($bicicletas, fn($a, $b) => $b['id'] - $a['id']);

    // Tomar solo el límite
    $bicicletas = array_slice($bicicletas, 0, $limit);

    // Añadir info
    foreach ($bicicletas as &$b) {
        $b['escuela'] = null;
        $b['alumno'] = null;

        if ($b['escuela_id']) {
            foreach ($escuelas as $e) {
                if ($e['id'] == $b['escuela_id']) {
                    $b['escuela'] = $e;
                    break;
                }
            }
        }

        if ($b['alumno_id']) {
            foreach ($alumnos as $a) {
                if ($a['id'] == $b['alumno_id']) {
                    $b['alumno'] = $a;
                    break;
                }
            }
        }
    }

    return $bicicletas;
}
