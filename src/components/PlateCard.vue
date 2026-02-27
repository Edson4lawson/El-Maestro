<script setup>
import { ref, inject } from 'vue'
import gsap from 'gsap'
import { ShoppingCart, Star, Clock } from 'lucide-vue-next'
import { useCartStore } from '../stores/cart'

const props = defineProps(['plate'])
const emit = defineEmits(['added-to-cart'])
const cartStore = useCartStore()
const isDark = inject('isDark', ref(true))

const quantity = ref(1)

const addToCart = () => {
  cartStore.addToCart(props.plate, quantity.value)
  emit('added-to-cart')
}

const cardRef = ref(null)
const imageRef = ref(null)

const handleMouseMove = (e) => {
  if (!cardRef.value) return
  const { left, top, width, height } = cardRef.value.getBoundingClientRect()
  const x = (e.clientX - left) / width - 0.5
  const y = (e.clientY - top) / height - 0.5

  gsap.to(imageRef.value, {
    rotationY: x * 30,
    rotationX: -y * 30,
    x: x * 20,
    y: y * 20,
    duration: 0.5,
    ease: 'power2.out'
  })
}

const handleMouseLeave = () => {
  gsap.to(imageRef.value, {
    rotationY: 0,
    rotationX: 0,
    x: 0,
    y: 0,
    duration: 0.8,
    ease: 'elastic.out(1, 0.3)'
  })
}
</script>

<template>
  <div 
    ref="cardRef"
    @mousemove="handleMouseMove"
    @mouseleave="handleMouseLeave"
    class="glass-card group flex flex-col h-full cursor-pointer perspective-1000"
  >
    <!-- Plate Image with 3D Float -->
    <div class="relative h-64 flex items-center justify-center p-8 overflow-hidden bg-gradient-to-br from-white/5 to-transparent">
      <div class="plate-4d-glow"></div>
      <img 
        ref="imageRef"
        :src="plate.image" 
        :alt="plate.name"
        class="w-full h-full object-contain filter drop-shadow-[0_40px_50px_rgba(255,215,0,0.25)] group-hover:drop-shadow-[0_60px_80px_rgba(255,215,0,0.4)] transition-all duration-500 z-10"
      />
      
      <!-- Quick Badges -->
      <div class="absolute top-4 left-4 z-20">
        <span class="bg-m-gold/90 text-m-obsidian text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest shadow-lg">Maestro Choice</span>
      </div>
    </div>

    <!-- Content -->
    <div class="p-8 flex-grow flex flex-col">
      <div class="flex justify-between items-start mb-4">
        <div>
          <h3 class="text-2xl font-display font-bold group-hover:text-m-gold transition-colors leading-tight">{{ plate.name }}</h3>
          <div class="flex items-center gap-1 mt-2 text-m-gold">
            <Star v-for="i in 6" :key="i" class="w-3 h-3" :class="[i <= Math.round(plate.rating) ? 'fill-m-gold' : 'opacity-20']" />
            <span class="text-[10px] ml-2 font-bold opacity-60 tracking-tighter">{{ plate.rating }}/6</span>
          </div>
        </div>
        <div class="text-right">
          <p class="text-xl font-bold text-gradient-gold">{{ plate.price }}</p>
          <p class="text-[8px] uppercase tracking-widest opacity-40">FCFA</p>
        </div>
      </div>

      <p class="text-sm opacity-50 line-clamp-2 mb-8 flex-grow leading-relaxed">
        {{ plate.description }}
      </p>

      <div class="flex items-center justify-between mt-auto pt-6 border-t border-white/5">
        <RouterLink :to="`/details/${plate.id}`" class="text-xs font-bold uppercase tracking-[0.2em] hover:text-m-gold transition-colors flex items-center gap-2">
          L'assiette <span class="group-hover:translate-x-1 transition-transform">→</span>
        </RouterLink>
        <button 
          @click.stop="addToCart"
          class="w-12 h-12 rounded-xl bg-white/5 hover:bg-m-gold hover:text-m-obsidian transition-all duration-500 flex items-center justify-center border border-white/10 group-hover:border-m-gold/50"
        >
          <ShoppingCart class="w-5 h-5" />
        </button>
      </div>
    </div>
  </div>
</template>
