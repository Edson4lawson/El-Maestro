<template>
  <div class="min-h-screen bg-m-obsidian flex">
    <!-- Sidebar -->
    <aside class="w-64 bg-m-obsidian/90 backdrop-blur-xl border-r border-white/10 fixed left-0 top-0 h-full z-40">
      <div class="p-6">
        <div class="flex items-center mb-8">
          <div class="w-10 h-10 bg-m-gold rounded-lg flex items-center justify-center">
            <span class="text-m-obsidian font-bold text-xl">M</span>
          </div>
          <h1 class="text-xl font-bold text-white ml-3">Admin</h1>
        </div>
        
        <nav class="space-y-2">
          <RouterLink 
            to="/admin/dashboard" 
            class="flex items-center p-3 rounded-lg hover:bg-white/10 transition-colors group"
            :class="{ 'bg-white/20': $route.path === '/admin/dashboard' }"
          >
            <LayoutDashboard class="w-5 h-5 mr-3" />
            <span class="text-white">Tableau de Bord</span>
          </RouterLink>
          
          <RouterLink 
            to="/admin/orders" 
            class="flex items-center p-3 rounded-lg hover:bg-white/10 transition-colors group"
            :class="{ 'bg-white/20': $route.path === '/admin/orders' }"
          >
            <ShoppingCart class="w-5 h-5 mr-3" />
            <span class="text-white">Commandes</span>
          </RouterLink>
          
          <RouterLink 
            to="/admin/menu" 
            class="flex items-center p-3 rounded-lg hover:bg-white/10 transition-colors group"
            :class="{ 'bg-white/20': $route.path === '/admin/menu' }"
          >
            <Utensils class="w-5 h-5 mr-3" />
            <span class="text-white">Menu</span>
          </RouterLink>
          
          <RouterLink 
            to="/admin/reservations" 
            class="flex items-center p-3 rounded-lg hover:bg-white/10 transition-colors group"
            :class="{ 'bg-white/20': $route.path === '/admin/reservations' }"
          >
            <Calendar class="w-5 h-5 mr-3" />
            <span class="text-white">Réservations</span>
          </RouterLink>
          
          <RouterLink 
            to="/admin/settings" 
            class="flex items-center p-3 rounded-lg hover:bg-white/10 transition-colors group"
            :class="{ 'bg-white/20': $route.path === '/admin/settings' }"
          >
            <Settings class="w-5 h-5 mr-3" />
            <span class="text-white">Paramètres</span>
          </RouterLink>
        </nav>
        
        <div class="absolute bottom-6 left-6 right-6">
          <button 
            @click="logout" 
            class="flex items-center p-2 rounded-lg bg-red-500/20 hover:bg-red-500/30 transition-colors text-white"
          >
            <LogOut class="w-4 h-4 mr-2" />
            <span class="text-sm">Déconnexion</span>
          </button>
        </div>
      </div>
    </aside>
    
    <!-- Main Content -->
    <main class="flex-1 ml-64">
      <slot />
    </main>
  </div>
</template>

<script setup>
import { RouterLink, useRoute } from 'vue-router'
import { 
  LayoutDashboard, 
  ShoppingCart, 
  Utensils, 
  Calendar, 
  Settings, 
  LogOut 
} from 'lucide-vue-next'

const logout = () => {
  localStorage.removeItem('admin_authenticated')
  localStorage.removeItem('admin_session_token')
  window.location.href = '/admin/login'
}
</script>

<style scoped>
.router-link-active {
  @apply bg-white/20 border-l-2 border-m-gold;
}
</style>
