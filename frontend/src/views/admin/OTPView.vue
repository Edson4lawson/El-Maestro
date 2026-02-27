<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-m-obsidian to-m-gold/20">
    <div class="max-w-sm w-full mx-4">
      <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-8 border border-white/20">
        <div class="text-center mb-8">
          <Smartphone class="w-16 h-16 mx-auto mb-4 text-m-gold" />
          <h2 class="text-2xl font-bold text-gradient-gold mb-4">Vérification OTP</h2>
          <p class="text-white/70 text-sm mb-6">Entrez le code à 6 chiffres envoyé sur votre téléphone</p>
        </div>
        
        <form @submit.prevent="verifyOTP" class="space-y-6">
          <div class="grid grid-cols-6 gap-2 mb-6">
            <input 
              v-for="(digit, i) in otp" 
              :key="i" 
              v-model="otp[i]" 
              type="text" 
              maxlength="1" 
              pattern="[0-9]"
              @input="handleInput($event, i)"
              ref="otpInputs"
              class="w-full aspect-square text-center text-2xl font-bold bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-m-gold/50"
            />
          </div>
          <div class="text-center mt-6">
            <button 
              type="button" 
              @click="resendOTP"
              :disabled="resendLoading"
              class="text-m-gold hover:text-m-gold/80 text-sm underline"
            >
              <span v-if="!resendLoading">Renvoyer le code</span>
              <span v-else>Envoi en cours...</span>
            </button>
          </div>
          <div v-if="error" class="bg-red-500/20 text-red-100 p-4 rounded-lg mb-4">
            {{ error }}
          </div>
          
          <div v-if="timeLeft > 0" class="text-white/70 text-center mt-4">
            Code expire dans: {{ formatTime(timeLeft) }}
          </div>
          <button 
            type="submit" 
            :disabled="loading || otp.join('').length !== 6"
            class="w-full btn-gold py-3 font-bold flex items-center justify-center"
          >
            <div v-if="loading" class="animate-spin mr-2">⟳</div>
            <span v-else>Valider</span>
          </button>
        </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Smartphone, ArrowLeft } from 'lucide-vue-next'

const otp = ref(['', '', '', '', '', '', '', ''])
const loading = ref(false)
const error = ref('')
const timeLeft = ref(60)
const resendLoading = ref(false)

let countdownInterval = null

const handleInput = (event, index) => {
  const value = event.target.value
  
  // Accepter seulement les chiffres
  if (!/^\d$/.test(value)) {
    event.target.value = ''
    return
  }
  
  otp.value[index] = value
  
  // Passer au champ suivant automatiquement
  if (value && index < 5) {
    const nextInput = document.querySelectorAll('input')[index + 1]
    if (nextInput) {
      nextInput.focus()
    }
  }
}

const verifyOTP = async () => {
  if (loading.value || otp.value.join('').length !== 6) return
  
  loading.value = true
  error.value = ''
  
  try {
    const token = localStorage.getItem('admin_session_token')
    if (!token) {
      error.value = 'Session expirée. Veuillez vous reconnecter.'
      return
    }
    
    const response = await fetch('http://localhost:8000/backend/api/index.php?action=admin/verify-otp', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify({
        session_token: token,
        otp_code: otp.value.join('')
      })
    })
    
    const data = await response.json()
    
    if (data.success) {
      // Succès - rediriger vers dashboard
      localStorage.setItem('admin_authenticated', 'true')
      window.location.href = '/admin/dashboard'
    } else {
      error.value = data.message
    }
  } catch (err) {
    error.value = 'Erreur de vérification. Veuillez réessayer.'
    console.error('OTP verification error:', err)
  } finally {
    loading.value = false
  }
}

const resendOTP = async () => {
  if (resendLoading.value) return
  
  resendLoading.value = true
  
  try {
    const token = localStorage.getItem('admin_session_token')
    if (!token) {
      error.value = 'Session expirée.'
      return
    }
    
    const response = await fetch('http://localhost:8000/backend/api/index.php?action=admin/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify({
        email: 'admin@elmaestro.bj',
        password: 'admin123'
      })
    })
    
    const data = await response.json()
    
    if (data.success) {
      timeLeft.value = 60
      error.value = 'Nouveau code envoyé!'
    } else {
      error.value = data.message
    }
  } catch (err) {
    error.value = 'Erreur d\'envoi. Veuillez réessayer.'
  } finally {
    resendLoading.value = false
  }
}

const formatTime = (seconds) => {
  const mins = Math.floor(seconds / 60)
  const secs = seconds % 60
  return `${mins}:${secs.toString().padStart(2, '0')}`
}

onMounted(() => {
  // Démarrer le countdown
  countdownInterval = setInterval(() => {
    if (timeLeft.value > 0) {
      timeLeft.value--
    }
  }, 1000)
  
  // Focus premier champ
  setTimeout(() => {
    const firstInput = document.querySelector('input')
    if (firstInput) {
      firstInput.focus()
    }
  }, 100)
})

onUnmounted(() => {
  if (countdownInterval) {
    clearInterval(countdownInterval)
  }
})
</script>

<style scoped>
.btn-gold {
  @apply bg-m-gold text-m-obsidian font-bold py-3 px-6 rounded-lg hover:bg-m-gold/90 transition-all duration-300;
}

.text-gradient-gold {
  @apply bg-clip-text text-transparent bg-premium-gradient;
  background-size: 200% auto;
  animation: shine 3s linear infinite;
}

@keyframes shine {
  to { background-position: 200% center; }
}
</style>
