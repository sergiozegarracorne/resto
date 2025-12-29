<?= view('includes/top'); ?>

<body
    class="bg-gray-100 h-screen w-screen overflow-hidden flex text-gray-800 font-sans <?= env('app.lowResourceMode') ? 'low-resource-mode' : '' ?>">

    <!-- COLUMNA IZQUIERDA: TICKET (25%) -->
    <aside class="w-3/10 h-full bg-white border-r border-gray-300 flex flex-col shadow-xl z-10">
        <?= view('venta/ticket') ?>

    </aside>

    <!-- COLUMNA DERECHA: POS (75%) -->
    <main class="w-7/10 h-full flex flex-col bg-gray-100">

        <?= view('venta/catalogo') ?>


    </main>

</body>

</html>