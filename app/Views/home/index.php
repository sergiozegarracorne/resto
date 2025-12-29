<?= view('includes/top'); ?>

<body
    class="bg-gray-100 h-screen overflow-hidden flex flex-col <?= env('app.lowResourceMode') ? 'low-resource-mode' : '' ?>">

    <!-- SECCIÓN SUPERIOR: 80% -->
    <main class="h-[80vh] p-2 relative group">

        <!-- Marco de Tarjeta -->
        <div class="flex flex-col h-full bg-white rounded-sm shadow-lg border border-gray-200 overflow-hidden">

            <!-- Encabezado -->
            <div class="bg-gray-50 border-b border-gray-200 px-6 py-2 flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-800 tracking-tight">SELECCIONE UN VENDEDOR</h2>
            </div>

            <!-- Cuerpo (Grilla) -->
            <div class="p-2 flex-1 min-h-0 relative bg-gray-50/50">
                <div id="grillaVendedores"
                    class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-6 lg:grid-cols-8 gap-2 h-full overflow-y-auto no-scrollbar scroll-smooth pb-2">

                    <?php if (!empty($usuarios) && is_array($usuarios)): ?>
                        <?php foreach ($usuarios as $usuario): ?>
                            <?= boton_vendedor($usuario['nombre'], $usuario['imagen']) ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-span-full text-center py-10 text-gray-400">
                            No hay usuarios activos.
                        </div>
                    <?php endif; ?>

                </div> <!-- /Grilla Vendedores -->
            </div>
        </div> <!-- /Marco de Tarjeta -->

        <!-- Botones de Scroll FLOTANTES (Función Helper) -->
        <?= controles_scroll('grillaVendedores', 'abajo-derecha') ?>
    </main>



    <!-- SECCIÓN INFERIOR: 20% -->
    <section class="h-[20vh] bg-white border-t border-gray-300 p-4 shadow-inner">
        <div class="flex items-center justify-center h-full text-gray-500">
            <div class="text-center">
                <h3 class="text-xl font-semibold">Area de Acciones (30%)</h3>
                <p>Aquí irán los controles adicionales</p>
            </div>
        </div>
    </section>

    <!-- Teclado Virtual Global -->
    <?= teclado_numerico() ?>

</body>

</html>