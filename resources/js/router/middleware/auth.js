import { useAuthStore } from '@/stores/auth';

export default async function authMiddleware(to, from, next) {
    const authStore = useAuthStore();

    console.log('Auth Middleware:', {
        to: to.path,
        from: from.path,
        token: !!authStore.token,
        user: !!authStore.user,
        requiresAuth: to.meta.requiresAuth,
        requiresGuest: to.meta.requiresGuest
    });

    // If route requires guest (login page) and user is already logged in
    if (to.meta.requiresGuest && authStore.token) {
        console.log('Redirecting to dashboard - user already logged in');
        return next({ name: 'Dashboard' });
    }

    // If route requires auth and user is not logged in
    if (to.meta.requiresAuth && !authStore.token) {
        console.log('Redirecting to login - no token');
        return next({ name: 'Login' });
    }

    // If user has token but no user data, fetch it
    if (authStore.token && !authStore.user && to.meta.requiresAuth) {
        try {
            console.log('Fetching user data...');
            await authStore.fetchUser();
            console.log('User data fetched successfully');
        } catch (error) {
            console.error('Failed to fetch user:', error);
            await authStore.logout();
            return next({ name: 'Login' });
        }
    }

    // Check if route requires specific roles
    if (to.meta.allowedRoles && !to.meta.allowedRoles.includes(authStore.user?.role)) {
        console.log('Redirecting to dashboard - insufficient permissions');
        return next({ name: 'Dashboard' });
    }

    // Check if route requires admin access
    if (to.meta.requiresAdmin && authStore.user?.role !== 'admin') {
        console.log('Redirecting to dashboard - admin access required');
        return next({ name: 'Dashboard' });
    }

    next();
}