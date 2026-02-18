<?php
/**
 * TUBI 2026 - Gestión de Usuarios (Admin)
 */
require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn() || !hasRole('admin')) {
    redirect('login.php');
}

$pageTitle = 'Gestión de Usuarios';
$msg = '';
$msgType = '';

// ============================================
// POST HANDLING
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token'])) {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        $msg = 'Token de seguridad inválido.';
        $msgType = 'error';
    } else {
        $action = isset($_POST['action']) ? $_POST['action'] : '';

        // --- ADD USER ---
        if ($action === 'add') {
            $nombre = isset($_POST['nombre']) ? sanitize($_POST['nombre']) : '';
            $email  = isset($_POST['email']) ? sanitize($_POST['email']) : '';
            $pass   = isset($_POST['password']) ? trim($_POST['password']) : '';
            $role   = isset($_POST['role']) ? sanitize($_POST['role']) : 'alumno';
            $dni    = isset($_POST['dni']) ? sanitize($_POST['dni']) : '';
            $cuit   = isset($_POST['cuit']) ? sanitize($_POST['cuit']) : '';
            $cue    = isset($_POST['cue']) ? sanitize($_POST['cue']) : '';

            if ($nombre === '' || $email === '' || $pass === '') {
                $msg = 'Nombre, email y contraseña son obligatorios.';
                $msgType = 'error';
            } else {
                $existing = dbFetchOne('SELECT id FROM usuarios WHERE email = ?', array($email));
                if ($existing) {
                    $msg = 'Ya existe un usuario con ese email.';
                    $msgType = 'error';
                } else {
                    $data = array(
                        'nombre' => $nombre,
                        'email'  => $email,
                        'password' => $pass,
                        'role'   => $role,
                        'dni'    => ($dni !== '') ? $dni : null,
                        'cuit'   => ($cuit !== '') ? $cuit : null,
                        'cue'    => ($cue !== '') ? $cue : null,
                        'activo' => 1,
                        'fecha_creacion' => date('Y-m-d H:i:s')
                    );
                    $newId = dbInsert('usuarios', $data);
                    if ($newId) {
                        $msg = 'Usuario creado correctamente (ID: ' . $newId . ').';
                        $msgType = 'success';
                    } else {
                        $msg = 'Error al crear el usuario.';
                        $msgType = 'error';
                    }
                }
            }
        }

        // --- EDIT USER ---
        if ($action === 'edit') {
            $id     = isset($_POST['id']) ? validateInt($_POST['id']) : 0;
            $nombre = isset($_POST['nombre']) ? sanitize($_POST['nombre']) : '';
            $email  = isset($_POST['email']) ? sanitize($_POST['email']) : '';
            $role   = isset($_POST['role']) ? sanitize($_POST['role']) : 'alumno';
            $dni    = isset($_POST['dni']) ? sanitize($_POST['dni']) : '';
            $cuit   = isset($_POST['cuit']) ? sanitize($_POST['cuit']) : '';
            $cue    = isset($_POST['cue']) ? sanitize($_POST['cue']) : '';

            if ($id < 1 || $nombre === '' || $email === '') {
                $msg = 'Datos incompletos para editar.';
                $msgType = 'error';
            } else {
                $existing = dbFetchOne('SELECT id FROM usuarios WHERE email = ? AND id != ?', array($email, $id));
                if ($existing) {
                    $msg = 'Ya existe otro usuario con ese email.';
                    $msgType = 'error';
                } else {
                    $data = array(
                        'nombre' => $nombre,
                        'email'  => $email,
                        'role'   => $role,
                        'dni'    => ($dni !== '') ? $dni : null,
                        'cuit'   => ($cuit !== '') ? $cuit : null,
                        'cue'    => ($cue !== '') ? $cue : null
                    );
                    $newPass = isset($_POST['password']) ? trim($_POST['password']) : '';
                    if ($newPass !== '') {
                        $data['password'] = $newPass;
                    }
                    $affected = dbUpdate('usuarios', $data, 'id = ?', array($id));
                    if ($affected !== false) {
                        $msg = 'Usuario actualizado correctamente.';
                        $msgType = 'success';
                    } else {
                        $msg = 'Error al actualizar el usuario.';
                        $msgType = 'error';
                    }
                }
            }
        }

        // --- TOGGLE ACTIVE (Activate / Deactivate) ---
        if ($action === 'toggle_active') {
            $id = isset($_POST['id']) ? validateInt($_POST['id']) : 0;
            if ($id > 0) {
                $user = dbFetchOne('SELECT activo FROM usuarios WHERE id = ?', array($id));
                if ($user) {
                    $newState = ($user['activo']) ? 0 : 1;
                    dbUpdate('usuarios', array('activo' => $newState), 'id = ?', array($id));
                    $msg = ($newState) ? 'Usuario activado.' : 'Usuario desactivado.';
                    $msgType = 'success';
                }
            }
        }

        // --- DELETE USER ---
        if ($action === 'delete') {
            $id = isset($_POST['id']) ? validateInt($_POST['id']) : 0;
            if ($id > 0) {
                $result = dbQuery('DELETE FROM usuarios WHERE id = ?', array($id));
                if (is_array($result) && $result['affected'] > 0) {
                    $msg = 'Usuario eliminado correctamente.';
                    $msgType = 'success';
                } else {
                    $msg = 'No se pudo eliminar el usuario.';
                    $msgType = 'error';
                }
            }
        }
    }
}

// ============================================
// FETCH DATA
// ============================================
$usuarios = dbFetchAll('SELECT * FROM usuarios ORDER BY id');

$roleLabels = array(
    'alumno' => array('label' => 'Alumno', 'class' => 'badge-info'),
    'tutor' => array('label' => 'Tutor', 'class' => 'badge-secondary'),
    'escuela' => array('label' => 'Escuela', 'class' => 'badge-primary'),
    'proveedor' => array('label' => 'Proveedor', 'class' => 'badge-warning'),
    'admin' => array('label' => 'Admin', 'class' => 'badge-error'),
);

$csrfToken = generateCSRFToken();

include __DIR__ . '/../../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Gestión de Usuarios</h1>
        <button class="btn btn-primary" onclick="openAddModal()">+ Nuevo Usuario</button>
    </div>

    <?php if ($msg !== ''): ?>
    <div class="alert alert-<?php echo e($msgType); ?>" style="margin-bottom: 1rem; padding: 0.75rem 1rem; border-radius: 8px; background: <?php echo ($msgType === 'success') ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo ($msgType === 'success') ? '#155724' : '#721c24'; ?>;">
        <?php echo e($msg); ?>
    </div>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="card" style="margin-bottom: 1rem;">
        <div class="card-body">
            <div class="filter-row">
                <select class="form-input" id="filterRole">
                    <option value="">Todos los roles</option>
                    <option value="alumno">Alumnos</option>
                    <option value="tutor">Tutores</option>
                    <option value="escuela">Escuelas</option>
                    <option value="proveedor">Proveedores</option>
                    <option value="admin">Administradores</option>
                </select>
                <select class="form-input" id="filterEstado">
                    <option value="">Todos los estados</option>
                    <option value="1">Activos</option>
                    <option value="0">Inactivos</option>
                </select>
                <input type="text" placeholder="Buscar por nombre o email..." class="form-input" style="flex: 1;" id="filterSearch">
                <button class="btn btn-secondary" onclick="filterTable()">Filtrar</button>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table" id="usersTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Fecha Creación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u):
                        $role = isset($u['role']) ? $u['role'] : 'alumno';
                        $rl = isset($roleLabels[$role]) ? $roleLabels[$role] : array('label' => $role, 'class' => 'badge-secondary');
                        $isActive = !empty($u['activo']);
                    ?>
                    <tr data-role="<?php echo e($role); ?>" data-activo="<?php echo $isActive ? '1' : '0'; ?>" data-nombre="<?php echo e($u['nombre']); ?>" data-email="<?php echo e($u['email']); ?>">
                        <td><?php echo (int)$u['id']; ?></td>
                        <td><?php echo e($u['nombre']); ?></td>
                        <td><?php echo e($u['email']); ?></td>
                        <td>
                            <span class="badge <?php echo e($rl['class']); ?>">
                                <?php echo e($rl['label']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($isActive): ?>
                                <span class="badge badge-success">Activo</span>
                            <?php else: ?>
                                <span class="badge badge-error">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo isset($u['fecha_creacion']) ? e($u['fecha_creacion']) : '-'; ?></td>
                        <td>
                            <button class="btn btn-secondary btn-sm" onclick="openEditModal(<?php echo (int)$u['id']; ?>, <?php echo e(json_encode($u['nombre'])); ?>, <?php echo e(json_encode($u['email'])); ?>, <?php echo e(json_encode($role)); ?>, <?php echo e(json_encode(isset($u['dni']) ? $u['dni'] : '')); ?>, <?php echo e(json_encode(isset($u['cuit']) ? $u['cuit'] : '')); ?>, <?php echo e(json_encode(isset($u['cue']) ? $u['cue'] : '')); ?>)">Editar</button>
                            <form method="post" style="display:inline;" onsubmit="return confirm('<?php echo $isActive ? '¿Desactivar este usuario?' : '¿Activar este usuario?'; ?>')">
                                <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
                                <input type="hidden" name="action" value="toggle_active">
                                <input type="hidden" name="id" value="<?php echo (int)$u['id']; ?>">
                                <button type="submit" class="btn <?php echo $isActive ? 'btn-error' : 'btn-success'; ?> btn-sm">
                                    <?php echo $isActive ? 'Desactivar' : 'Activar'; ?>
                                </button>
                            </form>
                            <form method="post" style="display:inline;" onsubmit="return confirm('¿Eliminar este usuario permanentemente?')">
                                <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo (int)$u['id']; ?>">
                                <button type="submit" class="btn btn-error btn-sm" style="opacity:0.7;">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Add User -->
<div id="addModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; overflow-y:auto;">
    <div style="max-width:500px; margin:5% auto; background:#fff; border-radius:12px; padding:2rem; position:relative;">
        <h2 style="margin-top:0;">Nuevo Usuario</h2>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
            <input type="hidden" name="action" value="add">
            <div style="margin-bottom:0.75rem;">
                <label>Nombre *</label>
                <input type="text" name="nombre" class="form-input" required style="width:100%;">
            </div>
            <div style="margin-bottom:0.75rem;">
                <label>Email *</label>
                <input type="email" name="email" class="form-input" required style="width:100%;">
            </div>
            <div style="margin-bottom:0.75rem;">
                <label>Contraseña *</label>
                <input type="password" name="password" class="form-input" required style="width:100%;">
            </div>
            <div style="margin-bottom:0.75rem;">
                <label>Rol</label>
                <select name="role" class="form-input" style="width:100%;">
                    <option value="alumno">Alumno</option>
                    <option value="tutor">Tutor</option>
                    <option value="escuela">Escuela</option>
                    <option value="proveedor">Proveedor</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div style="margin-bottom:0.75rem;">
                <label>DNI</label>
                <input type="text" name="dni" class="form-input" style="width:100%;">
            </div>
            <div style="margin-bottom:0.75rem;">
                <label>CUIT</label>
                <input type="text" name="cuit" class="form-input" style="width:100%;">
            </div>
            <div style="margin-bottom:0.75rem;">
                <label>CUE</label>
                <input type="text" name="cue" class="form-input" style="width:100%;">
            </div>
            <div style="text-align:right; margin-top:1rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">Cancelar</button>
                <button type="submit" class="btn btn-primary">Crear Usuario</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit User -->
<div id="editModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; overflow-y:auto;">
    <div style="max-width:500px; margin:5% auto; background:#fff; border-radius:12px; padding:2rem; position:relative;">
        <h2 style="margin-top:0;">Editar Usuario</h2>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="editId">
            <div style="margin-bottom:0.75rem;">
                <label>Nombre *</label>
                <input type="text" name="nombre" id="editNombre" class="form-input" required style="width:100%;">
            </div>
            <div style="margin-bottom:0.75rem;">
                <label>Email *</label>
                <input type="email" name="email" id="editEmail" class="form-input" required style="width:100%;">
            </div>
            <div style="margin-bottom:0.75rem;">
                <label>Nueva Contraseña <small>(dejar vacío para no cambiar)</small></label>
                <input type="password" name="password" class="form-input" style="width:100%;">
            </div>
            <div style="margin-bottom:0.75rem;">
                <label>Rol</label>
                <select name="role" id="editRole" class="form-input" style="width:100%;">
                    <option value="alumno">Alumno</option>
                    <option value="tutor">Tutor</option>
                    <option value="escuela">Escuela</option>
                    <option value="proveedor">Proveedor</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div style="margin-bottom:0.75rem;">
                <label>DNI</label>
                <input type="text" name="dni" id="editDni" class="form-input" style="width:100%;">
            </div>
            <div style="margin-bottom:0.75rem;">
                <label>CUIT</label>
                <input type="text" name="cuit" id="editCuit" class="form-input" style="width:100%;">
            </div>
            <div style="margin-bottom:0.75rem;">
                <label>CUE</label>
                <input type="text" name="cue" id="editCue" class="form-input" style="width:100%;">
            </div>
            <div style="text-align:right; margin-top:1rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('addModal').style.display = 'block';
}

function openEditModal(id, nombre, email, role, dni, cuit, cue) {
    document.getElementById('editId').value = id;
    document.getElementById('editNombre').value = nombre;
    document.getElementById('editEmail').value = email;
    document.getElementById('editRole').value = role;
    document.getElementById('editDni').value = dni || '';
    document.getElementById('editCuit').value = cuit || '';
    document.getElementById('editCue').value = cue || '';
    document.getElementById('editModal').style.display = 'block';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function filterTable() {
    var roleFilter = document.getElementById('filterRole').value;
    var estadoFilter = document.getElementById('filterEstado').value;
    var searchFilter = document.getElementById('filterSearch').value.toLowerCase();
    var rows = document.getElementById('usersTable').getElementsByTagName('tbody')[0].getElementsByTagName('tr');

    for (var i = 0; i < rows.length; i++) {
        var row = rows[i];
        var show = true;

        if (roleFilter !== '' && row.getAttribute('data-role') !== roleFilter) {
            show = false;
        }
        if (estadoFilter !== '' && row.getAttribute('data-activo') !== estadoFilter) {
            show = false;
        }
        if (searchFilter !== '') {
            var nombre = (row.getAttribute('data-nombre') || '').toLowerCase();
            var email = (row.getAttribute('data-email') || '').toLowerCase();
            if (nombre.indexOf(searchFilter) === -1 && email.indexOf(searchFilter) === -1) {
                show = false;
            }
        }

        row.style.display = show ? '' : 'none';
    }
}

// Close modals on outside click
document.getElementById('addModal').onclick = function(e) {
    if (e.target === this) closeModal('addModal');
};
document.getElementById('editModal').onclick = function(e) {
    if (e.target === this) closeModal('editModal');
};
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
