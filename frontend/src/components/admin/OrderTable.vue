<template>
  <div class="bg-m-gold/10 p-4 rounded-lg">
    <table class="w-full">
      <thead>
        <tr class="text-left">
          <th class="pb-3 text-m-obsidian font-semibold">Client</th>
          <th class="pb-3 text-m-obsidian font-semibold">Commande</th>
          <th class="pb-3 text-m-obsidian font-semibold">Total</th>
          <th class="pb-3 text-m-obsidian font-semibold">Statut</th>
          <th class="pb-3 text-m-obsidian font-semibold">Date</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="order in orders" :key="order.id" class="border-t border-m-gold/20">
          <td class="py-3 text-m-obsidian">{{ order.customer }}</td>
          <td class="py-3 text-m-obsidian">{{ order.items }}</td>
          <td class="py-3 font-bold text-m-gold">{{ formatCurrency(order.total) }}</td>
          <td>
            <span 
              :class="getStatusClass(order.status)"
              class="px-3 py-1 rounded-full text-xs font-semibold"
            >
              {{ getStatusText(order.status) }}
            </span>
          </td>
          <td class="py-3 text-m-obsidian text-sm">{{ formatDate(order.date) }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
defineProps({
  orders: Array,
  compact: {
    type: Boolean,
    default: false
  }
})

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF' }).format(amount)
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const getStatusClass = (status) => {
  const classes = {
    'completed': 'bg-green-500',
    'preparing': 'bg-yellow-500',
    'cancelled': 'bg-red-500'
  }
  return classes[status] || 'bg-gray-500'
}

const getStatusText = (status) => {
  const texts = {
    'completed': 'Terminée',
    'preparing': 'Préparation',
    'cancelled': 'Annulée'
  }
  return texts[status] || status
}
</script>

<style scoped>
.border-t {
  border-top-width: 1px;
}
</style>
