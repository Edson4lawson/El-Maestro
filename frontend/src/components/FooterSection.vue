<template>
  <footer class="relative" :class="isDark ? 'bg-m-obsidian' : 'bg-m-linen'">
    <div class="h-1 relative overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-r from-transparent via-m-gold/30 to-transparent animate-pulse"></div>
      <div class="absolute inset-0 bg-gradient-to-r from-transparent via-transparent to-m-gold/30 animate-slide"></div>
    </div>
    
    <div class="container mx-auto px-6 py-16">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
        <!-- Brand -->
        <div>
          <h3 class="text-xl font-bold mb-6 text-gradient-gold" :class="isDark ? 'text-white' : 'text-m-obsidian'">
            EL MAESTRO
          </h3>
          <p class="text-sm opacity-70 mb-4" :class="isDark ? 'text-white' : 'text-m-obsidian'">
            L'apogée culinaire où l'ingrédient devient œuvre d'art.
          </p>
        </div>

        <!-- Navigation -->
        <div>
          <h4 class="text-lg font-bold mb-4" :class="isDark ? 'text-white' : 'text-m-obsidian'">
            Navigation
          </h4>
          <ul class="space-y-2">
            <li><RouterLink to="/" class="text-sm hover:text-m-gold" :class="isDark ? 'text-white/70' : 'text-m-obsidian/70'">Accueil</RouterLink></li>
            <li><RouterLink to="/menu" class="text-sm hover:text-m-gold" :class="isDark ? 'text-white/70' : 'text-m-obsidian/70'">Menu</RouterLink></li>
            <li><RouterLink to="/reservations" class="text-sm hover:text-m-gold" :class="isDark ? 'text-white/70' : 'text-m-obsidian/70'">Réservations</RouterLink></li>
          </ul>
        </div>

        <!-- Newsletter -->
        <div>
          <h4 class="text-lg font-bold mb-4" :class="isDark ? 'text-white' : 'text-m-obsidian'">
            Newsletter
          </h4>
          <p class="text-sm mb-4" :class="isDark ? 'text-white/70' : 'text-m-obsidian/70'">
            Recevez nos offres exclusives et nouvelles gastronomiques
          </p>
          <form @submit.prevent="subscribeNewsletter" class="space-y-3">
            <input 
              v-model="email"
              type="email" 
              placeholder="Votre email" 
              required
              class="w-full px-4 py-2 rounded-lg border text-sm focus:outline-none focus:ring-2 focus:ring-m-gold/50"
              :class="isDark ? 'bg-m-obsidian/50 border-white/20 text-white placeholder-white/50' : 'bg-white border-m-obsidian/20 text-m-obsidian placeholder-m-obsidian/50'"
            >
            <button 
              type="submit"
              class="w-full px-4 py-2 bg-m-gold text-m-obsidian rounded-lg font-bold text-sm hover:bg-m-gold/90 transition-colors"
            >
              S'abonner
            </button>
          </form>
          <div v-if="subscribeMessage" class="mt-3 text-sm" :class="subscribeSuccess ? 'text-green-500' : 'text-red-500'">
            {{ subscribeMessage }}
          </div>
        </div>

        <!-- Réseaux Sociaux -->
        <div>
          <h4 class="text-lg font-bold mb-4" :class="isDark ? 'text-white' : 'text-m-obsidian'">
            Suivez-nous
          </h4>
          <div class="flex space-x-4">
            <a 
              href="https://facebook.com/elmaestrorestaurant" 
              target="_blank"
              class="w-10 h-10 rounded-full bg-m-gold/20 flex items-center justify-center hover:bg-m-gold transition-colors group"
              :class="isDark ? 'hover:bg-m-gold/30' : ''"
            >
              <svg class="w-5 h-5" :class="isDark ? 'text-white' : 'text-m-obsidian'" fill="currentColor" viewBox="0 0 24 24">
                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
              </svg>
            </a>
            <a 
              href="https://wa.me/229012345678" 
              target="_blank"
              class="w-10 h-10 rounded-full bg-m-gold/20 flex items-center justify-center hover:bg-m-gold transition-colors group"
              :class="isDark ? 'hover:bg-m-gold/30' : ''"
            >
              <svg class="w-5 h-5" :class="isDark ? 'text-white' : 'text-m-obsidian'" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.149-.67.149-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414-.074-.123-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
              </svg>
            </a>
            <a 
              href="https://instagram.com/elmaestrorestaurant" 
              target="_blank"
              class="w-10 h-10 rounded-full bg-m-gold/20 flex items-center justify-center hover:bg-m-gold transition-colors group"
              :class="isDark ? 'hover:bg-m-gold/30' : ''"
            >
              <svg class="w-5 h-5" :class="isDark ? 'text-white' : 'text-m-obsidian'" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zM5.838 12a6.162 6.162 0 1112.324 0 6.162 6.162 0 01-12.324 0zM12 16a4 4 0 110-8 4 4 0 010 8zm4.965-10.405a1.44 1.44 0 112.881.001 1.44 1.44 0 01-2.881-.001z"/>
              </svg>
            </a>
          </div>
        </div>

        <!-- Contact -->
        <div>
          <h4 class="text-lg font-bold mb-4" :class="isDark ? 'text-white' : 'text-m-obsidian'">
            Contact
          </h4>
          <ul class="space-y-2 text-sm" :class="isDark ? 'text-white/70' : 'text-m-obsidian/70'">
            <li>123 Rue Gastronomique, Cotonou</li>
            <li>+229 01 XX XX XX XX</li>
            <li>contact@elmaestro.bj</li>
          </ul>
        </div>

        <!-- Horaires -->
        <div>
          <h4 class="text-lg font-bold mb-4" :class="isDark ? 'text-white' : 'text-m-obsidian'">
            Horaires
          </h4>
          <p class="text-sm" :class="isDark ? 'text-white/70' : 'text-m-obsidian/70'">
            Mar-Dim: 11:30 - 23:00<br>
            Lun: Fermé
          </p>
        </div>
      </div>

      <!-- Bottom -->
      <div class="mt-16 pt-8 border-t flex flex-col md:flex-row justify-between items-center gap-4" :class="isDark ? 'border-white/10' : 'border-m-obsidian/10'">
        <p class="text-sm opacity-60" :class="isDark ? 'text-white' : 'text-m-obsidian'">
          © 2024 EL MAESTRO. Tous droits réservés.
        </p>
        <div class="flex space-x-6 text-sm" :class="isDark ? 'text-white/60' : 'text-m-obsidian/60'">
          <a href="#" class="hover:text-m-gold transition-colors">Mentions légales</a>
          <a href="#" class="hover:text-m-gold transition-colors">Politique de confidentialité</a>
          <a href="#" class="hover:text-m-gold transition-colors">CGV</a>
        </div>
      </div>
    </div>
  </footer>
</template>

<style scoped>
@keyframes slide {
  0% { transform: translateX(-100%); }
  50% { transform: translateX(0); }
  100% { transform: translateX(100%); }
}

@keyframes gold-flow {
  0% { 
    background-position: 0% 50%;
    background-size: 200% 100%;
  }
  50% { 
    background-position: 100% 50%;
  }
  100% { 
    background-position: 200% 50%;
  }
}

.animate-slide {
  animation: slide 4s ease-in-out infinite;
}

.animate-pulse {
  animation: gold-flow 3s ease-in-out infinite;
}

.bg-gradient-to-r.from-transparent.via-m-gold-30.via-m-gold.to-transparent {
  background: linear-gradient(90deg, 
    transparent 0%, 
    rgba(212, 175, 55, 0.3) 25%, 
    rgba(212, 175, 55, 0.6) 50%, 
    rgba(212, 175, 55, 0.3) 75%, 
    transparent 100%);
  background-size: 200% 100%;
}
</style>

<script setup>
import { ref, inject } from 'vue'
import { RouterLink } from 'vue-router'

const isDark = inject('isDark', ref(true))

// Newsletter state
const email = ref('')
const subscribeMessage = ref('')
const subscribeSuccess = ref(false)

const subscribeNewsletter = () => {
  // Simuler l'inscription à la newsletter
  if (email.value) {
    subscribeSuccess.value = true
    subscribeMessage.value = 'Inscription réussie ! Merci de votre confiance.'
    email.value = ''
    
    // Réinitialiser le message après 3 secondes
    setTimeout(() => {
      subscribeMessage.value = ''
    }, 3000)
  } else {
    subscribeSuccess.value = false
    subscribeMessage.value = 'Veuillez entrer une adresse email valide.'
  }
}
</script>
