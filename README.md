# Resto — Sistema POS para Restaurante

Sistema de punto de venta para restaurante construido con **CodeIgniter 4**. Permite gestionar mesas por pisos, tomar pedidos, cobrar y registrar ventas.

---

## Stack

- **Backend:** PHP 8.1+ / CodeIgniter 4
- **Frontend:** HTML + CSS + JS vanilla (sin framework)
- **BD:** MySQL via MAMP (dos bases de datos separadas)
- **Servidor local:** MAMP (`http://10.0.10.12:8081`)

---

## Bases de datos

El sistema usa dos bases de datos con responsabilidades bien separadas:

| Grupo CI4     | Base de datos       | Propósito                                                                 |
|---------------|---------------------|---------------------------------------------------------------------------|
| `default`     | `resto`             | Datos internos del sistema: catálogo, usuarios, almacén, configuración    |
| `operaciones` | `resto_operaciones` | Todas las operaciones de negocio: ventas, deliveries, canjes, pedidos, etc. |

> `resto` es la fuente de verdad del sistema (qué existe).
> `resto_operaciones` es el registro de todo lo que ocurre (qué pasó).

El grupo se define por modelo con `$DBGroup`.

### Tablas — `resto`

| Tabla                  | Descripción                                               |
|------------------------|-----------------------------------------------------------|
| `categorias`           | Categorías de productos                                   |
| `productos`            | Productos del menú                                        |
| `producto_componentes` | Componentes/ingredientes                                  |
| `mesas`                | Mesas con posición X/Y y estado                           |
| `pisos`                | Pisos del local                                           |
| `usuarios`             | Vendedores/meseros                                        |
| `insumos`              | Ingredientes del almacén con stock                        |
| `roles`                | Catálogo de roles del sistema (mozo, caja, admin, sudo…) |
| `rutas`                | Páginas gestionadas con control de acceso                 |
| `rutas_permisos`       | Permisos por rol × ruta (`puede_ver`, `puede_editar`)     |
| `botones_catalogo`     | Botones configurables de la barra de acciones en ventas   |

### Tablas — `resto_operaciones`

| Tabla                | PK           | Descripción                             |
|----------------------|--------------|-----------------------------------------|
| `pedidos`            | `id`         | Cabecera de pedido por mesa             |
| `pedido_detalles`    | `id`         | Ítems de cada pedido                    |
| `rest_venta`         | `id_venta`   | Cabecera de venta cobrada               |
| `rest_venta_detalle` | `id_detalle` | Ítems de cada venta                     |
| `compras`            | `id`         | Cabecera de compra de insumos           |
| `compra_detalles`    | `id`         | Ítems de cada compra                    |

> `rest_venta.fecha_registro` usa `DEFAULT CURRENT_TIMESTAMP` — no se envía en el insert.

---

## Modelos

| Modelo                          | DBGroup       | Tabla                  |
|---------------------------------|---------------|------------------------|
| `CategoriaModel`                | `default`     | `categorias`           |
| `ProductoModel`                 | `default`     | `productos`            |
| `ProductoComponenteModel`       | `default`     | `producto_componentes` |
| `MesaModel`                     | `default`     | `mesas`                |
| `PisoModel`                     | `default`     | `pisos`                |
| `UsuarioModel`                  | `default`     | `usuarios`             |
| `PedidoOperacionesModel`        | `operaciones` | `pedidos`              |
| `PedidoDetalleOperacionesModel` | `operaciones` | `pedido_detalles`      |
| `VentaModel`                    | `operaciones` | `rest_venta`           |
| `VentaDetalleModel`             | `operaciones` | `rest_venta_detalle`   |
| `InsumoModel`                   | `default`     | `insumos`              |
| `CompraModel`                   | `operaciones` | `compras`              |
| `CompraDetalleModel`            | `operaciones` | `compra_detalles`      |

---

## Controladores API

Los endpoints viven en `app/Controllers/Api/` — un archivo por módulo.

| Archivo                | Módulo          | Responsabilidad                                   |
|------------------------|-----------------|---------------------------------------------------|
| `AuthApi.php`          | Auth            | Login de vendedores, timestamp                    |
| `MesasApi.php`         | Mesas           | Pisos, unir/separar, posiciones                   |
| `PedidosApi.php`       | Pedidos         | Crear, actualizar y cancelar pedidos              |
| `VentasApi.php`        | Ventas          | Registrar cobro de una venta                      |
| `AlmacenApi.php`       | Almacén         | Insumos, stock, compras                           |
| `RolesApi.php`         | Roles/Permisos  | CRUD de roles, rutas, permisos y botones          |

> Todos extienden `BaseApiController` (usa `ResponseTrait` de CI4).
> `RolesApi` requiere rol `administrador` o `sudo` (`puedeGestionar()`).

## API Endpoints

| Método | Ruta                               | Controlador           | Descripción                                            |
|--------|------------------------------------|-----------------------|--------------------------------------------------------|
| GET    | `/api/time`                        | AuthApi               | Timestamp del servidor                                 |
| POST   | `/api/verificar_vendedor`          | AuthApi               | Login del vendedor por id + clave (sesión)             |
| GET    | `/api/get_pisos_mesas`             | MesasApi              | Pisos con sus mesas y estado                           |
| POST   | `/api/unir_mesas`                  | MesasApi              | Unir mesas secundarias a una principal                 |
| POST   | `/api/separar_mesas`               | MesasApi              | Desasociar mesas unidas                                |
| POST   | `/api/update_mesas_positions`      | MesasApi              | Guardar posiciones X/Y de mesas en el panel            |
| GET    | `/api/get_mesa_pedido/:id`         | PedidosApi            | Pedido activo (pendiente) de una mesa                  |
| POST   | `/api/save_pedido`                 | PedidosApi            | Crear o actualizar pedido de una mesa                  |
| POST   | `/api/cancelar_pedido`             | PedidosApi            | Eliminar pedido y liberar mesa                         |
| POST   | `/api/cobrar_pedido`               | VentasApi             | Registrar venta en `rest_venta` + `rest_venta_detalle` |
| GET    | `/api/get_insumos`                 | AlmacenApi            | Lista de insumos con stock actual                      |
| POST   | `/api/save_insumo`                 | AlmacenApi            | Crear o editar un insumo del almacén                   |
| POST   | `/api/save_compra`                 | AlmacenApi            | Registrar compra y sumar stock a los insumos           |
| GET    | `/api/roles/catalogo`              | RolesApi              | Lista de roles                                         |
| POST   | `/api/roles/save_rol`              | RolesApi              | Crear rol                                              |
| POST   | `/api/roles/delete_rol`            | RolesApi              | Eliminar rol (cascade en permisos)                     |
| GET    | `/api/roles/rutas`                 | RolesApi              | Catálogo de rutas gestionadas                          |
| GET    | `/api/roles/rutas_sistema`         | RolesApi              | Rutas GET de Routes.php (para el selector)             |
| POST   | `/api/roles/save_ruta`             | RolesApi              | Crear/editar ruta (auto-crea permisos por rol)         |
| POST   | `/api/roles/toggle_ruta`           | RolesApi              | Activar/desactivar ruta                                |
| POST   | `/api/roles/delete_ruta`           | RolesApi              | Eliminar ruta                                          |
| GET    | `/api/roles/permisos_rutas`        | RolesApi              | Matriz roles × rutas con permisos                      |
| POST   | `/api/roles/update_permiso_ruta`   | RolesApi              | Cambiar `puede_ver` / `puede_editar`                   |
| GET    | `/api/roles/botones`               | RolesApi              | Catálogo de botones de la barra de acciones            |
| POST   | `/api/roles/save_boton`            | RolesApi              | Crear/editar botón                                     |
| POST   | `/api/roles/delete_boton`          | RolesApi              | Eliminar botón                                         |

### Payload `cobrar_pedido`

```json
{
  "id_mesa": 3,
  "metodo": "efectivo",
  "total": 55.00,
  "recibido": 60.00,
  "items": [
    { "id": 7, "nombre": "Pizza Americana", "cantidad": 1, "precio": 45.00 },
    { "id": 2, "nombre": "Chicha Morada",   "cantidad": 2, "precio": 5.00 }
  ]
}
```

---

## Vistas

| Ruta                  | Controlador        | Descripción                             |
|-----------------------|--------------------|-----------------------------------------|
| `/`                   | `Home::index`      | Selector de vendedor / inicio de turno  |
| `/venta`              | `Venta::index`     | POS principal (mesas + pedido)          |
| `/venta/:id`          | `Venta::index`     | POS con mesa preseleccionada            |
| `/panel`              | `Panel::index`     | Panel admin de mesas (drag & drop)      |
| `/ingress/:id`        | `Home::ingress`    | Entrada directa por vendedor            |
| `/almacen/compras`    | `Almacen::compras` | Registrar una compra de insumos         |
| `/almacen/insumos`    | `Almacen::insumos` | Ver y editar stock del almacén          |
| `/roles`              | `Roles::index`     | Gestión de roles, rutas y permisos      |

---

## Control de acceso

El sistema de permisos funciona en dos capas:

### 1. Filtro de rutas (`PermisoRutaFilter`)

Filtro global CI4 aplicado a todas las rutas excepto `api/*`. Si una ruta está en `rutas` con `activo=1` y el rol activo tiene `puede_ver=0` en `rutas_permisos`, redirige al inicio.

### 2. Barra de acciones (`action_bar.php`)

Los botones de la barra de ventas se cargan desde `botones_catalogo` (BD) y se filtran por `rutas_permisos`. Botones fijos (siempre visibles para todos los roles):

| Botón       | Acción                    | Condición              |
|-------------|---------------------------|------------------------|
| 🍽️ Mesas   | `toggleMesasOverlay()`    | Siempre (salvo bloqueado) |
| ↩️ Corregir | `undoUltimaAccion()`      | Siempre                |
| 👤 Vendedores | Link `/`               | Siempre                |
| 🏠 Panel    | Link `/panel`             | Solo si rol ≠ `mozo`   |

### 3. Panel de configuración (`/roles`)

4 pestañas: **Roles** (CRUD), **Rutas** (catálogo + toggle activo), **Permisos** (matriz rutas × roles con toggles), **Botones** (catálogo de `botones_catalogo`).

---

## Configuración local

1. Copiar `_env` a `.env`
2. Ajustar `app.baseURL` y credenciales de BD si es necesario
3. Crear las dos BDs: `resto` y `resto_operaciones`
4. Importar `SQL/resto.sql` y `SQL/resto_operaciones.sql`
5. Apuntar MAMP a la carpeta `/public`

### SQL para tablas de permisos (ejecutar en `resto`)

```sql
CREATE TABLE `roles` (
  `id`     TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(40) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `rutas` (
  `id`     TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(60)  NOT NULL,
  `alias`  VARCHAR(20)  NOT NULL DEFAULT '',
  `ruta`   VARCHAR(100) NOT NULL,
  `activo` TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ruta` (`ruta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `rutas_permisos` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_rol`       TINYINT UNSIGNED NOT NULL,
  `id_ruta`      TINYINT UNSIGNED NOT NULL,
  `puede_ver`    TINYINT(1) NOT NULL DEFAULT 1,
  `puede_editar` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_rol_ruta` (`id_rol`, `id_ruta`),
  CONSTRAINT `fk_rp_rol`  FOREIGN KEY (`id_rol`)  REFERENCES `roles`(`id`)  ON DELETE CASCADE,
  CONSTRAINT `fk_rp_ruta` FOREIGN KEY (`id_ruta`) REFERENCES `rutas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `botones_catalogo` (
  `boton_key` VARCHAR(40)   NOT NULL,
  `label`     VARCHAR(60)   NOT NULL,
  `icon`      VARCHAR(10)   NOT NULL DEFAULT '🔘',
  `tipo`      ENUM('link','button') NOT NULL DEFAULT 'link',
  `onclick`   VARCHAR(200)  NULL DEFAULT NULL,
  `href`      VARCHAR(100)  NULL DEFAULT NULL,
  `color`     VARCHAR(20)   NOT NULL DEFAULT 'indigo',
  `orden`     TINYINT UNSIGNED NOT NULL DEFAULT 99,
  PRIMARY KEY (`boton_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## Estado actual (WIP)

- [x] Gestión de mesas por pisos (drag & drop de posiciones)
- [x] Login de vendedores por turno (sesión)
- [x] Tomar y actualizar pedidos por mesa
- [x] Unir / separar mesas
- [x] Cancelar pedido y liberar mesa
- [x] Endpoint `cobrar_pedido` — guarda venta en `rest_venta` + `rest_venta_detalle`
- [x] Módulo almacén: insumos con stock, alertas de stock bajo
- [x] Registro de compras → actualiza stock automáticamente
- [x] Sistema de roles y permisos — `rutas_permisos` controla acceso a páginas
- [x] Filtro global `PermisoRutaFilter` — bloquea rutas sin `puede_ver`
- [x] Panel `/roles` — CRUD de roles, rutas, permisos (matriz) y botones
- [x] Barra de acciones configurable via BD + botones fijos por rol
- [ ] Vista de ticket post-cobro
- [ ] Cierre de caja / reportes
- [ ] `ALTER TABLE usuarios ADD COLUMN codigo VARCHAR(20) NULL DEFAULT NULL UNIQUE AFTER id_usuario`
