<script setup>
import { ref, onMounted, watch, inject } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { Star, ChevronLeft, Minus, Plus, ShoppingBag, Clock, ShieldCheck, MapPin } from 'lucide-vue-next'
import gsap from 'gsap'
import StarRating from '../components/StarRating.vue'
import { useCartStore } from '../stores/cart'
import { plateService } from '../services/api'

const route = useRoute()
const router = useRouter()
const cartStore = useCartStore()

const plate = ref(null)
const loading = ref(true)
const quantity = ref(1)
const userRating = ref(0)
const plateRef = ref(null)
const isDark = inject('isDark', ref(true))

const increment = () => quantity.value++
const decrement = () => { if (quantity.value > 1) quantity.value-- }

const showToast = ref(false)

const addToCart = () => {
  if (plate.value) {
    cartStore.addToCart(plate.value, quantity.value)
    showToast.value = true
    setTimeout(() => showToast.value = false, 3000)
  }
}
async function submitRating(newRating) {
  try {
    await plateService.addReview({
      plate_id: plate.value.id,
      rating: newRating,
      user_name: 'Client Gourmet', // Optional: could be from a user store
      comment: 'Excellent !'
    })
  } catch (error) {
    console.error('Error submitting rating:', error)
  }
}

watch(userRating, (newVal) => {
  if (newVal > 0) submitRating(newVal)
})

onMounted(async () => {
  try {
    const response = await plateService.getAll()
    const allPlates = response.data
    plate.value = allPlates.find(p => p.id == route.params.id) || allPlates[0]
  } catch (error) {
    console.error('Error fetching plate detail:', error)
  } finally {
    loading.value = false
  }

  gsap.from('.stagger-in', {
    y: 30,
    opacity: 0,
    duration: 0.8,
    stagger: 0.1,
    ease: 'power3.out'
  })

  window.addEventListener('mousemove', (e) => {
    if (!plateRef.value) return
    const rect = plateRef.value.getBoundingClientRect()
    const centerX = rect.left + rect.width / 2
    const centerY = rect.top + rect.height / 2
    
    const x = (e.clientX - centerX) / 20
    const y = (e.clientY - centerY) / 20

    gsap.to(plateRef.value, {
      rotationY: x,
      rotationX: -y,
      x: x * 0.5,
      y: y * 0.5,
      duration: 0.8,
      ease: 'power2.out'
    })
  })
})
</script>

<template>
  <div class="px-6 md:px-12 py-12 relative" :class="isDark ? 'bg-m-obsidian' : 'bg-m-linen'">
    <!-- Toast Notification -->
    <Transition name="toast">
      <div v-if="showToast" class="fixed bottom-10 left-1/2 -translate-x-1/2 z-[100] bg-m-gold text-m-obsidian px-8 py-4 rounded-2xl shadow-2xl font-bold flex items-center gap-3">
        <ShoppingBag class="w-6 h-6" />
        {{ quantity }} plat(s) ajouté(s) au panier
      </div>
    </Transition>

    <div v-if="plate" class="container mx-auto">
      <button @click="router.back()" class="flex items-center gap-2 opacity-60 hover:opacity-100 transition-opacity mb-12 group">
        <ChevronLeft class="w-5 h-5 group-hover:-translate-x-1 transition-transform" /> Retour au menu
      </button>

      <div class="grid lg:grid-cols-2 gap-16 items-center">
        <!-- 4D Visual -->
        <div class="relative flex justify-center items-center" style="perspective: 1200px;">
          <div ref="plateRef" class="relative z-10 w-full max-w-lg cursor-grab active:cursor-grabbing">
            <img 
              :src="plate.image" 
              :alt="plate.name" 
              class="w-full drop-shadow-[0_60px_100px_rgba(212,175,55,0.4)]"
            />
            
            <!-- Dynamic Glow -->
            <div class="absolute inset-0 bg-m-gold/10 blur-[100px] rounded-full -z-10 animate-pulse"></div>
          </div>
          
          <!-- Floating elements decorators -->
          <div class="absolute -top-10 -right-10 w-24 h-24 border border-m-gold/20 rounded-full animate-bounce" style="animation-duration: 4s;"></div>
          <div class="absolute bottom-0 -left-10 w-40 h-40 border border-m-gold/10 rounded-full animate-spin-slow"></div>
        </div>

        <!-- Info Section -->
        <div>
          <div class="stagger-in flex items-center gap-4 mb-6">
            <span class="bg-m-gold text-m-obsidian px-4 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-[0.2em] shadow-lg shadow-m-gold/20">Signature Maestro</span>
            <div class="flex items-center gap-2">
              <StarRating :rating="plate.rating" />
            </div>
          </div>

          <h1 class="stagger-in text-5xl md:text-7xl font-display font-bold mb-6 leading-tight">{{ plate.name }}</h1>
          <p class="stagger-in text-4xl font-display text-gradient-gold mb-12 font-bold">{{ plate.price }} FCFA</p>
          <p class="stagger-in opacity-70 text-lg leading-relaxed mb-10 max-w-xl">
            {{ plate.description }}
          </p>

          <div class="stagger-in grid grid-cols-2 gap-8 mb-12">
            <div>
              <h4 class="text-sm font-bold uppercase tracking-widest mb-4 opacity-50">Ingrédients</h4>
              <ul class="flex flex-wrap gap-2">
                <li v-for="ing in (plate.ingredients || ['Ingrédient principal', 'Épices fines', 'Herbes aromatiques'])" :key="ing" class="px-3 py-1 rounded-lg text-sm border" :class="isDark ? 'bg-white/5 border-white/10' : 'bg-black/5 border-black/10'">
                  {{ ing }}
                </li>
              </ul>
            </div>
            <div class="flex flex-col gap-4">
              <div class="flex items-center gap-3">
                <Clock class="w-5 h-5 text-m-gold" />
                <span class="text-sm">Prêts en {{ plate.time || '15 minutes' }}</span>
              </div>
              <div class="flex items-center gap-3">
                <ShieldCheck class="w-5 h-5 text-m-gold" />
                <span class="text-sm">Hygiène certifiée Premium</span>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="stagger-in flex flex-wrap items-center gap-6 p-6 glass-card border-m-gold/10">
            <div class="flex items-center rounded-full p-1 border" :class="isDark ? 'bg-white/5 border-white/10' : 'bg-black/5 border-black/10'">
              <button @click="decrement" class="w-10 h-10 flex items-center justify-center rounded-full transition-colors" :class="isDark ? 'hover:bg-white/10' : 'hover:bg-black/10'">
                <Minus class="w-4 h-4" />
              </button>
              <span class="w-12 text-center font-bold text-lg">{{ quantity }}</span>
              <button @click="increment" class="w-10 h-10 flex items-center justify-center rounded-full transition-colors" :class="isDark ? 'hover:bg-white/10' : 'hover:bg-black/10'">
                <Plus class="w-4 h-4" />
              </button>
            </div>
            
            <button @click="addToCart" class="btn-gold flex-grow md:flex-grow-0 flex items-center justify-center gap-3 px-10">
              <ShoppingBag class="w-5 h-5" /> Ajouter au panier
            </button>
          </div>

          <!-- User Rating Section -->
          <div class="stagger-in mt-12 pt-12 border-t" :class="isDark ? 'border-white/5' : 'border-black/5'">
            <h4 class="text-lg font-bold mb-4">Notez ce plat</h4>
            <div class="flex items-center gap-6">
              <StarRating v-model:rating="userRating" :editable="true" />
              <p v-if="userRating > 0" class="text-sm opacity-60">Merci pour votre note de {{ userRating }} étoiles !</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.animate-spin-slow {
  animation: spin 25s linear infinite;
}
@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.toast-enter-active, .toast-leave-active { transition: all 0.5s cubic-bezier(0.68, -0.6, 0.32, 1.6); }
.toast-enter-from { opacity: 0; transform: translate(-50%, 50px) scale(0.9); }
.toast-leave-to { opacity: 0; transform: translate(-50%, 50px) scale(0.9); }
</style>
