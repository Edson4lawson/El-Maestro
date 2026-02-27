<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-m-obsidian to-m-gold/20">
    <div class="max-w-md w-full mx-4">
      <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-8 border border-white/20">
        <div class="text-center mb-8">
          <h1 class="text-3xl font-bold text-gradient-gold mb-4">Admin EL MAESTRO</h1>
          <p class="text-white/70 text-sm">Connexion sécurisée avec double authentification</p>
        </div>
        
        <form @submit.prevent="handleLogin" class="space-y-6">
          <div v-if="error" class="bg-red-500/20 text-red-100 p-4 rounded-lg mb-4">
            {{ error }}
          </div>
          
          <div>
            <label class="block text-sm font-medium mb-2 text-white">Email</label>
            <input 
              v-model="form.email" 
              type="email" 
              required 
              placeholder="admin@elmaestro.bj"
              class="w-full px-4 py-3 rounded-lg bg-white/10 border border-white/20 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-m-gold/50"
            />
          </div>
          
          <div>
            <label class="block text-sm font-medium mb-2 text-white">Mot de passe</label>
            <input 
              v-model="form.password" 
              type="password" 
              required
              placeholder="••••••••"
              class="w-full px-4 py-3 rounded-lg bg-white/10 border border-white/20 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-m-gold/50"
            />
          </div>
          
          <button 
            type="submit" 
            :disabled="loading" 
            class="w-full btn-gold py-3 font-bold flex items-center justify-center"
          >
            <div v-if="loading" class="animate-spin mr-2">⟳</div>
            <span v-else>Se connecter</span>
          </button>
        </form>
        
        <div class="text-center mt-6 text-white/60 text-sm">
          <p>Admin par défaut : admin@elmaestro.bj</p>
          <p>Mot de passe : admin123</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Shield, Mail, Lock } from 'lucide-vue-next'

const form = ref({
  email: '',
  password: ''
})

const loading = ref(false)
const error = ref('')

const handleLogin = async () => {
  if (loading.value) return
  
  loading.value = true
  error.value = ''
  
  try {
    const response = await fetch('http://localhost:8000/backend/api/index.php?action=admin/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        email: form.value.email,
        password: form.value.password
      })
    })
    
    const data = await response.json()
    
    if (data.success) {
      // Stocker le token et rediriger vers OTP
      localStorage.setItem('admin_session_token', data.session_token)
      window.location.href = '/admin/otp'
    } else {
      error.value = data.message
    }
  } catch (err) {
    error.value = 'Erreur de connexion. Veuillez réessayer.'
    console.error('Login error:', err)
  } finally {
    loading.value = false
  }
}
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
