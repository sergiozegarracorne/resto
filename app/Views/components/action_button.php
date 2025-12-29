<?php
/**
 * Action Button Component
 * 
 * @param string $label The text to display on the button
 * @param string $action The JavaScript function/code to execute on click
 * @param string $image (Optional) URL of the image icon
 */
$image = $image ?? 'https://cdn-icons-png.flaticon.com/512/888/888879.png'; // Default image
$label = $label ?? 'Botón'; // Default text
$action = $action ?? ''; // Default action
?>

<button onclick="<?= $action ?>"
    class="group bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-6 rounded-full inline-flex items-center space-x-3 transition duration-300 ease-in-out transform hover:-translate-y-1 shadow-lg border border-transparent hover:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 my-2">
    <!-- Imagen dentro del botón -->
    <img src="<?= $image ?>" alt="Icono" class="w-8 h-8 rounded-full bg-white p-1" />

    <!-- Texto del botón -->
    <span><?= esc($label) ?></span>

    <!-- Icono SVG opcional (flecha) -->
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover:translate-x-1 transition-transform" fill="none"
        viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
    </svg>
</button>

<script>
    // Verificamos si la función ya existe para evitar redefinirla al incluir el componente múltiples veces
    if (typeof window.mostrarMensaje === 'undefined') {
        window.mostrarMensaje = function (mensaje) {
            alert(mensaje);
            console.log('Acción del componente: ' + mensaje);
        };
    }
</script>
<link rel="stylesheet" href="/css/styles.css">