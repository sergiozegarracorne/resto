-- Agrega código de usuario (identificador corto único)
ALTER TABLE `usuarios`
    ADD COLUMN `codigo` VARCHAR(20) NULL DEFAULT NULL UNIQUE COMMENT 'Código corto del usuario' AFTER `id_usuario`;
