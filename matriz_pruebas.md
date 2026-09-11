# Matriz de Pruebas de Auditoría SOA

| ID | Escenario de Prueba | Método | Body JSON | Código Esperado | Código Obtenido | Mensaje de Respuesta | Estado | Evidencia |
|---|---|---|---|---|---|---|---|---|
| P01 | Petición Correcta | POST | `{"id_restaurante": 1, "id_producto": 101, "cantidad": 2}` | 200 OK | 200 OK | Inventario actualizado correctamente. | **APROBADO** | `P01_exito_200.png` |
| P02 | Método Incorrecto | GET | N/A | 405 Method Not Allowed | 405 Method Not Allowed | Método no permitido. Se requiere POST. | **APROBADO** | `P02_metodo_incorrecto_405.png` |
| P03 | JSON Malformado | POST | `{"id_restaurante": 1,` | 400 Bad Request | 400 Bad Request | Estructura JSON malformada o inválida. | **APROBADO** | `P03_json_malformado_400.png` |
| P04 | Campos Faltantes | POST | `{"id_restaurante": 1}` | 400 Bad Request | 400 Bad Request | Parámetros incompletos. Se requiere id_restaurante, id_producto y cantidad. | **APROBADO** | `P04_campos_faltantes_400.png` |
| P05 | Cantidad Inválida | POST | `{"id_restaurante": 1, "id_producto": 101, "cantidad": -5}` | 400 Bad Request | 400 Bad Request | La cantidad a actualizar debe ser mayor a cero. | **APROBADO** | `P05_cantidad_invalida_400.png` |
| P06 | Datos No Numéricos | POST | `{"id_restaurante": "abc", "id_producto": 101, "cantidad": 2}` | 400 Bad Request | 400 Bad Request | Los valores ingresados deben ser numéricos. | **APROBADO** | `P06_datos_no_numericos_400.png` |