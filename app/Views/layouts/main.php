<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title') ?></title>
    
    <!-- STYLES -->
    <link rel="stylesheet" href="<?= base_url('css/styles.css') ?>">
    
    <!-- Preload/Prefetch Stategies -->
    <link rel="prefetch" href="<?= base_url('panel') ?>">
    <link rel="prefetch" href="<?= base_url('venta') ?>">
    <script src="<?= base_url('js/instantpage.js') ?>" type="module"></script>
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('<?= base_url("sw.js") ?>');
        } else {
            console.log('Service Worker no soportado');
        }
    </script>

    <?= $this->renderSection('head_scripts') ?>
</head>
<body class="bg-gray-100 h-screen w-screen overflow-hidden flex flex-col text-gray-800 font-sans <?= env('app.lowResourceMode') ? 'low-resource-mode' : '' ?>">

    <?= $this->renderSection('content') ?>

    <!-- Scripts globales -->
    <?= $this->renderSection('scripts') ?>
</body>
</html>
