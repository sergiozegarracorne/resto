<?php

/**
 * The goal of this file is to allow the definition of functions that will be
 * available globally in the application.
 */

if (! function_exists('formato_moneda')) {
    function formato_moneda($amount) {
        return 'S/ ' . number_format((float)$amount, 2, '.', ',');
    }
}

if (! function_exists('limpiar_numero')) {
    function limpiar_numero($str) {
        return (float) preg_replace('/[^0-9.]/', '', $str);
    }
}

/**
 * Retorna las clases de Tailwind para el color del estado de la mesa
 */
if (! function_exists('clase_estado_mesa')) {
    function clase_estado_mesa($estado) {
        switch (strtolower($estado)) {
            case 'libre': return 'bg-green-500';
            case 'ocupada': return 'bg-red-500';
            case 'reservada': return 'bg-yellow-500';
            case 'limpieza': return 'bg-blue-500';
            case 'pagando': return 'bg-purple-500';
            default: return 'bg-gray-400';
        }
    }
}

/**
 * Retorna un badge HTML completo para el estado
 */
if (! function_exists('badge_estado_mesa')) {
    function badge_estado_mesa($estado) {
        $clase = clase_estado_mesa($estado);
        $texto = ucfirst($estado);
        // Retornamos HTML seguro
        return "<span class=\"px-2 py-1 rounded text-white text-[10px] font-bold uppercase {$clase}\">{$texto}</span>";
    }
}

/**
 * Genera iniciales para avatar: "Juan Perez" -> "JP"
 */
if (! function_exists('avatar_iniciales')) {
    function avatar_iniciales($nombre) {
        if (empty($nombre)) return 'U';
        
        $parts = explode(' ', $nombre);
        $initials = '';
        foreach ($parts as $part) {
            if (empty($part)) continue;
            if (strlen($initials) >= 2) break;
            $initials .= strtoupper(substr($part, 0, 1));
        }
        return $initials ?: substr(strtoupper($nombre), 0, 2);
    }
}

/**
 * Componente: Reloj
 * Wrapper simple para llamar a la vista
 */
if (! function_exists('componente_reloj')) {
    function componente_reloj() {
        return view('components/clock');
    }
} 


