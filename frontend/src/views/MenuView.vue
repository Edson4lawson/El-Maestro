<script setup>
import { ref, computed, onMounted } from 'vue'
import { Search, Filter } from 'lucide-vue-next'
import PlateCard from '../components/PlateCard.vue'
import { plateService } from '../services/api'

const categories = ['Tout', 'Entrées', 'Plats Résistants', 'Desserts', 'Boissons']
const selectedCategory = ref('Tout')
const searchQuery = ref('')
const plates = ref([])
const loading = ref(true)

const fetchPlates = async () => {
  try {
    const response = await plateService.getAll()
    plates.value = response.data
  } catch (error) {
    console.error('Error fetching plates:', error)
  } finally {
    loading.value = false
  }
}

onMounted(fetchPlates)

const filteredPlates = computed(() => {
  if (!plates.value) return []
  return plates.value.filter(plate => {
    const matchesCategory = selectedCategory.value === 'Tout' || plate.category === selectedCategory.value
    const matchesSearch = plate.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    return matchesCategory && matchesSearch
  })
})
</script>

<template>
  <div class="px-6 md:px-12 py-12">
    <div class="container mx-auto">
      <header class="mb-16 text-center max-w-2xl mx-auto">
        <h1 class="text-6xl font-display font-bold mb-6 italic underline decoration-m-gold/20">Notre <span class="text-gradient-gold">Menu</span></h1>
        <p class="opacity-60 text-lg">
          Une symphonie culinaire où chaque ingrédient raconte une histoire de terroir et de passion.
        </p>
      </header>

      <!-- Filters & Search -->
      <div class="flex flex-col md:flex-row gap-6 justify-between items-center mb-12">
        <div class="flex flex-wrap items-center gap-3 justify-center">
          <button 
            v-for="cat in categories" 
            :key="cat"
            @click="selectedCategory = cat"
            class="px-6 py-2 rounded-full border transition-all duration-300 font-bold text-sm"
            :class="[
              selectedCategory === cat 
                ? 'bg-m-gold border-m-gold text-m-obsidian' 
                : 'border-white/10 hover:border-m-gold/50'
            ]"
          >
            {{ cat }}
          </button>
        </div>

        <div class="relative w-full md:w-80">
          <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 opacity-40" />
          <input 
            v-model="searchQuery"
            type="text" 
            placeholder="Rechercher un plat..."
            class="w-full bg-white/5 border border-white/10 rounded-full py-3 pl-12 pr-6 focus:border-m-gold outline-none transition-all"
          />
        </div>
      </div>

      <!-- Plates Grid -->
      <TransitionGroup 
        name="list" 
        tag="div" 
        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8"
      >
        <PlateCard v-for="plate in filteredPlates" :key="plate.id" :plate="plate" />
      </TransitionGroup>

      <!-- Empty State -->
      <div v-if="filteredPlates.length === 0" class="text-center py-24">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-white/5 mb-6">
          <Filter class="w-10 h-10 opacity-20" />
        </div>
        <h3 class="text-2xl font-bold opacity-40">Aucun plat ne correspond à votre recherche</h3>
      </div>
    </div>
  </div>
</template>

<style scoped>
.list-enter-active, .list-leave-active {
  transition: all 0.5s ease;
}
.list-enter-from, .list-leave-to {
  opacity: 0;
  transform: translateY(30px) scale(0.9);
}
</style>
