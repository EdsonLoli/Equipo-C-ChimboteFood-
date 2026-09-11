# Matriz de Pruebas de Auditoría SOA

| ID  | Escenario de Prueba | Método | Body JSON | Código Esperado |
|-----|-------------------|--------|-----------|-----------------|
| P01 | Petición Correcta | POST | `{"id_restaurante": 1, "id_producto": 101, "cantidad": 2}` | 200 OK |
| P02 | Método Incorrecto | GET | N/A | 405 Method Not Allowed |
| P03 | JSON Malformado | POST | `{"id_restaurante": 1,` | 400 Bad Request |
| P04 | Campos Faltantes | POST | `{"id_restaurante": 1}` | 400 Bad Request |
| P05 | Cantidad Inválida | POST | `{"id_restaurante": 1, "id_producto": 101, "cantidad": -5}` | 400 Bad Request |
| P06 | Datos No Numéricos| POST | `{"id_restaurante": "abc", "id_producto": 101, "cantidad": 2}` | 400 Bad Request |