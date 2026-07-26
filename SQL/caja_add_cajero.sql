-- Agrega cajero a la tabla de ventas para filtros de caja
ALTER TABLE rest_venta
    ADD COLUMN id_usuario    INT          NULL DEFAULT NULL AFTER id_mesa,
    ADD COLUMN nombre_cajero VARCHAR(100) NULL DEFAULT NULL AFTER id_usuario;
