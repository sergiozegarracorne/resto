<?php
/**
 * Seller Button Component
 * 
 * @param string $name The name of the seller
 * @param string $image (Optional) URL of the seller's image
 * @param string $action (Optional) JS function to call on click
 */
$image = $image ?? 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png'; // Default avatar
$name = $name ?? 'Vendedor';
$action = $action ?? "alert('Seleccionaste: " . esc($name) . "')";
?>

<button onclick="<?= $action ?>"
    class="group aspect-square flex flex-col items-center justify-center bg-white border border-gray-300 rounded-sm shadow-sm hover:shadow-md hover:border-indigo-500 hover:bg-indigo-50 transition-all duration-300 w-full h-full p-4">
    <!-- Avatar Container -->
    <div class="w-1/2 h-1/2 mb-3 relative">
        <img src="<?= $image ?>" alt="<?= esc($name) ?>"
            class="w-full h-full object-contain drop-shadow-sm group-hover:scale-110 transition-transform duration-300" />
    </div>

    <!-- Name Label -->
    <span class="text-md font-bold text-gray-700 group-hover:text-indigo-700 transition-colors">
        <?= esc($name) ?>
    </span>
</button>