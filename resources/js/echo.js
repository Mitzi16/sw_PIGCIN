import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

console.log('🔄 [echo.js] Configurando Echo (Local puerto 8081)...');

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    
    // ✅ SOLUCIÓN: Usamos parseInt() para que no marque error de tipo
    wsPort: import.meta.env.VITE_REVERB_PORT ? parseInt(import.meta.env.VITE_REVERB_PORT) : 8081,
    wssPort: import.meta.env.VITE_REVERB_PORT ? parseInt(import.meta.env.VITE_REVERB_PORT) : 443,
    
    // Forzamos HTTP porque estás en local sin certificado SSL real
    forceTLS: false,
    encrypted: false,
    disableStats: true,
    
    enabledTransports: ['ws', 'wss'],
});

console.log('✅ [echo.js] Configuración aplicada.');