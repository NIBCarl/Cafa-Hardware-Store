import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

// Only initialize Echo if app key is configured
// This allows the app to work without WebSockets (no real-time updates)
const appKey = import.meta.env.VITE_REVERB_APP_KEY;

if (appKey && appKey !== 'undefined' && appKey.trim() !== '') {
    try {
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: appKey,
            wsHost: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
            wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
            wssPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
            forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
            enabledTransports: ['ws', 'wss'],
        });
        console.log('✅ WebSocket connection initialized');
    } catch (error) {
        console.warn('⚠️ WebSocket initialization failed:', error.message);
        console.warn('Real-time features will be disabled. The app will continue to work normally.');
        window.Echo = null;
    }
} else {
    console.warn('⚠️ WebSocket not configured (VITE_REVERB_APP_KEY not set)');
    console.warn('Real-time features will be disabled. The app will continue to work normally.');
    window.Echo = null;
}
