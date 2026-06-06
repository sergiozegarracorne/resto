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

| Tabla                  | Descripción                        |
|------------------------|------------------------------------|
| `categorias`           | Categorías de productos            |
| `productos`            | Productos del menú                 |
| `producto_componentes` | Componentes/ingredientes           |
| `mesas`                | Mesas con posición X/Y y estado    |
| `pisos`                | Pisos del local                    |
| `usuarios`             | Vendedores/meseros                 |
| `insumos`              | Ingredientes del almacén con stock |

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

| Archivo          | Módulo    | Responsabilidad                        |
|------------------|-----------|----------------------------------------|
| `AuthApi.php`    | Auth      | Login de vendedores, timestamp         |
| `MesasApi.php`   | Mesas     | Pisos, unir/separar, posiciones        |
| `PedidosApi.php` | Pedidos   | Crear, actualizar y cancelar pedidos   |
| `VentasApi.php`  | Ventas    | Registrar cobro de una venta           |
| `AlmacenApi.php` | Almacén   | Insumos, stock, compras                |

> Todos extienden `BaseApiController` (usa `ResponseTrait` de CI4).

## API Endpoints

Las URLs del JS **no cambiaron** — solo cambió dónde vive el código.

| Método | Ruta                          | Controlador      | Descripción                                            |
|--------|-------------------------------|------------------|--------------------------------------------------------|
| GET    | `/api/time`                   | AuthApi          | Timestamp del servidor                                 |
| POST   | `/api/verificar_vendedor`     | AuthApi          | Login del vendedor por id + clave (sesión)             |
| GET    | `/api/get_pisos_mesas`        | MesasApi         | Pisos con sus mesas y estado                           |
| POST   | `/api/unir_mesas`             | MesasApi         | Unir mesas secundarias a una principal                 |
| POST   | `/api/separar_mesas`          | MesasApi         | Desasociar mesas unidas                                |
| POST   | `/api/update_mesas_positions` | MesasApi         | Guardar posiciones X/Y de mesas en el panel            |
| GET    | `/api/get_mesa_pedido/:id`    | PedidosApi       | Pedido activo (pendiente) de una mesa                  |
| POST   | `/api/save_pedido`            | PedidosApi       | Crear o actualizar pedido de una mesa                  |
| POST   | `/api/cancelar_pedido`        | PedidosApi       | Eliminar pedido y liberar mesa                         |
| POST   | `/api/cobrar_pedido`          | VentasApi        | Registrar venta en `rest_venta` + `rest_venta_detalle` |
| GET    | `/api/get_insumos`            | AlmacenApi       | Lista de insumos con stock actual                      |
| POST   | `/api/save_insumo`            | AlmacenApi       | Crear o editar un insumo del almacén                   |
| POST   | `/api/save_compra`            | AlmacenApi       | Registrar compra y sumar stock a los insumos           |

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

| Ruta           | Controlador      | Descripción                          |
|----------------|------------------|--------------------------------------|
| `/`            | `Home::index`    | Página de inicio / login             |
| `/venta`       | `Venta::index`   | POS principal (mesas + pedido)       |
| `/venta/:id`   | `Venta::index`   | POS con mesa preseleccionada         |
| `/panel`       | `Panel::index`   | Panel admin de mesas (drag & drop)   |
| `/ingress/:id`      | `Home::ingress`    | Entrada directa por vendedor         |
| `/almacen/compras`  | `Almacen::compras` | Registrar una compra de insumos      |
| `/almacen/insumos`  | `Almacen::insumos` | Ver y editar stock del almacén       |

---

## Configuración local

1. Copiar `_env` a `.env`
2. Ajustar `app.baseURL` y credenciales de BD si es necesario
3. Crear las dos BDs: `resto` y `resto_operaciones`
4. Importar `SQL/resto.sql` y `SQL/resto_operaciones.sql`
5. Apuntar MAMP a la carpeta `/public`

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
- [ ] Liberar mesa al cobrar (pendiente en `cobrar_pedido`)
- [ ] Marcar pedido como pagado al cobrar
- [ ] Vista de ticket post-cobro
- [ ] Vincular insumos a productos (descuento automático de stock al vender)
- [ ] Cierre de caja / reportes
