import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView
    },
    {
      path: '/details/:id',
      name: 'plate-details',
      component: () => import('../views/PlateDetailView.vue')
    },
    {
      path: '/menu',
      name: 'menu',
      component: () => import('../views/MenuView.vue')
    },
    {
      path: '/commander',
      name: 'checkout',
      component: () => import('../views/CheckoutView.vue')
    },
    {
      path: '/fidelite',
      name: 'loyalty',
      component: () => import('../views/LoyaltyView.vue')
    },
    {
      path: '/reservations',
      name: 'reservations',
      component: () => import('../views/ReservationsView.vue')
    },
    {
      path: '/admin/login',
      name: 'admin-login',
      component: () => import('../views/admin/LoginView.vue')
    },
    {
      path: '/admin/otp',
      name: 'admin-otp',
      component: () => import('../views/admin/OTPView.vue')
    },
    {
      path: '/admin/dashboard',
      name: 'admin-dashboard',
      component: () => import('../views/admin/DashboardView.vue')
    }
  ],
  scrollBehavior() {
    return { top: 0 }
  }
})

export default router
