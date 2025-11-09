import { useCustomerAuthStore } from '../../stores/customerAuth';

export default async function customerAuthMiddleware(to, from, next) {
  const authStore = useCustomerAuthStore();

  // If route requires customer authentication
  if (to.meta.requiresCustomerAuth) {
    if (!authStore.token) {
      // Not authenticated, redirect to login
      return next({
        path: '/customer/login',
        query: { redirect: to.fullPath },
      });
    }

    // Fetch customer profile if not loaded
    if (!authStore.customer) {
      try {
        await authStore.fetchProfile();
      } catch (error) {
        // Token invalid, redirect to login
        return next({
          path: '/customer/login',
          query: { redirect: to.fullPath },
        });
      }
    }
  }

  // If route is for guests only (login, register)
  if (to.meta.customerGuest && authStore.token) {
    // Already authenticated, redirect to shop
    return next('/customer/shop');
  }

  next();
}

