-- ================================================================================
-- 03_roles_y_usuarios.sql
-- Sistema de Gestión de Gimnasio (Sis_Gestion_GYM)
-- Descripción: Catálogo de roles y usuarios administradores por defecto.
-- ================================================================================

USE `Sis_Gestion_GYM`;

-- 1. Tabla de roles de usuarios (catálogo independiente)
CREATE TABLE IF NOT EXISTS roles (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(255)
);

-- Roles iniciales
INSERT INTO roles (nombre, descripcion) VALUES
('admin', 'Administrador con acceso total al sistema'),
('staff', 'Personal de recepción y atención');

-- 2. Tabla de usuarios del panel administrativo (login del sistema)
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(100),
    id_rol INT NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuarios_roles FOREIGN KEY (id_rol)
        REFERENCES roles(id_rol)
);

-- Usuarios por defecto para cada rol (Admin y Staff)
-- 1) usuario: admin | clave: admin123
-- 2) usuario: staff | clave: staff123
INSERT INTO usuarios (usuario, password, nombre, id_rol) VALUES
('admin', '$2y$10$GAxj002RoqvZgPUtNrK.x.WiAPyd7TWEdA/k6NHsWBkib.b70RKAm', 'Administrador', (SELECT id_rol FROM roles WHERE nombre = 'admin')),
('staff', '$2y$10$8rLEPFoZcuPw1PG6MfBDAu/qNRdKIRgBW/ocZb8VwjH7wfY29fqZ.', 'Personal de Recepción', (SELECT id_rol FROM roles WHERE nombre = 'staff'));

