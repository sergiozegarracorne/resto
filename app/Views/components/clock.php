<!-- Componente Reloj Neon Sincronizado -->
<div class="bg-slate-900 rounded-md p-2 shadow-inner border border-slate-700 flex justify-center items-center gap-3 select-none" title="Hora del Servidor">
    <p id="comp-fecha" class="text-[10px] text-cyan-300 font-mono tracking-widest uppercase">--/--/--</p>
    <span class="text-slate-600 text-[10px]">|</span>
    <p id="comp-reloj" class="text-sm font-mono text-green-400 font-bold  drop-shadow-sm">--:--:--</p>
</div>

<script>
(function() {
    let diffTiempo = 0; // Diferencia entre cliente y servidor

    function actualizarInterfaz(fechaObj) {
        if(!document.getElementById('comp-reloj')) return;

        // Formato Hora
        document.getElementById('comp-reloj').innerText = fechaObj.toLocaleTimeString('es-PE', { hour12: true });
        
        // Formato Fecha
        document.getElementById('comp-fecha').innerText = fechaObj.toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit', year: '2-digit' });
    }

    function tick() {
        // La hora actual es la hora del sistema + la diferencia calculada
        const ahoraReal = new Date(Date.now() + diffTiempo);
        actualizarInterfaz(ahoraReal);
    }

    async function sincronizarHora() {
        try {
            const inicio = Date.now();
            const response = await fetch('<?= base_url("api/time") ?>');
            if (response.ok) {
                const data = await response.json();
                const fin = Date.now();
                // Latencia de red estimada (ida y vuelta / 2)
                const latencia = (fin - inicio) / 2;
                
                const tiempoServidor = data.timestamp;
                const tiempoCliente = Date.now();

                // Calculamos cuánto debemos sumar/restar al reloj local
                diffTiempo = tiempoServidor - tiempoCliente + latencia;
                
                // Forzar un tick inmediato con la nueva hora
                tick();
                console.log('🕒 Reloj sincronizado. Drift:', Math.round(diffTiempo), 'ms');
            }
        } catch (e) {
            console.error('Error sincronizando hora:', e);
        }
    }

    // Iniciar
    setInterval(tick, 1000); // Actualizar visualmente cada segundo
    setInterval(sincronizarHora, 60000); // Resincronizar cada minuto (evita trampas)
    
    // Primera sincronización
    sincronizarHora();
})();
</script>
