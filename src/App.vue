<script setup>
import { ref, onMounted, provide } from 'vue'
import { useRouter } from 'vue-router'
import { Sun, Moon, ShoppingBag, Menu as MenuIcon, X } from 'lucide-vue-next'
import Lenis from '@studio-freight/lenis'
import { useCartStore } from './stores/cart'
import ToastNotifications from './components/ToastNotifications.vue'
import FooterSection from './components/FooterSection.vue'

const isDark = ref(true)
const isMenuOpen = ref(false)
const router = useRouter()
const cartStore = useCartStore()

// Provide theme state to child components
provide('isDark', isDark)

const toggleTheme = () => {
  isDark.value = !isDark.value
  document.documentElement.classList.toggle('light', !isDark.value)
}

const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value
}

onMounted(() => {
  // Auto-detect system preference
  const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
  isDark.value = prefersDark
  document.documentElement.classList.toggle('light', !prefersDark)

  // Initialize Lenis Smooth Scroll
  const lenis = new Lenis({
    duration: 1.2,
    easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
    smoothWheel: true
  })

  function raf(time) {
    lenis.raf(time)
    requestAnimationFrame(raf)
  }

  requestAnimationFrame(raf)
})
</script>

<template>
  <div class="min-h-screen flex flex-col overflow-x-hidden">
  <!-- Navbar -->
  <nav
    class="fixed top-0 left-0 right-0 z-50 py-4 px-6 md:px-12 flex justify-between items-center transition-all duration-500 border-b"
    :class="[isDark ? 'bg-m-obsidian/85 backdrop-blur-xl border-white/10' : 'bg-m-linen/85 backdrop-blur-xl border-black/5']">
    <div class="flex items-center gap-10">
      <RouterLink to="/"
        class="text-3xl font-display font-bold tracking-tighter text-gradient-gold hover:scale-110 transition-transform">
        EL MAESTRO
      </RouterLink>

      <div class="hidden md:flex gap-8 items-center text-sm font-bold tracking-widest uppercase">
        <RouterLink to="/" class="hover:text-m-gold transition-colors relative group py-2">
          Accueil
          <span class="absolute bottom-0 left-0 w-0 h-[2px] bg-m-gold transition-all group-hover:w-full"></span>
        </RouterLink>
        <RouterLink to="/menu" class="hover:text-m-gold transition-colors relative group py-2">
          Menu
          <span class="absolute bottom-0 left-0 w-0 h-[2px] bg-m-gold transition-all group-hover:w-full"></span>
        </RouterLink>
        <RouterLink to="/reservations" class="hover:text-m-gold transition-colors relative group py-2">
          Réservations
          <span class="absolute bottom-0 left-0 w-0 h-[2px] bg-m-gold transition-all group-hover:w-full"></span>
        </RouterLink>
        <RouterLink to="/fidelite" class="hover:text-m-gold transition-colors relative group py-2">
          Fidélité
          <span class="absolute bottom-0 left-0 w-0 h-[2px] bg-m-gold transition-all group-hover:w-full"></span>
        </RouterLink>
      </div>
    </div>

    <div class="flex items-center gap-4">
      <button @click="toggleTheme" class="p-2 rounded-full hover:bg-white/10 transition-colors">
        <Sun v-if="isDark" class="w-5 h-5" />
        <Moon v-else class="w-5 h-5" />
      </button>

      <RouterLink to="/commander" class="relative p-2 rounded-full hover:bg-white/10 transition-colors">
        <ShoppingBag class="w-5 h-5" />
        <span v-if="cartStore.totalItems > 0"
          class="absolute top-0 right-0 bg-m-gold text-m-obsidian text-[10px] font-bold w-4 h-4 flex items-center justify-center rounded-full animate-bounce">
          {{ cartStore.totalItems }}
        </span>
      </RouterLink>

      <button @click="toggleMenu" class="md:hidden p-2">
        <MenuIcon v-if="!isMenuOpen" class="w-6 h-6" />
        <X v-else class="w-6 h-6" />
      </button>

      <RouterLink to="/menu" class="hidden md:block btn-gold text-sm whitespace-nowrap">
        Plat copieux
      </RouterLink>
    </div>
  </nav>


  <!-- Mobile Menu -->
  <Transition name="fade">
    <div v-if="isMenuOpen"
      class="fixed inset-0 z-40 backdrop-blur-xl flex flex-col items-center justify-center gap-8 text-2xl font-display"
      :class="isDark ? 'bg-m-obsidian/95' : 'bg-m-linen/95'">
      <RouterLink @click="toggleMenu" to="/" class="hover:text-m-gold"
        :class="isDark ? 'text-white' : 'text-m-obsidian'">Accueil</RouterLink>
      <RouterLink @click="toggleMenu" to="/menu" class="hover:text-m-gold"
        :class="isDark ? 'text-white' : 'text-m-obsidian'">Menu</RouterLink>
      <RouterLink @click="toggleMenu" to="/reservations" class="hover:text-m-gold"
        :class="isDark ? 'text-white' : 'text-m-obsidian'">Réservations</RouterLink>
      <RouterLink @click="toggleMenu" to="/fidelite" class="hover:text-m-gold"
        :class="isDark ? 'text-white' : 'text-m-obsidian'">Fidélité</RouterLink>
      <RouterLink @click="toggleMenu" to="/commander" class="btn-gold mt-4">Commander</RouterLink>
    </div>
  </Transition>

  <!-- Main Content -->
  <main class="flex-grow pt-20">
    <RouterView v-slot="{ Component }">
      <Transition name="page" mode="out-in">
        <component :is="Component" />
      </Transition>
    </RouterView>
  </main>

  <!-- Toast Notifications -->
  <ToastNotifications />

  <!-- Footer -->
  <FooterSection v-if="!$route.path.includes('/commander') && !$route.path.includes('/plate/') && !$route.path.includes('/details/')" />
</div>
</template>

<style>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.page-enter-active,
.page-leave-active {
  transition: all 0.4s ease;
}

.page-enter-from {
  opacity: 0;
  transform: translateY(20px);
}

.page-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}
</style>
