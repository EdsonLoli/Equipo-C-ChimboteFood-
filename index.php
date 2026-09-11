<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar inventario</title>
</head>
<body>
    <h1>Actualizar inventario</h1>

    <form id="inventario-form">
        <label>
            ID del restaurante
            <input type="number" name="id_restaurante" required>
        </label>
        <br>
        <label>
            ID del producto
            <input type="number" name="id_producto" required>
        </label>
        <br>
        <label>
            Cantidad
            <input type="number" name="cantidad" min="1" required>
        </label>
        <br>
        <button type="submit">Actualizar inventario</button>
    </form>

    <pre id="respuesta"></pre>

    <script>
        const form = document.getElementById('inventario-form');
        const respuesta = document.getElementById('respuesta');

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const datos = Object.fromEntries(new FormData(form));
            datos.id_restaurante = Number(datos.id_restaurante);
            datos.id_producto = Number(datos.id_producto);
            datos.cantidad = Number(datos.cantidad);

            const response = await fetch('actualizar_inventario.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(datos)
            });

            const resultado = await response.json();
            respuesta.textContent = JSON.stringify(resultado, null, 2);
        });
    </script>
</body>
</html>
