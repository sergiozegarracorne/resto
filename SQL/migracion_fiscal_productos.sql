-- ============================================================
-- MIGRACIÓN: Campos fiscales, alertas y catálogo ampliado
-- ============================================================

-- ─── BASE: resto ────────────────────────────────────────────

USE resto;

CREATE TABLE IF NOT EXISTS config_igv (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    porcentaje   DECIMAL(5,2) NOT NULL COMMENT 'Ej: 10.50, 18.00',
    fecha_inicio DATE         NOT NULL,
    fecha_fin    DATE         NULL     COMMENT 'NULL = tasa vigente actualmente',
    created_at   DATETIME     DEFAULT CURRENT_TIMESTAMP
);

INSERT IGNORE INTO config_igv (id, porcentaje, fecha_inicio) VALUES (1, 10.50, '2025-01-01');

CREATE TABLE IF NOT EXISTS config_icbr (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    monto        DECIMAL(5,2) NOT NULL COMMENT 'Monto fijo por bolsa: 0.50, 0.40',
    fecha_inicio DATE         NOT NULL,
    fecha_fin    DATE         NULL     COMMENT 'NULL = monto vigente actualmente',
    created_at   DATETIME     DEFAULT CURRENT_TIMESTAMP
);

INSERT IGNORE INTO config_icbr (id, monto, fecha_inicio) VALUES (1, 0.50, '2025-01-01');

ALTER TABLE productos
    ADD COLUMN codigo_rapido    VARCHAR(8)                                          NULL        COMMENT 'Código corto POS' AFTER nombre,
    ADD COLUMN descripcion      VARCHAR(150)                                        NULL        COMMENT 'Descripción en ticket' AFTER codigo_rapido,
    ADD COLUMN precio_costo     DECIMAL(10,2)                                       NULL        COMMENT 'Costo de compra',
    ADD COLUMN precio_libre     TINYINT(1)    NOT NULL DEFAULT 0                    COMMENT 'Permite cambiar precio al vender',
    ADD COLUMN precio_max       DECIMAL(10,2)                                       NULL        COMMENT 'Precio máximo si precio_libre=1',
    ADD COLUMN por_peso         TINYINT(1)    NOT NULL DEFAULT 0                    COMMENT 'Venta por kilogramos',
    ADD COLUMN tipo_igv         ENUM('gravado','exonerado','inafecto') NOT NULL DEFAULT 'gravado',
    ADD COLUMN aplica_icbr      TINYINT(1)    NOT NULL DEFAULT 0                    COMMENT 'Cobra impuesto bolsa plástica',
    ADD COLUMN fecha_vencimiento DATE                                               NULL        COMMENT 'Vencimiento del producto (opcional)',
    ADD COLUMN stock_minimo     DECIMAL(10,2) NOT NULL DEFAULT 0,
    ADD COLUMN stock_maximo     DECIMAL(10,2)                                       NULL,
    ADD COLUMN activo           TINYINT(1)    NOT NULL DEFAULT 1                    COMMENT '0 = no aparece en POS',
    ADD COLUMN orden            INT           NOT NULL DEFAULT 0;

-- ─── BASE: resto_operaciones ─────────────────────────────────

USE resto_operaciones;

ALTER TABLE rest_venta_detalle
    ADD COLUMN tipo_igv         VARCHAR(12)   NOT NULL DEFAULT 'gravado',
    ADD COLUMN igv_porcentaje   DECIMAL(5,2)  NOT NULL DEFAULT 0  COMMENT 'Tasa IGV al momento de la venta',
    ADD COLUMN igv_monto        DECIMAL(10,2) NOT NULL DEFAULT 0  COMMENT 'IGV incluido en el precio',
    ADD COLUMN aplica_icbr      TINYINT(1)    NOT NULL DEFAULT 0,
    ADD COLUMN icbr_monto       DECIMAL(10,2) NOT NULL DEFAULT 0  COMMENT 'ICBR cobrado (monto × cantidad)';

ALTER TABLE rest_venta
    ADD COLUMN igv_porcentaje   DECIMAL(5,2)  NOT NULL DEFAULT 0  COMMENT 'Tasa IGV vigente al cobrar',
    ADD COLUMN total_neto       DECIMAL(10,2) NOT NULL DEFAULT 0  COMMENT 'Total sin impuestos',
    ADD COLUMN total_igv        DECIMAL(10,2) NOT NULL DEFAULT 0  COMMENT 'Total IGV',
    ADD COLUMN total_icbr       DECIMAL(10,2) NOT NULL DEFAULT 0  COMMENT 'Total ICBR';
