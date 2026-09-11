<?php

require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $conexion = obtenerConexion();
    $pedidos = $conexion->query(
        'SELECT id, id_restaurante, id_producto, cantidad, estado, actualizado_en
         FROM pedidos ORDER BY id'
    )->fetchAll();

    echo json_encode([
        'status' => 'exito',
        'datos' => $pedidos
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $error) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'code' => 500,
        'mensaje' => 'No se pudieron consultar los pedidos.'
    ], JSON_UNESCAPED_UNICODE);
}
