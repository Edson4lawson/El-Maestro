import { defineStore } from 'pinia'
import { ref, computed, watch } from 'vue'

export const useCartStore = defineStore('cart', () => {
  // Load initial state from localStorage
  const items = ref(JSON.parse(localStorage.getItem('maestro_cart') || '[]'))

  // Save to localStorage whenever items change
  watch(items, (newItems) => {
    localStorage.setItem('maestro_cart', JSON.stringify(newItems))
  }, { deep: true })

  const totalItems = computed(() => items.value.reduce((acc, item) => acc + item.quantity, 0))
  
  const totalPrice = computed(() => {
    return items.value.reduce((acc, item) => {
      // Robust price parsing for both string and number
      let price = item.price
      if (typeof price === 'string') {
        price = parseFloat(price.replace(/[^\d.]/g, '').replace(',', '.'))
      }
      return acc + (price * item.quantity)
    }, 0)
  })

  function addToCart(plate, quantity = 1) {
    const existingItem = items.value.find(item => item.id === plate.id)
    if (existingItem) {
      existingItem.quantity += quantity
    } else {
      items.value.push({ ...plate, quantity })
    }
    
    // Add a simple logic for animation/feedback if needed here
  }

  function updateQuantity(plateId, delta) {
    const item = items.value.find(i => i.id === plateId)
    if (item) {
      item.quantity += delta
      if (item.quantity <= 0) {
        removeFromCart(plateId)
      }
    }
  }

  function removeFromCart(plateId) {
    items.value = items.value.filter(item => item.id !== plateId)
  }

  function clearCart() {
    items.value = []
  }

  return {
    items,
    totalItems,
    totalPrice,
    addToCart,
    updateQuantity,
    removeFromCart,
    clearCart
  }
})

