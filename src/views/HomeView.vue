<script setup>
import { ref, onMounted, inject } from 'vue'
import { plateService } from '../services/api'
import gsap from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'
import { ChevronRight, Utensils, Truck, Star, PlayCircle, CheckCircle2 } from 'lucide-vue-next'
import PlateCard from '../components/PlateCard.vue'
import ThreeDSection from '../components/ThreeDSection.vue'

gsap.registerPlugin(ScrollTrigger)

const heroPlateRef = ref(null)
const parallaxBg = ref(null)
const plates = ref([])
const loading = ref(true)
const isDark = inject('isDark', ref(true))

const addToCart = (plate) => {
  if (window.showToast) {
    window.showToast({
      type: 'cart',
      title: 'Plat ajouté au panier',
      message: `${plate.name} a été ajouté avec succès`,
      duration: 3000
    })
  }
}

const fetchSignaturePlates = async () => {
  try {
    const response = await plateService.getAll()
    // Take first 3 plates or filtered signature plates if available
    plates.value = response.data.slice(0, 3)
  } catch (error) {
    console.error('Error fetching plates:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchSignaturePlates()

  // Hero Entrance
  const tl = gsap.timeline()
  
  // Attendre que les éléments soient dans le DOM
  setTimeout(() => {
    const heroTitle = document.querySelector('.hero-title span')
    const heroSubtitle = document.querySelector('.hero-subtitle')
    const heroCta = document.querySelector('.hero-cta')
    const heroSection = document.querySelector('.hero-section')
    
    if (heroTitle) {
      tl.from(heroTitle, {
        opacity: 0,
        y: 100,
        duration: 1.2,
        ease: 'power3.out'
      })
    }
    
    if (heroSubtitle) {
      tl.from(heroSubtitle, {
        opacity: 0,
        y: 50,
        duration: 1,
        ease: 'power3.out'
      }, '-=0.6')
    }
    
    if (heroCta) {
      tl.from(heroCta, {
        opacity: 0,
        scale: 0.8,
        duration: 1,
        ease: 'power3.out'
      }, '-=0.4')
    }
    
    if (heroPlateRef.value) {
      tl.from(heroPlateRef.value, {
        opacity: 0,
        scale: 0.5,
        rotation: 15,
        duration: 1.5,
        ease: 'elastic.out(1, 0.5)'
      }, '-=0.8')
    }
  }, 100)

  // Hero Plate Float
  if (heroPlateRef.value) {
    gsap.to(heroPlateRef.value, {
      y: -30,
      rotationZ: 5,
      duration: 3,
      repeat: -1,
      yoyo: true,
      ease: 'power1.inOut'
    })
  }

  // Background Parallax
  if (parallaxBg.value) {
    gsap.to(parallaxBg.value, {
      scrollTrigger: {
        trigger: '.hero-section',
        start: 'top top',
        end: 'bottom top',
        scrub: true
      },
      y: 200,
      ease: 'none'
    })
  }

  // Reveal Animations
  setTimeout(() => {
    gsap.utils.toArray('.reveal-up').forEach(el => {
      gsap.from(el, {
        scrollTrigger: {
          trigger: el,
          start: 'top 85%'
        },
        y: 60,
        opacity: 0,
        duration: 1,
        ease: 'power3.out'
      })
    })
  }, 200)
})
</script>

<template>
  <div class="relative" :class="isDark ? 'bg-m-obsidian' : 'bg-m-linen'">
    <!-- Hero Section -->

    <section class="hero-section relative min-h-screen flex items-center overflow-hidden">
      <!-- High-end Background Image with Parallax -->
      <div ref="parallaxBg" class="absolute inset-0 z-0">
        <div class="absolute inset-0 bg-black/40 z-10 rounded-b-3xl"></div>
        <img 
          src="../assets/imageJ.jpg" 
          class="w-full h-full object-cover scale-110 rounded-b-3xl"
        />
      </div>

      <div class="container mx-auto px-6 md:px-12 grid md:grid-cols-2 gap-12 items-center relative z-20">
        <div class="hero-content">
          <div class="hero-title overflow-hidden">
            <h1 class="text-7xl md:text-9xl font-display font-bold leading-[0.9] mb-8">
              <span class="block">MODERNE</span>
              <span class="block text-gradient-gold italic">RAFFINÉ</span>
              <span class="block">UNIQUE</span>
            </h1>
          </div>
          
          <p class="hero-subtitle text-xl text-white font-boal opacity-80 max-w-lg mb-12 leading-relaxed border-l-4 border-m-gold/50 pl-8">
            L'apogée culinaire où l'ingrédient devient œuvre d'art. Une expérience immersive certifiée Maestro.
          </p>
          
          <div class="hero-cta flex flex-wrap gap-8 items-center">
            <RouterLink to="/menu" class="btn-gold px-12 py-5 text-xl group overflow-hidden relative">
              <span class="relative z-10 flex items-center gap-3">
                DÉCOUVRIR LE MENU <ChevronRight class="w-6 h-6 group-hover:translate-x-2 transition-transform" />
              </span>
            </RouterLink>
            <button class="flex items-center gap-4 group">
              <div class="w-14 h-14 rounded-full border border-white/20 flex items-center justify-center group-hover:border-m-gold transition-colors">
                <PlayCircle class="w-6 h-6 text-m-gold" />
              </div>
              <span class="font-bold tracking-widest text-sm uppercase">Le Film du Chef</span>
            </button>
          </div>
        </div>

        <div class="relative flex justify-center items-center">
          <div ref="heroPlateRef" class="relative justify-start z-10 w-50 h-45 max-w-md mr-5">
            <img 
              src="../assets/image12.jpg" 
              class="w-full rounded-xl drop-shadow-[0_100px_150px_rgba(255,215,0,0.4)]"
            />
          </div>
          <!-- Orbit Decor -->
          <div class="absolute inset-0 bg-m-gold/20 blur-[180px] rounded-full animate-pulse"></div>
        </div>
      </div>

      <!-- Video Background Mockup Overlay -->
      <div class="absolute bottom-12 right-12 flex gap-4 items-center z-30 p-4 rounded-2xl border reveal-up" :class="isDark ? 'bg-white/5 backdrop-blur-md border-white/10' : 'bg-black/5 backdrop-blur-md border-black/10'">
        <div class="w-24 h-14 rounded-lg overflow-hidden relative group cursor-pointer" :class="isDark ? 'bg-white/10' : 'bg-black/10'">
           <img src="../assets/image17.jpg" class="w-700 h-700 object-cover group-hover:scale-110 transition-transform" />
           <div class="absolute inset-0 flex items-center justify-center" :class="isDark ? 'bg-black/40' : 'bg-black/20'">
             <PlayCircle class="w-6 h-6" :class="isDark ? 'text-white' : 'text-m-obsidian'" />
           </div>
        </div>
        <div class="pr-4">
          <p class="text-[10px] font-bold uppercase tracking-widest text-m-gold">Now Cooking</p>
          <p class="text-xs font-bold font-display">L'Art du Braisage</p>
        </div>
      </div>
    </section>

    <!-- 3D Feature Section -->
    <section class="py-32 relative" :class="isDark ? 'bg-m-obsidian' : 'bg-m-linen'">
       <div class="container mx-auto px-6">
          <ThreeDSection />
       </div>
    </section>

    <!-- Marquee Text Flow -->
    <!-- <div class="py-12 bg-m-gold overflow-hidden whitespace-nowrap flex select-none">
       <div v-for="i in 10" :key="i" class="text-m-obsidian font-display font-black text-6xl md:text-8xl mx-8 animate-marquee">
          EL MAESTRO — L'EXCELLENCE — EL MAESTRO — L'EXCELLENCE — 
       </div>
    </div> -->

    <!-- Feature Grid -->
    <section class="py-48 px-6 md:px-12" :class="isDark ? 'bg-m-obsidian' : 'bg-m-linen'">
      <div class="container mx-auto grid md:grid-cols-3 gap-24">
        <div class="reveal-up flex flex-col items-center text-center group">
          <div class="w-24 h-24 rounded-[2rem] flex items-center justify-center text-m-gold mb-10 group-hover:rotate-12 transition-transform duration-500 border" :class="isDark ? 'bg-white/5 border-white/10' : 'bg-black/5 border-black/10'">
            <Utensils class="w-10 h-10" />
          </div>
          <h3 class="text-3xl font-bold mb-6 font-display">Ingrédients Nobles</h3>
          <p class="opacity-50 text-lg leading-relaxed">Nous sélectionnons uniquement le sommet de la production locale et internationale.</p>
        </div>
        
        <div class="reveal-up flex flex-col items-center text-center group" style="transition-delay: 200ms;">
          <div class="w-24 h-24 rounded-[2rem] flex items-center justify-center text-m-gold mb-10 group-hover:-rotate-12 transition-transform duration-500 border" :class="isDark ? 'bg-white/5 border-white/10' : 'bg-black/5 border-black/10'">
            <Star class="w-10 h-10" />
          </div>
          <h3 class="text-3xl font-bold mb-6 font-display">Expérience Royale</h3>
          <p class="opacity-50 text-lg leading-relaxed">Un service orchestré au millimètre pour une satisfaction qui dépasse les 5 étoiles.</p>
        </div>

        <div class="reveal-up flex flex-col items-center text-center group" style="transition-delay: 400ms;">
          <div class="w-24 h-24 rounded-[2rem] flex items-center justify-center text-m-gold mb-10 group-hover:rotate-12 transition-transform duration-500 border" :class="isDark ? 'bg-white/5 border-white/10' : 'bg-black/5 border-black/10'">
            <Truck class="w-10 h-10" />
          </div>
          <h3 class="text-3xl font-bold mb-6 font-display">Maestro at Home</h3>
          <p class="opacity-50 text-lg leading-relaxed">Le même prestige, livré chez vous dans un packaging thermo-isolé révolutionnaire.</p>
        </div>
      </div>
    </section>

    <!-- Selection du Chef Section -->
    <section class="py-48 relative overflow-hidden" :class="isDark ? 'bg-white/5' : 'bg-black/5'">
      <div class="absolute top-0 right-0 w-1/2 h-full bg-m-gold/10 blur-[200px] -z-10 translate-x-1/2"></div>
      <div class="container mx-auto px-6">
        <div class="flex flex-col md:flex-row justify-between items-end gap-8">
          <div class="reveal-up">
            <span class="text-m-gold font-bold tracking-[0.5em] uppercase text-sm block mb-6 px-4 border-l-2 border-m-gold">La Sélection</span>
            <h2 class="text-6xl md:text-8xl font-display font-bold leading-tight">Chefs-d'œuvre <br/> Culinaires</h2>
          </div>
        </div>

        <div class="grid md:grid-cols-3 gap-12 pt-5">
          <div v-for="(plate, index) in plates" :key="plate.id" class="reveal-up" :style="{ transitionDelay: `${index * 200}ms` }">
            <PlateCard :plate="plate" @added-to-cart="() => addToCart(plate)" />
          </div>
        </div>

        <div class="flex justify-center mt-16">
          <RouterLink to="/menu" class="reveal-up btn-gold px-12 py-5">
            VOIR LA GALERIE <ChevronRight class="w-5 h-5" />
          </RouterLink>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
.animate-marquee {
  animation: marquee 40s linear infinite;
}

@keyframes marquee {
  from { transform: translateX(0); }
  to { transform: translateX(-100%); }
}

@keyframes shine {
  to { background-position: 200% center; }
}

.toast-enter-active, .toast-leave-active { transition: all 0.5s cubic-bezier(0.68, -0.6, 0.32, 1.6); }
.toast-enter-from { opacity: 0; transform: translate(-50%, 50px) scale(0.9); }
.toast-leave-to { opacity: 0; transform: translate(-50%, 50px) scale(0.9); }
</style>
