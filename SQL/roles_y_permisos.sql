-- =============================================
-- MIGRACIÓN: Sistema de roles y permisos de botones
-- Base de datos: resto
-- Fecha: 2026-08-20
-- =============================================

-- 1. Tabla maestra de roles
CREATE TABLE IF NOT EXISTS roles (
    id             TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre         VARCHAR(20) NOT NULL UNIQUE COMMENT 'slug: mozo, caja, administrador, sudo',
    nombre_display VARCHAR(30) NOT NULL COMMENT 'Nombre visible en UI'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO roles (nombre, nombre_display) VALUES
    ('mozo',          'MOZO'),
    ('caja',          'CAJA'),
    ('administrador', 'ADMINISTRADOR'),
    ('sudo',          'SUDO');

-- 2. Tabla de permisos de botones de acción por rol
--    Administrable desde el panel de configuración de accesos
CREATE TABLE IF NOT EXISTS permisos_botones_accion (
    id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_rol    TINYINT UNSIGNED NOT NULL,
    boton_key VARCHAR(40) NOT NULL,
    activo    TINYINT(1) NOT NULL DEFAULT 1,
    UNIQUE KEY uk_rol_boton (id_rol, boton_key),
    CONSTRAINT fk_pba_rol FOREIGN KEY (id_rol) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Permisos por defecto según reglas de negocio definidas
INSERT INTO permisos_botones_accion (id_rol, boton_key, activo) VALUES
-- MOZO (1): sin gabeta ni opciones del sistema
(1,'corregir',1),(1,'mesas',1),(1,'comandas',1),(1,'gabeta',0),(1,'vendedores',1),(1,'opciones',0),
-- CAJA (2): acceso completo
(2,'corregir',1),(2,'mesas',1),(2,'comandas',1),(2,'gabeta',1),(2,'vendedores',1),(2,'opciones',1),
-- ADMINISTRADOR (3): acceso completo
(3,'corregir',1),(3,'mesas',1),(3,'comandas',1),(3,'gabeta',1),(3,'vendedores',1),(3,'opciones',1),
-- SUDO (4): acceso completo
(4,'corregir',1),(4,'mesas',1),(4,'comandas',1),(4,'gabeta',1),(4,'vendedores',1),(4,'opciones',1);

-- 3. Migrar columna rol en usuarios
--    3a: Cambiar ENUM a VARCHAR para poder hacer UPDATE libre
ALTER TABLE usuarios MODIFY COLUMN rol VARCHAR(20) NOT NULL DEFAULT 'mozo';

--    3b: Renombrar valores al nuevo esquema
UPDATE usuarios SET rol = 'mozo'          WHERE rol = 'vendedor';
UPDATE usuarios SET rol = 'caja'          WHERE rol = 'supervisor';
UPDATE usuarios SET rol = 'administrador' WHERE rol = 'admin';
-- 'sudo' queda igual

-- 4. Agregar FK id_rol a usuarios
ALTER TABLE usuarios ADD COLUMN id_rol TINYINT UNSIGNED NULL AFTER rol;

UPDATE usuarios u
    JOIN roles r ON r.nombre = u.rol
    SET u.id_rol = r.id;

ALTER TABLE usuarios
    MODIFY COLUMN id_rol TINYINT UNSIGNED NOT NULL DEFAULT 1,
    ADD CONSTRAINT fk_usuarios_rol FOREIGN KEY (id_rol) REFERENCES roles(id);
