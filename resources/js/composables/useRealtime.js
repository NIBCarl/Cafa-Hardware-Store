import { onMounted, onUnmounted, ref } from 'vue';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Initialize Echo instance
let echoInstance = null;

export function initializeEcho() {
    if (echoInstance) {
        return echoInstance;
    }

    const appKey = import.meta.env.VITE_REVERB_APP_KEY;
    
    // Don't initialize if no app key is configured
    if (!appKey || appKey === 'undefined' || appKey.trim() === '') {
        console.warn('⚠️ WebSocket not configured. Real-time features disabled.');
        return null;
    }

    try {
        window.Pusher = Pusher;

        echoInstance = new Echo({
            broadcaster: 'reverb',
            key: appKey,
            wsHost: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
            wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
            wssPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
            forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
            enabledTransports: ['ws', 'wss'],
            auth: {
                headers: {
                    Authorization: `Bearer ${localStorage.getItem('token')}`,
                },
            },
        });

        return echoInstance;
    } catch (error) {
        console.error('Failed to initialize Echo:', error);
        return null;
    }
}

export function useRealtime() {
    const isConnected = ref(false);

    const getEcho = () => {
        if (!echoInstance) {
            echoInstance = initializeEcho();
        }
        return echoInstance;
    };

    const disconnect = () => {
        if (echoInstance) {
            echoInstance.disconnect();
            echoInstance = null;
            isConnected.value = false;
        }
    };

    const echo = getEcho();

    return {
        echo,
        isConnected,
        disconnect,
    };
}

// Composable for listening to dashboard events
export function useDashboardEvents(callbacks = {}) {
    const { echo } = useRealtime();
    
    // If Echo is not initialized, return empty functions
    if (!echo) {
        console.warn('⚠️ WebSocket not available. Dashboard events will not update in real-time.');
        return;
    }
    const latestTransaction = ref(null);
    const transactionCount = ref(0);

    onMounted(() => {
        echo.channel('dashboard')
            .listen('.transaction.completed', (event) => {
                console.log('New transaction:', event);
                latestTransaction.value = event.transaction;
                transactionCount.value++;
                
                if (callbacks.onTransaction) {
                    callbacks.onTransaction(event);
                }
            });
    });

    onUnmounted(() => {
        echo.leave('dashboard');
    });

    return {
        latestTransaction,
        transactionCount,
    };
}

// Composable for listening to inventory events
export function useInventoryEvents(callbacks = {}) {
    const { echo } = useRealtime();
    
    // If Echo is not initialized, return empty functions
    if (!echo) {
        console.warn('⚠️ WebSocket not available. Inventory events will not update in real-time.');
        return;
    }
    const updatedProducts = ref([]);
    const lowStockAlerts = ref([]);

    onMounted(() => {
        echo.channel('inventory')
            .listen('.inventory.updated', (event) => {
                console.log('Inventory updated:', event);
                updatedProducts.value.unshift(event.product);
                
                if (callbacks.onInventoryUpdate) {
                    callbacks.onInventoryUpdate(event);
                }
            });

        echo.channel('alerts')
            .listen('.stock.low', (event) => {
                console.log('Low stock alert:', event);
                lowStockAlerts.value.unshift(event);
                
                if (callbacks.onLowStock) {
                    callbacks.onLowStock(event);
                }
            });
    });

    onUnmounted(() => {
        echo.leave('inventory');
        echo.leave('alerts');
    });

    return {
        updatedProducts,
        lowStockAlerts,
    };
}

// Composable for listening to order events (staff)
export function useOrderEvents(callbacks = {}) {
    const { echo } = useRealtime();
    
    // If Echo is not initialized, return empty functions
    if (!echo) {
        console.warn('⚠️ WebSocket not available. Order events will not update in real-time.');
        return {
            latestOrder: ref(null),
            orderUpdates: ref([]),
        };
    }
    const latestOrder = ref(null);
    const orderUpdates = ref([]);

    onMounted(() => {
        echo.channel('orders')
            .listen('.order.status.changed', (event) => {
                console.log('Order status changed:', event);
                latestOrder.value = event.order;
                orderUpdates.value.unshift(event);
                
                if (callbacks.onOrderStatusChange) {
                    callbacks.onOrderStatusChange(event);
                }
            });
    });

    onUnmounted(() => {
        echo.leave('orders');
    });

    return {
        latestOrder,
        orderUpdates,
    };
}

// Composable for listening to customer's private channel
export function useCustomerChannel(customerId, callbacks = {}) {
    const { echo } = useRealtime();
    const orderNotifications = ref([]);

    // If Echo is not initialized, return empty functions
    if (!echo) {
        console.warn('⚠️ WebSocket not available. Customer notifications will not update in real-time.');
        return {
            orderNotifications,
        };
    }

    onMounted(() => {
        if (!customerId) return;

        echo.private(`customer.${customerId}`)
            .listen('.order.status.changed', (event) => {
                console.log('Your order status changed:', event);
                orderNotifications.value.unshift(event);
                
                if (callbacks.onOrderStatusChange) {
                    callbacks.onOrderStatusChange(event);
                }
            });
    });

    onUnmounted(() => {
        if (customerId && echo) {
            echo.leave(`customer.${customerId}`);
        }
    });

    return {
        orderNotifications,
    };
}

