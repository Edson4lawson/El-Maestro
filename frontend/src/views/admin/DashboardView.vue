<template>
  <div class="min-h-screen bg-m-obsidian">
    <!-- Sidebar -->
    <AdminSidebar />
    
    <!-- Main Content -->
    <div class="ml-64 p-8">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gradient-gold mb-2">Tableau de Bord</h1>
        <p class="text-white/70">Bienvenue, {{ adminName }}</p>
      </div>
      
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <StatsCard 
          title="Commandes Aujourd'hui" 
          :value="stats.todayOrders" 
          icon="ShoppingCart" 
          color="blue" 
        />
        <StatsCard 
          title="Réservations" 
          :value="stats.todayReservations" 
          icon="Calendar" 
          color="green" 
        />
        <StatsCard 
          title="Chiffre d'Affaires" 
          :value="formatCurrency(stats.revenue)" 
          icon="DollarSign" 
          color="gold" 
        />
        <StatsCard 
          title="Plats Populaires" 
          :value="stats.popularDishes" 
          icon="TrendingUp" 
          color="purple" 
        />
      </div>
      
      <!-- Charts Section -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white/5 backdrop-blur-xl rounded-2xl p-6 border border-white/10">
          <h3 class="text-xl font-bold mb-4 text-white">Évolution des Commandes</h3>
          <canvas ref="ordersChart"></canvas>
        </div>
        <div class="bg-white/5 backdrop-blur-xl rounded-2xl p-6 border border-white/10">
          <h3 class="text-xl font-bold mb-4 text-white">Répartition des Catégories</h3>
          <canvas ref="categoriesChart"></canvas>
        </div>
      </div>
      
      <!-- Recent Orders -->
      <div class="bg-white/5 backdrop-blur-xl rounded-2xl p-6 border border-white/10">
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-xl font-bold text-white">Commandes Récentes</h3>
          <RouterLink to="/admin/orders" class="text-m-gold hover:text-m-gold/80">
            Voir tout →
          </RouterLink>
        </div>
        <OrderTable :orders="recentOrders" :compact="true" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, inject } from 'vue'
import { ShoppingCart, Calendar, DollarSign, TrendingUp } from 'lucide-vue-next'
import StatsCard from '../components/admin/StatsCard.vue'
import OrderTable from '../components/admin/OrderTable.vue'
import AdminSidebar from '../components/admin/Sidebar.vue'

const isDark = inject('isDark', ref(true))

// Données simulées - à remplacer par API réelle
const adminName = ref('Super Admin')
const stats = ref({
  todayOrders: 24,
  todayReservations: 8,
  revenue: 485000,
  popularDishes: 12
})

const recentOrders = ref([
  { id: 1, customer: 'Marie K.', items: 'Poulet Yassa x2', total: 25000, status: 'completed' },
  { id: 2, customer: 'Jean B.', items: 'Sushi Mix x1', total: 15000, status: 'preparing' },
  { id: 3, customer: 'Aline T.', items: 'Dessert Assortiment', total: 8500, status: 'completed' }
])

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF' }).format(amount)
}

onMounted(() => {
  // Initialiser les graphiques
  setTimeout(() => {
    initOrdersChart()
    initCategoriesChart()
  }, 500)
})

const initOrdersChart = () => {
  const canvas = document.querySelector('[ref="ordersChart"]')
  if (!canvas) return
  
  const ctx = canvas.getContext('2d')
  
  // Données pour le graphique
  const ordersData = {
    labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
    datasets: [{
      label: 'Commandes',
      data: [15, 22, 18, 25, 30, 28, 20],
      borderColor: 'rgb(212, 175, 55)',
      backgroundColor: 'rgba(212, 175, 55, 0.1)',
      tension: 0.4
    }]
  }
  
  new Chart(ctx, {
    type: 'line',
    data: ordersData,
    options: {
      responsive: true,
      plugins: {
        legend: {
          labels: {
            color: 'white'
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: {
            color: 'rgba(255, 255, 255, 0.1)'
          }
        },
        x: {
          grid: {
            color: 'rgba(255, 255, 255, 0.1)'
          }
        }
      }
    }
  })
}

const initCategoriesChart = () => {
  const canvas = document.querySelector('[ref="categoriesChart"]')
  if (!canvas) return
  
  const ctx = canvas.getContext('2d')
  
  const categoriesData = {
    labels: ['Entrées', 'Plats Résistants', 'Desserts', 'Boissons'],
    datasets: [{
      label: 'Répartition',
      data: [25, 45, 20, 10],
      backgroundColor: [
        'rgba(59, 130, 246, 0.8)',
        'rgba(212, 175, 55, 0.8)',
        'rgba(249, 115, 22, 0.8)',
        'rgba(147, 51, 234, 0.8)'
      ],
      borderColor: [
        'rgb(59, 130, 246)',
        'rgb(212, 175, 55)',
        'rgb(249, 115, 22)',
        'rgb(147, 51, 234)'
      ],
      borderWidth: 2
    }]
  }
  
  new Chart(ctx, {
    type: 'doughnut',
    data: categoriesData,
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            color: 'white',
            padding: 20,
            font: {
              size: 12
            }
          }
        }
      }
    }
  })
}
</script>

<style scoped>
.text-gradient-gold {
  @apply bg-clip-text text-transparent bg-premium-gradient;
  background-size: 200% auto;
  animation: shine 3s linear infinite;
}

@keyframes shine {
  to { background-position: 200% center; }
}
</style>
