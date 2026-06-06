/*
 Navicat Premium Data Transfer

 Source Server         : local local
 Source Server Type    : MySQL
 Source Server Version : 80403 (8.4.3)
 Source Host           : localhost:3306
 Source Schema         : resto

 Target Server Type    : MySQL
 Target Server Version : 80403 (8.4.3)
 File Encoding         : 65001

 Date: 01/03/2026 12:29:14
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for categorias
-- ----------------------------
DROP TABLE IF EXISTS `categorias`;
CREATE TABLE `categorias`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `icono` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `estado` tinyint(1) NULL DEFAULT 1,
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  `deleted_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 19 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of categorias
-- ----------------------------
INSERT INTO `categorias` VALUES (1, 'Hamburguesas', '🍔', 1, '2025-12-27 00:17:32', '2025-12-27 00:17:32', NULL);
INSERT INTO `categorias` VALUES (2, 'Pizzas', '🍕', 1, '2025-12-27 00:17:32', '2025-12-27 00:17:32', NULL);
INSERT INTO `categorias` VALUES (3, 'Bebidas', '🥤', 1, '2025-12-27 00:17:32', '2025-12-27 00:17:32', NULL);
INSERT INTO `categorias` VALUES (4, 'Postres', '🍰', 1, '2025-12-27 00:17:32', '2025-12-27 00:17:32', NULL);
INSERT INTO `categorias` VALUES (5, 'Pollos', '🍗', 1, '2025-12-27 00:17:32', '2025-12-27 00:17:32', NULL);
INSERT INTO `categorias` VALUES (6, 'Entradas', '🍟', 1, '2025-12-27 00:17:32', '2025-12-27 00:17:32', NULL);
INSERT INTO `categorias` VALUES (7, 'Ensaladas', '🥗', 1, '2025-12-27 00:17:32', '2025-12-27 00:17:32', NULL);
INSERT INTO `categorias` VALUES (8, 'Cafetería', '☕', 1, '2025-12-27 00:17:32', '2025-12-27 00:17:32', NULL);
INSERT INTO `categorias` VALUES (9, 'Helados', '🍦', 1, '2025-12-27 00:17:32', '2025-12-27 00:17:32', NULL);
INSERT INTO `categorias` VALUES (10, 'Carnes', '🥩', 1, '2025-12-27 00:17:32', '2025-12-27 00:17:32', NULL);
INSERT INTO `categorias` VALUES (11, 'Pastas', '🍝', 1, '2025-12-27 00:17:32', '2025-12-27 00:17:32', NULL);
INSERT INTO `categorias` VALUES (12, 'Sopas', '🍜', 1, '2025-12-27 00:17:32', '2025-12-27 00:17:32', NULL);
INSERT INTO `categorias` VALUES (13, 'Desayunos', '🥞', 1, '2025-12-27 00:17:32', '2025-12-27 00:17:32', NULL);
INSERT INTO `categorias` VALUES (14, 'Vinos', '🍷', 1, '2025-12-27 00:17:32', '2025-12-27 00:17:32', NULL);
INSERT INTO `categorias` VALUES (15, 'Cervezas', '🍺', 1, '2025-12-27 00:17:32', '2025-12-27 00:17:32', NULL);
INSERT INTO `categorias` VALUES (16, 'Promociones', '⭐', 1, '2025-12-27 00:17:32', '2025-12-27 00:17:32', NULL);
INSERT INTO `categorias` VALUES (17, 'Sandwiches', '🥪', 1, '2025-12-27 00:17:32', '2025-12-27 00:17:32', NULL);
INSERT INTO `categorias` VALUES (18, 'Mariscos', '🦐', 1, '2025-12-27 00:17:32', '2025-12-27 00:17:32', NULL);

-- ----------------------------
-- Table structure for mesas
-- ----------------------------
DROP TABLE IF EXISTS `mesas`;
CREATE TABLE `mesas`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `id_piso` int UNSIGNED NOT NULL,
  `x` int NOT NULL DEFAULT 0,
  `y` int NOT NULL DEFAULT 0,
  `estado` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'libre',
  `id_padre` int UNSIGNED NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `mesas_id_piso_foreign`(`id_piso` ASC) USING BTREE,
  INDEX `fk_mesas_mesas_id_padre`(`id_padre` ASC) USING BTREE,
  CONSTRAINT `fk_mesas_mesas_id_padre` FOREIGN KEY (`id_padre`) REFERENCES `mesas` (`id`) ON DELETE CASCADE ON UPDATE SET NULL,
  CONSTRAINT `mesas_id_piso_foreign` FOREIGN KEY (`id_piso`) REFERENCES `pisos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 20 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of mesas
-- ----------------------------
INSERT INTO `mesas` VALUES (1, 'Mesa 1', 1, 20, 50, 'libre', NULL);
INSERT INTO `mesas` VALUES (2, 'Mesa 2', 1, 99, 50, 'libre', NULL);
INSERT INTO `mesas` VALUES (3, 'Mesa 3', 1, 436, 47, 'ocupada', NULL);
INSERT INTO `mesas` VALUES (4, 'Mesa 4', 1, 436, 118, 'libre', NULL);
INSERT INTO `mesas` VALUES (5, 'Mesa 5', 1, 438, 181, 'libre', NULL);
INSERT INTO `mesas` VALUES (6, 'Mesa 6', 1, 103, 205, 'libre', NULL);
INSERT INTO `mesas` VALUES (7, 'Mesa 1', 2, 20, 50, 'libre', NULL);
INSERT INTO `mesas` VALUES (8, 'Mesa 2', 2, 17, 107, 'libre', NULL);
INSERT INTO `mesas` VALUES (9, 'Mesa 3', 2, 230, 44, 'libre', NULL);
INSERT INTO `mesas` VALUES (10, 'Mesa 4', 2, 232, 102, 'libre', NULL);
INSERT INTO `mesas` VALUES (11, 'Mesa 5', 2, 232, 161, 'libre', NULL);
INSERT INTO `mesas` VALUES (12, 'Mesa 6', 2, 233, 221, 'libre', NULL);
INSERT INTO `mesas` VALUES (13, 'Mesa 1', 3, 12, 44, 'libre', 15);
INSERT INTO `mesas` VALUES (14, 'Mesa 2', 3, 13, 109, 'libre', 15);
INSERT INTO `mesas` VALUES (15, 'Mesa 3', 3, 97, 41, 'libre', NULL);
INSERT INTO `mesas` VALUES (16, 'Mesa 4', 3, 94, 105, 'libre', NULL);
INSERT INTO `mesas` VALUES (17, 'Mesa 5', 3, 276, 41, 'libre', NULL);
INSERT INTO `mesas` VALUES (18, 'Mesa 6', 3, 349, 40, 'libre', NULL);
INSERT INTO `mesas` VALUES (19, 'Mesa 7', 1, 104, 259, 'libre', 6);

-- ----------------------------
-- Table structure for migrations
-- ----------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `version` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `class` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `group` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `namespace` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `time` int NOT NULL,
  `batch` int UNSIGNED NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 14 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of migrations
-- ----------------------------
INSERT INTO `migrations` VALUES (1, '2025-12-23-061757', 'App\\Database\\Migrations\\CreateCategories', 'default', 'App', 1766471524, 1);
INSERT INTO `migrations` VALUES (2, '2025-12-23-061840', 'App\\Database\\Migrations\\CreateProducts', 'default', 'App', 1766471524, 1);
INSERT INTO `migrations` VALUES (3, '2025-12-23-061908', 'App\\Database\\Migrations\\CreateProductComponents', 'default', 'App', 1766471524, 1);
INSERT INTO `migrations` VALUES (4, '2025-12-25-002202', 'App\\Database\\Migrations\\CreateUsuarios', 'default', 'App', 1766622255, 2);
INSERT INTO `migrations` VALUES (5, '2025-12-25-002316', 'App\\Database\\Migrations\\CreateUsuarios', 'default', 'App', 1766622396, 3);
INSERT INTO `migrations` VALUES (6, '2025-12-27-001000', 'App\\Database\\Migrations\\CreateCategorias', 'default', 'App', 1766812230, 4);
INSERT INTO `migrations` VALUES (7, '2025-12-27-002000', 'App\\Database\\Migrations\\AddIconoToCategorias', 'default', 'App', 1766812356, 5);
INSERT INTO `migrations` VALUES (8, '2025-12-27-003000', 'App\\Database\\Migrations\\AddTimestampsToCategorias', 'default', 'App', 1766812547, 6);
INSERT INTO `migrations` VALUES (9, '2025-12-27-004000', 'App\\Database\\Migrations\\AddEstadoToCategorias', 'default', 'App', 1766812643, 7);
INSERT INTO `migrations` VALUES (10, '2025-12-27-010000', 'App\\Database\\Migrations\\AddColumnsToProductos', 'default', 'App', 1766815851, 8);
INSERT INTO `migrations` VALUES (11, '2026-01-09-021741', 'App\\Database\\Migrations\\TablesAndOrders', 'default', 'App', 1767925118, 9);
INSERT INTO `migrations` VALUES (12, '2026-01-09-062955', 'App\\Database\\Migrations\\AddIdPadreToMesas', 'default', 'App', 1767940237, 10);
INSERT INTO `migrations` VALUES (13, '2026-01-14-120000', 'App\\Database\\Migrations\\CreateOrders', 'operaciones', 'App', 1768443637, 11);

-- ----------------------------
-- Table structure for pedido_detalles
-- ----------------------------
DROP TABLE IF EXISTS `pedido_detalles`;
CREATE TABLE `pedido_detalles`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_pedido` int UNSIGNED NOT NULL,
  `id_producto` int UNSIGNED NOT NULL,
  `nombre_producto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `cantidad` int NOT NULL DEFAULT 1,
  `precio` decimal(10, 2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `pedido_detalles_id_pedido_foreign`(`id_pedido` ASC) USING BTREE,
  CONSTRAINT `pedido_detalles_id_pedido_foreign` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 22 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of pedido_detalles
-- ----------------------------
INSERT INTO `pedido_detalles` VALUES (5, 2, 16, 'Menu Pizza Familiar', 1, 60.00);
INSERT INTO `pedido_detalles` VALUES (6, 2, 7, 'Pizza Americana', 1, 45.00);
INSERT INTO `pedido_detalles` VALUES (7, 2, 13, 'Pizza 4 Quesos', 1, 50.00);
INSERT INTO `pedido_detalles` VALUES (8, 1, 7, 'Pizza Americana', 1, 45.00);
INSERT INTO `pedido_detalles` VALUES (9, 1, 9, 'Pizza Hawaiana', 1, 42.00);
INSERT INTO `pedido_detalles` VALUES (12, 3, 1, '1/4 de Pollo', 1, 15.00);
INSERT INTO `pedido_detalles` VALUES (13, 3, 7, 'Pizza Americana', 1, 45.00);
INSERT INTO `pedido_detalles` VALUES (14, 3, 14, 'Pizza Continental', 1, 47.00);
INSERT INTO `pedido_detalles` VALUES (15, 3, 9, 'Pizza Hawaiana', 1, 42.00);
INSERT INTO `pedido_detalles` VALUES (20, 4, 16, 'Menu Pizza Familiar', 3, 60.00);
INSERT INTO `pedido_detalles` VALUES (21, 4, 13, 'Pizza 4 Quesos', 1, 50.00);

-- ----------------------------
-- Table structure for pedidos
-- ----------------------------
DROP TABLE IF EXISTS `pedidos`;
CREATE TABLE `pedidos`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_mesa` int UNSIGNED NULL DEFAULT NULL,
  `id_usuario` int UNSIGNED NOT NULL,
  `total` decimal(10, 2) NOT NULL DEFAULT 0.00,
  `estado` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pendiente',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of pedidos
-- ----------------------------
INSERT INTO `pedidos` VALUES (1, 2, 16, 87.00, 'pendiente', '2026-01-08 21:37:46', '2026-01-08 23:23:07');
INSERT INTO `pedidos` VALUES (2, 1, 16, 155.00, 'pendiente', '2026-01-08 21:37:58', '2026-01-08 21:39:01');
INSERT INTO `pedidos` VALUES (3, 6, 5, 149.00, 'pendiente', '2026-01-08 23:42:26', '2026-01-11 20:33:50');
INSERT INTO `pedidos` VALUES (4, 19, 5, 230.00, 'pendiente', '2026-01-09 01:20:21', '2026-01-11 23:44:42');

-- ----------------------------
-- Table structure for pisos
-- ----------------------------
DROP TABLE IF EXISTS `pisos`;
CREATE TABLE `pisos`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `orden` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of pisos
-- ----------------------------
INSERT INTO `pisos` VALUES (1, 'Piso 1', 1);
INSERT INTO `pisos` VALUES (2, 'Piso 2', 2);
INSERT INTO `pisos` VALUES (3, 'Piso 3', 3);

-- ----------------------------
-- Table structure for producto_componentes
-- ----------------------------
DROP TABLE IF EXISTS `producto_componentes`;
CREATE TABLE `producto_componentes`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `producto_padre_id` int UNSIGNED NOT NULL,
  `producto_hijo_id` int UNSIGNED NOT NULL,
  `cantidad` int NOT NULL DEFAULT 1,
  `es_opcional` tinyint(1) NOT NULL DEFAULT 0,
  `grupo_opcion` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `producto_componentes_producto_padre_id_foreign`(`producto_padre_id` ASC) USING BTREE,
  INDEX `producto_componentes_producto_hijo_id_foreign`(`producto_hijo_id` ASC) USING BTREE,
  CONSTRAINT `producto_componentes_producto_hijo_id_foreign` FOREIGN KEY (`producto_hijo_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `producto_componentes_producto_padre_id_foreign` FOREIGN KEY (`producto_padre_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 8 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of producto_componentes
-- ----------------------------
INSERT INTO `producto_componentes` VALUES (1, 6, 1, 1, 0, 'fondo');
INSERT INTO `producto_componentes` VALUES (2, 6, 4, 1, 1, 'entrada');
INSERT INTO `producto_componentes` VALUES (3, 6, 5, 1, 1, 'entrada');
INSERT INTO `producto_componentes` VALUES (4, 6, 2, 1, 1, 'bebida');
INSERT INTO `producto_componentes` VALUES (5, 6, 3, 1, 1, 'bebida');
INSERT INTO `producto_componentes` VALUES (6, 16, 7, 1, 0, NULL);
INSERT INTO `producto_componentes` VALUES (7, 16, 15, 1, 0, NULL);

-- ----------------------------
-- Table structure for productos
-- ----------------------------
DROP TABLE IF EXISTS `productos`;
CREATE TABLE `productos`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `categoria_id` int UNSIGNED NOT NULL,
  `nombre` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `precio` decimal(10, 2) NOT NULL DEFAULT 0.00,
  `imagen` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `es_combo` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  `deleted_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `productos_categoria_id_foreign`(`categoria_id` ASC) USING BTREE,
  CONSTRAINT `productos_categoria_id_foreign` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 17 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of productos
-- ----------------------------
INSERT INTO `productos` VALUES (1, 1, '1/4 de Pollo', 15.00, NULL, 0, NULL, NULL, NULL);
INSERT INTO `productos` VALUES (2, 3, 'Chicha Morada', 5.00, NULL, 0, NULL, NULL, NULL);
INSERT INTO `productos` VALUES (3, 3, 'Gaseosa Inka Cola', 4.00, NULL, 0, NULL, NULL, NULL);
INSERT INTO `productos` VALUES (4, 4, 'Entrada: Sopa de Morón', 0.00, NULL, 0, NULL, NULL, NULL);
INSERT INTO `productos` VALUES (5, 4, 'Entrada: Ensalada Rusa', 0.00, NULL, 0, NULL, NULL, NULL);
INSERT INTO `productos` VALUES (6, 4, 'Menú Ejecutivo', 12.00, NULL, 1, NULL, NULL, NULL);
INSERT INTO `productos` VALUES (7, 2, 'Pizza Americana', 45.00, 'https://cdn-icons-png.flaticon.com/512/3132/3132693.png', 0, '2025-12-27 01:13:25', '2025-12-27 01:13:25', NULL);
INSERT INTO `productos` VALUES (8, 2, 'Pizza Pepperoni', 48.00, 'https://cdn-icons-png.flaticon.com/512/3595/3595455.png', 0, '2025-12-27 01:13:25', '2025-12-27 01:13:25', NULL);
INSERT INTO `productos` VALUES (9, 2, 'Pizza Hawaiana', 42.00, 'https://cdn-icons-png.flaticon.com/512/3595/3595453.png', 0, '2025-12-27 01:13:25', '2025-12-27 01:13:25', NULL);
INSERT INTO `productos` VALUES (10, 2, 'Pizza Suprema', 55.00, 'https://cdn-icons-png.flaticon.com/512/6978/6978255.png', 0, '2025-12-27 01:13:25', '2025-12-27 01:13:25', NULL);
INSERT INTO `productos` VALUES (11, 2, 'Pizza Vegetariana', 40.00, 'https://cdn-icons-png.flaticon.com/512/1404/1404945.png', 0, '2025-12-27 01:13:25', '2025-12-27 01:13:25', NULL);
INSERT INTO `productos` VALUES (12, 2, 'Pizza Carnivora', 58.00, 'https://cdn-icons-png.flaticon.com/512/706/706934.png', 0, '2025-12-27 01:13:25', '2025-12-27 01:13:25', NULL);
INSERT INTO `productos` VALUES (13, 2, 'Pizza 4 Quesos', 50.00, 'https://cdn-icons-png.flaticon.com/512/3595/3595458.png', 0, '2025-12-27 01:13:25', '2025-12-27 01:13:25', NULL);
INSERT INTO `productos` VALUES (14, 2, 'Pizza Continental', 47.00, 'https://cdn-icons-png.flaticon.com/512/6978/6978255.png', 0, '2025-12-27 01:13:25', '2025-12-27 01:13:25', NULL);
INSERT INTO `productos` VALUES (15, 3, 'Coca Cola 1.5L', 10.00, 'https://cdn-icons-png.flaticon.com/512/2722/2722527.png', 0, '2025-12-27 01:13:25', '2025-12-27 01:13:25', NULL);
INSERT INTO `productos` VALUES (16, 2, 'Menu Pizza Familiar', 60.00, 'https://cdn-icons-png.flaticon.com/512/3075/3075977.png', 1, '2025-12-27 01:13:25', '2025-12-27 01:13:25', NULL);

-- ----------------------------
-- Table structure for usuarios
-- ----------------------------
DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios`  (
  `id_usuario` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `clave` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `rol` enum('sudo','admin','supervisor','vendedor') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'vendedor',
  `imagen` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  `deleted_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id_usuario`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 20 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of usuarios
-- ----------------------------
INSERT INTO `usuarios` VALUES (1, 'Super Admin', '$2y$10$zzuPPynivHj8Dg/21rUAkOwRFb6CEFddqbHGk6UcVlvZ6gWYVUT9m', 'sudo', 'https://cdn-icons-png.flaticon.com/512/2922/2922510.png', 1, '2025-12-26 22:44:09', '2025-12-26 22:44:09', NULL);
INSERT INTO `usuarios` VALUES (2, 'Administrador', '$2y$10$6sgmTSmp2u8/b0mUWjCxaepZyJ9lNh6rhIcSPng.AjSRMPFGZeyYe', 'admin', 'https://cdn-icons-png.flaticon.com/512/2922/2922506.png', 1, '2025-12-26 22:44:09', '2025-12-26 22:44:09', NULL);
INSERT INTO `usuarios` VALUES (3, 'Supervisor Tienda', '$2y$10$U4TdTMTUg5Fty5F3FzkyN.pFytcu8W7JxoU1RQTGPIegTbbw6CqE.', 'supervisor', 'https://cdn-icons-png.flaticon.com/512/2922/2922561.png', 1, '2025-12-26 22:44:09', '2025-12-26 22:44:09', NULL);
INSERT INTO `usuarios` VALUES (4, 'Juan Vendedor', '$2y$10$GFYYU7g7Pcv4AmuIWZKNGe/DSApCnXbcvl5pa9kmKeDrC/zKdSn02', 'vendedor', 'https://cdn-icons-png.flaticon.com/512/4140/4140048.png', 1, '2025-12-26 22:44:09', '2025-12-26 22:44:09', NULL);
INSERT INTO `usuarios` VALUES (5, 'Maria Vendedora', '$2y$10$/70ABY4IQmCR9SmxYx5/D.Pg7lYmg.4WiH4THuMwMMkE107HExGjW', 'vendedor', 'https://cdn-icons-png.flaticon.com/512/4140/4140047.png', 1, '2025-12-26 22:44:09', '2025-12-26 22:44:09', NULL);
INSERT INTO `usuarios` VALUES (6, 'Usuario Invitado', '$2y$10$cq6KiETH5h6LFWo.QisBa.beAprgei/lH.y8IHOl8wxuH1BgYyALm', '', 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png', 1, '2025-12-26 22:44:09', '2025-12-26 22:44:09', NULL);
INSERT INTO `usuarios` VALUES (7, 'Vendedor Extra 1', '$2y$10$JSW2.n/sqCeipXE723BZEer466y4.75e9DlHdouY4XVZDk7VDOg8y', 'vendedor', 'https://cdn-icons-png.flaticon.com/512/847/847969.png', 1, '2025-12-26 22:44:09', '2025-12-26 22:44:09', NULL);
INSERT INTO `usuarios` VALUES (8, 'Vendedor Extra 2', '$2y$10$Y1IYNdnOrA.McgGXuz4Hwu9KCsMNisz434Xi6IRZkBOfDQtG4qDey', 'vendedor', 'https://cdn-icons-png.flaticon.com/512/847/847969.png', 1, '2025-12-26 22:44:09', '2025-12-26 22:44:09', NULL);
INSERT INTO `usuarios` VALUES (9, 'Vendedor Extra 3', '$2y$10$FSUyI/rq44f1PKZKYm6iOOcxtwVHrirQreuOJseD60BL4TL70Dkte', 'vendedor', 'https://cdn-icons-png.flaticon.com/512/847/847969.png', 1, '2025-12-26 22:44:09', '2025-12-26 22:44:09', NULL);
INSERT INTO `usuarios` VALUES (10, 'Vendedor Extra 4', '$2y$10$VU/TFSSPmKf/QH5gDPF2H.2pEFIQi9fMq6YblSuW9pq.JLsiEI2o.', 'vendedor', 'https://cdn-icons-png.flaticon.com/512/847/847969.png', 1, '2025-12-26 22:44:09', '2025-12-26 22:44:09', NULL);
INSERT INTO `usuarios` VALUES (11, 'Vendedor Extra 5', '$2y$10$oo4buwr6ZE0742.XPyrsWesSpCCReetIQ.u97jOODAaxLl5n3cewu', 'vendedor', 'https://cdn-icons-png.flaticon.com/512/847/847969.png', 1, '2025-12-26 22:44:09', '2025-12-26 22:44:09', NULL);
INSERT INTO `usuarios` VALUES (12, 'Vendedor Extra 6', '$2y$10$FlpW9/vyGwAkP6pOwsNQAeoo.pykzcDI3SjlR2ZqjeBL7zkzxL9Y6', 'vendedor', 'https://cdn-icons-png.flaticon.com/512/847/847969.png', 1, '2025-12-26 22:44:09', '2025-12-26 22:44:09', NULL);
INSERT INTO `usuarios` VALUES (13, 'Vendedor Extra 7', '$2y$10$kHLe0vRDHE3.EOxc652TROUKMoto9uqjlwAAjpHMUHlCQMHx/.WcK', 'vendedor', 'https://cdn-icons-png.flaticon.com/512/847/847969.png', 1, '2025-12-26 22:44:09', '2025-12-26 22:44:09', NULL);
INSERT INTO `usuarios` VALUES (14, 'Vendedor Extra 8', '$2y$10$75I7e/WQ6vs5bd6st/DSSe5teu0PpyrFuPucU4UOQbNA0380undZa', 'vendedor', 'https://cdn-icons-png.flaticon.com/512/847/847969.png', 1, '2025-12-26 22:44:09', '2025-12-26 22:44:09', NULL);
INSERT INTO `usuarios` VALUES (15, 'Vendedor Extra 9', '$2y$10$fpQ4jTKKZ8IehmlKKBrANeVayBgBWMIE2.gMHwrwQ1BEUMZhOc9d.', 'vendedor', 'https://cdn-icons-png.flaticon.com/512/847/847969.png', 1, '2025-12-26 22:44:09', '2025-12-26 22:44:09', NULL);
INSERT INTO `usuarios` VALUES (16, 'Vendedor Extra 10', '$2y$10$vgw39HU8CnX2jhsPQqlZNur2d25M16R8Rl3NgNlsYcfC2Qh4TInZq', 'vendedor', 'https://cdn-icons-png.flaticon.com/512/847/847969.png', 1, '2025-12-26 22:44:09', '2025-12-26 22:44:09', NULL);
INSERT INTO `usuarios` VALUES (17, 'Vendedor Extra 11', '$2y$10$xKllIiz/yBWPCgcqGvqHf.o.QEn2lPHXVt/XKK0vVhCpt9J2JSJ7K', 'vendedor', 'https://cdn-icons-png.flaticon.com/512/847/847969.png', 1, '2025-12-26 22:44:09', '2025-12-26 22:44:09', NULL);
INSERT INTO `usuarios` VALUES (18, 'Vendedor Extra 12', '$2y$10$ZZ.U7MzzDlRuWDVEjzZWAuqpzSirvXtbA.YxjEwbd.YeZtGo.9L.e', 'vendedor', 'https://cdn-icons-png.flaticon.com/512/847/847969.png', 1, '2025-12-26 22:44:09', '2025-12-26 22:44:09', NULL);
INSERT INTO `usuarios` VALUES (19, 'Vendedor Extra 13', '$2y$10$WKV1DsFsbu5RFwXUEMHM6uAFJaTS4q7IAxCBrlb9nnFpcDNPun23G', 'vendedor', 'https://cdn-icons-png.flaticon.com/512/847/847969.png', 1, '2025-12-26 22:44:09', '2025-12-26 22:44:09', NULL);

SET FOREIGN_KEY_CHECKS = 1;
