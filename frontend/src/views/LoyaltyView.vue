<script setup>
import { ref } from 'vue'
import { Crown, Gift, Zap, Users, ChevronRight, QrCode, Star, Coffee, Utensils, Award, Search } from 'lucide-vue-next'
import { loyaltyService } from '../services/api'

const searchPhone = ref('')
const loading = ref(false)
const userPoints = ref(0)
const nextTierPoints = 5000
const progress = ref(0)

const checkLoyalty = async () => {
  if (!searchPhone.value) return
  loading.value = true
  try {
    const response = await loyaltyService.getUser(searchPhone.value)
    if (response.data) {
      userPoints.value = response.data.points
      progress.value = (userPoints.value / nextTierPoints) * 100
    }
  } catch (error) {
    console.error('User not found or error:', error)
    alert('Utilisateur introuvable. Passez votre première commande pour rejoindre le club !')
  } finally {
    loading.value = false
  }
}

const tiers = [
  { name: 'Bronze', points: '0', icon: Coffee, active: true },
  { name: 'Argent', points: '2500', icon: Utensils, active: false },
  { name: 'Or', points: '10000', icon: Crown, active: false },
  { name: 'Platine', points: '25000', icon: Award, active: false }
]

const rewards = [
  { 
    id: 1, 
    title: 'Apéritif Offert', 
    points: 1000, 
    description: 'Un cocktail maison ou un jus frais pour bien commencer.',
    icon: Gift,
    claimable: true
  },
  { 
    id: 2, 
    title: 'Réduction 15%', 
    points: 3500, 
    description: 'Valable sur toute votre prochaine commande.',
    icon: Zap,
    claimable: false
  },
  { 
    id: 3, 
    title: 'Plat Signature Gratuit', 
    points: 8000, 
    description: 'Au choix : Poulet Braisé ou Poisson Grillé Maestro.',
    icon: Utensils,
    claimable: false
  }
]

const history = ref([])
</script>

<template>
  <div class="px-6 md:px-12 py-12">
    <div class="container mx-auto">
      <header class="mb-16 text-center">
        <h1 class="text-5xl md:text-7xl font-display font-bold mb-6">Fidélité <span class="text-m-gold">Maestro</span></h1>
        <p class="opacity-60 max-w-2xl mx-auto mb-10">
          Rejoignez le cercle fermé des passionnés d'El Maestro. Chaque commande vous rapproche de privilèges royaux.
        </p>

        <!-- Phone Search -->
        <div class="max-w-md mx-auto relative group">
          <input 
            v-model="searchPhone"
            type="text" 
            placeholder="Entrez votre numéro de téléphone..."
            class="w-full bg-white/5 border border-white/10 rounded-full py-4 pl-12 pr-6 focus:border-m-gold outline-none transition-all text-center font-bold"
            @keyup.enter="checkLoyalty"
          />
          <Search class="absolute left-6 top-1/2 -translate-y-1/2 w-5 h-5 opacity-40 group-focus-within:text-m-gold group-focus-within:opacity-100 transition-all" />
          <button @click="checkLoyalty" class="absolute right-2 top-1/2 -translate-y-1/2 bg-m-gold text-m-obsidian px-6 py-2 rounded-full font-bold text-sm hover:scale-105 transition-transform">
            {{ loading ? '...' : 'Vérifier' }}
          </button>
        </div>
      </header>

      <div class="grid lg:grid-cols-3 gap-8">
        <!-- Left: Status Card -->
        <div class="lg:col-span-2 space-y-8">
          <div class="glass-card p-10 relative overflow-hidden bg-gradient-to-br from-dark-gold/20 to-transparent">
            <!-- Decorative Background -->
            <Crown class="absolute -right-10 -bottom-10 w-60 h-60 opacity-5 -rotate-12" />
            
            <div class="flex flex-col md:flex-row justify-between items-center gap-10">
              <div class="relative w-48 h-48 flex items-center justify-center">
                <!-- Circular Progress SVG -->
                <svg class="w-full h-full -rotate-90">
                  <circle cx="96" cy="96" r="88" stroke="currentColor" stroke-width="8" fill="transparent" class="text-white/5" />
                  <circle cx="96" cy="96" r="88" stroke="currentColor" stroke-width="8" fill="transparent" class="text-m-gold" :stroke-dasharray="2 * Math.PI * 88" :stroke-dashoffset="2 * Math.PI * 88 * (1 - progress/100)" stroke-linecap="round" />
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                  <span class="text-4xl font-display font-bold">{{ userPoints }}</span>
                  <span class="text-[10px] uppercase tracking-widest opacity-50 font-bold">Points actuels</span>
                </div>
              </div>

              <div class="flex-grow space-y-6">
                <div>
                  <h2 class="text-3xl font-display font-bold mb-2">Statut <span class="text-m-gold">BRONZE</span></h2>
                  <p class="opacity-60 text-sm">Encore <span class="font-bold text-white">{{ nextTierPoints - userPoints }} points</span> pour passer au statut <span class="text-m-gold font-bold">ARGENT</span></p>
                </div>
                
                <div class="grid grid-cols-4 gap-2">
                  <div v-for="tier in tiers" :key="tier.name" class="flex flex-col items-center gap-2">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 transition-all"
                      :class="[tier.active ? 'border-m-gold bg-m-gold text-m-obsidian scale-110' : 'border-white/10 opacity-30']"
                    >
                      <component :is="tier.icon" class="w-5 h-5" />
                    </div>
                    <span class="text-[8px] uppercase tracking-tighter font-bold" :class="tier.active ? 'text-m-gold' : 'opacity-30'">{{ tier.name }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Rewards Grid -->
          <div>
            <h3 class="text-2xl font-display font-bold mb-8">Récompenses Disponibles</h3>
            <div class="grid md:grid-cols-3 gap-6">
              <div v-for="reward in rewards" :key="reward.id" class="glass-card group p-6 flex flex-col items-center text-center hover:border-m-gold/50 transition-all">
                <div class="w-16 h-16 rounded-2xl bg-white/5 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                  <component :is="reward.icon" class="w-8 h-8 text-m-gold" />
                </div>
                <h4 class="font-bold mb-2">{{ reward.title }}</h4>
                <p class="text-xs opacity-50 mb-6 flex-grow">{{ reward.description }}</p>
                <button 
                  class="w-full py-2 rounded-full text-xs font-bold transition-all"
                  :class="[reward.claimable ? 'btn-gold' : 'bg-white/5 border border-white/10 opacity-50 cursor-not-allowed']"
                >
                  {{ reward.claimable ? 'Utiliser Points' : `${reward.points} points requis` }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Right: Activity & Refer -->
        <div class="space-y-8">
          <!-- Parrainage Card -->
          <div class="glass-card p-8 text-center border-2 border-m-gold/10">
            <div class="w-16 h-16 bg-m-gold/20 text-m-gold rounded-full flex items-center justify-center mx-auto mb-6">
              <Users class="w-8 h-8" />
            </div>
            <h4 class="text-xl font-display font-bold mb-2">Parrainez un ami</h4>
            <p class="text-sm opacity-60 mb-8">Gagnez 1000 points dès que votre ami passe sa première commande.</p>
            
            <div class="bg-white/5 p-6 rounded-2xl mb-6 relative group overflow-hidden">
              <QrCode class="w-32 h-32 mx-auto mb-4 opacity-80" />
              <p class="text-[10px] font-mono tracking-widest opacity-40">MAESTRO-REF-8921</p>
              <div class="absolute inset-0 bg-m-obsidian/80 backdrop-blur-sm opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity cursor-pointer">
                <span class="text-m-gold font-bold">Copier le lien</span>
              </div>
            </div>
          </div>

          <!-- History -->
          <div class="glass-card p-8">
            <h4 class="text-lg font-display font-bold mb-6">Historique des Points</h4>
            <div class="space-y-6">
              <div v-for="item in history" :key="item.id" class="flex justify-between items-center text-sm">
                <div>
                  <p class="font-bold">{{ item.action }}</p>
                  <p class="text-[10px] opacity-40 uppercase">{{ item.date }}</p>
                </div>
                <span class="font-bold" :class="item.points.startsWith('+') ? 'text-green-500' : 'text-red-500'">
                  {{ item.points }}
                </span>
              </div>
            </div>
            <button class="w-full mt-8 text-xs font-bold opacity-40 hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
              Voir tout l'historique <ChevronRight class="w-3 h-3" />
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Any specific component styles */
</style>
