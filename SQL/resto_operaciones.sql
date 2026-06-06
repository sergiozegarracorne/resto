/*
 Navicat Premium Data Transfer

 Source Server         : local local
 Source Server Type    : MySQL
 Source Server Version : 80403 (8.4.3)
 Source Host           : localhost:3306
 Source Schema         : resto_operaciones

 Target Server Type    : MySQL
 Target Server Version : 80403 (8.4.3)
 File Encoding         : 65001

 Date: 01/03/2026 12:29:27
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

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
) ENGINE = InnoDB AUTO_INCREMENT = 32 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of pedido_detalles
-- ----------------------------
INSERT INTO `pedido_detalles` VALUES (29, 9, 2, 'Chicha Morada', 2, 5.00);
INSERT INTO `pedido_detalles` VALUES (30, 9, 7, 'Pizza Americana', 1, 45.00);
INSERT INTO `pedido_detalles` VALUES (31, 9, 16, 'Menu Pizza Familiar', 3, 60.00);

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
) ENGINE = InnoDB AUTO_INCREMENT = 10 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of pedidos
-- ----------------------------
INSERT INTO `pedidos` VALUES (9, 3, 11, 235.00, 'pendiente', '2026-01-18 22:45:06', '2026-01-18 23:41:04');

SET FOREIGN_KEY_CHECKS = 1;
