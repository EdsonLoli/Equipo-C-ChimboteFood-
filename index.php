<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar inventario</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <main class="page-shell">
        <section class="intro">
            <p class="eyebrow">Chimbote Food</p>
            <h1>Actualizar inventario</h1>
            <p>Registra la salida de productos y consulta el stock actualizado.</p>
        </section>

        <section class="panel">
            <form id="inventario-form">
                <div class="form-grid">
                    <label>
                        <span>ID del restaurante</span>
                        <input type="number" name="id_restaurante" min="1" placeholder="Ej. 1" required>
                    </label>
                    <label>
                        <span>ID del producto</span>
                        <input type="number" name="id_producto" min="1" placeholder="Ej. 101" required>
                    </label>
                    <label>
                        <span>Cantidad a descontar</span>
                        <input type="number" name="cantidad" min="1" placeholder="Ej. 2" required>
                    </label>
                </div>
                <button type="submit">Actualizar inventario</button>
            </form>
        </section>

        <section class="result-panel" aria-live="polite">
            <div class="result-heading">
                <div>
                    <p class="eyebrow">Respuesta del sistema</p>
                    <h2>Resultado</h2>
                </div>
                <span id="estado" class="status">Pendiente</span>
            </div>
            <pre id="respuesta">Completa el formulario para realizar una actualización.</pre>
        </section>
    </main>

    <script>
        const form = document.getElementById('inventario-form');
        const respuesta = document.getElementById('respuesta');
        const estado = document.getElementById('estado');

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            estado.textContent = 'Enviando...';
            estado.className = 'status status-loading';
            const datos = Object.fromEntries(new FormData(form));
            datos.id_restaurante = Number(datos.id_restaurante);
            datos.id_producto = Number(datos.id_producto);
            datos.cantidad = Number(datos.cantidad);

            try {
                const response = await fetch('actualizar_inventario.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(datos)
                });
                const resultado = await response.json();
                respuesta.textContent = JSON.stringify(resultado, null, 2);
                estado.textContent = response.ok ? 'Actualizado' : 'Revisar datos';
                estado.className = response.ok ? 'status status-success' : 'status status-error';
            } catch (error) {
                respuesta.textContent = 'No se pudo conectar con el servidor PHP.';
                estado.textContent = 'Sin conexión';
                estado.className = 'status status-error';
            }
        });
    </script>
</body>
</html>
