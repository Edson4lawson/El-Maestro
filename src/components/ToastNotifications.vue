<template>
  <Teleport to="body">
    <Transition name="toast" appear>
      <div
        v-if="toasts.length > 0"
        class="fixed top-4 right-4 z-[100] space-y-2"
      >
        <div
          v-for="toast in toasts"
          :key="toast.id"
          class="flex items-center gap-3 px-6 py-4 rounded-2xl shadow-2xl backdrop-blur-md min-w-[300px] max-w-md"
          :class="[
            toast.type === 'success' ? 'bg-green-500/90 text-white' :
            toast.type === 'error' ? 'bg-red-500/90 text-white' :
            toast.type === 'warning' ? 'bg-yellow-500/90 text-white' :
            'bg-m-gold text-m-obsidian'
          ]"
        >
          <!-- Icon -->
          <component 
            :is="getIcon(toast.type)" 
            class="w-5 h-5 flex-shrink-0" 
          />
          
          <!-- Content -->
          <div class="flex-1">
            <p class="font-bold">{{ toast.title }}</p>
            <p v-if="toast.message" class="text-sm opacity-90">
              {{ toast.message }}
            </p>
          </div>
          
          <!-- Close button -->
          <button
            @click="removeToast(toast.id)"
            class="ml-2 opacity-70 hover:opacity-100 transition-opacity"
          >
            <X class="w-4 h-4" />
          </button>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref } from 'vue'
import { CheckCircle2, AlertCircle, AlertTriangle, X, ShoppingCart } from 'lucide-vue-next'

const toasts = ref([])

let toastId = 0

const getIcon = (type) => {
  switch (type) {
    case 'success': return CheckCircle2
    case 'error': return AlertCircle
    case 'warning': return AlertTriangle
    case 'cart': return ShoppingCart
    default: return CheckCircle2
  }
}

const addToast = (options) => {
  const toast = {
    id: ++toastId,
    type: options.type || 'success',
    title: options.title || 'Succès',
    message: options.message || '',
    duration: options.duration || 3000
  }
  
  toasts.value.push(toast)
  
  // Auto remove after duration
  setTimeout(() => {
    removeToast(toast.id)
  }, toast.duration)
}

const removeToast = (id) => {
  const index = toasts.value.findIndex(toast => toast.id === id)
  if (index > -1) {
    toasts.value.splice(index, 1)
  }
}

// Expose functions globally
window.showToast = addToast

// Export for use in other components
defineExpose({
  addToast,
  removeToast
})
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(100%);
}

.toast-move {
  transition: transform 0.3s ease;
}
</style>
