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