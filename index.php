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

        <section class="panel create-order-panel">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">Nuevo registro</p>
                    <h2>Agregar pedido</h2>
                </div>
            </div>
            <form id="nuevo-pedido-form">
                <div class="form-grid">
                    <label>
                        <span>ID del restaurante</span>
                        <input type="number" name="id_restaurante" min="1" placeholder="Ej. 1" required>
                    </label>
                    <label>
                        <span>ID del producto</span>
                        <input type="number" name="id_producto" min="1" placeholder="Ej. 102" required>
                    </label>
                    <label>
                        <span>Cantidad</span>
                        <input type="number" name="cantidad" min="1" placeholder="Ej. 2" required>
                    </label>
                </div>
                <button type="submit">Agregar pedido</button>
            </form>
        </section>

        <section class="panel">
            <div class="section-heading form-section-heading">
                <div>
                    <p class="eyebrow">Verificación de inventario</p>
                    <h2>Actualizar inventario</h2>
                </div>
            </div>
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
                <input type="hidden" name="id_pedido">
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
                <div class="record-summary" aria-label="Resumen de pedidos">
                    <span class="summary-item"><strong id="total-pedidos">0</strong> total</span>
                    <span class="summary-item summary-pending"><strong id="pedidos-pendientes">0</strong> pendientes</span>
                    <span class="summary-item summary-updated"><strong id="pedidos-actualizados">0</strong> actualizados</span>
                </div>
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
                            <th>Eliminar</th>
                        </tr>
                    </thead>
                    <tbody id="pedidos-body"></tbody>
                </table>
            </div>
        </section>
    </main>

    <script>
        const form = document.getElementById('inventario-form');
        const nuevoPedidoForm = document.getElementById('nuevo-pedido-form');
        const respuesta = document.getElementById('respuesta');
        const estado = document.getElementById('estado');
        const pedidosBody = document.getElementById('pedidos-body');
        let pedidoSeleccionado = null;

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

        const conectarBotones = () => document.querySelectorAll('.table-action').forEach((button) => {
            button.addEventListener('click', () => {
                pedidoSeleccionado = button.closest('tr');
                form.id_pedido.value = pedidoSeleccionado.dataset.pedido;
                form.id_restaurante.value = button.dataset.restaurante;
                form.id_producto.value = button.dataset.producto;
                form.cantidad.value = button.dataset.cantidad;
                form.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
        });

        form.id_producto.addEventListener('input', () => {
            const producto = form.id_producto.value.trim();
            const fila = producto
                ? document.querySelector(`tbody tr[data-producto="${producto}"]`)
                : null;

            pedidoSeleccionado = fila;
            form.id_pedido.value = fila ? fila.dataset.pedido : '';
        });

        const conectarEliminaciones = () => document.querySelectorAll('.delete-action').forEach((button) => {
            button.addEventListener('click', async () => {
                const fila = button.closest('tr');
                const idPedido = fila.dataset.pedido;
                if (!window.confirm(`¿Eliminar el pedido ${idPedido}?`)) {
                    return;
                }

                try {
                    const response = await fetch('eliminar_pedido.php', {
                        method: 'DELETE',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id_pedido: idPedido })
                    });
                    const resultado = await response.json();
                    respuesta.textContent = JSON.stringify(resultado, null, 2);
                    estado.textContent = response.ok ? 'Pedido eliminado' : 'No eliminado';
                    estado.className = response.ok ? 'status status-success' : 'status status-error';
                    if (response.ok) {
                        pedidoSeleccionado = null;
                        await cargarPedidos();
                    }
                } catch (error) {
                    respuesta.textContent = 'No se pudo conectar con el servidor PHP.';
                    estado.textContent = 'Sin conexión';
                    estado.className = 'status status-error';
                }
            });
        });

        const cargarPedidos = async () => {
            const response = await fetch('pedidos.php');
            const resultado = await response.json();
            pedidosBody.innerHTML = resultado.datos.map((pedido) => `
                <tr data-pedido="${pedido.id}" data-restaurante="${pedido.id_restaurante}" data-producto="${pedido.id_producto}" data-cantidad="${pedido.cantidad}">
                    <td>#${pedido.id}</td>
                    <td class="order-restaurant">${pedido.id_restaurante}</td>
                    <td class="order-product">${pedido.id_producto}</td>
                    <td class="order-units">${pedido.cantidad}</td>
                    <td><span class="order-status ${pedido.estado === 'Actualizado' ? 'order-status-success' : ''}">${pedido.estado}</span></td>
                    <td class="order-time">${pedido.actualizado_en || '-'}</td>
                    <td><button class="table-action" type="button" data-restaurante="${pedido.id_restaurante}" data-producto="${pedido.id_producto}" data-cantidad="${pedido.cantidad}">${pedido.estado === 'Actualizado' ? 'Actualizar de nuevo' : 'Actualizar pedido'}</button></td>
                    <td><button class="delete-action" type="button" aria-label="Eliminar pedido" title="Eliminar pedido"><img class="trash-icon" src="Img/papelera.png" alt=""></button></td>
                </tr>
            `).join('');
            const pendientes = resultado.datos.filter((pedido) => pedido.estado === 'Pendiente').length;
            const actualizados = resultado.datos.filter((pedido) => pedido.estado === 'Actualizado').length;
            document.getElementById('total-pedidos').textContent = resultado.datos.length;
            document.getElementById('pedidos-pendientes').textContent = pendientes;
            document.getElementById('pedidos-actualizados').textContent = actualizados;
            conectarBotones();
            conectarEliminaciones();
        };

        nuevoPedidoForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            const datos = Object.fromEntries(new FormData(nuevoPedidoForm));
            datos.id_restaurante = Number(datos.id_restaurante);
            datos.id_producto = Number(datos.id_producto);
            datos.cantidad = Number(datos.cantidad);

            try {
                const response = await fetch('guardar_pedido.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(datos)
                });
                const resultado = await response.json();
                respuesta.textContent = JSON.stringify(resultado, null, 2);
                estado.textContent = response.ok ? 'Pedido guardado' : 'Revisar datos';
                estado.className = response.ok ? 'status status-success' : 'status status-error';
                if (response.ok) {
                    nuevoPedidoForm.reset();
                    await cargarPedidos();
                }
            } catch (error) {
                respuesta.textContent = 'No se pudo conectar con el servidor PHP.';
                estado.textContent = 'Sin conexión';
                estado.className = 'status status-error';
            }
        });

        cargarPedidos().catch(() => {
            pedidosBody.innerHTML = '<tr><td colspan="8">No se pudieron cargar los pedidos.</td></tr>';
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
