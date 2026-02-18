<?php
/**
 * TUBI 2026 - Conexion a Base de Datos MySQL
 * Compatible con PHP 5.4+ y MySQL 5.0+
 * Usa mysqli (procedural para maxima compatibilidad)
 */

// Conexion global
$_db_conn = null;

/**
 * Obtener conexion a la base de datos
 */
function db() {
    global $_db_conn;

    if ($_db_conn !== null) {
        return $_db_conn;
    }

    $_db_conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if (!$_db_conn) {
        die('Error de conexion a la base de datos: ' . mysqli_connect_error());
    }

    mysqli_set_charset($_db_conn, 'utf8');

    return $_db_conn;
}

/**
 * Ejecutar query y devolver resultado
 */
function dbQuery($sql, $params = null) {
    $conn = db();

    if ($params !== null && count($params) > 0) {
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            return false;
        }

        // Construir tipos
        $types = '';
        foreach ($params as $p) {
            if (is_int($p)) {
                $types .= 'i';
            } elseif (is_float($p)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
        }

        // Bind params (compatible PHP 5.4)
        $bindParams = array($stmt, $types);
        foreach ($params as $k => $v) {
            $bindParams[] = &$params[$k];
        }
        call_user_func_array('mysqli_stmt_bind_param', $bindParams);

        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result === false) {
            // INSERT/UPDATE/DELETE
            $affected = mysqli_stmt_affected_rows($stmt);
            $insertId = mysqli_stmt_insert_id($stmt);
            mysqli_stmt_close($stmt);
            return array('affected' => $affected, 'insert_id' => $insertId);
        }

        mysqli_stmt_close($stmt);
        return $result;
    }

    return mysqli_query($conn, $sql);
}

/**
 * Obtener un solo registro
 */
function dbFetchOne($sql, $params = null) {
    $result = dbQuery($sql, $params);
    if ($result && !is_array($result)) {
        $row = mysqli_fetch_assoc($result);
        mysqli_free_result($result);
        return $row;
    }
    return null;
}

/**
 * Obtener todos los registros
 */
function dbFetchAll($sql, $params = null) {
    $result = dbQuery($sql, $params);
    $rows = array();
    if ($result && !is_array($result)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_free_result($result);
    }
    return $rows;
}

/**
 * Insertar registro y devolver ID
 */
function dbInsert($table, $data) {
    $columns = array_keys($data);
    $placeholders = array_fill(0, count($data), '?');
    $values = array_values($data);

    $sql = 'INSERT INTO ' . $table . ' (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')';

    $result = dbQuery($sql, $values);
    if (is_array($result)) {
        return $result['insert_id'];
    }
    return false;
}

/**
 * Actualizar registro
 */
function dbUpdate($table, $data, $where, $whereParams = null) {
    $sets = array();
    $values = array();
    foreach ($data as $col => $val) {
        $sets[] = $col . ' = ?';
        $values[] = $val;
    }

    if ($whereParams !== null) {
        foreach ($whereParams as $wp) {
            $values[] = $wp;
        }
    }

    $sql = 'UPDATE ' . $table . ' SET ' . implode(', ', $sets) . ' WHERE ' . $where;

    $result = dbQuery($sql, $values);
    if (is_array($result)) {
        return $result['affected'];
    }
    return false;
}

/**
 * Contar registros
 */
function dbCount($table, $where = '1=1', $params = null) {
    $sql = 'SELECT COUNT(*) as total FROM ' . $table . ' WHERE ' . $where;
    $row = dbFetchOne($sql, $params);
    return $row ? (int)$row['total'] : 0;
}

/**
 * Escapar string (fallback)
 */
function dbEscape($str) {
    $conn = db();
    return mysqli_real_escape_string($conn, $str);
}

/**
 * Verificar si la BD esta instalada
 */
function dbIsInstalled() {
    $conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$conn) {
        return false;
    }
    $result = @mysqli_query($conn, 'SELECT 1 FROM usuarios LIMIT 1');
    if (!$result) {
        mysqli_close($conn);
        return false;
    }
    mysqli_free_result($result);
    mysqli_close($conn);
    return true;
}

/**
 * Instalar base de datos desde install.sql
 */
function dbInstall() {
    // Conectar sin seleccionar BD
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS);
    if (!$conn) {
        return 'Error de conexion: ' . mysqli_connect_error();
    }

    mysqli_set_charset($conn, 'utf8');

    $sqlFile = realpath(__DIR__ . '/../install.sql');
    if (!$sqlFile || !file_exists($sqlFile)) {
        return 'No se encontro install.sql';
    }

    $sql = file_get_contents($sqlFile);

    // Ejecutar multi-query
    if (mysqli_multi_query($conn, $sql)) {
        // Consumir todos los resultados
        do {
            $result = mysqli_store_result($conn);
            if ($result) {
                mysqli_free_result($result);
            }
        } while (mysqli_more_results($conn) && mysqli_next_result($conn));
    }

    $error = mysqli_error($conn);
    mysqli_close($conn);

    if ($error) {
        return 'Error SQL: ' . $error;
    }

    return true;
}
