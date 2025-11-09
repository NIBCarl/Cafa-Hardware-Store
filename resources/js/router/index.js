import { createRouter, createWebHistory } from 'vue-router';
import authMiddleware from './middleware/auth';
import customerAuthMiddleware from './middleware/customerAuth';

// Layouts
import DefaultLayout from '../components/layout/DefaultLayout.vue';
import GuestLayout from '../components/layout/GuestLayout.vue';

const routes = [
  // Home/Landing Page
  {
    path: '/',
    name: 'Home',
    component: () => import('../views/customer/Home.vue'),
  },

  // Staff Portal Routes
  {
    path: '/auth/login',
    component: GuestLayout,
    meta: { requiresGuest: true },
    children: [
      {
        path: '',
        name: 'Login',
        component: () => import('../views/auth/Login.vue'),
      },
    ],
  },
  {
    path: '/staff',
    component: DefaultLayout,
    meta: { requiresAuth: true },
    redirect: '/staff/dashboard',
    children: [
      {
        path: 'dashboard',
        name: 'Dashboard',
        component: () => import('../views/staff/Dashboard.vue'),
      },
      {
        path: 'pos',
        name: 'POS',
        component: () => import('../views/staff/POS.vue'),
      },
      {
        path: 'inventory',
        name: 'Inventory',
        component: () => import('../views/staff/Inventory.vue'),
      },
      {
        path: 'orders',
        name: 'Orders',
        component: () => import('../views/staff/Orders.vue'),
      },
      {
        path: 'reports',
        name: 'Reports',
        component: () => import('../views/staff/Reports.vue'),
      },
      {
        path: 'settings',
        name: 'Settings',
        component: () => import('../views/staff/Settings.vue'),
      },
      {
        path: 'users',
        name: 'Users',
        component: () => import('../views/staff/Users.vue'),
        meta: { requiresAuth: true, requiresAdmin: true },
      },
    ],
  },
  
  // Customer Portal Routes
  {
    path: '/customer/login',
    name: 'CustomerLogin',
    component: () => import('../views/customer/Login.vue'),
    meta: { customerGuest: true },
  },
  {
    path: '/customer/register',
    name: 'CustomerRegister',
    component: () => import('../views/customer/Register.vue'),
    meta: { customerGuest: true },
  },
  {
    path: '/customer/shop',
    name: 'CustomerShop',
    component: () => import('../views/customer/Shop.vue'),
  },
  {
    path: '/customer/cart',
    name: 'CustomerCart',
    component: () => import('../views/customer/Cart.vue'),
  },
  {
    path: '/customer/orders',
    name: 'CustomerOrders',
    component: () => import('../views/customer/Orders.vue'),
    meta: { requiresCustomerAuth: true },
  },
  {
    path: '/customer/orders/:id',
    name: 'CustomerOrderDetail',
    component: () => import('../views/customer/OrderDetail.vue'),
    meta: { requiresCustomerAuth: true },
  },
  {
    path: '/customer/profile',
    name: 'CustomerProfile',
    component: () => import('../views/customer/Profile.vue'),
    meta: { requiresCustomerAuth: true },
  },
  
  // Fallback
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: () => import('../views/NotFound.vue'),
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition;
    }
    return { top: 0 };
  },
});

// Apply appropriate middleware based on route
router.beforeEach((to, from, next) => {
  // Customer routes middleware
  if (to.path.startsWith('/customer')) {
    return customerAuthMiddleware(to, from, next);
  }
  // Staff routes middleware
  return authMiddleware(to, from, next);
});

export default router;