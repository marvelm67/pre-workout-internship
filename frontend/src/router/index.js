/**
 * Vue Router Configuration
 * Role-Based Access Control routing
 */

import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

// Lazy-loaded route components
const routes = [
  {
    path: '/',
    redirect: '/shop'
  },
  
  // Auth routes
  {
    path: '/login',
    name: 'login',
    component: () => import('../pages/LoginPage.vue'),
    meta: { guest: true }
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('../pages/RegisterPage.vue'),
    meta: { guest: true }
  },
  
  // User routes (with main layout)
  {
    path: '/',
    component: () => import('../layouts/MainLayout.vue'),
    children: [
      {
        path: 'shop',
        name: 'shop',
        component: () => import('../pages/ShopPage.vue'),
        meta: { title: 'Shop' }
      },
      {
        path: 'cart',
        name: 'cart',
        component: () => import('../pages/CartPage.vue'),
        meta: { requiresAuth: true, title: 'Keranjang' }
      },
      {
        path: 'checkout',
        name: 'checkout',
        component: () => import('../pages/CheckoutPage.vue'),
        meta: { requiresAuth: true, title: 'Checkout' }
      },
      {
        path: 'orders',
        name: 'orders',
        component: () => import('../pages/OrdersPage.vue'),
        meta: { requiresAuth: true, title: 'Riwayat Pesanan' }
      },
      {
        path: 'profile',
        name: 'profile',
        component: () => import('../pages/ProfilePage.vue'),
        meta: { requiresAuth: true, title: 'Profil' }
      }
    ]
  },
  
  // Admin routes
  {
    path: '/admin',
    component: () => import('../layouts/AdminLayout.vue'),
    meta: { requiresAuth: true, requiresAdmin: true },
    children: [
      {
        path: '',
        redirect: '/admin/dashboard'
      },
      {
        path: 'dashboard',
        name: 'admin-dashboard',
        component: () => import('../pages/admin/DashboardPage.vue'),
        meta: { title: 'Dashboard Admin' }
      },
      {
        path: 'products',
        name: 'admin-products',
        component: () => import('../pages/admin/ProductsPage.vue'),
        meta: { title: 'Kelola Produk' }
      },
      {
        path: 'orders',
        name: 'admin-orders',
        component: () => import('../pages/admin/OrdersPage.vue'),
        meta: { title: 'Kelola Pesanan' }
      },
      {
        path: 'users',
        name: 'admin-users',
        component: () => import('../pages/admin/UsersPage.vue'),
        meta: { title: 'Kelola Pengguna' }
      }
    ]
  },
  
  // 404 Not Found
  {
    path: '/:catchAll(.*)*',
    name: 'not-found',
    component: () => import('../pages/ErrorPage.vue')
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    } else {
      return { top: 0 }
    }
  }
})

// Navigation guards
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  
  // Set page title
  const title = to.meta.title || 'OpticModern'
  document.title = `${title} | OpticModern`
  
  // Check if route requires authentication
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return next({ 
      name: 'login', 
      query: { redirect: to.fullPath } 
    })
  }
  
  // Check if route requires admin role
  if (to.meta.requiresAdmin && !authStore.isAdmin) {
    return next({ name: 'shop' })
  }
  
  // Redirect authenticated users away from guest-only pages
  if (to.meta.guest && authStore.isAuthenticated) {
    // Redirect based on role
    if (authStore.isAdmin) {
      return next({ name: 'admin-dashboard' })
    }
    return next({ name: 'shop' })
  }
  
  next()
})

export default router
