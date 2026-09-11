<?php
require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'code' => 405,
        'mensaje' => 'Método no permitido. Se requiere POST.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'mensaje' => 'Estructura JSON malformada o inválida.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$camposRequeridos = ['id_restaurante', 'id_producto', 'cantidad'];
foreach ($camposRequeridos as $campo) {
    if (!isset($data[$campo]) || $data[$campo] === '') {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'code' => 400,
            'mensaje' => "El campo $campo es obligatorio."
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

$idRestaurante = $data['id_restaurante'];
$idProducto = $data['id_producto'];
$cantidad = $data['cantidad'];

if (!is_numeric($idRestaurante) || !is_numeric($idProducto) || !is_numeric($cantidad) || (int) $cantidad <= 0) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'code' => 400,
        'mensaje' => 'Los IDs deben ser numéricos y la cantidad debe ser mayor a cero.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $conexion = obtenerConexion();
    $conexion->beginTransaction();

    $ultimoPedido = $conexion->query(
        "SELECT id FROM pedidos WHERE id LIKE 'PED-%' ORDER BY CAST(SUBSTR(id, 5) AS INTEGER) DESC LIMIT 1"
    )->fetchColumn();
    $ultimoNumero = $ultimoPedido ? (int) substr($ultimoPedido, 4) : 0;
    $idPedido = 'PED-' . str_pad((string) ($ultimoNumero + 1), 3, '0', STR_PAD_LEFT);

    // Crear solo las referencias que no existan, sin modificar las existentes.
    $crearRestaurante = $conexion->prepare(
        "INSERT OR IGNORE INTO restaurantes (id, nombre) VALUES (?, ?)"
    );
    $crearRestaurante->execute([(int) $idRestaurante, 'Restaurante ' . (int) $idRestaurante]);

    $crearProducto = $conexion->prepare(
        "INSERT OR IGNORE INTO productos (id, nombre, stock) VALUES (?, ?, 20)"
    );
    $crearProducto->execute([(int) $idProducto, 'Producto ' . (int) $idProducto]);

    $insertar = $conexion->prepare(
        'INSERT INTO pedidos (id, id_restaurante, id_producto, cantidad, estado)
         VALUES (?, ?, ?, ?, \'Pendiente\')'
    );
    $insertar->execute([$idPedido, (int) $idRestaurante, (int) $idProducto, (int) $cantidad]);
    $conexion->commit();

    http_response_code(201);
    echo json_encode([
        'status' => 'exito',
        'code' => 201,
        'mensaje' => 'Pedido registrado correctamente.',
        'datos' => [
            'id_pedido' => $idPedido,
            'id_restaurante' => (int) $idRestaurante,
            'id_producto' => (int) $idProducto,
            'cantidad' => (int) $cantidad,
            'estado' => 'Pendiente'
        ]
    ], JSON_UNESCAPED_UNICODE);
} catch (PDOException $error) {
    if (isset($conexion) && $conexion->inTransaction()) {
        $conexion->rollBack();
    }

    http_response_code($error->getCode() === '23000' ? 409 : 500);
    echo json_encode([
        'status' => 'error',
        'code' => http_response_code(),
        'mensaje' => 'No se pudo registrar el pedido. Verifica que el ID no esté repetido.'
    ], JSON_UNESCAPED_UNICODE);
}
