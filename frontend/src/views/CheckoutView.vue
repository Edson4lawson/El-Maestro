<script setup>
import { ref, computed } from 'vue'
import { Truck, MapPin, CreditCard, Wallet, CheckCircle2, FileText, Smartphone } from 'lucide-vue-next'
import { useCartStore } from '../stores/cart'
import { orderService } from '../services/api'

const cartStore = useCartStore()
const step = ref(1) // 1: Order, 2: Payment, 3: Tracking
const trackingNumber = ref('')

const formData = ref({
  name: '',
  phone: '',
  address: '',
  paymentMethod: 'mtn'
})

const deliveryFee = 1000
const total = computed(() => cartStore.totalPrice + deliveryFee)

const processOrder = () => {
  step.value = 2
}

const processPayment = async () => {
  try {
    const orderData = {
      ...formData.value,
      total: total.value,
      items: cartStore.items.map(item => ({
        id: item.id,
        quantity: item.quantity,
        price: item.price
      }))
    }
    const response = await orderService.create(orderData)
    trackingNumber.value = response.data.tracking
    step.value = 3
    cartStore.clearCart() // Assuming a clearCart action exists
  } catch (error) {
    console.error('Error processing payment:', error)
  }
}

// Delivery Tracking Mock Data
const deliveryProgress = ref(65) // percentage
const deliveryStatus = ref('En route')
const deliveryLover = ref('Ismaël')
</script>

<template>
  <div class="px-6 md:px-12 py-12">
    <div class="container mx-auto max-w-6xl">
      
      <!-- Stepper -->
      <div class="flex justify-between items-center mb-16 max-w-2xl mx-auto relative">
        <div class="absolute top-1/2 left-0 right-0 h-[2px] bg-white/5 -translate-y-1/2 -z-10"></div>
        <div class="absolute top-1/2 left-0 h-[2px] bg-gradient-to-r from-m-gold to-yellow-500 -translate-y-1/2 -z-10 transition-all duration-700 shadow-[0_0_15px_rgba(255,215,0,0.5)]" :style="{ width: step === 1 ? '0%' : step === 2 ? '50%' : '100%' }"></div>
        
        <div v-for="i in 3" :key="i" 
          class="w-12 h-12 rounded-full flex items-center justify-center font-bold transition-all duration-500 shadow-xl"
          :class="[step >= i ? 'bg-m-gold text-m-obsidian scale-110 shadow-m-gold/30' : 'bg-m-obsidian border-2 border-white/10 text-white/20']"
        >
          {{ i }}
        </div>
      </div>

      <div class="grid lg:grid-cols-3 gap-12">
        
        <!-- Main Form Area -->
        <div class="lg:col-span-2">
          
          <!-- Step 1: Delivery Details -->
          <div v-if="step === 1" class="glass-card p-8">
            <h2 class="text-3xl font-display font-bold mb-8 flex items-center gap-3">
              <Truck class="text-m-gold" /> Détails de Livraison
            </h2>
            
            <div class="grid md:grid-cols-2 gap-6">
              <div class="space-y-2">
                <label class="text-xs uppercase tracking-widest opacity-50">Nom complet</label>
                <input v-model="formData.name" type="text" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 focus:border-m-gold outline-none" placeholder="Jean Dupont">
              </div>
              <div class="space-y-2">
                <label class="text-xs uppercase tracking-widest opacity-50">Téléphone</label>
                <input v-model="formData.phone" type="tel" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 focus:border-m-gold outline-none" placeholder="+229 01 XX XX XX XX">
              </div>
              <div class="md:col-span-2 space-y-2">
                <label class="text-xs uppercase tracking-widest opacity-50">Adresse de Livraison (Bénin)</label>
                <textarea v-model="formData.address" rows="3" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 focus:border-m-gold outline-none" placeholder="Quartier, Rue, Maison..."></textarea>
              </div>
            </div>

            <button @click="processOrder" class="btn-gold w-full mt-10 py-4 text-lg">
              Passer au paiement
            </button>
          </div>

          <!-- Step 2: Payment Choices -->
          <div v-if="step === 2" class="glass-card p-8">
            <h2 class="text-3xl font-display font-bold mb-8 flex items-center gap-3">
              <CreditCard class="text-m-gold" /> Mode de Paiement
            </h2>

            <div class="grid md:grid-cols-2 gap-4 mb-10">
              <label class="relative cursor-pointer group">
                <input type="radio" v-model="formData.paymentMethod" value="mtn" class="peer sr-only">
                <div class="p-6 rounded-2xl border-2 border-white/5 peer-checked:border-m-gold transition-all flex items-center gap-4 hover:bg-white/5">
                  <div class="w-12 h-12 bg-yellow-400 rounded-lg flex items-center justify-center font-bold text-black text-xs">MTN</div>
                  <div>
                    <p class="font-bold font-display">MTN Mobile Money</p>
                    <p class="text-xs opacity-50">Paiement instantané BJ</p>
                  </div>
                </div>
              </label>

              <label class="relative cursor-pointer group">
                <input type="radio" v-model="formData.paymentMethod" value="moov" class="peer sr-only">
                <div class="p-6 rounded-2xl border-2 border-white/5 peer-checked:border-m-gold transition-all flex items-center gap-4 hover:bg-white/5">
                  <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center font-bold text-white text-xs">MOOV</div>
                  <div>
                    <p class="font-bold font-display">Moov Money</p>
                    <p class="text-xs opacity-50">Paiement sécurisé BJ</p>
                  </div>
                </div>
              </label>

              <label class="relative cursor-pointer group">
                <input type="radio" v-model="formData.paymentMethod" value="card" class="peer sr-only">
                <div class="p-6 rounded-2xl border-2 border-white/5 peer-checked:border-m-gold transition-all flex items-center gap-4 hover:bg-white/5">
                  <div class="w-12 h-12 bg-m-gold rounded-lg flex items-center justify-center text-m-obsidian">
                    <CreditCard class="w-6 h-6" />
                  </div>
                  <div>
                    <p class="font-bold font-display">Carte Bancaire</p>
                    <p class="text-xs opacity-50">Visa, Mastercard</p>
                  </div>
                </div>
              </label>

              <label class="relative cursor-pointer group">
                <input type="radio" v-model="formData.paymentMethod" value="cash" class="peer sr-only">
                <div class="p-6 rounded-2xl border-2 border-white/5 peer-checked:border-m-gold transition-all flex items-center gap-4 hover:bg-white/5">
                  <div class="w-12 h-12 bg-green-600 rounded-lg flex items-center justify-center text-white">
                    <Wallet class="w-6 h-6" />
                  </div>
                  <div>
                    <p class="font-bold font-display">Paiement à la livraison</p>
                    <p class="text-xs opacity-50">Espèces à la réception</p>
                  </div>
                </div>
              </label>
            </div>

            <button @click="processPayment" class="btn-gold w-full py-4 text-lg">
              Confirmer et Payer ({{ total.toLocaleString() }} FCFA)
            </button>
          </div>

          <!-- Step 3: Success & Tracking -->
          <div v-if="step === 3" class="space-y-8">
            <div class="glass-card p-12 text-center">
              <div class="w-20 h-20 bg-green-500/20 text-green-500 rounded-full flex items-center justify-center mx-auto mb-6">
                <CheckCircle2 class="w-12 h-12" />
              </div>
              <h2 class="text-4xl font-display font-bold mb-4">Commande Confirmée !</h2>
              <p class="opacity-60 mb-8">Votre festin est en cours de préparation. Une facture PDF a été envoyée sur votre WhatsApp.</p>
              
              <div class="flex flex-wrap justify-center gap-4">
                <button class="bg-white/10 hover:bg-white/20 px-6 py-3 rounded-full flex items-center gap-2 font-bold transition-all">
                  <FileText class="w-4 h-4 text-m-gold" /> Télécharger la Facture
                </button>
                <button class="bg-white/10 hover:bg-white/20 px-6 py-3 rounded-full flex items-center gap-2 font-bold transition-all">
                  <Smartphone class="w-4 h-4 text-m-gold" /> Notifications SMS actrivées
                </button>
              </div>
            </div>

            <!-- Real-time Tracking Mockup -->
            <div class="glass-card p-8">
              <div class="flex justify-between items-center mb-8">
                <h3 class="text-2xl font-display font-bold">Suivi de Livraison</h3>
                <span class="bg-m-gold text-m-obsidian px-4 py-1 rounded-full text-xs font-bold uppercase">{{ deliveryStatus }}</span>
              </div>

              <div class="flex items-center gap-4 mb-8">
                <div class="w-16 h-16 rounded-full overflow-hidden bg-white/10 border-2 border-m-gold">
                  <img src="https://i.pravatar.cc/150?u=ismael" class="w-full h-full object-cover">
                </div>
                <div>
                  <p class="font-bold uppercase tracking-tighter">{{ deliveryLover }}, votre livreur</p>
                  <p class="text-sm opacity-50">Arrivée estimée : 15 mins</p>
                </div>
                <button class="ml-auto bg-green-600 p-3 rounded-full hover:scale-110 transition-transform">
                  <Smartphone class="w-5 h-5" />
                </button>
              </div>

              <div class="h-48 bg-white/5 rounded-2xl relative overflow-hidden border border-white/10 flex items-center justify-center">
                 <MapPin class="w-8 h-8 text-m-gold absolute left-[65%] top-[40%] animate-bounce" />
                 <div class="w-full h-full bg-cover opacity-60 grayscale-[50%]" style="background-image: var(--map-url);"></div>
                 <div class="absolute inset-0" :style="{ background: `linear-gradient(to top, var(--bg), transparent)` }"></div>
                 <p class="absolute bottom-4 left-4 text-xs font-bold uppercase tracking-widest text-m-gold">Localisation en temps réel</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Order Summary (Sticky) -->
        <div class="lg:col-span-1">
          <div class="glass-card p-6 sticky top-32">
            <h3 class="text-xl font-display font-bold mb-6 border-b border-white/10 pb-4">Résumé du Panier</h3>
            
            <div class="space-y-4 mb-8 max-h-60 overflow-y-auto pr-2">
              <div v-for="item in cartStore.items" :key="item.id" class="flex justify-between items-center gap-4">
                <div class="flex items-center gap-3">
                  <div class="w-12 h-12 bg-white/5 rounded overflow-hidden">
                    <img :src="item.image" class="w-full h-full object-cover">
                  </div>
                  <div>
                    <p class="text-sm font-bold">{{ item.name }}</p>
                    <p class="text-xs opacity-50">x{{ item.quantity }}</p>
                  </div>
                </div>
                <p class="text-sm font-bold">{{ item.price }} FCFA</p>
              </div>
              <p v-if="cartStore.items.length === 0" class="text-center py-6 opacity-30 text-sm">Votre panier est vide</p>
            </div>

            <div class="border-t border-white/10 pt-6 space-y-3">
              <div class="flex justify-between text-sm">
                <span class="opacity-50">Sous-total</span>
                <span>{{ cartStore.totalPrice.toLocaleString() }} FCFA</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="opacity-50">Livraison</span>
                <span>{{ deliveryFee.toLocaleString() }} FCFA</span>
              </div>
              <div class="flex justify-between text-xl font-display font-bold pt-4">
                <span>Total</span>
                <span class="text-gradient-gold text-2xl">{{ total.toLocaleString() }} FCFA</span>
              </div>
            </div>

            <div v-if="step === 1" class="mt-8 p-4 bg-m-gold/5 rounded-xl border border-m-gold/20 flex items-center gap-3">
               <div class="w-10 h-10 rounded-full bg-m-gold/20 flex items-center justify-center text-m-gold">
                 <Star class="w-5 h-5 fill-m-gold" />
               </div>
               <div>
                 <p class="text-[10px] uppercase font-bold tracking-widest">Points Fidélité</p>
                 <p class="text-xs font-bold">+250 points avec cette commande</p>
               </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>
