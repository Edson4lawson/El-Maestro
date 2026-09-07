import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useAuthStore = defineStore('auth', () => {
  // State
  const user = ref(null)
  const token = ref(localStorage.getItem('admin_token'))
  const isLoading = ref(false)
  const error = ref(null)

  // Getters
  const isAuthenticated = computed(() => !!token.value && !!user.value)
  const isAdmin = computed(() => user.value?.role === 'admin')

  // Actions
  const login = async (credentials) => {
    isLoading.value = true
    error.value = null

    try {
      const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8080/api'
      const response = await fetch(`${apiBase}/index.php?action=admin/login`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(credentials)
      })

      const data = await response.json()

      if (data.success) {
        token.value = data.session_token
        localStorage.setItem('admin_token', data.session_token)
        return { success: true }
      } else {
        error.value = data.message || 'Login failed'
        return { success: false, message: error.value }
      }
    } catch (err) {
      error.value = 'Network error. Please try again.'
      return { success: false, message: error.value }
    } finally {
      isLoading.value = false
    }
  }

  const verifyOTP = async (otp) => {
    isLoading.value = true
    error.value = null

    try {
      const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8080/api'
      const response = await fetch(`${apiBase}/index.php?action=admin/verify-otp`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token.value}`
        },
        body: JSON.stringify({ 
          session_token: token.value,
          otp_code: otp 
        })
      })

      const data = await response.json()

      if (data.success) {
        user.value = data.admin
        localStorage.setItem('admin_user', JSON.stringify(data.admin))
        return { success: true }
      } else {
        error.value = data.message || 'Invalid OTP'
        return { success: false, message: error.value }
      }
    } catch (err) {
      error.value = 'Network error. Please try again.'
      return { success: false, message: error.value }
    } finally {
      isLoading.value = false
    }
  }

  const logout = () => {
    user.value = null
    token.value = null
    localStorage.removeItem('admin_token')
    localStorage.removeItem('admin_user')
  }

  const initializeAuth = () => {
    const storedToken = localStorage.getItem('admin_token')
    const storedUser = localStorage.getItem('admin_user')

    if (storedToken && storedUser) {
      token.value = storedToken
      try {
        user.value = JSON.parse(storedUser)
      } catch (e) {
        console.error('Failed to parse stored user data')
        logout()
      }
    }
  }

  const refreshToken = async () => {
    if (!token.value) return false

    try {
      const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8080/api'
      const response = await fetch(`${apiBase}/index.php?action=admin/refresh-token`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token.value}`
        }
      })

      const data = await response.json()

      if (data.success) {
        token.value = data.token
        localStorage.setItem('admin_token', data.token)
        return true
      } else {
        logout()
        return false
      }
    } catch (err) {
      logout()
      return false
    }
  }

  // Initialize auth on store creation
  initializeAuth()

  return {
    // State
    user,
    token,
    isLoading,
    error,
    
    // Getters
    isAuthenticated,
    isAdmin,
    
    // Actions
    login,
    verifyOTP,
    logout,
    initializeAuth,
    refreshToken
  }
})
