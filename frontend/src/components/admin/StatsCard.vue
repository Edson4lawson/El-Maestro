<template>
  <div class="bg-m-gold/10 p-4 rounded-lg border border-m-gold/30">
    <div class="flex items-center justify-between">
      <h4 class="font-bold text-m-obsidian">{{ title }}</h4>
      <div class="text-3xl font-bold text-m-obsidian">{{ value }}</div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: String,
  value: [String, Number],
  color: {
    type: String,
    default: 'blue',
    validator: (value) => ['blue', 'green', 'gold', 'purple'].includes(value)
  }
})

const iconComponents = {
  ShoppingCart: 'ShoppingCart',
  Calendar: 'Calendar', 
  DollarSign: 'DollarSign',
  TrendingUp: 'TrendingUp'
}
</script>

<style scoped>
.bg-blue\/10 {
  @apply bg-blue-500/10 border-blue-500/30;
}

.bg-green\/10 {
  @apply bg-green-500/10 border-green-500/30;
}

.bg-gold\/10 {
  @apply bg-m-gold/10 border-m-gold/30;
}

.bg-purple\/10 {
  @apply bg-purple-500/10 border-purple-500/30;
}
</style>
