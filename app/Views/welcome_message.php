<?= view('includes/top'); ?>

<body>

    <!-- HEADER: MENU + HEROE SECTION -->
    <header>

        <div class="menu bg-gray-200 text-red-600">
            <ul>
                <li class="logo">

                </li>
                <li class="menu-toggle">
                    <button id="menuToggle">&#9776;</button>
                </li>
                <li class="menu-item hidden"><a href="#">Home</a></li>
                <li class="menu-item hidden"><a href="https://codeigniter.com/user_guide/" target="_blank">Docs</a>
                </li>
                <li class="menu-item hidden"><a href="https://forum.codeigniter.com/" target="_blank">Community</a></li>
                <li class="menu-item hidden"><a href="https://codeigniter.com/contribute" target="_blank">Contribute</a>
                </li>
            </ul>
        </div>

        <div class="heroe">

            <h1>Welcome to CodeIgniter <?= CodeIgniter\CodeIgniter::CI_VERSION ?></h1>

            <h2>The small framework with powerful features</h2>

        </div>

    </header>

    <!-- CONTENT -->

    <section>

        <h1>About this page</h1>

        <p>The page you are looking at is being generated dynamically by CodeIgniter.</p>

        <p>If you would like to edit this page you will find it located at:</p>

        <pre><code>app/Views/welcome_message.php</code></pre>

        <p>The corresponding controller for this page can be found at:</p>

        <pre><code>app/Controllers/Home.php</code></pre>

    </section>

    <!-- EJEMPLO DE BOTÓN CON IMAGEN Y ACCIÓN (USANDO COMPONENTE) -->
    <section class="bg-white p-8 rounded-lg shadow-md my-8 mx-auto max-w-2xl text-center">
        <h2 class="text-2xl font-bold mb-4 text-gray-800">Botones Reutilizables</h2>
        <p class="text-gray-600 mb-6">Ahora los botones son componentes reutilizables. ¡Puedes crear tantos como
            quieras!</p>

        <div class="flex flex-col md:flex-row justify-center items-center gap-4">
            <!-- Botón 1: Procesar Datos -->
            <?= view('components/action_button', [
                'label' => 'Procesar Datos',
                'action' => "mostrarMensaje('Procesando datos...')",
                'image' => 'https://cdn-icons-png.flaticon.com/512/2921/2921222.png'
            ]) ?>

            <!-- Botón 2: Confirmar Orden (Nombre diferente) -->
            <?= view('components/action_button', [
                'label' => 'Confirmar Orden',
                'action' => "mostrarMensaje('Orden confirmada exitosamente!')",
                // Usando imagen por defecto o diferente
                'image' => 'https://cdn-icons-png.flaticon.com/512/190/190411.png'
            ]) ?>
            <!-- Botón 3: Acción Personalizada (Fetch) -->
            <?= view('components/action_button', [
                'label' => 'Cargar Usuarios',
                'action' => "hacerFetch()", // Llamamos a nuestra función personalizada
                'image' => 'https://cdn-icons-png.flaticon.com/512/724/724933.png'
            ]) ?>
        </div>

        <!-- Lógica Específica de esta Página -->
        <script>
            // Esta función es específica de esta vista, así que la definimos aquí
            async function hacerFetch() {
                const btn = event.currentTarget; // Podemos acceder al botón que hizo click
                const span = btn.querySelector('span'); // Accedemos al texto
                const originalText = span.innerText;

                span.innerText = 'Cargando...';
                btn.disabled = true;

                try {
                    // Simulamos un fetch a una API
                    // const response = await fetch('https://api.exemplo.com/data');
                    await new Promise(r => setTimeout(r, 2000)); // Espera simulada 2s

                    alert('¡Datos cargados correctamente (Fetch simulado)!');
                    console.log('Fetch completado');
                } catch (error) {
                    console.error(error);
                } finally {
                    span.innerText = originalText;
                    btn.disabled = false;
                }
            }
        </script>

    </section>

    <div class="further">

        <section>

            <h1>Go further</h1>

            <h2>

                Learn
            </h2>

            <p>The User Guide contains an introduction, tutorial, a number of "how to"
                guides, and then reference documentation for the components that make up
                the framework. Check the <a href="https://codeigniter.com/user_guide/" target="_blank">User Guide</a> !
            </p>



        </section>

    </div>

    <!-- FOOTER: DEBUG INFO + COPYRIGHTS -->

    <footer>
        <div class="environment">

            <p>Page rendered in {elapsed_time} seconds using {memory_usage} MB of memory.</p>

            <p>Environment: <?= ENVIRONMENT ?></p>

        </div>

        <div class="copyrights">

            <p>&copy; <?= date('Y') ?> CodeIgniter Foundation. CodeIgniter is open source project released under the MIT
                open source licence.</p>

        </div>

    </footer>

    <!-- SCRIPTS -->

    <script {csp-script-nonce}>
        document.getElementById("menuToggle").addEventListener('click', toggleMenu);
        function toggleMenu() {
            var menuItems = document.getElementsByClassName('menu-item');
            for (var i = 0; i < menuItems.length; i++) {
                var menuItem = menuItems[i];
                menuItem.classList.toggle("hidden");
            }
        }
    </script>

    <!-- -->

</body>

</html>