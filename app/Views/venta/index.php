<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
Resta - Punto de Venta
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <!-- Contenedor Principal Flex Row -->
    <div class="flex w-full h-full overflow-hidden">
        
        <!-- COLUMNA IZQUIERDA: TICKET (240px) -->
        <aside class="w-[240px] h-full bg-white border-r border-gray-300 flex flex-col shadow-xl z-10 shrink-0">
            <?= view('venta/ticket') ?>
        </aside>

        <!-- COLUMNA DERECHA: POS (Resto) -->
        <main class="flex-1 h-full flex flex-col bg-gray-100 min-w-0">
            <?= view('venta/catalogo') ?>
        </main>

    </div>

<?= $this->endSection() ?>