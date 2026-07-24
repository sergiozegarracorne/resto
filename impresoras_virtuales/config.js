/**
 * Configuración de las impresoras virtuales.
 *
 * En modo DEVELOPMENT (default) escucha en 0.0.0.0 con devPort para
 * no necesitar configurar IPs alias en la máquina de desarrollo.
 *
 * En modo PRODUCTION (NODE_ENV=production) bind directo a la IP real
 * — requiere que el servidor Ubuntu tenga esas IPs en su interfaz de red.
 */
module.exports = {

    webPort: 3000,  // Puerto de la interfaz web

    printers: [
        {
            id:      'cocina',
            name:    'Cocina',
            emoji:   '🍳',
            ip:      '192.168.10.10',
            port:    9100,
            devPort: 9101,            // Puerto local en modo dev
            color:   '#f97316',
            bg:      '#fff7ed',
        },
        {
            id:      'horno',
            name:    'Horno',
            emoji:   '🔥',
            ip:      '192.168.10.20',
            port:    9100,
            devPort: 9102,
            color:   '#ef4444',
            bg:      '#fef2f2',
        },
        {
            id:      'bar',
            name:    'Bar',
            emoji:   '🍹',
            ip:      '192.168.10.30',
            port:    9100,
            devPort: 9103,
            color:   '#3b82f6',
            bg:      '#eff6ff',
        },
    ],
};
