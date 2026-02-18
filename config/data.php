<?php
/**
 * TUBI 2026 - Sistema de Datos con MySQL
 * Compatible con PHP 5.4+ y MySQL 5.0+
 * Reemplaza el sistema anterior basado en $_SESSION
 */

// ============================================
// FUNCIONES DE ACCESO A DATOS
// ============================================

/**
 * Compatibilidad: initDemoData devuelve datos de la BD
 * Mantiene la misma interfaz que el sistema anterior
 */
function initDemoData() {
    return array(
        'bicicletas' => dbFetchAll('SELECT * FROM bicicletas ORDER BY id'),
        'alumnos' => dbFetchAll('SELECT * FROM alumnos ORDER BY id'),
        'escuelas' => dbFetchAll('SELECT * FROM escuelas ORDER BY id'),
        'proveedores' => dbFetchAll('SELECT * FROM proveedores ORDER BY id'),
        'ordenes' => dbFetchAll('SELECT * FROM ordenes ORDER BY id'),
        'modulos' => dbFetchAll('SELECT * FROM modulos ORDER BY id'),
        'logros' => dbFetchAll('SELECT * FROM logros ORDER BY id'),
    );
}

/**
 * Obtener datos por clave
 */
function getData($key) {
    $tables = array(
        'bicicletas' => 'SELECT * FROM bicicletas ORDER BY id',
        'alumnos' => 'SELECT * FROM alumnos ORDER BY id',
        'escuelas' => 'SELECT * FROM escuelas ORDER BY id',
        'proveedores' => 'SELECT * FROM proveedores ORDER BY id',
        'ordenes' => 'SELECT * FROM ordenes ORDER BY id',
        'modulos' => 'SELECT * FROM modulos ORDER BY id',
        'logros' => 'SELECT * FROM logros ORDER BY id',
    );

    if ($key && isset($tables[$key])) {
        return dbFetchAll($tables[$key]);
    }
    return array();
}

// ============================================
// BICICLETAS
// ============================================

function getBicicletas($filtros = array()) {
    $where = '1=1';
    $params = array();

    if (!empty($filtros['estado'])) {
        $where .= ' AND estado = ?';
        $params[] = $filtros['estado'];
    }
    if (!empty($filtros['escuela_id'])) {
        $where .= ' AND escuela_id = ?';
        $params[] = (int)$filtros['escuela_id'];
    }
    if (!empty($filtros['proveedor_id'])) {
        $where .= ' AND proveedor_id = ?';
        $params[] = (int)$filtros['proveedor_id'];
    }

    return dbFetchAll('SELECT * FROM bicicletas WHERE ' . $where . ' ORDER BY id', count($params) > 0 ? $params : null);
}

function getBicicleta($id) {
    return dbFetchOne('SELECT * FROM bicicletas WHERE id = ?', array((int)$id));
}

function updateBicicleta($id, $datos) {
    return dbUpdate('bicicletas', $datos, 'id = ?', array((int)$id));
}

function addBicicleta($datos) {
    return dbInsert('bicicletas', $datos);
}

function cambiarEstadoBicicleta($id, $nuevoEstado, $escuelaId = null) {
    $datos = array('estado' => $nuevoEstado);

    if ($escuelaId) {
        $datos['escuela_id'] = (int)$escuelaId;
    }
    if ($nuevoEstado === 'armada') {
        $datos['fecha_armado'] = date('Y-m-d H:i:s');
    }
    if ($nuevoEstado === 'entregada') {
        $datos['fecha_entrega'] = date('Y-m-d H:i:s');
    }

    return dbUpdate('bicicletas', $datos, 'id = ?', array((int)$id));
}

// ============================================
// ALUMNOS
// ============================================

function getAlumnos($filtros = array()) {
    $where = '1=1';
    $params = array();

    if (!empty($filtros['escuela_id'])) {
        $where .= ' AND escuela_id = ?';
        $params[] = (int)$filtros['escuela_id'];
    }
    if (!empty($filtros['estado'])) {
        $where .= ' AND estado = ?';
        $params[] = $filtros['estado'];
    }

    return dbFetchAll('SELECT * FROM alumnos WHERE ' . $where . ' ORDER BY id', count($params) > 0 ? $params : null);
}

function getAlumno($id) {
    return dbFetchOne('SELECT * FROM alumnos WHERE id = ?', array((int)$id));
}

function getAlumnoByUsuario($usuarioId) {
    return dbFetchOne('SELECT * FROM alumnos WHERE usuario_id = ?', array((int)$usuarioId));
}

function updateAlumno($id, $datos) {
    return dbUpdate('alumnos', $datos, 'id = ?', array((int)$id));
}

function addAlumno($datos) {
    $datos['fecha_registro'] = date('Y-m-d');
    return dbInsert('alumnos', $datos);
}

// ============================================
// ESCUELAS
// ============================================

function getEscuelas() {
    return dbFetchAll('SELECT * FROM escuelas ORDER BY id');
}

function getEscuela($id) {
    return dbFetchOne('SELECT * FROM escuelas WHERE id = ?', array((int)$id));
}

// ============================================
// PROVEEDORES
// ============================================

function getProveedores() {
    return dbFetchAll('SELECT * FROM proveedores ORDER BY id');
}

function getProveedor($id) {
    return dbFetchOne('SELECT * FROM proveedores WHERE id = ?', array((int)$id));
}

// ============================================
// ORDENES
// ============================================

function getOrdenes($filtros = array()) {
    $where = '1=1';
    $params = array();

    if (!empty($filtros['proveedor_id'])) {
        $where .= ' AND proveedor_id = ?';
        $params[] = (int)$filtros['proveedor_id'];
    }
    if (!empty($filtros['escuela_id'])) {
        $where .= ' AND escuela_id = ?';
        $params[] = (int)$filtros['escuela_id'];
    }
    if (!empty($filtros['estado'])) {
        $where .= ' AND estado = ?';
        $params[] = $filtros['estado'];
    }

    return dbFetchAll('SELECT * FROM ordenes WHERE ' . $where . ' ORDER BY id', count($params) > 0 ? $params : null);
}

// ============================================
// MODULOS Y LOGROS
// ============================================

function getModulos() {
    return dbFetchAll('SELECT * FROM modulos ORDER BY id');
}

function getLogros() {
    return dbFetchAll('SELECT * FROM logros ORDER BY id');
}

// ============================================
// RETOS COMPLETADOS
// ============================================

function getRetosCompletados($alumnoId, $fecha = null) {
    if ($fecha === null) {
        $fecha = date('Y-m-d');
    }
    return dbFetchAll(
        'SELECT * FROM retos_completados WHERE alumno_id = ? AND fecha = ?',
        array((int)$alumnoId, $fecha)
    );
}

function retoCompletadoHoy($alumnoId, $tipo) {
    $row = dbFetchOne(
        'SELECT id FROM retos_completados WHERE alumno_id = ? AND tipo = ? AND fecha = ?',
        array((int)$alumnoId, $tipo, date('Y-m-d'))
    );
    return $row ? true : false;
}

function registrarReto($alumnoId, $tipo, $puntos, $datos = '') {
    return dbInsert('retos_completados', array(
        'alumno_id' => (int)$alumnoId,
        'tipo' => $tipo,
        'fecha' => date('Y-m-d'),
        'puntos_ganados' => (int)$puntos,
        'datos' => $datos
    ));
}

// ============================================
// ESTADISTICAS
// ============================================

function getEstadisticas() {
    return array(
        'total_bicicletas' => dbCount('bicicletas'),
        'bicicletas_entregadas' => dbCount('bicicletas', 'estado = ?', array('entregada')),
        'bicicletas_en_escuela' => dbCount('bicicletas', 'estado = ?', array('en_escuela')),
        'bicicletas_armadas' => dbCount('bicicletas', 'estado = ?', array('armada')),
        'bicicletas_deposito' => dbCount('bicicletas', 'estado = ?', array('deposito')),
        'total_alumnos' => dbCount('alumnos'),
        'alumnos_con_bici' => dbCount('alumnos', 'estado = ?', array('entregado')),
        'total_escuelas' => dbCount('escuelas'),
    );
}

function getEstadisticasProveedor($proveedorId = null) {
    $where = $proveedorId ? 'proveedor_id = ?' : '1=1';
    $params = $proveedorId ? array((int)$proveedorId) : null;

    $total = dbCount('bicicletas', $where, $params);

    $wDeposito = $proveedorId ? 'proveedor_id = ? AND estado = ?' : 'estado = ?';
    $wArmada = $wDeposito;
    $wSuministrada = $wDeposito;
    $wEntregada = $wDeposito;

    if ($proveedorId) {
        $pDeposito = array((int)$proveedorId, 'deposito');
        $pArmada = array((int)$proveedorId, 'armada');
        $pSuministrada = array((int)$proveedorId, 'en_escuela');
        $pEntregada = array((int)$proveedorId, 'entregada');
    } else {
        $pDeposito = array('deposito');
        $pArmada = array('armada');
        $pSuministrada = array('en_escuela');
        $pEntregada = array('entregada');
    }

    $enDeposito = dbCount('bicicletas', $wDeposito, $pDeposito);
    $armadas = dbCount('bicicletas', $wArmada, $pArmada);
    $suministradas = dbCount('bicicletas', $wSuministrada, $pSuministrada);
    $entregadas = dbCount('bicicletas', $wEntregada, $pEntregada);

    return array(
        'en_deposito' => $enDeposito,
        'armadas' => $armadas,
        'suministradas' => $suministradas,
        'en_escuelas' => $entregadas,
        'total' => $total,
        'armadas_hoy' => 0,
        'esta_semana' => $armadas,
        'promedio_dia' => max(1, intval(($total - $enDeposito) / max(1, 30))),
        'pendientes' => $enDeposito,
    );
}

function getEstadisticasEscuela($escuelaId) {
    $totalBicis = dbCount('bicicletas', 'escuela_id = ?', array((int)$escuelaId));
    $entregadas = dbCount('bicicletas', 'escuela_id = ? AND estado = ?', array((int)$escuelaId, 'entregada'));
    $totalAlumnos = dbCount('alumnos', 'escuela_id = ?', array((int)$escuelaId));

    return array(
        'total_bicicletas' => $totalBicis,
        'entregadas' => $entregadas,
        'pendientes' => $totalBicis - $entregadas,
        'total_alumnos' => $totalAlumnos,
    );
}

function getEstadisticasAdmin() {
    $total = dbCount('bicicletas');
    $entregadas = dbCount('bicicletas', 'estado = ?', array('entregada'));
    $enEscuelas = dbCount('bicicletas', 'estado = ?', array('en_escuela'));
    $enDeposito = dbCount('bicicletas', 'estado = ?', array('deposito'));
    $armadas = dbCount('bicicletas', 'estado = ?', array('armada'));
    $totalEscuelas = dbCount('escuelas');

    return array(
        'total_bicicletas' => $total,
        'entregadas' => $entregadas,
        'en_escuelas' => $enEscuelas,
        'en_deposito' => $enDeposito,
        'armadas' => $armadas,
        'total_escuelas' => $totalEscuelas,
        'entregas_hoy' => 0,
        'entregas_semana' => $entregadas,
        'entregas_mes' => $entregadas,
        'tasa_entrega' => $total > 0 ? round(($entregadas / $total) * 100, 1) : 0,
    );
}

// ============================================
// TABLAS CON JOIN (para dashboards)
// ============================================

function getBicicletasParaProveedor($limit = 10) {
    $sql = 'SELECT b.*, e.nombre AS escuela_nombre, a.nombre AS alumno_nombre, a.dni AS alumno_dni
            FROM bicicletas b
            LEFT JOIN escuelas e ON b.escuela_id = e.id
            LEFT JOIN alumnos a ON b.alumno_id = a.id
            ORDER BY b.id DESC
            LIMIT ' . (int)$limit;
    $rows = dbFetchAll($sql);

    // Formatear para compatibilidad con templates existentes
    foreach ($rows as $k => $b) {
        $rows[$k]['escuela'] = $b['escuela_nombre'] ? array('nombre' => $b['escuela_nombre']) : null;
        $rows[$k]['alumno'] = $b['alumno_nombre'] ? array('nombre' => $b['alumno_nombre'], 'dni' => isset($b['alumno_dni']) ? $b['alumno_dni'] : '') : null;
    }
    return $rows;
}

function getBicicletasParaEscuela($escuelaId, $limit = 10) {
    $sql = 'SELECT b.*, a.nombre AS alumno_nombre, a.dni AS alumno_dni
            FROM bicicletas b
            LEFT JOIN alumnos a ON b.alumno_id = a.id
            WHERE b.escuela_id = ?
            ORDER BY b.id DESC
            LIMIT ' . (int)$limit;
    $rows = dbFetchAll($sql, array((int)$escuelaId));

    foreach ($rows as $k => $b) {
        $rows[$k]['alumno'] = $b['alumno_nombre'] ? array('nombre' => $b['alumno_nombre'], 'dni' => $b['alumno_dni']) : null;
    }
    return $rows;
}

function getBicicletasParaAdmin($limit = 10) {
    $sql = 'SELECT b.*, e.nombre AS escuela_nombre, a.nombre AS alumno_nombre
            FROM bicicletas b
            LEFT JOIN escuelas e ON b.escuela_id = e.id
            LEFT JOIN alumnos a ON b.alumno_id = a.id
            ORDER BY b.id DESC
            LIMIT ' . (int)$limit;
    $rows = dbFetchAll($sql);

    foreach ($rows as $k => $b) {
        $rows[$k]['escuela'] = $b['escuela_nombre'] ? array('nombre' => $b['escuela_nombre']) : null;
        $rows[$k]['alumno'] = $b['alumno_nombre'] ? array('nombre' => $b['alumno_nombre']) : null;
    }
    return $rows;
}
