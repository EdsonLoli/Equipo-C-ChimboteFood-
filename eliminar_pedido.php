<?php
require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'code' => 405,
        'mensaje' => 'Método no permitido. Se requiere DELETE o POST.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$idPedido = is_array($data) ? trim((string) ($data['id_pedido'] ?? '')) : '';

if ($idPedido === '') {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'mensaje' => 'El campo id_pedido es obligatorio.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $conexion = obtenerConexion();
    $eliminar = $conexion->prepare('DELETE FROM pedidos WHERE id = ?');
    $eliminar->execute([$idPedido]);

    if ($eliminar->rowCount() === 0) {
        http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'code' => 404,
            'mensaje' => 'El pedido indicado no existe.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode([
        'status' => 'exito',
        'code' => 200,
        'mensaje' => 'Pedido eliminado correctamente.',
        'datos' => ['id_pedido' => $idPedido]
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $error) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'code' => 500,
        'mensaje' => 'No se pudo eliminar el pedido.'
    ], JSON_UNESCAPED_UNICODE);
}
