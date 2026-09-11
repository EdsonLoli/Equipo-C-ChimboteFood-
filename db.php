<?php

function obtenerConexion(): PDO
{
    static $conexion;

    if ($conexion instanceof PDO) {
        return $conexion;
    }

    $conexion = new PDO('sqlite:' . __DIR__ . DIRECTORY_SEPARATOR . 'inventario.sqlite');
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $conexion->exec('PRAGMA foreign_keys = ON');

    $conexion->exec(
        'CREATE TABLE IF NOT EXISTS restaurantes (
            id INTEGER PRIMARY KEY,
            nombre TEXT NOT NULL
        )'
    );
    $conexion->exec(
        'CREATE TABLE IF NOT EXISTS productos (
            id INTEGER PRIMARY KEY,
            nombre TEXT NOT NULL,
            stock INTEGER NOT NULL CHECK (stock >= 0)
        )'
    );
    $conexion->exec(
        'CREATE TABLE IF NOT EXISTS pedidos (
            id TEXT PRIMARY KEY,
            id_restaurante INTEGER NOT NULL,
            id_producto INTEGER NOT NULL,
            cantidad INTEGER NOT NULL CHECK (cantidad > 0),
            estado TEXT NOT NULL DEFAULT "Pendiente",
            actualizado_en TEXT NULL,
            FOREIGN KEY (id_restaurante) REFERENCES restaurantes(id),
            FOREIGN KEY (id_producto) REFERENCES productos(id)
        )'
    );

    $cantidadPedidos = (int) $conexion->query('SELECT COUNT(*) FROM pedidos')->fetchColumn();
    if ($cantidadPedidos === 0) {
        $conexion->beginTransaction();
        $conexion->exec(
            "INSERT INTO restaurantes (id, nombre) VALUES
            (1, 'Restaurante 1'), (2, 'Restaurante 2'), (3, 'Restaurante 3')"
        );
        $conexion->exec(
            "INSERT INTO productos (id, nombre, stock) VALUES
            (101, 'Producto 101', 20), (102, 'Producto 102', 20),
            (205, 'Producto 205', 20), (310, 'Producto 310', 20)"
        );
        $conexion->exec(
            "INSERT INTO pedidos (id, id_restaurante, id_producto, cantidad) VALUES
            ('PED-001', 1, 101, 2), ('PED-002', 2, 205, 4), ('PED-003', 3, 310, 1)"
        );
        $conexion->commit();
    }

    return $conexion;
}
