import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import adminRoutes from './admin.js'
import { useAuthStore } from '../stores/auth'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    // Public routes
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
    
    // Admin routes
    ...adminRoutes
  ],
  scrollBehavior() {
    return { top: 0 }
  }
})

// Global navigation guards
router.beforeEach((to, from, next) => {
  console.log('Navigating to:', to.path)
  next()
})

export default router
