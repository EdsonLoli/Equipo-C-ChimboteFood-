<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar inventario</title>
    <link rel="stylesheet" href="diseño.css">
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

        <section class="panel orders-panel">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">Datos registrados</p>
                    <h2>Pedidos pendientes</h2>
                </div>
                <span class="record-count">3 pedidos</span>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Pedido</th>
                            <th>Restaurante</th>
                            <th>Producto</th>
                            <th>Unidades</th>
                            <th>Estado</th>
                            <th>Actualizado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr data-pedido="PED-001" data-restaurante="1" data-producto="101" data-cantidad="2">
                            <td>#PED-001</td>
                            <td class="order-restaurant">1</td>
                            <td class="order-product">101</td>
                            <td class="order-units">2</td>
                            <td><span class="order-status">Pendiente</span></td>
                            <td class="order-time">-</td>
                            <td><button class="table-action" type="button" data-restaurante="1" data-producto="101" data-cantidad="2">Actualizar pedido</button></td>
                        </tr>
                        <tr data-pedido="PED-002" data-restaurante="2" data-producto="205" data-cantidad="4">
                            <td>#PED-002</td>
                            <td class="order-restaurant">2</td>
                            <td class="order-product">205</td>
                            <td class="order-units">4</td>
                            <td><span class="order-status">Pendiente</span></td>
                            <td class="order-time">-</td>
                            <td><button class="table-action" type="button" data-restaurante="2" data-producto="205" data-cantidad="4">Actualizar pedido</button></td>
                        </tr>
                        <tr data-pedido="PED-003" data-restaurante="3" data-producto="310" data-cantidad="1">
                            <td>#PED-003</td>
                            <td class="order-restaurant">3</td>
                            <td class="order-product">310</td>
                            <td class="order-units">1</td>
                            <td><span class="order-status">Pendiente</span></td>
                            <td class="order-time">-</td>
                            <td><button class="table-action" type="button" data-restaurante="3" data-producto="310" data-cantidad="1">Actualizar pedido</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script>
        const form = document.getElementById('inventario-form');
        const respuesta = document.getElementById('respuesta');
        const estado = document.getElementById('estado');
        let pedidosGuardados = {};
        let pedidoSeleccionado = null;

        try {
            pedidosGuardados = JSON.parse(localStorage.getItem('pedidosInventario') || '{}');
        } catch (error) {
            localStorage.removeItem('pedidosInventario');
        }

        const marcarPedidoActualizado = (datos, hora, pedido = pedidoSeleccionado) => {
            const fila = pedido || document.querySelector(
                `tr[data-restaurante="${datos.id_restaurante}"][data-producto="${datos.id_producto}"]`
            );

            if (!fila) {
                return;
            }

            fila.querySelector('.order-restaurant').textContent = datos.id_restaurante;
            fila.querySelector('.order-product').textContent = datos.id_producto;
            fila.querySelector('.order-units').textContent = datos.cantidad;
            fila.dataset.restaurante = datos.id_restaurante;
            fila.dataset.producto = datos.id_producto;
            fila.dataset.cantidad = datos.cantidad;
            fila.querySelector('.order-status').textContent = 'Actualizado';
            fila.querySelector('.order-status').className = 'order-status order-status-success';
            fila.querySelector('.order-time').textContent = hora;
            const boton = fila.querySelector('.table-action');
            boton.textContent = 'Actualizar de nuevo';
            boton.disabled = false;
        };

        document.querySelectorAll('tbody tr[data-pedido]').forEach((fila) => {
            const pedidoGuardado = pedidosGuardados[fila.dataset.pedido];
            if (pedidoGuardado) {
                marcarPedidoActualizado(pedidoGuardado.datos, pedidoGuardado.hora, fila);
            }
        });

        document.querySelectorAll('.table-action').forEach((button) => {
            button.addEventListener('click', () => {
                pedidoSeleccionado = button.closest('tr');
                form.id_restaurante.value = button.dataset.restaurante;
                form.id_producto.value = button.dataset.producto;
                form.cantidad.value = button.dataset.cantidad;
                form.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
        });

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
                if (response.ok) {
                    const hora = new Date().toLocaleTimeString('es-PE', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    const filaPorProducto = document.querySelector(
                        `tbody tr[data-producto="${datos.id_producto}"]`
                    );
                    const pedido = pedidoSeleccionado || filaPorProducto;

                    if (pedido) {
                        const pedidoId = pedido.dataset.pedido;
                        pedidosGuardados[pedidoId] = { datos, hora };
                        localStorage.setItem('pedidosInventario', JSON.stringify(pedidosGuardados));
                        marcarPedidoActualizado(datos, hora, pedido);
                    }
                }
            } catch (error) {
                respuesta.textContent = 'No se pudo conectar con el servidor PHP.';
                estado.textContent = 'Sin conexión';
                estado.className = 'status status-error';
            }
        });
    </script>
</body>
</html>
