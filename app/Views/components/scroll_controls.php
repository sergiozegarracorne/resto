<?php
/**
 * Scroll Controls Component
 * 
 * @param string $targetId The ID of the container to scroll.
 * @param string $position Position of buttons: 'center-right' (default), 'center-left', 'top-right', 'bottom-right', etc.
 */

$targetId = $targetId ?? '';
$position = $position ?? 'center-right';

// Map logical positions to Tailwind CSS classes
$positionClasses = [
    // Vertical Center
    'center-right' => 'absolute right-0 top-1/2 transform -translate-y-1/2 flex flex-col space-y-4 mr-2',
    'center-left' => 'absolute left-0 top-1/2 transform -translate-y-1/2 flex flex-col space-y-4 ml-2',

    // Corners (Horizontal layout often better for corners, but sticking to user's current vertical stack preference or flexible?)
    // Let's assume vertical stack for side placement, horizontal for top/bottom placement if requested?
    // For now, let's keep it simple: generic absolute positioning.

    'top-right' => 'absolute right-0 top-0 mt-4 mr-4 flex flex-col space-y-2',
    'top-left' => 'absolute left-0 top-0 mt-4 ml-4 flex flex-col space-y-2',
    'bottom-right' => 'absolute right-0 bottom-0 mb-4 mr-4 flex flex-col space-y-2',
    'bottom-left' => 'absolute left-0 bottom-0 mb-4 ml-4 flex flex-col space-y-2',
];

$containerClass = $positionClasses[$position] ?? $positionClasses['center-right'];
?>

<div class="<?= $containerClass ?> z-20 pointer-events-none">
    <!-- Botón Subir/Izquierda -->
    <!-- pointer-events-auto needed because container has pointer-events-none to not block clicks behind it -->
    <button onclick="scrollContainer('<?= $targetId ?>', 'up')"
        class="pointer-events-auto bg-indigo-600/80 hover:bg-indigo-700 text-white rounded-full p-3 shadow-lg backdrop-blur-sm transition-all active:scale-95">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
        </svg>
    </button>

    <!-- Botón Bajar/Derecha -->
    <button onclick="scrollContainer('<?= $targetId ?>', 'down')"
        class="pointer-events-auto bg-indigo-600/80 hover:bg-indigo-700 text-white rounded-full p-3 shadow-lg backdrop-blur-sm transition-all active:scale-95">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>
</div>

<script>
    if (typeof window.scrollContainer === 'undefined') {
        window.scrollContainer = function (elementId, direction) {
            const container = document.getElementById(elementId);
            if (!container) {
                console.warn('Scroll container not found:', elementId);
                return;
            }

            // Determinar si scrolleamos vertical u horizontalmente o paginado
            const scrollAmount = container.clientHeight * 0.8;

            if (direction === 'down') {
                container.scrollBy({ top: scrollAmount, behavior: 'smooth' });
            } else {
                container.scrollBy({ top: -scrollAmount, behavior: 'smooth' });
            }
        };
    }
</script>