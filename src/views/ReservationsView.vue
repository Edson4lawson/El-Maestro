<script setup>
import { ref } from 'vue'
import { Calendar, Users, Clock, Send, CheckCircle2, MapPin, Phone, Mail } from 'lucide-vue-next'
import gsap from 'gsap'
import { reservationService } from '../services/api'

const isSubmitted = ref(false)
const loading = ref(false)
const formData = ref({
  date: '',
  time: '',
  people: '2 personnes',
  name: '',
  email: '',
  message: ''
})

const handleSubmit = async () => {
  loading.value = true
  try {
    const data = {
      ...formData.value,
      people: formData.value.people // Already in the correct format
    }
    await reservationService.create(data)
    
    // Animation de succès
    isSubmitted.value = true
    setTimeout(() => {
      gsap.from('.success-content > *', {
        y: 20,
        opacity: 0,
        stagger: 0.2,
        duration: 0.8,
        ease: 'power3.out'
      })
    }, 100)
  } catch (error) {
    console.error('Error submitting reservation:', error)
    alert('Une erreur est survenue lors de la réservation. Veuillez réessayer.')
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="px-6 md:px-12 py-12">
    <div class="container mx-auto">
      <header class="mb-16 text-center max-w-2xl mx-auto">
        <h1 class="text-5xl md:text-7xl font-display font-bold mb-6">Réserver une <span class="text-m-gold">Table</span></h1>
        <p class="opacity-60">
          Vivez l'expérience El Maestro dans notre cadre somptueux. Nous vous conseillons de réserver au moins 24h à l'avance.
        </p>
      </header>

      <div class="grid lg:grid-cols-2 gap-16 items-start">
        <!-- Reservation Form -->
        <div class="glass-card p-10 relative overflow-hidden">
          <Transition name="fade" mode="out-in">
            <div v-if="!isSubmitted" key="form">
              <div class="grid md:grid-cols-2 gap-8 mb-8">
                <div class="space-y-4">
                  <label class="flex items-center gap-2 text-xs uppercase tracking-widest font-bold opacity-50">
                    <Calendar class="w-4 h-4 text-m-gold" /> Date
                  </label>
                  <input v-model="formData.date" type="date" class="w-full bg-white/5 border border-white/10 rounded-2xl p-4 outline-none focus:border-m-gold transition-colors">
                </div>
                <div class="space-y-4">
                  <label class="flex items-center gap-2 text-xs uppercase tracking-widest font-bold opacity-50">
                    <Clock class="w-4 h-4 text-m-gold" /> Heure
                  </label>
                  <input v-model="formData.time" type="time" class="w-full bg-white/5 border border-white/10 rounded-2xl p-4 outline-none focus:border-m-gold transition-colors">
                </div>
              </div>

              <div class="space-y-4 mb-8">
                <label class="flex items-center gap-2 text-xs uppercase tracking-widest font-bold opacity-50">
                  <Users class="w-4 h-4 text-m-gold" /> Nombre de Convives
                </label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                  <button 
                    v-for="p in ['2', '4', '6', '8+']" 
                    :key="p"
                    @click="formData.people = p + ' personnes'"
                    class="py-3 rounded-xl border transition-all font-bold"
                    :class="[formData.people.includes(p) ? 'bg-m-gold border-m-gold text-m-obsidian' : 'bg-white/5 border-white/10 opacity-60']"
                  >
                    {{ p }}
                  </button>
                </div>
              </div>

              <div class="space-y-8">
                <div class="space-y-2">
                  <input v-model="formData.name" type="text" placeholder="Votre nom complet" class="w-full bg-transparent border-b border-white/10 py-4 outline-none focus:border-m-gold transition-colors">
                </div>
                <div class="space-y-2">
                  <input v-model="formData.email" type="email" placeholder="Votre email" class="w-full bg-transparent border-b border-white/10 py-4 outline-none focus:border-m-gold transition-colors">
                </div>
                <div class="space-y-2">
                  <textarea v-model="formData.message" placeholder="Demande spéciale (Anniversaire, allergie...)" class="w-full bg-transparent border-b border-white/10 py-4 outline-none focus:border-m-gold transition-colors resize-none" rows="2"></textarea>
                </div>
              </div>

              <button @click="handleSubmit" class="btn-gold w-full mt-12 py-5 text-lg flex items-center justify-center gap-3">
                Confirmer la demande <Send class="w-5 h-5" />
              </button>
            </div>

            <div v-else key="success" class="success-content py-20 text-center">
              <div class="w-24 h-24 bg-green-500/20 text-green-500 rounded-full flex items-center justify-center mx-auto mb-8">
                <CheckCircle2 class="w-12 h-12" />
              </div>
              <h2 class="text-4xl font-display font-bold mb-4">Demande Envoyée</h2>
              <p class="opacity-60 mb-10 max-w-sm mx-auto">
                {{ formData.name }}, nous avons bien reçu votre demande pour le {{ formData.date }}. Notre maître d'hôtel vous contactera par email pour confirmer.
              </p>
              <button @click="isSubmitted = false" class="text-m-gold font-bold border-b border-m-gold/30 hover:border-m-gold transition-all">
                Faire une autre réservation
              </button>
            </div>
          </Transition>
        </div>

        <!-- Contact Info & Map -->
        <div class="space-y-12">
          <div class="space-y-8">
            <h3 class="text-3xl font-display font-bold">Informations <span class="text-m-gold">Pratiques</span></h3>
            
            <div class="flex items-start gap-6">
              <div class="w-12 h-12 rounded-2xl bg-m-gold/10 flex items-center justify-center text-m-gold shrink-0">
                <MapPin class="w-6 h-6" />
              </div>
              <div>
                <h4 class="font-bold mb-1">Localisation</h4>
                <p class="opacity-60 text-sm">Avenue de la Marina, Cotonou, Bénin</p>
              </div>
            </div>

            <div class="flex items-start gap-6">
              <div class="w-12 h-12 rounded-2xl bg-m-gold/10 flex items-center justify-center text-m-gold shrink-0">
                <Phone class="w-6 h-6" />
              </div>
              <div>
                <h4 class="font-bold mb-1">Téléphone</h4>
                <p class="opacity-60 text-sm">+229 01 02 03 04 05</p>
              </div>
            </div>

            <div class="flex items-start gap-6">
              <div class="w-12 h-12 rounded-2xl bg-m-gold/10 flex items-center justify-center text-m-gold shrink-0">
                <Mail class="w-6 h-6" />
              </div>
              <div>
                <h4 class="font-bold mb-1">Email</h4>
                <p class="opacity-60 text-sm">contact@elmaestro-restaurant.com</p>
              </div>
            </div>
          </div>

          <div class="glass-card h-80 relative overflow-hidden group">
            <div class="absolute inset-0 bg-cover opacity-60 group-hover:opacity-100 grayscale-[50%] group-hover:grayscale-0 transition-all duration-700" style="background-image: var(--map-url);"></div>
            <div class="absolute inset-0" :style="{ background: `linear-gradient(to top, var(--bg), transparent)` }"></div>
            <div class="absolute inset-0 flex items-center justify-center">
               <div class="w-10 h-10 bg-m-gold rounded-full flex items-center justify-center shadow-[0_0_30px_rgba(212,175,55,0.8)] animate-bounce">
                  <MapPin class="w-6 h-6 text-m-obsidian" />
               </div>
            </div>
            <p class="absolute bottom-6 left-6 text-xs font-bold uppercase tracking-widest text-m-gold">Cotonou, Bénin</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.5s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
