<template>
  <div class="otp-view">
    <!-- Ambient Background Elements -->
    <div class="ambient-grid" aria-hidden="true"></div>
    <div class="ambient-orb ambient-orb--1" aria-hidden="true"></div>
    <div class="ambient-orb ambient-orb--2" aria-hidden="true"></div>

    <div class="otp-container">
      <div class="otp-card">
        <!-- Header -->
        <div class="otp-header">
          <div class="otp-icon-wrap">
            <Smartphone class="otp-icon" />
          </div>
          <h1 class="otp-title">Vérification Sécurisée</h1>
          <p class="otp-subtitle">Entrez le code à 6 chiffres envoyé sur votre téléphone pour confirmer votre identité.</p>
        </div>

        <!-- Form -->
        <form @submit.prevent="verifyOTP" class="otp-form">
          <div class="otp-inputs-grid">
            <input 
              v-for="(digit, i) in otp" 
              :key="i" 
              v-model="otp[i]" 
              type="text" 
              maxlength="1" 
              pattern="[0-9]"
              inputmode="numeric"
              @input="handleInput($event, i)"
              @keydown.delete="handleDelete($event, i)"
              @paste="handlePaste"
              ref="otpInputs"
              class="otp-input"
              :class="{ 'otp-input--filled': otp[i], 'otp-input--error': error }"
            />
          </div>

          <!-- Timer & Resend -->
          <div class="otp-status">
            <div v-if="timeLeft > 0" class="otp-timer">
              <Clock class="timer-icon" />
              <span>Expire dans : <strong>{{ formatTime(timeLeft) }}</strong></span>
            </div>
            <div v-else class="otp-timer otp-timer--expired">
              <AlertCircle class="timer-icon" />
              <span>Code expiré</span>
            </div>

            <button 
              type="button" 
              @click="resendOTP"
              :disabled="resendLoading || timeLeft > 0"
              class="resend-btn"
              :class="{ 'resend-btn--disabled': timeLeft > 0 }"
            >
              <span v-if="!resendLoading">Renvoyer le code</span>
              <span v-else class="loader-inline">
                <svg class="spin" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="32" stroke-dashoffset="12" stroke-linecap="round"/></svg>
                Envoi...
              </span>
            </button>
          </div>

          <!-- Error Message -->
          <transition name="slide-up">
            <div v-if="error" class="error-box">
              <AlertTriangle class="error-icon" />
              <span>{{ error }}</span>
            </div>
          </transition>

          <!-- Submit Button -->
          <button 
            type="submit" 
            :disabled="loading || otp.join('').length !== 6"
            class="submit-btn"
          >
            <span v-if="!loading" class="btn-content">
              Valider l'accès
              <ChevronRight class="btn-arrow" />
            </span>
            <span v-else class="btn-loader">
              <svg class="spin" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="32" stroke-dashoffset="12" stroke-linecap="round"/></svg>
              Vérification...
            </span>
          </button>
        </form>

        <div class="otp-footer">
          <button @click="router.push('/admin/login')" class="back-link">
            <ArrowLeft class="back-icon" />
            Retour à la connexion
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { Smartphone, Clock, AlertCircle, AlertTriangle, ChevronRight, ArrowLeft } from 'lucide-vue-next'
import { useAuthStore } from '../../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const otp = ref(['', '', '', '', '', ''])
const otpInputs = ref([])
const loading = ref(false)
const error = ref('')
const timeLeft = ref(300) // 5 minutes
const resendLoading = ref(false)

let countdownInterval = null

const handleInput = (event, index) => {
  const value = event.target.value
  if (!/^\d$/.test(value)) {
    event.target.value = ''
    return
  }
  otp.value[index] = value
  error.value = '' // Clear error on input
  
  if (value && index < 5) {
    otpInputs.value[index + 1]?.focus()
  }
}

const handleDelete = (event, index) => {
  if (!otp.value[index] && index > 0) {
    otpInputs.value[index - 1]?.focus()
  }
}

const handlePaste = (event) => {
  event.preventDefault()
  const data = event.clipboardData.getData('text').slice(0, 6)
  if (!/^\d+$/.test(data)) return

  data.split('').forEach((char, i) => {
    if (i < 6) otp.value[i] = char
  })
  
  const lastIndex = Math.min(data.length, 5)
  otpInputs.value[lastIndex]?.focus()
}

const verifyOTP = async () => {
  const code = otp.value.join('')
  if (loading.value || code.length !== 6) return
  
  loading.value = true
  error.value = ''
  
  try {
    const result = await authStore.verifyOTP(code)
    if (result.success) {
      router.push('/admin/dashboard')
    } else {
      error.value = result.message || 'Code OTP incorrect ou expiré'
    }
  } catch (err) {
    error.value = 'Erreur de communication avec le serveur'
    console.error('OTP verification error:', err)
  } finally {
    loading.value = false
  }
}

const resendOTP = async () => {
  if (resendLoading.value || timeLeft.value > 0) return
  resendLoading.value = true
  error.value = ''
  
  try {
    // In a real app, call a resend OTP endpoint
    // For now we simulate success or guide user
    error.value = 'Fonctionnalité de renvoi limitée. Veuillez vous reconnecter.'
  } catch (err) {
    error.value = 'Erreur lors du renvoi.'
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
  countdownInterval = setInterval(() => {
    if (timeLeft.value > 0) timeLeft.value--
  }, 1000)
  
  // Initial focus
  setTimeout(() => otpInputs.value[0]?.focus(), 500)
})

onUnmounted(() => {
  if (countdownInterval) clearInterval(countdownInterval)
})
</script>

<style scoped>
@import '../../styles/admin-tokens.css';

.otp-view {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg);
  position: relative;
  overflow: hidden;
  font-family: var(--font-sans);
}

/* Ambient Elements */
.ambient-grid {
  position: absolute; inset: 0; pointer-events: none;
  background-image: 
    linear-gradient(rgba(212,175,55,0.03) 1px, transparent 1px),
    linear-gradient(90deg, rgba(212,175,55,0.03) 1px, transparent 1px);
  background-size: 60px 60px;
  mask-image: radial-gradient(circle at center, black 10%, transparent 80%);
}

.ambient-orb {
  position: absolute; border-radius: 50%; filter: blur(120px); pointer-events: none; opacity: 0.4;
}
.ambient-orb--1 { width: 500px; height: 500px; top: -100px; left: -100px; background: radial-gradient(circle, rgba(212,175,55,0.1) 0%, transparent 70%); }
.ambient-orb--2 { width: 400px; height: 400px; bottom: -50px; right: -50px; background: radial-gradient(circle, rgba(99,102,241,0.08) 0%, transparent 70%); }

.otp-container {
  width: 100%;
  max-width: 480px;
  padding: var(--s6);
  position: relative;
  z-index: 10;
}

.otp-card {
  background: var(--card);
  backdrop-blur: 20px;
  border: 1px solid var(--border);
  border-radius: var(--r-xl);
  padding: var(--s10) var(--s8);
  box-shadow: 0 40px 100px rgba(0,0,0,0.6);
  text-align: center;
}

/* Header */
.otp-header { margin-bottom: var(--s8); }
.otp-icon-wrap {
  width: 64px; height: 64px;
  margin: 0 auto var(--s6);
  background: rgba(212,175,55,0.1);
  border: 1px solid rgba(212,175,55,0.2);
  border-radius: var(--r-lg);
  display: flex; align-items: center; justify-content: center;
  color: var(--gold);
}
.otp-icon { width: 32px; height: 32px; }

.otp-title {
  font-family: var(--font-display);
  font-size: 28px;
  font-weight: 700;
  color: var(--text);
  margin-bottom: var(--s3);
  letter-spacing: -0.02em;
}

.otp-subtitle {
  font-size: 14px;
  color: var(--text-3);
  line-height: 1.6;
  max-width: 320px;
  margin: 0 auto;
}

/* Form */
.otp-form { display: flex; flex-direction: column; gap: var(--s8); }

.otp-inputs-grid {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: var(--s3);
}

.otp-input {
  width: 100%;
  aspect-ratio: 1;
  background: rgba(255,255,255,0.03);
  border: 1px solid var(--border);
  border-radius: var(--r-md);
  color: var(--text);
  font-size: 24px;
  font-weight: 700;
  text-align: center;
  outline: none;
  transition: all var(--t);
}

.otp-input:focus {
  background: rgba(212,175,55,0.05);
  border-color: var(--gold);
  box-shadow: 0 0 20px rgba(212,175,55,0.15);
  transform: translateY(-2px);
}

.otp-input--filled {
  border-color: rgba(212,175,55,0.4);
  background: rgba(212,175,55,0.02);
}

.otp-input--error {
  border-color: rgba(239, 68, 68, 0.5);
  background: rgba(239, 68, 68, 0.05);
}

/* Status & Resend */
.otp-status {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: var(--s4);
  background: rgba(255,255,255,0.02);
  border-radius: var(--r-lg);
}

.otp-timer {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12px;
  color: var(--text-3);
}
.timer-icon { width: 14px; height: 14px; color: var(--gold); }
.otp-timer strong { color: var(--text); font-weight: 600; }

.otp-timer--expired { color: #ef4444; }
.otp-timer--expired .timer-icon { color: #ef4444; }

.resend-btn {
  background: transparent;
  border: none;
  color: var(--gold);
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  padding: 4px 8px;
  border-radius: var(--r);
  transition: all var(--t);
}
.resend-btn:hover:not(.resend-btn--disabled) {
  background: rgba(212,175,55,0.1);
  color: #f5d17a;
}
.resend-btn--disabled {
  opacity: 0.5;
  cursor: not-allowed;
  color: var(--text-4);
}

/* Error Box */
.error-box {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 16px;
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.2);
  border-radius: var(--r-md);
  color: #ef4444;
  font-size: 13px;
}
.error-icon { width: 16px; height: 16px; flex-shrink: 0; }

/* Submit Button */
.submit-btn {
  width: 100%;
  height: 54px;
  background: linear-gradient(135deg, #D4AF37 0%, #B8860B 100%);
  border: none;
  border-radius: var(--r-lg);
  color: #0A0A0A;
  font-weight: 700;
  font-size: 16px;
  cursor: pointer;
  transition: all var(--t);
  box-shadow: 0 10px 30px rgba(212,175,55,0.3);
  display: flex;
  align-items: center;
  justify-content: center;
}

.submit-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 15px 40px rgba(212,175,55,0.45);
}

.submit-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  filter: grayscale(0.5);
  box-shadow: none;
}

.btn-content { display: flex; align-items: center; gap: 8px; }
.btn-arrow { width: 18px; height: 18px; }

.btn-loader, .loader-inline { display: flex; align-items: center; gap: 8px; }
.spin {
  width: 20px; height: 20px;
  animation: spin 1s linear infinite;
}

@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

/* Footer */
.otp-footer { margin-top: var(--s6); }
.back-link {
  background: transparent;
  border: none;
  color: var(--text-4);
  font-size: 13px;
  display: flex;
  align-items: center;
  gap: 8px;
  margin: 0 auto;
  cursor: pointer;
  transition: color var(--t);
}
.back-link:hover { color: var(--text-2); }
.back-icon { width: 14px; height: 14px; }

/* Animations */
.slide-up-enter-active { animation: slide-up 0.3s ease-out; }
@keyframes slide-up { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

@media (max-width: 480px) {
  .otp-container { padding: var(--s4); }
  .otp-card { padding: var(--s8) var(--s4); }
  .otp-inputs-grid { gap: var(--s2); }
  .otp-input { font-size: 20px; }
}
</style>
