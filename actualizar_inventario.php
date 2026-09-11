<?php
header('Content-Type: application/json; charset=utf-8');

// Validar que la petición sea estrictamente POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "status" => "error",
        "code" => 405,
        "mensaje" => "Método no permitido. Se requiere POST."
    ]);
    exit;
}

// Leer el cuerpo JSON de la petición
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validar estructura del JSON
if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "code" => 400,
        "mensaje" => "Estructura JSON malformada o inválida."
    ]);
    exit;
}

// Validar que existan los tres campos requeridos
if (!isset($data['id_restaurante']) || !isset($data['id_producto']) || !isset($data['cantidad'])) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "code" => 400,
        "mensaje" => "Parámetros incompletos. Se requiere id_restaurante, id_producto y cantidad."
    ]);
    exit;
}

$id_restaurante = $data['id_restaurante'];
$id_producto = $data['id_producto'];
$cantidad = $data['cantidad'];

// Validar que los valores sean numéricos
if (!is_numeric($id_restaurante) || !is_numeric($id_producto) || !is_numeric($cantidad)) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "code" => 400,
        "mensaje" => "Los valores ingresados deben ser numéricos."
    ]);
    exit;
}

// Validar que la cantidad sea positiva
if ($cantidad <= 0) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "code" => 400,
        "mensaje" => "La cantidad a actualizar debe ser mayor a cero."
    ]);
    exit;
}

// Respuesta exitosa (Simulación de actualización en stock)
$stock_actual = 20;

// Evitar que el inventario quede con stock negativo
if ($cantidad > $stock_actual) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "code" => 400,
        "mensaje" => "La cantidad solicitada supera el stock disponible.",
        "datos" => [
            "stock_disponible" => $stock_actual,
            "cantidad_solicitada" => (int)$cantidad
        ]
    ]);
    exit;
}

$nuevo_stock = $stock_actual - (int)$cantidad;

http_response_code(200);
echo json_encode([
    "status" => "exito",
    "code" => 200,
    "mensaje" => "Inventario actualizado correctamente.",
    "datos" => [
        "id_restaurante" => (int)$id_restaurante,
        "id_producto" => (int)$id_producto,
        "cantidad_descontada" => (int)$cantidad,
        "stock_restante" => $nuevo_stock
    ]
]);
