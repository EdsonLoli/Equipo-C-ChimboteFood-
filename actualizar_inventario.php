<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'db.php';

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
$id_pedido = $data['id_pedido'] ?? null;

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

try {
    $conexion = obtenerConexion();
    $conexion->beginTransaction();

    $consultaProducto = $conexion->prepare('SELECT stock FROM productos WHERE id = ?');
    $consultaProducto->execute([(int)$id_producto]);
    $producto = $consultaProducto->fetch();

    if (!$producto) {
        $conexion->rollBack();
        http_response_code(404);
        echo json_encode([
            "status" => "error",
            "code" => 404,
            "mensaje" => "El producto indicado no existe."
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stock_actual = (int)$producto['stock'];
    if ((int)$cantidad > $stock_actual) {
        $conexion->rollBack();
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "code" => 400,
            "mensaje" => "La cantidad solicitada supera el stock disponible.",
            "datos" => [
                "stock_disponible" => $stock_actual,
                "cantidad_solicitada" => (int)$cantidad
            ]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $consultaRestaurante = $conexion->prepare('SELECT id FROM restaurantes WHERE id = ?');
    $consultaRestaurante->execute([(int)$id_restaurante]);
    if (!$consultaRestaurante->fetch()) {
        $conexion->rollBack();
        http_response_code(404);
        echo json_encode([
            "status" => "error",
            "code" => 404,
            "mensaje" => "El restaurante indicado no existe."
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $nuevo_stock = $stock_actual - (int)$cantidad;
    $actualizarStock = $conexion->prepare('UPDATE productos SET stock = ? WHERE id = ?');
    $actualizarStock->execute([$nuevo_stock, (int)$id_producto]);

    if ($id_pedido !== null && $id_pedido !== '') {
        $consultaPedido = $conexion->prepare('SELECT id FROM pedidos WHERE id = ?');
        $consultaPedido->execute([$id_pedido]);
    } else {
        $consultaPedido = $conexion->prepare(
            'SELECT id FROM pedidos WHERE id_restaurante = ? AND id_producto = ? ORDER BY id LIMIT 1'
        );
        $consultaPedido->execute([(int)$id_restaurante, (int)$id_producto]);
    }
    $pedido = $consultaPedido->fetch();

    if ($pedido) {
        $actualizarPedido = $conexion->prepare(
            "UPDATE pedidos
             SET id_restaurante = ?, id_producto = ?, cantidad = ?,
                 estado = 'Actualizado', actualizado_en = datetime('now', 'localtime')
             WHERE id = ?"
        );
        $actualizarPedido->execute([
            (int)$id_restaurante,
            (int)$id_producto,
            (int)$cantidad,
            $pedido['id']
        ]);
    }

    $conexion->commit();
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
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $error) {
    if (isset($conexion) && $conexion->inTransaction()) {
        $conexion->rollBack();
    }

    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "code" => 500,
        "mensaje" => "No se pudo actualizar el inventario."
    ], JSON_UNESCAPED_UNICODE);
}
