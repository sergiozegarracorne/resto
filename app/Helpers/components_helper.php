<?php

if (!function_exists('boton_vendedor')) {
    /**
     * Genera el HTML para el botón de vendedor.
     *
     * @param string $nombre El nombre del vendedor
     * @param string $imagen (Opcional) URL de la imagen
     * @param string $accion (Opcional) Función JS al hacer click
     * @return string HTML output
     */
    function boton_vendedor(string $nombre = 'Vendedor', string $imagen = '', string $accion = '', string $data = ''): string
    {
      
        $imagen = $imagen ?: 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png';
        $nombreEsc = esc($nombre);
        // IMPORTANTE: Escapar comillas para que el JSON no rompa el atributo HTML ni el string JS
        // 1. Escapamos ' para JS
        $jsSafe = str_replace("'", "\'", $data);
        // 2. Escapamos " para HTML
        $dataSafe = htmlspecialchars($jsSafe, ENT_QUOTES, 'UTF-8');

        // Acción por defecto abre el teclado
        $accion = $accion ?: "abrirTeclado('" . $nombreEsc . "','" . $dataSafe . "')"; 

        return <<<HTML
        <button 
            onclick="{$accion}" 
            class="group aspect-square flex flex-col items-center justify-center bg-white border border-gray-300 rounded-sm shadow-sm hover:shadow-md hover:border-indigo-500 hover:bg-indigo-50 transition-all duration-300 w-full h-full p-4"
        >
            <div class="w-1/2 h-1/3 mb-3 relative">
                <img 
                    src="{$imagen}" 
                    alt="{$nombreEsc}" 
                    class="w-full h-full object-contain drop-shadow-sm group-hover:scale-110 transition-transform duration-300"
                />
            </div>
            <span class="text-sm font-bold text-gray-700 group-hover:text-indigo-700 transition-colors">
                {$nombreEsc}
            </span>
        </button>
HTML;
    }
}

if (!function_exists('controles_scroll')) {
    /**
     * Genera los botones de control de scroll.
     *
     * @param string $objetivoId ID del contenedor a scrollear
     * @param string $posicion Posición: 'center-right', etc.
     * @return string HTML output
     */
    function controles_scroll(string $objetivoId = '', string $posicion = 'centro-derecha'): string
    {
        // Mapa de posiciones a clases Tailwind
        $clasesPosicion = [
            'centro-derecha' => 'absolute right-0 top-1/2 transform -translate-y-1/2 flex flex-col space-y-4 mr-4',
            'centro-izquierda' => 'absolute left-0 top-1/2 transform -translate-y-1/2 flex flex-col space-y-4 ml-4',
            'arriba-derecha' => 'absolute right-0 top-0 mt-6 mr-4 flex flex-col space-y-2',
            'arriba-izquierda' => 'absolute left-0 top-0 mt-6 ml-4 flex flex-col space-y-2',
            'abajo-derecha' => 'absolute right-0 bottom-0 mb-6 mr-4 flex flex-col space-y-2',
            'abajo-izquierda' => 'absolute left-0 bottom-0 mb-6 ml-4 flex flex-col space-y-2',
        ];

        $claseContenedor = $clasesPosicion[$posicion] ?? $clasesPosicion['centro-derecha'];

        return <<<HTML
        <div class="{$claseContenedor} z-20 pointer-events-none">
            <!-- Botón Subir/Izquierda -->
            <button onclick="controlarScroll('{$objetivoId}', 'arriba')" class="pointer-events-auto bg-indigo-600/80 hover:bg-indigo-700 text-white rounded-full p-3 shadow-lg backdrop-blur-sm transition-all active:scale-95 opacity-70 hover:opacity-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                </svg>
            </button>
            
            <!-- Botón Bajar/Derecha -->
            <button onclick="controlarScroll('{$objetivoId}', 'abajo')" class="pointer-events-auto bg-indigo-600/80 hover:bg-indigo-700 text-white rounded-full p-3 shadow-lg backdrop-blur-sm transition-all active:scale-95 opacity-70 hover:opacity-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
        </div>

        <script>
            if (typeof window.controlarScroll === 'undefined') {
                window.controlarScroll = function (elementoId, direccion) {
                    const contenedor = document.getElementById(elementoId);
                    if (!contenedor) {
                        console.warn('Contenedor scroll no encontrado:', elementoId);
                        return;
                    }

                    const cantidadScroll = contenedor.clientHeight * 0.9;

                    if (direccion === 'abajo') {
                        contenedor.scrollBy({ top: cantidadScroll, behavior: 'auto' });
                    } else {
                        contenedor.scrollBy({ top: -cantidadScroll, behavior: 'auto' });
                    }
                };
            }
        </script>
HTML;
    }
}

if (!function_exists('teclado_numerico')) {
    /**
     * Genera el componente de teclado numérico modal.
     */
    function teclado_numerico(): string
    {
        // Construir HTML de teclas
        $teclasHtml = '';
        $teclas = [1, 2, 3, 4, 5, 6, 7, 8, 9];
        foreach ($teclas as $tecla) {
            $teclasHtml .= <<<BUTTON
                <button onclick="presionarTecla('{$tecla}')" class="h-14 sm:h-auto min-h-[4.0rem] bg-white border border-gray-200 rounded-lg shadow-sm text-2xl font-bold text-gray-700 hover:bg-gray-100 hover:border-indigo-300 active:bg-indigo-50 active:scale-95 transition-all">
                    {$tecla}
                </button>
BUTTON;
        }

        return <<<HTML
        <!-- Modal Teclado (Click fuera cierra) -->
        <div id="modalTeclado" onclick="cerrarTeclado()" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden flex items-center justify-center transition-opacity duration-300">
            <!-- Contenido (Stop propagation) -->
            <div onclick="event.stopPropagation()" class="bg-white rounded-xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden transform scale-95 transition-transform duration-300 max-h-[95vh] flex flex-col" id="contenidoTeclado">
                
                <!-- Encabezado -->
                <div class="bg-indigo-600 p-4 text-center relative flex-shrink-0">
                    <!-- Botón Cerrar -->
                    <button onclick="cerrarTeclado()" class="absolute right-2 top-2 p-2 text-white/70 hover:text-white hover:bg-white/10 rounded-full transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <h3 class="text-white text-lg font-medium">Ingresar Clave</h3>
                    <p id="nombreVendedorTeclado" class="text-indigo-100 text-sm mt-0.5">Vendedor</p>
                    
                    <!-- Display Clave -->
                    <div class="mt-2 bg-indigo-800/50 rounded-lg p-2 flex justify-center items-center h-10">
                        <span id="displayTeclado" class="text-xl text-white font-mono tracking-widest"></span>
                    </div>
                </div>

                <!-- Grilla Teclas -->
                <div class="p-4 grid grid-cols-3 gap-2 bg-gray-50 flex-1 min-h-0">
                    {$teclasHtml}
                    
                    <!-- Fila Inferior -->
                    <button onclick="limpiarTecla()" class="h-14 sm:h-auto  min-h-[4.0rem] bg-red-50 border border-red-100 rounded-lg shadow-sm text-red-600 font-bold hover:bg-red-100 active:scale-95 transition-all flex items-center justify-center">
                        C
                    </button>
                    <button onclick="presionarTecla('0')" class="h-14 sm:h-auto min-h-[4.0rem] bg-white border border-gray-200 rounded-lg shadow-sm text-2xl font-bold text-gray-700 hover:bg-gray-100 hover:border-indigo-300 active:bg-indigo-50 active:scale-95 transition-all">
                        0
                    </button>
                    <button onclick="enviarTeclado()" class="h-14 sm:h-auto bg-indigo-600 border border-indigo-700 rounded-lg shadow-sm text-white font-bold hover:bg-indigo-700 active:scale-95 transition-all flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <script>
            // Variables de estado
            let claveActual = '';
            let vendedorActual = '';
            let vendedorData = {};

            if (typeof window.abrirTeclado === 'undefined') {
                 // Abrir
                window.abrirTeclado = function(nombreVendedor, dataJson) {
                    vendedorActual = nombreVendedor;
                    document.getElementById('nombreVendedorTeclado').innerText = nombreVendedor;
                    
                    // Parsear datos del usuario
                    try {
                        vendedorData = JSON.parse(dataJson || '{}');
                        console.log('Datos Usuario:', vendedorData);

                        //return false;
                        // Ejemplo: alert(usuario.rol);
                    } catch (e) {
                         console.error('Error parseando JSON usuario', e);
                    }

                    claveActual = '';
                    actualizarDisplay();
                    
                    const modal = document.getElementById('modalTeclado');
                    const contenido = document.getElementById('contenidoTeclado');
                    
                    modal.classList.remove('hidden');
                    setTimeout(() => {
                        contenido.classList.remove('scale-95');
                        contenido.classList.add('scale-100');
                    }, 10);
                };

                // Cerrar
                window.cerrarTeclado = function() {
                    const modal = document.getElementById('modalTeclado');
                    const contenido = document.getElementById('contenidoTeclado');
                    
                    contenido.classList.remove('scale-100');
                    contenido.classList.add('scale-95');
                    
                    setTimeout(() => {
                        modal.classList.add('hidden');
                    }, 300);
                };

                // Presionar
                window.presionarTecla = function(num) {
                    if (claveActual.length < 6) {
                        claveActual += num;
                        actualizarDisplay();
                    }
                };

                // Limpiar
                window.limpiarTecla = function() {
                    claveActual = '';
                    actualizarDisplay();
                };

                // Actualizar Display
                window.actualizarDisplay = function() {
                    const display = document.getElementById('displayTeclado');
                    display.innerText = claveActual.length > 0 ? '*'.repeat(claveActual.length) : '';
                    
                    if(claveActual.length === 0) {
                        display.innerText = '_ _ _ _ _ _';
                        display.classList.add('opacity-50');
                    } else {
                        display.classList.remove('opacity-50');
                    }
                };

                // Enviar
                window.enviarTeclado = function() {
                    if (claveActual.length > 0) {
                        const display = document.getElementById('displayTeclado');
                        display.classList.add('text-green-400');
                        
                        setTimeout(() => {
                            // alert('Clave Correcta para ' + vendedorActual + '. Redirigiendo...');
                            //alert(vendedorData);
                            window.location.href = '/venta/' + vendedorData.id_usuario;
                            display.classList.remove('text-green-400');
                            cerrarTeclado();
                        }, 500);
                        
                    } else {
                        const contenido = document.getElementById('contenidoTeclado');
                        contenido.classList.add('animate-pulse', 'border-red-500'); 
                        setTimeout(() => contenido.classList.remove('animate-pulse'), 500);
                    }
                };
            }
        </script>
HTML;
    }
}
