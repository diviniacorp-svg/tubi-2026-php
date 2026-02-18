-- =============================================
-- TUBI 2026 - Base de Datos MySQL
-- Compatible con MySQL 5.0+ / MariaDB 5.5+
-- =============================================

CREATE DATABASE IF NOT EXISTS tubi_2026 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE tubi_2026;

-- Tabla de usuarios (login)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(200) NOT NULL,
    dni VARCHAR(20) DEFAULT NULL,
    cuit VARCHAR(20) DEFAULT NULL,
    cue VARCHAR(20) DEFAULT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'alumno',
    activo TINYINT(1) NOT NULL DEFAULT 1,
    fecha_creacion DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Tabla de escuelas
CREATE TABLE IF NOT EXISTS escuelas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(250) NOT NULL,
    cue VARCHAR(20) NOT NULL,
    localidad VARCHAR(150) NOT NULL,
    total_alumnos INT NOT NULL DEFAULT 0,
    bicicletas_asignadas INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Tabla de proveedores
CREATE TABLE IF NOT EXISTS proveedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(250) NOT NULL,
    cuit VARCHAR(20) NOT NULL,
    localidad VARCHAR(150) NOT NULL,
    estado VARCHAR(20) NOT NULL DEFAULT 'activo',
    bicicletas_armadas INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Tabla de alumnos
CREATE TABLE IF NOT EXISTS alumnos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT DEFAULT NULL,
    nombre VARCHAR(200) NOT NULL,
    dni VARCHAR(20) NOT NULL,
    email VARCHAR(150) DEFAULT NULL,
    escuela_id INT DEFAULT NULL,
    curso VARCHAR(30) DEFAULT NULL,
    estado VARCHAR(30) NOT NULL DEFAULT 'preinscripto',
    puntos INT NOT NULL DEFAULT 0,
    modulos_completados INT NOT NULL DEFAULT 0,
    racha INT NOT NULL DEFAULT 0,
    fecha_registro DATE NOT NULL,
    FOREIGN KEY (escuela_id) REFERENCES escuelas(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Tabla de bicicletas
CREATE TABLE IF NOT EXISTS bicicletas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(30) NOT NULL,
    serie VARCHAR(30) NOT NULL,
    rodado INT NOT NULL DEFAULT 26,
    color VARCHAR(30) NOT NULL DEFAULT 'Azul',
    estado VARCHAR(20) NOT NULL DEFAULT 'deposito',
    alumno_id INT DEFAULT NULL,
    escuela_id INT DEFAULT NULL,
    proveedor_id INT DEFAULT NULL,
    fecha_armado DATETIME DEFAULT NULL,
    fecha_entrega DATETIME DEFAULT NULL,
    garantia_hasta DATE DEFAULT NULL,
    FOREIGN KEY (alumno_id) REFERENCES alumnos(id) ON DELETE SET NULL,
    FOREIGN KEY (escuela_id) REFERENCES escuelas(id) ON DELETE SET NULL,
    FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Tabla de ordenes
CREATE TABLE IF NOT EXISTS ordenes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(30) NOT NULL,
    escuela_id INT DEFAULT NULL,
    proveedor_id INT DEFAULT NULL,
    cantidad INT NOT NULL DEFAULT 0,
    estado VARCHAR(20) NOT NULL DEFAULT 'pendiente',
    fecha_creacion DATE NOT NULL,
    fecha_entrega DATE DEFAULT NULL,
    FOREIGN KEY (escuela_id) REFERENCES escuelas(id) ON DELETE SET NULL,
    FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Tabla de modulos de aprendizaje
CREATE TABLE IF NOT EXISTS modulos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descripcion VARCHAR(500) NOT NULL,
    puntos INT NOT NULL DEFAULT 0,
    duracion VARCHAR(20) NOT NULL,
    icono VARCHAR(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Tabla de logros
CREATE TABLE IF NOT EXISTS logros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descripcion VARCHAR(500) NOT NULL,
    icono VARCHAR(10) NOT NULL,
    puntos INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Tabla de retos completados (por alumno, por dia)
CREATE TABLE IF NOT EXISTS retos_completados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alumno_id INT NOT NULL,
    tipo VARCHAR(30) NOT NULL,
    fecha DATE NOT NULL,
    puntos_ganados INT NOT NULL DEFAULT 0,
    datos TEXT DEFAULT NULL,
    FOREIGN KEY (alumno_id) REFERENCES alumnos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- =============================================
-- DATOS INICIALES (SEED)
-- =============================================

-- Usuarios demo
INSERT INTO usuarios (email, password, nombre, dni, cuit, cue, role, fecha_creacion) VALUES
('alumno@tubi.com', 'demo123', 'Juan Perez', '45123456', NULL, NULL, 'alumno', NOW()),
('tutor@tubi.com', 'demo123', 'Maria Gonzalez', '20123456', NULL, NULL, 'tutor', NOW()),
('escuela@tubi.com', 'demo123', 'Escuela N 123', NULL, NULL, '740001234', 'escuela', NOW()),
('proveedor@tubi.com', 'demo123', 'Logistica San Luis S.A.', NULL, '30-12345678-9', NULL, 'proveedor', NOW()),
('admin@tubi.com', 'admin123', 'Administrador TuBi', NULL, NULL, NULL, 'admin', NOW());

-- Escuelas
INSERT INTO escuelas (nombre, cue, localidad, total_alumnos, bicicletas_asignadas) VALUES
('Escuela N 123 "Gral. San Martin"', '740001234', 'Ciudad de San Luis', 450, 35),
('Escuela N 45 "Juan B. Alberdi"', '740001245', 'Villa Mercedes', 380, 28),
('Escuela N 78 "Domingo F. Sarmiento"', '740001278', 'Merlo', 290, 22),
('Instituto San Martin', '740001290', 'Juana Koslay', 520, 40),
('Escuela N 156 "Eva Peron"', '740001356', 'La Punta', 310, 25),
('Colegio Provincial N 12', '740001412', 'Potrero de los Funes', 180, 15),
('Escuela Tecnica N 8', '740001508', 'San Luis', 420, 32),
('Escuela N 234 "Manuel Belgrano"', '740001634', 'Tilisarao', 250, 20),
('Instituto Santa Rosa', '740001745', 'Santa Rosa del Conlara', 340, 26),
('Escuela N 567 "Arturo Illia"', '740001867', 'Quines', 200, 18);

-- Proveedores
INSERT INTO proveedores (nombre, cuit, localidad, estado, bicicletas_armadas) VALUES
('Logistica San Luis S.A.', '30-12345678-9', 'Villa Mercedes', 'activo', 450),
('Bicicletas del Sur SRL', '30-98765432-1', 'San Luis', 'activo', 380),
('Transporte Puntano', '30-55667788-5', 'Merlo', 'activo', 290);

-- Modulos
INSERT INTO modulos (titulo, descripcion, puntos, duracion, icono) VALUES
('Conociendo tu TuBi', 'Aprende las partes de tu bicicleta', 50, '15 min', '?'),
('Seguridad Vial Basica', 'Normas de transito para ciclistas', 75, '20 min', '??'),
('Mantenimiento Basico', 'Como cuidar tu bicicleta', 100, '25 min', '?'),
('Circulacion Urbana', 'Circular seguro en la ciudad', 75, '20 min', '??'),
('Primeros Auxilios', 'Que hacer ante un accidente', 100, '30 min', '?'),
('Seguridad Nocturna', 'Circular de noche con seguridad', 75, '15 min', '?'),
('Mecanica Avanzada', 'Reparaciones que podes hacer', 150, '35 min', '??'),
('Ciclismo Responsable', 'Ser un ciclista ejemplar', 100, '20 min', '?');

-- Logros
INSERT INTO logros (titulo, descripcion, icono, puntos) VALUES
('Primera Vuelta', 'Completaste tu primer modulo', '?', 25),
('Experto Vial', 'Aprobaste educacion vial', '??', 50),
('Mecanico Basico', 'Aprendiste mantenimiento basico', '?', 50),
('Ciclista Nocturno', 'Dominas la seguridad nocturna', '?', 50),
('Campeon TuBi', 'Completaste todos los modulos', '?', 200),
('Ayudante', 'Ayudaste a un companero', '?', 30);

-- Alumnos demo (50)
INSERT INTO alumnos (usuario_id, nombre, dni, email, escuela_id, curso, estado, puntos, modulos_completados, racha, fecha_registro) VALUES
(1, 'Juan Perez', '45123456', 'alumno1@email.com', 1, '5 B', 'asignado', 150, 3, 7, '2026-01-15');

-- Generar mas alumnos
INSERT INTO alumnos (nombre, dni, email, escuela_id, curso, estado, puntos, modulos_completados, racha, fecha_registro) VALUES
('Maria Garcia', '45234567', 'alumno2@email.com', 1, '4 A', 'asignado', 200, 4, 5, '2026-01-16'),
('Carlos Lopez', '45345678', 'alumno3@email.com', 2, '6 B', 'aprobado', 80, 2, 3, '2026-01-17'),
('Ana Martinez', '45456789', 'alumno4@email.com', 2, '5 A', 'entregado', 320, 5, 10, '2026-01-18'),
('Pedro Gonzalez', '45567890', 'alumno5@email.com', 3, '3 C', 'preinscripto', 0, 0, 0, '2026-01-19'),
('Laura Rodriguez', '45678901', 'alumno6@email.com', 3, '4 B', 'en_revision', 50, 1, 2, '2026-01-20'),
('Diego Fernandez', '45789012', 'alumno7@email.com', 4, '6 A', 'asignado', 180, 3, 6, '2026-01-21'),
('Sofia Sanchez', '45890123', 'alumno8@email.com', 4, '5 C', 'entregado', 400, 6, 12, '2026-01-22'),
('Lucas Ramirez', '45901234', 'alumno9@email.com', 5, '3 A', 'aprobado', 100, 2, 4, '2026-01-23'),
('Valentina Torres', '46012345', 'alumno10@email.com', 5, '4 A', 'asignado', 250, 4, 8, '2026-01-24'),
('Martin Diaz', '46123456', 'alumno11@email.com', 6, '5 B', 'entregado', 350, 5, 9, '2026-01-25'),
('Camila Ruiz', '46234567', 'alumno12@email.com', 6, '6 C', 'asignado', 170, 3, 5, '2026-01-26'),
('Tomas Moreno', '46345678', 'alumno13@email.com', 7, '4 A', 'preinscripto', 0, 0, 0, '2026-01-27'),
('Lucia Alvarez', '46456789', 'alumno14@email.com', 7, '5 A', 'en_revision', 30, 1, 1, '2026-01-28'),
('Nicolas Romero', '46567890', 'alumno15@email.com', 8, '3 B', 'aprobado', 90, 2, 3, '2026-01-29'),
('Isabella Sosa', '46678901', 'alumno16@email.com', 8, '6 A', 'asignado', 220, 4, 7, '2026-01-30'),
('Mateo Acosta', '46789012', 'alumno17@email.com', 9, '4 C', 'entregado', 280, 5, 8, '2026-01-31'),
('Emma Herrera', '46890123', 'alumno18@email.com', 9, '5 B', 'asignado', 160, 3, 4, '2026-02-01'),
('Santiago Medina', '46901234', 'alumno19@email.com', 10, '3 A', 'preinscripto', 0, 0, 0, '2026-02-02'),
('Mia Peralta', '47012345', 'alumno20@email.com', 10, '6 B', 'aprobado', 110, 2, 5, '2026-02-03');

-- Bicicletas demo (genera 100 con estados variados)
INSERT INTO bicicletas (codigo, serie, rodado, color, estado, alumno_id, escuela_id, proveedor_id, fecha_armado, fecha_entrega, garantia_hasta) VALUES
('TUBI-2026-00001', 'SN-C4CA4238', 26, 'Azul', 'entregada', 1, 1, 1, '2026-01-20 10:00:00', '2026-02-01 14:00:00', '2028-02-01'),
('TUBI-2026-00002', 'SN-C81E728D', 26, 'Verde', 'entregada', 2, 1, 1, '2026-01-20 10:30:00', '2026-02-01 14:30:00', '2028-02-01'),
('TUBI-2026-00003', 'SN-ECCBC87E', 24, 'Rojo', 'en_escuela', NULL, 2, 1, '2026-01-21 09:00:00', NULL, NULL),
('TUBI-2026-00004', 'SN-A87FF679', 26, 'Negro', 'armada', NULL, NULL, 2, '2026-02-10 11:00:00', NULL, NULL),
('TUBI-2026-00005', 'SN-E4DA3B7F', 26, 'Azul', 'deposito', NULL, NULL, 2, NULL, NULL, NULL),
('TUBI-2026-00006', 'SN-1679091C', 24, 'Verde', 'entregada', 4, 2, 1, '2026-01-22 10:00:00', '2026-02-03 09:00:00', '2028-02-03'),
('TUBI-2026-00007', 'SN-8F14E45F', 26, 'Azul', 'en_escuela', NULL, 3, 2, '2026-01-23 08:00:00', NULL, NULL),
('TUBI-2026-00008', 'SN-C9F0F895', 26, 'Rojo', 'armada', NULL, NULL, 3, '2026-02-11 10:00:00', NULL, NULL),
('TUBI-2026-00009', 'SN-45C48CCE', 24, 'Negro', 'deposito', NULL, NULL, 1, NULL, NULL, NULL),
('TUBI-2026-00010', 'SN-D3D94468', 26, 'Azul', 'entregada', 8, 4, 2, '2026-01-25 10:00:00', '2026-02-05 11:00:00', '2028-02-05'),
('TUBI-2026-00011', 'SN-6512BD43', 26, 'Verde', 'en_escuela', NULL, 5, 3, '2026-01-26 09:00:00', NULL, NULL),
('TUBI-2026-00012', 'SN-C20AD4D7', 24, 'Azul', 'armada', NULL, NULL, 1, '2026-02-12 08:00:00', NULL, NULL),
('TUBI-2026-00013', 'SN-C51CE410', 26, 'Rojo', 'deposito', NULL, NULL, 2, NULL, NULL, NULL),
('TUBI-2026-00014', 'SN-AAB3238B', 26, 'Negro', 'entregada', 11, 6, 3, '2026-01-28 10:00:00', '2026-02-07 14:00:00', '2028-02-07'),
('TUBI-2026-00015', 'SN-9BF31C7F', 24, 'Azul', 'en_escuela', NULL, 7, 1, '2026-01-29 10:00:00', NULL, NULL),
('TUBI-2026-00016', 'SN-C74D97B0', 26, 'Verde', 'deposito', NULL, NULL, 2, NULL, NULL, NULL),
('TUBI-2026-00017', 'SN-70EFDF2E', 26, 'Azul', 'armada', NULL, NULL, 3, '2026-02-09 11:00:00', NULL, NULL),
('TUBI-2026-00018', 'SN-6F4922F4', 24, 'Rojo', 'entregada', 17, 9, 1, '2026-02-01 10:00:00', '2026-02-10 09:00:00', '2028-02-10'),
('TUBI-2026-00019', 'SN-1F0E3DAD', 26, 'Negro', 'en_escuela', NULL, 10, 2, '2026-02-02 08:00:00', NULL, NULL),
('TUBI-2026-00020', 'SN-98F13708', 26, 'Azul', 'deposito', NULL, NULL, 3, NULL, NULL, NULL);

-- Ordenes demo
INSERT INTO ordenes (codigo, escuela_id, proveedor_id, cantidad, estado, fecha_creacion, fecha_entrega) VALUES
('ORD-2026-001', 1, 1, 20, 'entregada', '2026-01-10', '2026-01-25'),
('ORD-2026-002', 2, 1, 15, 'completada', '2026-01-12', '2026-02-01'),
('ORD-2026-003', 3, 2, 12, 'en_proceso', '2026-01-15', NULL),
('ORD-2026-004', 4, 2, 25, 'pendiente', '2026-01-20', NULL),
('ORD-2026-005', 5, 3, 18, 'entregada', '2026-01-08', '2026-01-22'),
('ORD-2026-006', 6, 3, 10, 'completada', '2026-01-25', '2026-02-05'),
('ORD-2026-007', 7, 1, 22, 'en_proceso', '2026-02-01', NULL),
('ORD-2026-008', 8, 2, 14, 'pendiente', '2026-02-03', NULL),
('ORD-2026-009', 9, 1, 16, 'entregada', '2026-01-05', '2026-01-20'),
('ORD-2026-010', 10, 3, 8, 'en_proceso', '2026-02-06', NULL);
