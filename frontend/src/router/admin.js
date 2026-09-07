import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

// Admin route guard
const requireAuth = (to, from, next) => {
  const authStore = useAuthStore()
  
  if (!authStore.isAuthenticated) {
    next('/admin/login')
  } else {
    next()
  }
}

// Admin routes
const adminRoutes = [
  {
    path: '/admin',
    redirect: '/admin/dashboard'
  },
  {
    path: '/admin/dashboard',
    name: 'admin-dashboard',
    component: () => import('../views/admin/DashboardView.vue'),
    meta: { requiresAuth: true },
    beforeEnter: requireAuth
  },
  {
    path: '/admin/login',
    name: 'admin-login',
    component: () => import('../views/admin/LoginView.vue'),
    meta: { guest: true }
  },
  {
    path: '/admin/otp',
    name: 'admin-otp',
    component: () => import('../views/admin/OTPView.vue'),
    meta: { guest: true }
  },
  // Core admin routes
  {
    path: '/admin/orders',
    name: 'admin-orders',
    component: () => import('../views/admin/OrdersView.vue'),
    meta: { requiresAuth: true },
    beforeEnter: requireAuth
  },
  {
    path: '/admin/menu',
    name: 'admin-menu',
    component: () => import('../views/admin/MenuView.vue'),
    meta: { requiresAuth: true },
    beforeEnter: requireAuth
  },
  {
    path: '/admin/reservations',
    name: 'admin-reservations',
    component: () => import('../views/admin/ReservationsView.vue'),
    meta: { requiresAuth: true },
    beforeEnter: requireAuth
  },
  {
    path: '/admin/analytics',
    name: 'admin-analytics',
    component: () => import('../views/admin/AnalyticsView.vue'),
    meta: { requiresAuth: true },
    beforeEnter: requireAuth
  },
  {
    path: '/admin/notifications',
    name: 'admin-notifications',
    component: () => import('../views/admin/NotificationsView.vue'),
    meta: { requiresAuth: true },
    beforeEnter: requireAuth
  },
  {
    path: '/admin/settings',
    name: 'admin-settings',
    component: () => import('../views/admin/SettingsView.vue'),
    meta: { requiresAuth: true },
    beforeEnter: requireAuth
  }
]

export default adminRoutes
