-- ================================================================================
-- 04_datos_de_prueba.sql
-- Sistema de Gestión de Gimnasio (Sis_Gestion_GYM)
-- Descripción: Datos de prueba para testing y demostración del sistema.
-- ================================================================================

USE `Sis_Gestion_GYM`;

-- 1. Catálogos independientes
-- 1.1 Disciplinas
INSERT INTO disciplinas (disciplina, descripcion) VALUES
('CrossFit', 'Entrenamiento funcional de alta intensidad para fuerza y resistencia.'),
('Spinning', 'Ciclismo de interior guiado con intervalos cardiovasculares.'),
('Yoga', 'Clases de flexibilidad, respiración y postura corporal.');

-- 1.2 Entrenadores
INSERT INTO entrenadores (DNI, nombre, apellido, email, telefono) VALUES
(30111222, 'Carlos', 'Gómez', 'carlos.gomez@email.com', '1122334455'),
(31222333, 'Laura', 'Martínez', 'laura.martinez@email.com', '1166778899');


-- 2. Tarifas y asignaciones
-- 2.1 Tarifas
INSERT INTO tarifas (id_entrenador, nombre, monto, descripcion) VALUES
(
    (SELECT id_entrenador FROM entrenadores WHERE DNI = 30111222),
    'Tarifa Personalizada CrossFit',
    15000.00,
    'Plan mensual de 3 clases semanales guiadas'
),
(
    (SELECT id_entrenador FROM entrenadores WHERE DNI = 31222333),
    'Tarifa Integral Spinning',
    12000.00,
    'Plan mensual pase libre de spinning'
);

-- 2.2 Entrenadores x Disciplinas
INSERT INTO entrenadoresxdisciplinas (id_entrenador, id_disciplina) VALUES
(
    (SELECT id_entrenador FROM entrenadores WHERE DNI = 30111222),
    (SELECT id_disciplina FROM disciplinas WHERE disciplina = 'CrossFit')
),
(
    (SELECT id_entrenador FROM entrenadores WHERE DNI = 31222333),
    (SELECT id_disciplina FROM disciplinas WHERE disciplina = 'Spinning')
);


-- 3. Clientes iniciales y registros de actividad
-- 3.1 Clientes
INSERT INTO clientes (DNI, nombre, apellido, email, telefono, id_estado) VALUES
(
    40555666, 'Juan', 'Pérez', 'juan.perez@email.com', '1144556677',
    (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Activo')
),
(
    41777888, 'María', 'López', 'maria.lopez@email.com', '1188990011',
    (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Activo')
);

-- 3.2 Historia de Asistencia
INSERT INTO historia_de_asistencia (id_cliente, fecha_hora, asistio) VALUES
(
    (SELECT id_cliente FROM clientes WHERE DNI = 40555666),
    NOW(),
    1
);

-- 3.3 Historial de Pagos (con método de pago y vencimiento)
INSERT INTO historial_de_pagos (id_cliente, id_tarifa, monto, metodo_pago, fecha_pago, fecha_vencimiento) VALUES
(
    (SELECT id_cliente FROM clientes WHERE DNI = 40555666),
    (SELECT id_tarifa FROM tarifas WHERE nombre = 'Tarifa Personalizada CrossFit'),
    15000.00,
    'Transferencia',
    NOW(),
    DATE_ADD(CURDATE(), INTERVAL 30 DAY)
),
(
    (SELECT id_cliente FROM clientes WHERE DNI = 41777888),
    (SELECT id_tarifa FROM tarifas WHERE nombre = 'Tarifa Integral Spinning'),
    12000.00,
    'MercadoPago',
    NOW(),
    DATE_ADD(CURDATE(), INTERVAL 30 DAY)
);


-- ===================================================================
-- 4. Carga masiva de clientes de prueba para testing y paginación
-- ===================================================================

INSERT INTO clientes (DNI, nombre, apellido, email, telefono, id_estado) VALUES
(38101001, 'Lucas', 'González', 'lucas.gonzalez@email.com', '1145000001', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Activo')),
(38101002, 'Sofía', 'Rodríguez', 'sofia.rodriguez@email.com', '1145000002', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Activo')),
(38101003, 'Mateo', 'Fernández', 'mateo.fernandez@email.com', '1145000003', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Activo')),
(38101004, 'Valentina', 'Díaz', 'valentina.diaz@email.com', '1145000004', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Activo')),
(38101005, 'Joaquín', 'Benítez', 'joaquin.benitez@email.com', '1145000005', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Activo')),
(38101006, 'Camila', 'Silva', 'camila.silva@email.com', '1145000006', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Inactivo')),
(38101007, 'Nicolás', 'Romero', 'nicolas.romero@email.com', '1145000007', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Activo')),
(38101008, 'Martina', 'Álvarez', 'martina.alvarez@email.com', '1145000008', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Activo')),
(38101009, 'Santiago', 'Torres', 'santiago.torres@email.com', '1145000009', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Pausado')),
(38101010, 'Lucía', 'Suárez', 'lucia.suarez@email.com', '1145000010', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Activo')),
(38101011, 'Matías', 'Ruiz', 'matias.ruiz@email.com', '1145000011', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Activo')),
(38101012, 'Julieta', 'Castro', 'julieta.castro@email.com', '1145000012', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Activo')),
(38101013, 'Agustín', 'Morales', 'agustin.morales@email.com', '1145000013', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Suspensión')),
(38101014, 'Paula', 'Ortíz', 'paula.ortiz@email.com', '1145000014', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Activo')),
(38101015, 'Franco', 'Gutiérrez', 'franco.gutierrez@email.com', '1145000015', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Activo')),
(38101016, 'Florencia', 'Ramos', 'florencia.ramos@email.com', '1145000016', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Activo')),
(38101017, 'Tomás', 'Giménez', 'tomas.gimenez@email.com', '1145000017', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Inactivo')),
(38101018, 'Micaela', 'Herrera', 'micaela.herrera@email.com', '1145000018', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Activo')),
(38101019, 'Lautaro', 'Medina', 'lautaro.medina@email.com', '1145000019', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Activo')),
(38101020, 'Antonella', 'Acosta', 'antonella.acosta@email.com', '1145000020', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Activo')),
(38101021, 'Facundo', 'Aguilar', 'facundo.aguilar@email.com', '1145000021', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Pausado')),
(38101022, 'Rocío', 'Pereyra', 'rocio.pereyra@email.com', '1145000022', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Activo')),
(38101023, 'Emiliano', 'Rojas', 'emiliano.rojas@email.com', '1145000023', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Activo')),
(38101024, 'Delfina', 'Molina', 'delfina.molina@email.com', '1145000024', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Activo')),
(38101025, 'Gonzalo', 'Cabrera', 'gonzalo.cabrera@email.com', '1145000025', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Suspensión')),
(38101026, 'Candela', 'Vega', 'candela.vega@email.com', '1145000026', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Activo')),
(38101027, 'Julián', 'Campos', 'julian.campos@email.com', '1145000027', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Activo')),
(38101028, 'Milagros', 'Figueroa', 'milagros.figueroa@email.com', '1145000028', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Activo')),
(38101029, 'Bruno', 'Cardozo', 'bruno.cardozo@email.com', '1145000029', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Inactivo')),
(38101030, 'Zoe', 'Paz', 'zoe.paz@email.com', '1145000030', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Activo')),
(38101031, 'Ramiro', 'Luna', 'ramiro.luna@email.com', '1145000031', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Activo')),
(38101032, 'Abril', 'Ríos', 'abril.rios@email.com', '1145000032', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Activo')),
(38101033, 'Ignacio', 'Navarro', 'ignacio.navarro@email.com', '1145000033', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Pausado')),
(38101034, 'Malena', 'Mendoza', 'malena.mendoza@email.com', '1145000034', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Activo')),
(38101035, 'Manuel', 'Vera', 'manuel.vera@email.com', '1145000035', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Activo')),
(38101036, 'Victoria', 'Ponce', 'victoria.ponce@email.com', '1145000036', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Activo')),
(38101037, 'Rodrigo', 'Peralta', 'rodrigo.peralta@email.com', '1145000037', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Suspensión')),
(38101038, 'Clara', 'Quiroga', 'clara.quiroga@email.com', '1145000038', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Activo')),
(38101039, 'Patricio', 'Godoy', 'patricio.godoy@email.com', '1145000039', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Activo')),
(38101040, 'Bianca', 'Miranda', 'bianca.miranda@email.com', '1145000040', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Inactivo')),
(38101041, 'Ezequiel', 'Bustos', 'ezequiel.bustos@email.com', '1145000041', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Activo')),
(38101042, 'Morena', 'Castillo', 'morena.castillo@email.com', '1145000042', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Activo')),
(38101043, 'Leonardo', 'Núñez', 'leonardo.nunez@email.com', '1145000043', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Activo')),
(38101044, 'Alma', 'Ferreyra', 'alma.ferreyra@email.com', '1145000044', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Pausado')),
(38101045, 'Esteban', 'Franco', 'esteban.franco@email.com', '1145000045', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Activo')),
(38101046, 'Kiara', 'Soto', 'kiara.soto@email.com', '1145000046', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Activo')),
(38101047, 'Gabriel', 'Carrizo', 'gabriel.carrizo@email.com', '1145000047', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Activo')),
(38101048, 'Sol', 'Maidana', 'sol.maidana@email.com', '1145000048', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Inactivo')),
(38101049, 'Damián', 'Ojeda', 'damian.ojeda@email.com', '1145000049', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Activo')),
(38101050, 'Emma', 'Villalba', 'emma.villalba@email.com', '1145000050', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Activo')),
(38101051, 'Federico', 'Arias', 'federico.arias@email.com', '1145000051', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Activo')),
(38101052, 'Lara', 'Coronel', 'lara.coronel@email.com', '1145000052', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Pausado')),
(38101053, 'Marcos', 'Vidal', 'marcos.vidal@email.com', '1145000053', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Activo')),
(38101054, 'Catalina', 'Heredia', 'catalina.heredia@email.com', '1145000054', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Inactivo')),
(38101055, 'Diego', 'Córdoba', 'diego.cordoba@email.com', '1145000055', (SELECT ID_est_clientes FROM estado_cliente WHERE Estado = 'Suspensión'));

