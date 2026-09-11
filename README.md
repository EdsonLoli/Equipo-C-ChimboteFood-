# Equipo-C-ChimboteFood-

Gestionar el ciclo de vida del pedido y Actualizar inventario/menú del restaurante.

---

## Contrato de Servicio SOA

### Endpoint
`POST /actualizar_inventario.php`

### Estructura de Entrada (JSON)
```json
{
  "id_restaurante": 1,
  "id_producto": 101,
  "cantidad": 2
}
```

## Base de datos SQLite

La aplicación utiliza SQLite en el archivo `inventario.sqlite`. La base se crea automáticamente y contiene restaurantes, productos y pedidos.

Para iniciar el servidor con SQLite habilitado:

```powershell
php -c php.ini -S localhost:8000
```

La tabla de pedidos se carga desde `pedidos.php`. Las actualizaciones se guardan junto con el estado y la fecha de actualización.