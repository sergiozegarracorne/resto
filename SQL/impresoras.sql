-- ============================================================
-- Sistema de impresoras de red y enrutamiento de pedidos
-- Ejecutar en la BD: resto
-- ============================================================

CREATE TABLE IF NOT EXISTS `impresoras` (
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `nombre`     VARCHAR(100) NOT NULL,
    `ip`         VARCHAR(15)  NOT NULL,
    `puerto`     INT          NOT NULL DEFAULT 9100,
    `activo`     TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at` DATETIME     NULL,
    `updated_at` DATETIME     NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Impresoras de red configuradas';

-- Regla por categoría: qué impresora recibe los items de esa categoría
-- id_impresora = NULL significa "no imprimir" (regla explícita de silencio)
-- Sin fila = sin regla (tampoco se imprime, pero se distingue en UI)
CREATE TABLE IF NOT EXISTS `impresora_categoria` (
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `id_categoria`  INT NOT NULL,
    `id_impresora`  INT NULL COMMENT 'NULL = no imprimir esta categoría',
    UNIQUE KEY `uk_categoria` (`id_categoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Regla de impresión por categoría';

-- Excepción por producto (sobreescribe la regla de la categoría)
-- id_impresora = NULL significa "no imprimir este producto específico"
CREATE TABLE IF NOT EXISTS `impresora_producto_excepcion` (
    `id`           INT AUTO_INCREMENT PRIMARY KEY,
    `id_producto`  INT NOT NULL,
    `id_impresora` INT NULL COMMENT 'NULL = no imprimir este producto',
    UNIQUE KEY `uk_producto` (`id_producto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Excepciones de impresión por producto';
