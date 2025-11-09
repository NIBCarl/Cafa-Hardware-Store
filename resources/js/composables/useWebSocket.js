import Echo from 'laravel-echo';
import { useToastStore } from '../stores/toast';

let echo = null;

export function useWebSocket() {
  const toastStore = useToastStore();

  const connect = () => {
    if (echo) return;

    const appKey = import.meta.env.VITE_PUSHER_APP_KEY;
    
    // Don't initialize if no app key is configured
    if (!appKey || appKey === 'undefined' || appKey.trim() === '') {
      console.warn('⚠️ WebSocket not configured. Real-time features disabled.');
      return;
    }

    try {
      echo = new Echo({
        broadcaster: 'reverb',
        key: appKey,
        wsHost: import.meta.env.VITE_PUSHER_HOST || window.location.hostname,
        wsPort: import.meta.env.VITE_PUSHER_PORT || 8080,
        wssPort: import.meta.env.VITE_PUSHER_PORT || 8080,
        forceTLS: false,
        disableStats: true,
        enabledTransports: ['ws', 'wss'],
      });

      // Listen for connection events
      echo.connector.pusher.connection.bind('connected', () => {
        console.log('WebSocket connected');
      });

      echo.connector.pusher.connection.bind('disconnected', () => {
        console.log('WebSocket disconnected');
      });

      echo.connector.pusher.connection.bind('error', (error) => {
        console.error('WebSocket error:', error);
        toastStore.error('Lost connection to server. Retrying...');
      });
    } catch (error) {
      console.error('Failed to initialize WebSocket:', error);
    }
  };

  const disconnect = () => {
    if (echo) {
      echo.disconnect();
      echo = null;
    }
  };

  const subscribe = (channel, event, callback) => {
    if (!echo) connect();
    if (!echo) {
      console.warn('⚠️ Cannot subscribe to channel - WebSocket not available');
      return null;
    }
    return echo.private(channel).listen(event, callback);
  };

  const unsubscribe = (channel) => {
    if (echo) {
      echo.leave(channel);
    }
  };

  return {
    connect,
    disconnect,
    subscribe,
    unsubscribe,
  };
}
