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
    console.log('Tentative de connexion...')
    console.log('Email:', form.value.email)
    console.log('Password:', form.value.password ? '***' : 'vide')
    
    const response = await fetch('http://localhost:8080/api/index.php?action=admin/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        email: form.value.email,
        password: form.value.password
      })
    })
    
    console.log('Response status:', response.status)
    console.log('Response headers:', response.headers)
    
    const data = await response.json()
    console.log('API Response:', data)  // Debug
    
    if (data.success) {
      // Stocker le token et rediriger vers OTP
      localStorage.setItem('admin_session_token', data.session_token)
      window.location.href = '/admin/otp'
    } else {
      error.value = data.message
      console.log('API Error Response:', data)  // Debug
    }
  } catch (err) {
    console.error('Login error details:', err)
    
    // Si c'est une erreur de parsing JSON
    if (err.message && err.message.includes('JSON')) {
      error.value = 'Erreur de communication avec le serveur. Vérifiez que le backend est démarré.'
    } else if (err.response) {
      // Si le serveur a répondu avec une erreur
      error.value = `Erreur ${err.response.status}: ${err.response.statusText || 'Connexion refusée'}`
    } else {
      error.value = 'Erreur de connexion. Veuillez réessayer.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.btn-gold {
  background-color: rgb(212, 175, 55);
  color: rgb(28, 28, 28);
  font-weight: bold;
  padding: 0.75rem 1.5rem;
  border-radius: 0.5rem;
  transition: all 0.3s;
}

.btn-gold:hover {
  background-color: rgba(212, 175, 55, 0.9);
}

.text-gradient-gold {
  background-clip: text;
  color: transparent;
  background-image: linear-gradient(45deg, #d4af37, #fbbf24, #d4af37);
  background-size: 200% auto;
  animation: shine 3s linear infinite;
}

@keyframes shine {
  to { background-position: 200% center; }
}
</style>
