<template>
  <div class="dash-layout">
    <AdminSidebar :collapsed="sidebarCollapsed" @toggle="sidebarCollapsed = !sidebarCollapsed" />

    <main class="dash-main">
      <AdminTopBar :sidebar-collapsed="sidebarCollapsed" @toggle-sidebar="sidebarCollapsed = !sidebarCollapsed" />

      <!-- Atmospheric Ambient Glows -->
      <div class="ambient-glow ambient-glow--gold" aria-hidden="true"></div>
      <div class="ambient-glow ambient-glow--blue" aria-hidden="true"></div>

      <div class="dash-content">

        <!-- ══════════════════════════════════════════════════════════════ -->
        <!-- 1. EXECUTIVE COMMAND HEADER                                    -->
        <!-- ══════════════════════════════════════════════════════════════ -->
        <header class="cmd-header">
          <div class="cmd-header__left">
            <div class="cmd-header__status-badge">
              <span class="pulse-indicator"></span>
              <span class="status-text">{{ currentShiftLabel }}</span>
              <span class="status-sep">•</span>
              <span class="status-time">{{ liveClock }}</span>
            </div>
            
            <h1 class="cmd-header__title">
              {{ greeting }}, <span class="text-gold-gradient">{{ adminName }}</span>
            </h1>
            <p class="cmd-header__subtitle">
              Orchestration gastronomique en direct • {{ formattedTodayDate }}
            </p>
          </div>

          <div class="cmd-header__right">
            <!-- Period Selector Tabs -->
            <div class="period-switcher">
              <button 
                v-for="p in periodOptions" 
                :key="p.id" 
                class="period-btn" 
                :class="{ 'period-btn--active': activePeriod === p.id }"
                @click="setPeriod(p.id)"
              >
                {{ p.label }}
              </button>
            </div>

            <!-- Action Buttons -->
            <div class="cmd-header__actions">
              <button class="btn-action-ghost" @click="refreshData" :class="{ 'btn-action--spinning': isRefreshing }" title="Actualiser le flux">
                <RefreshCw class="btn-icon" :size="16" />
                <span class="btn-label">Actualiser</span>
              </button>

              <button class="btn-action-ghost" @click="exportReport">
                <Download class="btn-icon" :size="16" />
                <span class="btn-label">Exporter</span>
              </button>

              <button class="btn-action-gold" @click="openQuickOrderModal">
                <PlusCircle class="btn-icon" :size="16" />
                <span class="btn-label">Nouvelle Commande</span>
              </button>
            </div>
          </div>
        </header>

        <!-- ══════════════════════════════════════════════════════════════ -->
        <!-- 2. EXECUTIVE KPI CARDS GRID                                    -->
        <!-- ══════════════════════════════════════════════════════════════ -->
        <section class="kpi-grid">
          <StatsCard 
            title="Chiffre d'Affaires" 
            :value="kpiStats.revenue" 
            suffix="FCFA"
            icon="TrendingUp" 
            color="gold"   
            trend="up"   
            :trend-value="kpiStats.revenueGrowth" 
            description="vs période précédente"
            sub-badge="Objectif 94%"
            :sparkline-data="kpiStats.revenueSparkline" 
          />

          <StatsCard 
            title="Commandes Actives" 
            :value="kpiStats.activeOrders" 
            suffix="cmd"
            icon="ShoppingBag" 
            color="blue"  
            trend="up"   
            :trend-value="kpiStats.ordersGrowth"  
            description="6 en cuisine • 4 en livraison"
            sub-badge="Temps moyen: 22 min"
            :sparkline-data="kpiStats.ordersSparkline" 
          />

          <StatsCard 
            title="Réservations & Couverts" 
            :value="kpiStats.coversCount" 
            suffix="couverts"
            icon="Users"  
            color="green"   
            trend="up"   
            :trend-value="kpiStats.coversGrowth" 
            description="14 tables confirmées ce soir"
            sub-badge="Taux d'occupation: 92%"
            :sparkline-data="kpiStats.coversSparkline" 
          />

          <StatsCard 
            title="Ticket Moyen & Score" 
            :value="kpiStats.avgTicket" 
            suffix="FCFA"
            icon="Award" 
            color="purple" 
            trend="up" 
            trend-value="+5.2%" 
            description="Satisfaction 5.9 / 6 ★"
            sub-badge="Score d'Excellence"
            :sparkline-data="kpiStats.ticketSparkline" 
          />
        </section>

        <!-- ══════════════════════════════════════════════════════════════ -->
        <!-- 3. ADVANCED ANALYTICS & SALES CHARTS                           -->
        <!-- ══════════════════════════════════════════════════════════════ -->
        <section class="analytics-grid">
          
          <!-- Main Area Chart: Revenue vs Orders Evolution -->
          <div class="analytics-panel analytics-panel--main">
            <div class="panel-header">
              <div class="panel-header__info">
                <span class="panel-eyebrow">Performance Financière & Volume</span>
                <h2 class="panel-title">Dynamique des Ventes</h2>
              </div>

              <div class="chart-controls">
                <div class="chart-mode-pills">
                  <button 
                    class="chart-pill" 
                    :class="{ 'chart-pill--active': chartMode === 'revenue' }"
                    @click="setChartMode('revenue')"
                  >
                    Chiffre d'Affaires (FCFA)
                  </button>
                  <button 
                    class="chart-pill" 
                    :class="{ 'chart-pill--active': chartMode === 'volume' }"
                    @click="setChartMode('volume')"
                  >
                    Volume Commandes
                  </button>
                </div>
              </div>
            </div>

            <div class="panel-body panel-body--chart">
              <div class="chart-canvas-container">
                <canvas ref="mainChartRef"></canvas>
              </div>
            </div>
          </div>

          <!-- Secondary Analytics: Category Distribution & Best Sellers -->
          <div class="analytics-panel analytics-panel--side">
            <div class="panel-header">
              <div class="panel-header__info">
                <span class="panel-eyebrow">Répartition du Menu</span>
                <h2 class="panel-title">Mix Catégories & Top Plats</h2>
              </div>
            </div>

            <div class="panel-body panel-body--donut-flex">
              <!-- Donut Chart with Centered Metric -->
              <div class="donut-container">
                <div class="donut-canvas-wrap">
                  <canvas ref="donutChartRef"></canvas>
                  <div class="donut-center-metric">
                    <span class="donut-center-metric__num">{{ totalCategoryOrders }}</span>
                    <span class="donut-center-metric__lbl">Articles vendus</span>
                  </div>
                </div>

                <!-- Custom Luxury Legend -->
                <div class="donut-legend-grid">
                  <div v-for="(cat, idx) in categoriesDistribution" :key="idx" class="legend-chip">
                    <span class="legend-chip__dot" :style="{ backgroundColor: cat.color }"></span>
                    <span class="legend-chip__name">{{ cat.name }}</span>
                    <span class="legend-chip__pct">{{ cat.percentage }}%</span>
                  </div>
                </div>
              </div>

              <!-- Top Signature Dishes Leaderboard -->
              <div class="top-dishes-leaderboard">
                <div class="leaderboard-title">
                  <Flame :size="14" class="text-gold" />
                  <span>Plats Signatures les plus commandés</span>
                </div>

                <div class="dishes-ranking-list">
                  <div v-for="(dish, dIdx) in topSellingDishes" :key="dIdx" class="dish-rank-item">
                    <div class="dish-rank-badge" :class="`dish-rank-badge--${dIdx + 1}`">
                      #{{ dIdx + 1 }}
                    </div>
                    <div class="dish-rank-info">
                      <div class="dish-rank-header">
                        <span class="dish-rank-name">{{ dish.name }}</span>
                        <span class="dish-rank-val">{{ dish.salesCount }} ventes</span>
                      </div>
                      <div class="dish-rank-bar-track">
                        <div class="dish-rank-bar-fill" :style="{ width: `${dish.popularityPct}%` }"></div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>

        </section>

        <!-- ══════════════════════════════════════════════════════════════ -->
        <!-- 4. OPERATIONS HUB & LIVE ACTIVITY TIMELINE                     -->
        <!-- ══════════════════════════════════════════════════════════════ -->
        <section class="ops-grid">

          <!-- Left Large Panel: Interactive Orders & Reservations Table -->
          <div class="ops-panel ops-panel--table">
            <div class="panel-header panel-header--with-tabs">
              <div class="ops-tabs">
                <button 
                  class="ops-tab" 
                  :class="{ 'ops-tab--active': activeOpsTab === 'orders' }"
                  @click="activeOpsTab = 'orders'"
                >
                  <ShoppingBag :size="16" />
                  <span>Commandes en Direct</span>
                  <span class="ops-tab__count">{{ liveOrdersList.length }}</span>
                </button>

                <button 
                  class="ops-tab" 
                  :class="{ 'ops-tab--active': activeOpsTab === 'reservations' }"
                  @click="activeOpsTab = 'reservations'"
                >
                  <CalendarDays :size="16" />
                  <span>Réservations du Service</span>
                  <span class="ops-tab__count">{{ liveReservationsList.length }}</span>
                </button>
              </div>

              <!-- Quick Table Filters -->
              <div class="ops-filters">
                <div class="table-search-box">
                  <Search :size="14" class="search-icon" />
                  <input 
                    v-model="searchQuery" 
                    type="text" 
                    :placeholder="activeOpsTab === 'orders' ? 'Rechercher client, plat, ID...' : 'Rechercher nom, table, heure...'" 
                    class="table-search-input"
                  />
                  <button v-if="searchQuery" @click="searchQuery = ''" class="search-clear-btn">✕</button>
                </div>

                <div v-if="activeOpsTab === 'orders'" class="status-filter-pills">
                  <button 
                    v-for="st in orderStatusFilters" 
                    :key="st.key" 
                    class="filter-pill"
                    :class="{ 'filter-pill--active': selectedOrderStatus === st.key }"
                    @click="selectedOrderStatus = st.key"
                  >
                    {{ st.label }}
                  </button>
                </div>
              </div>
            </div>

            <!-- TAB 1: LIVE ORDERS TABLE -->
            <div v-if="activeOpsTab === 'orders'" class="table-wrapper">
              <table class="luxury-table">
                <thead>
                  <tr>
                    <th>Réf. & Client</th>
                    <th>Articles & Menu</th>
                    <th>Montant Total</th>
                    <th>Paiement</th>
                    <th>Statut Opérationnel</th>
                    <th class="text-right">Action Rapide</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="order in filteredOrders" :key="order.id" class="table-row">
                    <td>
                      <div class="client-cell">
                        <div class="client-avatar" :class="`client-avatar--${order.id % 4}`">
                          {{ getInitials(order.customer_name) }}
                        </div>
                        <div class="client-info">
                          <span class="client-name">{{ order.customer_name }}</span>
                          <span class="client-meta">#CMD-{{ String(order.id).padStart(4, '0') }} • {{ order.phone }}</span>
                        </div>
                      </div>
                    </td>

                    <td>
                      <div class="items-cell">
                        <span class="items-title">{{ order.items_summary }}</span>
                        <span class="items-time">{{ order.order_time }}</span>
                      </div>
                    </td>

                    <td>
                      <div class="amount-cell">
                        <span class="amount-num">{{ formatFCFA(order.total_amount) }}</span>
                        <span class="amount-cur">FCFA</span>
                      </div>
                    </td>

                    <td>
                      <span class="pay-badge" :class="`pay-badge--${order.payment_method}`">
                        <span class="pay-dot"></span>
                        {{ getPaymentMethodLabel(order.payment_method) }}
                      </span>
                    </td>

                    <td>
                      <div class="status-dropdown-wrap">
                        <select 
                          v-model="order.status" 
                          class="status-select" 
                          :class="`status-select--${order.status}`"
                          @change="updateOrderStatus(order)"
                        >
                          <option value="pending">⏳ En attente</option>
                          <option value="preparing">🍳 En cuisine</option>
                          <option value="ready">✨ Prête</option>
                          <option value="on_route">🛵 En livraison</option>
                          <option value="delivered">✅ Livrée</option>
                          <option value="cancelled">❌ Annulée</option>
                        </select>
                      </div>
                    </td>

                    <td class="text-right">
                      <button class="btn-detail" @click="viewOrderDetails(order)">
                        Détails
                        <ChevronRight :size="14" />
                      </button>
                    </td>
                  </tr>

                  <tr v-if="filteredOrders.length === 0">
                    <td colspan="6" class="empty-table-cell">
                      <div class="empty-state">
                        <Inbox :size="32" class="empty-icon" />
                        <p class="empty-title">Aucune commande ne correspond aux filtres</p>
                        <span class="empty-sub">Modifiez vos critères ou réinitialisez la recherche.</span>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- TAB 2: RESERVATIONS TABLE -->
            <div v-else class="table-wrapper">
              <table class="luxury-table">
                <thead>
                  <tr>
                    <th>Client & Contact</th>
                    <th>Heure & Table</th>
                    <th>Couverts</th>
                    <th>Demande Particulière</th>
                    <th>Statut</th>
                    <th class="text-right">Confirmation</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="res in filteredReservations" :key="res.id" class="table-row">
                    <td>
                      <div class="client-cell">
                        <div class="client-avatar client-avatar--gold">
                          {{ getInitials(res.customer_name) }}
                        </div>
                        <div class="client-info">
                          <span class="client-name">{{ res.customer_name }}</span>
                          <span class="client-meta">{{ res.phone }} • {{ res.email || 'Pas d\'email' }}</span>
                        </div>
                      </div>
                    </td>

                    <td>
                      <div class="items-cell">
                        <span class="items-title font-semibold text-gold">{{ res.time }}</span>
                        <span class="items-time">Table {{ res.table_num || 'Standard' }}</span>
                      </div>
                    </td>

                    <td>
                      <span class="covers-badge">
                        <Users :size="12" />
                        {{ res.people_count }} pers.
                      </span>
                    </td>

                    <td>
                      <span class="request-text">{{ res.special_request || 'Aucune consigne spécifique' }}</span>
                    </td>

                    <td>
                      <span class="status-badge" :class="`status-badge--${res.status}`">
                        {{ getReservationStatusLabel(res.status) }}
                      </span>
                    </td>

                    <td class="text-right">
                      <div class="res-action-group">
                        <button 
                          v-if="res.status === 'pending'" 
                          class="btn-res-confirm" 
                          @click="confirmReservation(res)"
                          title="Confirmer la table"
                        >
                          Confirmer
                        </button>
                        <button class="btn-res-view" @click="viewReservation(res)">
                          <Eye :size="14" />
                        </button>
                      </div>
                    </td>
                  </tr>

                  <tr v-if="filteredReservations.length === 0">
                    <td colspan="6" class="empty-table-cell">
                      <div class="empty-state">
                        <CalendarDays :size="32" class="empty-icon" />
                        <p class="empty-title">Aucune réservation pour ce service</p>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Footer Pagination / Quick View Link -->
            <div class="panel-table-footer">
              <span class="footer-count">
                Affichage de {{ activeOpsTab === 'orders' ? filteredOrders.length : filteredReservations.length }} éléments
              </span>
              <RouterLink :to="activeOpsTab === 'orders' ? '/admin/orders' : '/admin/reservations'" class="link-full-view">
                Consulter le registre complet
                <ArrowUpRight :size="14" />
              </RouterLink>
            </div>
          </div>

          <!-- Right Column: Kitchen Live Feed & Smart VIP Alerts -->
          <div class="ops-panel ops-panel--activity">
            <div class="panel-header">
              <div class="panel-header__info">
                <span class="panel-eyebrow">Flux de Cuisine & Alertes</span>
                <h2 class="panel-title">Activité en Direct</h2>
              </div>
              <span class="live-status-chip">
                <span class="live-dot-pulse"></span>
                LIVE SYNC
              </span>
            </div>

            <!-- VIP & Kitchen Alert Banner -->
            <div class="alert-stream-box">
              <div class="alert-card alert-card--gold">
                <Sparkles :size="16" class="alert-card__icon" />
                <div class="alert-card__text">
                  <p class="alert-card__headline">Table VIP n°4 réservée (20h00)</p>
                  <p class="alert-card__sub">Protocole dégustation Prestige Maestro</p>
                </div>
              </div>

              <div class="alert-card alert-card--warning">
                <AlertTriangle :size="16" class="alert-card__icon" />
                <div class="alert-card__text">
                  <p class="alert-card__headline">Stock Bas : Filet de Capitaine</p>
                  <p class="alert-card__sub">Plus que 4 portions disponibles</p>
                </div>
              </div>
            </div>

            <!-- Live Event Timeline -->
            <div class="activity-timeline">
              <div v-for="(act, aIdx) in liveActivityLog" :key="aIdx" class="activity-item">
                <div class="activity-item__track">
                  <div class="activity-item__pip" :class="`activity-item__pip--${act.type}`">
                    <component :is="getActivityIcon(act.type)" :size="11" />
                  </div>
                  <div v-if="aIdx < liveActivityLog.length - 1" class="activity-item__line"></div>
                </div>

                <div class="activity-item__body">
                  <p class="activity-item__text">{{ act.message }}</p>
                  <span class="activity-item__time">{{ act.timeAgo }}</span>
                </div>
              </div>
            </div>
          </div>

        </section>

      </div>
    </main>

    <!-- Notification Toast Feedback -->
    <transition name="toast-fade">
      <div v-if="toastMessage" class="luxury-toast">
        <CheckCircle2 :size="18" class="text-gold" />
        <span>{{ toastMessage }}</span>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { RouterLink } from 'vue-router'
import { Chart, registerables } from 'chart.js'
import {
  TrendingUp, ShoppingBag, Users, Award, RefreshCw, Download, PlusCircle,
  Search, ChevronRight, Inbox, CalendarDays, Eye, ArrowUpRight, Sparkles,
  AlertTriangle, CheckCircle2, Check, Clock, Utensils, Star, Flame
} from 'lucide-vue-next'

import { useAuthStore } from '../../stores/auth'
import StatsCard from '../../components/admin/StatsCard.vue'
import AdminSidebar from '../../components/admin/AdminSidebar.vue'
import AdminTopBar from '../../components/admin/AdminTopBar.vue'

Chart.register(...registerables)

const authStore = useAuthStore()
const sidebarCollapsed = ref(false)
const isRefreshing = ref(false)
const toastMessage = ref('')

const showToast = (msg) => {
  toastMessage.value = msg
  setTimeout(() => { toastMessage.value = '' }, 3500)
}

// ── Admin Greetings & Shift Info ───────────────────────────
const adminName = computed(() => authStore.user?.name || 'Chef Exécutif')
const currentHour = new Date().getHours()
const greeting = computed(() => {
  if (currentHour < 12) return 'Bonjour'
  if (currentHour < 18) return 'Bon après-midi'
  return 'Bonsoir'
})

const currentShiftLabel = computed(() => {
  if (currentHour >= 11 && currentHour <= 15) return 'Service du Midi'
  if (currentHour >= 18 && currentHour <= 23) return 'Service du Soir'
  return 'Préparation & Mise en Place'
})

const liveClock = ref('')
const updateClock = () => {
  const now = new Date()
  liveClock.value = now.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit', second: '2-digit' })
}
let clockTimer = null

const formattedTodayDate = computed(() => {
  const dateStr = new Date().toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
  return dateStr.charAt(0).toUpperCase() + dateStr.slice(1)
})

// ── Period Switcher ─────────────────────────────────────────
const periodOptions = [
  { id: 'today', label: "Aujourd'hui" },
  { id: '7d',    label: '7 Jours' },
  { id: '30d',   label: '30 Jours' },
  { id: 'month', label: 'Ce Mois' }
]
const activePeriod = ref('today')

const setPeriod = (pId) => {
  activePeriod.value = pId
  renderMainChart()
  showToast(`Données actualisées pour la période : ${periodOptions.find(p => p.id === pId)?.label}`)
}

// ── Executive KPIs Reactive State ───────────────────────────
const kpiStats = ref({
  revenue: 845000,
  revenueGrowth: '+18.4%',
  revenueSparkline: [520, 610, 580, 720, 690, 780, 845],
  activeOrders: 32,
  ordersGrowth: '+12.5%',
  ordersSparkline: [18, 22, 20, 26, 28, 29, 32],
  coversCount: 48,
  coversGrowth: '+8.0%',
  coversSparkline: [30, 36, 32, 40, 42, 45, 48],
  avgTicket: 26400,
  avgTicketGrowth: '+5.2%',
  ticketSparkline: [22, 24, 23, 25, 24, 25, 26.4],
})

// ── Chart.js Configurations & Analytics ─────────────────────
const mainChartRef = ref(null)
const donutChartRef = ref(null)
let mainChartInstance = null
let donutChartInstance = null
const chartMode = ref('revenue') // 'revenue' or 'volume'

const setChartMode = (mode) => {
  chartMode.value = mode
  renderMainChart()
}

const analyticsData = {
  today: {
    labels: ['11h', '12h', '13h', '14h', '15h', '18h', '19h', '20h', '21h', '22h'],
    revenue: [45000, 115000, 185000, 95000, 40000, 80000, 190000, 260000, 210000, 120000],
    volume:  [2, 5, 8, 4, 2, 4, 9, 12, 10, 5]
  },
  '7d': {
    labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
    revenue: [480000, 560000, 520000, 640000, 890000, 1120000, 940000],
    volume:  [22, 26, 24, 30, 42, 54, 46]
  },
  '30d': {
    labels: ['Semaine 1', 'Semaine 2', 'Semaine 3', 'Semaine 4'],
    revenue: [3200000, 3850000, 4100000, 4650000],
    volume:  [150, 175, 190, 215]
  },
  month: {
    labels: ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4'],
    revenue: [3200000, 3850000, 4100000, 4650000],
    volume:  [150, 175, 190, 215]
  }
}

const categoriesDistribution = [
  { name: 'Plats Résistants', percentage: 48, color: '#D4AF37' },
  { name: 'Entrées Nobles',   percentage: 22, color: '#3B82F6' },
  { name: 'Desserts & Douceurs', percentage: 18, color: '#A855F7' },
  { name: 'Cocktails & Vins', percentage: 12, color: '#10B981' },
]

const totalCategoryOrders = ref(384)

const topSellingDishes = ref([
  { name: 'Poulet Braisé Maestro', salesCount: 142, popularityPct: 95 },
  { name: 'Poisson Grillé Royal (Capitaine)', salesCount: 118, popularityPct: 82 },
  { name: 'Pâtes Carbonara Truffe Noire', salesCount: 89, popularityPct: 68 },
  { name: 'Mousse au Chocolat Gold 24k', salesCount: 64, popularityPct: 52 },
])

const renderMainChart = () => {
  if (!mainChartRef.value) return
  if (mainChartInstance) mainChartInstance.destroy()

  const currentData = analyticsData[activePeriod.value] || analyticsData['today']
  const isRev = chartMode.value === 'revenue'
  const datasetValues = isRev ? currentData.revenue : currentData.volume

  const ctx = mainChartRef.value.getContext('2d')
  const gradient = ctx.createLinearGradient(0, 0, 0, 320)
  gradient.addColorStop(0, 'rgba(212, 175, 55, 0.38)')
  gradient.addColorStop(0.6, 'rgba(212, 175, 55, 0.08)')
  gradient.addColorStop(1, 'rgba(212, 175, 55, 0.0)')

  mainChartInstance = new Chart(mainChartRef.value, {
    type: 'line',
    data: {
      labels: currentData.labels,
      datasets: [{
        label: isRev ? "Chiffre d'Affaires (FCFA)" : 'Nombre de Commandes',
        data: datasetValues,
        borderColor: '#D4AF37',
        borderWidth: 2.8,
        pointBackgroundColor: '#F3E5AB',
        pointBorderColor: '#09090B',
        pointBorderWidth: 2.5,
        pointRadius: 4.5,
        pointHoverRadius: 7,
        pointHoverBackgroundColor: '#FFFFFF',
        pointHoverBorderColor: '#D4AF37',
        pointHoverBorderWidth: 3,
        fill: true,
        backgroundColor: gradient,
        tension: 0.42
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: 'rgba(19, 19, 23, 0.95)',
          titleColor: '#F3E5AB',
          titleFont: { family: 'Playfair Display', size: 13, weight: 'bold' },
          bodyColor: '#FBF9F5',
          bodyFont: { family: 'Inter', size: 12, weight: '600' },
          borderColor: 'rgba(212, 175, 55, 0.35)',
          borderWidth: 1,
          padding: 12,
          cornerRadius: 10,
          displayColors: false,
          callbacks: {
            label: (context) => {
              if (isRev) {
                return `Recette : ${context.parsed.y.toLocaleString('fr-FR')} FCFA`
              }
              return `Commandes : ${context.parsed.y} livraisons`
            }
          }
        }
      },
      scales: {
        x: {
          grid: { color: 'rgba(255, 255, 255, 0.04)', drawBorder: false },
          ticks: { color: 'rgba(251, 249, 245, 0.45)', font: { family: 'Inter', size: 11 } }
        },
        y: {
          grid: { color: 'rgba(255, 255, 255, 0.05)', drawBorder: false },
          ticks: {
            color: 'rgba(251, 249, 245, 0.45)',
            font: { family: 'Inter', size: 11 },
            callback: (value) => isRev ? `${(value / 1000).toFixed(0)}k` : value
          },
          beginAtZero: true
        }
      }
    }
  })
}

const renderDonutChart = () => {
  if (!donutChartRef.value) return
  if (donutChartInstance) donutChartInstance.destroy()

  donutChartInstance = new Chart(donutChartRef.value, {
    type: 'doughnut',
    data: {
      labels: categoriesDistribution.map(c => c.name),
      datasets: [{
        data: categoriesDistribution.map(c => c.percentage),
        backgroundColor: categoriesDistribution.map(c => c.color),
        borderColor: '#131317',
        borderWidth: 3.5,
        hoverOffset: 6
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '76%',
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: 'rgba(19, 19, 23, 0.95)',
          titleColor: '#F3E5AB',
          bodyColor: '#FBF9F5',
          borderColor: 'rgba(212, 175, 55, 0.3)',
          borderWidth: 1,
          padding: 10,
          cornerRadius: 8,
          callbacks: {
            label: (ctx) => ` ${ctx.label} : ${ctx.parsed}%`
          }
        }
      }
    }
  })
}

// ── Operations Hub: Live Orders & Reservations ──────────────
const activeOpsTab = ref('orders')
const searchQuery = ref('')
const selectedOrderStatus = ref('all')

const orderStatusFilters = [
  { key: 'all', label: 'Toutes' },
  { key: 'pending', label: 'En attente' },
  { key: 'preparing', label: 'En cuisine' },
  { key: 'on_route', label: 'En livraison' },
  { key: 'delivered', label: 'Livrées' },
]

const liveOrdersList = ref([
  { id: 1048, customer_name: 'Dr. Aurel Dossou', phone: '+229 97 12 34 56', items_summary: 'Poulet Braisé Maestro ×2, Cocktail Ananas ×2', total_amount: 32000, payment_method: 'mtn', status: 'preparing', order_time: 'Il y a 6 min' },
  { id: 1047, customer_name: 'Mme Sophie Lawson', phone: '+229 95 88 44 22', items_summary: 'Poisson Grillé Royal ×1, Attiéké Prestige', total_amount: 18500, payment_method: 'card', status: 'on_route', order_time: 'Il y a 14 min' },
  { id: 1046, customer_name: 'Carlos Mensah', phone: '+229 66 33 21 00', items_summary: 'Pâtes Truffe ×2, Tiramisu Classique ×2', total_amount: 45000, payment_method: 'moov', status: 'ready', order_time: 'Il y a 22 min' },
  { id: 1045, customer_name: 'Aline Tossou', phone: '+229 96 55 11 88', items_summary: 'Carpaccio Bœuf, Crevettes Grillées', total_amount: 28000, payment_method: 'cash', status: 'delivered', order_time: 'Il y a 38 min' },
  { id: 1044, customer_name: 'Jean-Baptiste K.', phone: '+229 90 77 66 55', items_summary: 'Menu Signature 4 Services ×3', total_amount: 95000, payment_method: 'mtn', status: 'pending', order_time: 'Il y a 45 min' },
  { id: 1043, customer_name: 'Fatou Diallo', phone: '+229 61 22 33 44', items_summary: 'Poulet Yassa ×1, Jus de Grenadine ×2', total_amount: 14500, payment_method: 'moov', status: 'delivered', order_time: 'Il y a 1h 10min' },
])

const liveReservationsList = ref([
  { id: 201, customer_name: 'Général Bio', phone: '+229 97 00 11 22', email: 'bio.g@gov.bj', time: '19:30', table_num: '04 (Salon VIP)', people_count: 6, special_request: 'Champagne à l\'arrivée, service discret', status: 'confirmed' },
  { id: 202, customer_name: 'Marcelle Adjovi', phone: '+229 96 44 55 66', email: 'm.adjovi@gmail.com', time: '20:00', table_num: '12 (Terrasse)', people_count: 2, special_request: 'Anniversaire de mariage (table avec bougies)', status: 'confirmed' },
  { id: 203, customer_name: 'Stéphane Houndété', phone: '+229 95 33 22 11', email: '', time: '20:30', table_num: '08 (Centrale)', people_count: 4, special_request: 'Préférence terrasse si météo clémente', status: 'pending' },
  { id: 204, customer_name: 'Dr. Clarisse Gomez', phone: '+229 67 88 99 00', email: 'dr.clarisse@yahoo.fr', time: '21:00', table_num: '02', people_count: 3, special_request: '', status: 'confirmed' },
])

const filteredOrders = computed(() => {
  return liveOrdersList.value.filter(o => {
    const matchesSearch = !searchQuery.value || 
      o.customer_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      o.items_summary.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      String(o.id).includes(searchQuery.value)

    const matchesStatus = selectedOrderStatus.value === 'all' || o.status === selectedOrderStatus.value
    return matchesSearch && matchesStatus
  })
})

const filteredReservations = computed(() => {
  return liveReservationsList.value.filter(r => {
    if (!searchQuery.value) return true
    const q = searchQuery.value.toLowerCase()
    return r.customer_name.toLowerCase().includes(q) || r.time.includes(q) || (r.special_request && r.special_request.toLowerCase().includes(q))
  })
})

const getInitials = (name) => {
  if (!name) return 'EM'
  const parts = name.trim().split(' ')
  if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase()
  return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
}

const formatFCFA = (val) => {
  return Number(val).toLocaleString('fr-FR')
}

const getPaymentMethodLabel = (method) => {
  const map = {
    mtn: 'MTN MoMo',
    moov: 'Moov Money',
    card: 'Carte Visa/Mastercard',
    cash: 'Espèces à Livraison'
  }
  return map[method] || method
}

const getReservationStatusLabel = (st) => {
  const map = {
    confirmed: 'Confirmée',
    pending: 'En attente',
    cancelled: 'Annulée'
  }
  return map[st] || st
}

const updateOrderStatus = (order) => {
  showToast(`Statut de la commande #CMD-${String(order.id).padStart(4, '0')} mis à jour.`)
}

const confirmReservation = (res) => {
  res.status = 'confirmed'
  showToast(`Réservation de ${res.customer_name} confirmée avec succès.`)
}

const viewOrderDetails = (order) => {
  showToast(`Fiche de commande #CMD-${order.id} consultée.`)
}

const viewReservation = (res) => {
  showToast(`Détails de la table ${res.table_num} affichés.`)
}

// ── Live Activity Log ───────────────────────────────────────
const liveActivityLog = ref([
  { type: 'order', message: 'Nouvelle commande VIP reçue de Dr. Aurel Dossou (32 000 FCFA)', timeAgo: 'Il y a 2 min' },
  { type: 'kitchen', message: 'Chef Carlos a validé la cuisson du Tilapia Royal pour Table 4', timeAgo: 'Il y a 8 min' },
  { type: 'reservation', message: 'Table VIP n°4 confirmée pour le Général Bio (6 couverts)', timeAgo: 'Il y a 18 min' },
  { type: 'review', message: 'Nouvel avis 6 étoiles déposé : "Une symphonie de saveurs !"', timeAgo: 'Il y a 32 min' },
  { type: 'payment', message: 'Encaissement MTN MoMo validé (+45 000 FCFA) pour CMD-1046', timeAgo: 'Il y a 50 min' },
])

const getActivityIcon = (type) => {
  switch (type) {
    case 'order': return ShoppingBag
    case 'kitchen': return Utensils
    case 'reservation': return CalendarDays
    case 'review': return Star
    case 'payment': return TrendingUp
    default: return Check
  }
}

// ── Quick Actions ───────────────────────────────────────────
const refreshData = () => {
  isRefreshing.value = true
  setTimeout(() => {
    isRefreshing.value = false
    renderMainChart()
    renderDonutChart()
    showToast('Flux opérationnel synchronisé avec le serveur en direct !')
  }, 750)
}

const exportReport = () => {
  showToast('Rapport financier & opérationnel exporté en format PDF / CSV.')
}

const openQuickOrderModal = () => {
  showToast('Ouverture du panneau de commande rapide VIP.')
}

// ── Lifecycle Hooks ─────────────────────────────────────────
onMounted(() => {
  updateClock()
  clockTimer = setInterval(updateClock, 1000)
  
  setTimeout(() => {
    renderMainChart()
    renderDonutChart()
  }, 100)
})

onUnmounted(() => {
  if (clockTimer) clearInterval(clockTimer)
  if (mainChartInstance) mainChartInstance.destroy()
  if (donutChartInstance) donutChartInstance.destroy()
})
</script>

<style scoped>
@import '../../styles/admin-tokens.css';

/* ── LAYOUT PRINCIPAL ── (voir admin-layout.css) */

/* Atmospheric Ambient Glows */
.ambient-glow {
  position: fixed;
  pointer-events: none;
  border-radius: 50%;
  filter: blur(140px);
  z-index: 0;
  opacity: 0.12;
}

.ambient-glow--gold {
  width: 550px;
  height: 550px;
  top: -100px;
  right: 5%;
  background: radial-gradient(circle, var(--gold) 0%, transparent 70%);
}

.ambient-glow--blue {
  width: 450px;
  height: 450px;
  bottom: 10%;
  left: 15%;
  background: radial-gradient(circle, #3B82F6 0%, transparent 70%);
  opacity: 0.08;
}

/* ══════════════════════════════════════════════════════════════ */
/* 1. COMMAND HEADER                                              */
/* ══════════════════════════════════════════════════════════════ */
.cmd-header {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 1.5rem;
  padding-bottom: 0.5rem;
}

.cmd-header__left {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.cmd-header__status-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.08);
  padding: 4px 12px;
  border-radius: var(--r-full);
  font-size: 11px;
  font-weight: 600;
  width: fit-content;
  color: var(--text-2);
}

.pulse-indicator {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: var(--success);
  animation: pulse-live 2s infinite;
}

.status-time {
  color: var(--gold);
  font-family: var(--font-mono);
}

.cmd-header__title {
  font-family: var(--font-display);
  font-size: 2.25rem;
  font-weight: 700;
  letter-spacing: -0.02em;
  line-height: 1.15;
}

.cmd-header__subtitle {
  font-size: 13px;
  color: var(--text-3);
}

.cmd-header__right {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.period-switcher {
  display: flex;
  align-items: center;
  background: rgba(19, 19, 23, 0.8);
  padding: 3px;
  border-radius: var(--r-md);
  border: 1px solid var(--border);
}

.period-btn {
  background: transparent;
  border: none;
  color: var(--text-3);
  font-size: 12px;
  font-weight: 600;
  padding: 6px 13px;
  border-radius: var(--r-sm);
  cursor: pointer;
  transition: all var(--t-fast);
}

.period-btn:hover {
  color: var(--text);
}

.period-btn--active {
  background: rgba(255, 255, 255, 0.08);
  color: var(--gold);
  border: 1px solid rgba(212, 175, 55, 0.25);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.cmd-header__actions {
  display: flex;
  align-items: center;
  gap: 0.65rem;
}

.btn-action-ghost {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  background: rgba(19, 19, 23, 0.7);
  border: 1px solid var(--border);
  color: var(--text-2);
  padding: 8px 14px;
  border-radius: var(--r-md);
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all var(--t);
}

.btn-action-ghost:hover {
  background: rgba(255, 255, 255, 0.06);
  border-color: var(--border-hover);
  color: var(--text);
}

.btn-action--spinning .btn-icon {
  animation: spin 0.8s linear infinite;
}

.btn-action-gold {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  background: var(--gold-gradient);
  border: none;
  color: #09090B;
  padding: 8px 18px;
  border-radius: var(--r-md);
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 4px 16px rgba(212, 175, 55, 0.25);
  transition: all var(--t);
}

.btn-action-gold:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 24px rgba(212, 175, 55, 0.4);
}

/* ══════════════════════════════════════════════════════════════ */
/* 2. KPI GRID                                                    */
/* ══════════════════════════════════════════════════════════════ */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.25rem;
}

@media (max-width: 1380px) {
  .kpi-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 720px) {
  .kpi-grid { grid-template-columns: 1fr; }
}

/* ══════════════════════════════════════════════════════════════ */
/* 3. ANALYTICS GRID                                              */
/* ══════════════════════════════════════════════════════════════ */
.analytics-grid {
  display: grid;
  grid-template-columns: 1.65fr 1fr;
  gap: 1.5rem;
}

@media (max-width: 1280px) {
  .analytics-grid { grid-template-columns: 1fr; }
}

.analytics-panel {
  background: rgba(19, 19, 23, 0.7);
  backdrop-filter: blur(24px);
  -webkit-backdrop-filter: blur(24px);
  border: 1px solid var(--border);
  border-radius: var(--r-xl);
  padding: 1.5rem 1.65rem;
  box-shadow: var(--sh);
  display: flex;
  flex-direction: column;
}

.panel-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.25rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.panel-eyebrow {
  font-size: 11px;
  font-weight: 600;
  color: var(--gold);
  text-transform: uppercase;
  letter-spacing: 0.08em;
  display: block;
  margin-bottom: 3px;
}

.panel-title {
  font-family: var(--font-display);
  font-size: 1.35rem;
  font-weight: 700;
  letter-spacing: -0.01em;
}

.chart-mode-pills {
  display: flex;
  background: rgba(0, 0, 0, 0.4);
  padding: 3px;
  border-radius: var(--r-sm);
  border: 1px solid rgba(255, 255, 255, 0.06);
}

.chart-pill {
  background: transparent;
  border: none;
  font-size: 11px;
  font-weight: 600;
  color: var(--text-3);
  padding: 5px 11px;
  border-radius: var(--r-xs);
  cursor: pointer;
  transition: all var(--t-fast);
}

.chart-pill--active {
  background: rgba(255, 255, 255, 0.08);
  color: var(--text);
  box-shadow: 0 1px 4px rgba(0,0,0,0.4);
}

.panel-body--chart {
  flex: 1;
  min-height: 310px;
}

.chart-canvas-container {
  width: 100%;
  height: 310px;
  position: relative;
}

/* Donut & Best-Sellers Layout */
.panel-body--donut-flex {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.donut-container {
  display: grid;
  grid-template-columns: 140px 1fr;
  align-items: center;
  gap: 1.25rem;
  padding-bottom: 1.25rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.donut-canvas-wrap {
  width: 140px;
  height: 140px;
  position: relative;
}

.donut-center-metric {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  pointer-events: none;
}

.donut-center-metric__num {
  font-size: 20px;
  font-weight: 800;
  color: var(--text);
  font-variant-numeric: tabular-nums;
  line-height: 1;
}

.donut-center-metric__lbl {
  font-size: 9px;
  color: var(--text-3);
  text-transform: uppercase;
  margin-top: 3px;
  font-weight: 600;
}

.donut-legend-grid {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.legend-chip {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 11px;
}

.legend-chip__dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  flex-shrink: 0;
}

.legend-chip__name {
  color: var(--text-2);
  flex: 1;
}

.legend-chip__pct {
  font-weight: 700;
  color: var(--text);
  font-family: var(--font-mono);
}

/* Leaderboard */
.top-dishes-leaderboard {
  display: flex;
  flex-direction: column;
  gap: 0.85rem;
}

.leaderboard-title {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  color: var(--gold);
  letter-spacing: 0.04em;
}

.dishes-ranking-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.dish-rank-item {
  display: flex;
  align-items: center;
  gap: 12px;
}

.dish-rank-badge {
  width: 24px;
  height: 24px;
  border-radius: var(--r-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 10px;
  font-weight: 800;
  font-family: var(--font-mono);
  background: rgba(255, 255, 255, 0.05);
  color: var(--text-3);
  flex-shrink: 0;
}

.dish-rank-badge--1 { background: rgba(212, 175, 55, 0.2); color: var(--gold); border: 1px solid rgba(212, 175, 55, 0.4); }
.dish-rank-badge--2 { background: rgba(147, 197, 253, 0.2); color: #93C5FD; }
.dish-rank-badge--3 { background: rgba(196, 181, 253, 0.2); color: #C4B5FD; }

.dish-rank-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.dish-rank-header {
  display: flex;
  justify-content: space-between;
  font-size: 12px;
}

.dish-rank-name {
  color: var(--text);
  font-weight: 600;
}

.dish-rank-val {
  color: var(--text-3);
  font-size: 11px;
}

.dish-rank-bar-track {
  width: 100%;
  height: 4px;
  background: rgba(255, 255, 255, 0.06);
  border-radius: var(--r-full);
  overflow: hidden;
}

.dish-rank-bar-fill {
  height: 100%;
  background: var(--gold-gradient);
  border-radius: var(--r-full);
}

/* ══════════════════════════════════════════════════════════════ */
/* 4. OPERATIONS HUB & LIVE ACTIVITY                              */
/* ══════════════════════════════════════════════════════════════ */
.ops-grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 1.5rem;
}

@media (max-width: 1380px) {
  .ops-grid { grid-template-columns: 1fr; }
}

.ops-panel {
  background: rgba(19, 19, 23, 0.7);
  backdrop-filter: blur(24px);
  -webkit-backdrop-filter: blur(24px);
  border: 1px solid var(--border);
  border-radius: var(--r-xl);
  padding: 1.5rem;
  box-shadow: var(--sh);
  display: flex;
  flex-direction: column;
}

.panel-header--with-tabs {
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
  padding-bottom: 1.25rem;
  margin-bottom: 1.25rem;
}

.ops-tabs {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.ops-tab {
  display: flex;
  align-items: center;
  gap: 8px;
  background: transparent;
  border: none;
  color: var(--text-3);
  font-size: 13px;
  font-weight: 700;
  padding: 8px 14px;
  border-radius: var(--r-md);
  cursor: pointer;
  transition: all var(--t);
}

.ops-tab:hover {
  color: var(--text);
  background: rgba(255, 255, 255, 0.03);
}

.ops-tab--active {
  background: rgba(212, 175, 55, 0.12);
  color: var(--gold);
  border: 1px solid rgba(212, 175, 55, 0.3);
}

.ops-tab__count {
  background: rgba(255, 255, 255, 0.08);
  font-size: 11px;
  padding: 1px 7px;
  border-radius: var(--r-full);
  font-family: var(--font-mono);
}

.ops-tab--active .ops-tab__count {
  background: var(--gold);
  color: #09090B;
  font-weight: 800;
}

.ops-filters {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.75rem;
}

.table-search-box {
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(0, 0, 0, 0.45);
  border: 1px solid var(--border);
  padding: 6px 12px;
  border-radius: var(--r-md);
  width: 220px;
}

.table-search-box:focus-within {
  border-color: var(--gold);
  box-shadow: 0 0 12px rgba(212, 175, 55, 0.2);
}

.search-icon {
  color: var(--text-3);
}

.table-search-input {
  background: transparent;
  border: none;
  outline: none;
  font-size: 12px;
  color: var(--text);
  width: 100%;
}

.table-search-input::placeholder {
  color: var(--text-4);
}

.search-clear-btn {
  background: transparent;
  border: none;
  color: var(--text-3);
  cursor: pointer;
  font-size: 10px;
}

.status-filter-pills {
  display: flex;
  background: rgba(0, 0, 0, 0.4);
  padding: 2px;
  border-radius: var(--r-sm);
  border: 1px solid rgba(255, 255, 255, 0.05);
}

.filter-pill {
  background: transparent;
  border: none;
  font-size: 11px;
  font-weight: 600;
  color: var(--text-3);
  padding: 5px 9px;
  border-radius: var(--r-xs);
  cursor: pointer;
  transition: all var(--t-fast);
}

.filter-pill--active {
  background: rgba(255, 255, 255, 0.08);
  color: var(--text);
}

/* Luxury Table Styling */
.table-wrapper {
  overflow-x: auto;
  min-height: 320px;
}

.luxury-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0 6px;
  font-size: 13px;
}

.luxury-table thead th {
  padding: 8px 12px;
  color: var(--text-3);
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  text-align: left;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}

.table-row {
  background: rgba(255, 255, 255, 0.02);
  transition: all var(--t-fast);
}

.table-row:hover {
  background: rgba(255, 255, 255, 0.05);
  transform: scale(1.002);
}

.table-row td {
  padding: 12px;
  vertical-align: middle;
  border-top: 1px solid rgba(255, 255, 255, 0.03);
  border-bottom: 1px solid rgba(255, 255, 255, 0.03);
}

.table-row td:first-child {
  border-left: 1px solid rgba(255, 255, 255, 0.03);
  border-radius: var(--r-md) 0 0 var(--r-md);
}

.table-row td:last-child {
  border-right: 1px solid rgba(255, 255, 255, 0.03);
  border-radius: 0 var(--r-md) var(--r-md) 0;
}

.client-cell {
  display: flex;
  align-items: center;
  gap: 10px;
}

.client-avatar {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 11px;
  font-weight: 800;
  flex-shrink: 0;
}

.client-avatar--0 { background: rgba(212, 175, 55, 0.15); color: var(--gold); border: 1px solid rgba(212, 175, 55, 0.3); }
.client-avatar--1 { background: rgba(59, 130, 246, 0.15); color: #60A5FA; border: 1px solid rgba(59, 130, 246, 0.3); }
.client-avatar--2 { background: rgba(168, 85, 247, 0.15); color: #C084FC; border: 1px solid rgba(168, 85, 247, 0.3); }
.client-avatar--3 { background: rgba(16, 185, 129, 0.15); color: #34D399; border: 1px solid rgba(16, 185, 129, 0.3); }
.client-avatar--gold { background: var(--gold-gradient); color: #09090B; font-weight: 900; }

.client-info {
  display: flex;
  flex-direction: column;
}

.client-name {
  font-weight: 700;
  color: var(--text);
}

.client-meta {
  font-size: 11px;
  color: var(--text-3);
  font-family: var(--font-mono);
}

.items-cell {
  display: flex;
  flex-direction: column;
  max-width: 260px;
}

.items-title {
  color: var(--text-2);
  font-weight: 500;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.items-time {
  font-size: 11px;
  color: var(--text-3);
}

.amount-cell {
  display: flex;
  align-items: baseline;
  gap: 4px;
}

.amount-num {
  font-weight: 800;
  color: var(--text);
  font-variant-numeric: tabular-nums;
}

.amount-cur {
  font-size: 10px;
  color: var(--gold);
  font-weight: 600;
}

/* Badges */
.pay-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 11px;
  font-weight: 600;
  padding: 3px 8px;
  border-radius: var(--r-sm);
}

.pay-dot {
  width: 5px;
  height: 5px;
  border-radius: 50%;
  background: currentColor;
}

.pay-badge--mtn  { background: var(--mtn-bg);  color: var(--mtn-color); }
.pay-badge--moov { background: var(--moov-bg); color: #38BDF8; }
.pay-badge--card { background: var(--card-bg); color: #C084FC; }
.pay-badge--cash { background: var(--cash-bg); color: #34D399; }

/* Status Dropdown */
.status-select {
  padding: 5px 10px;
  border-radius: var(--r-md);
  font-size: 11px;
  font-weight: 700;
  border: 1px solid transparent;
  outline: none;
  cursor: pointer;
  background: rgba(0, 0, 0, 0.4);
}

.status-select--pending   { background: rgba(245, 158, 11, 0.12); color: #FBBF24; border-color: rgba(245, 158, 11, 0.3); }
.status-select--preparing { background: rgba(59, 130, 246, 0.12); color: #60A5FA; border-color: rgba(59, 130, 246, 0.3); }
.status-select--ready     { background: rgba(168, 85, 247, 0.12); color: #C084FC; border-color: rgba(168, 85, 247, 0.3); }
.status-select--on_route  { background: rgba(212, 175, 55, 0.15); color: var(--gold); border-color: rgba(212, 175, 55, 0.35); }
.status-select--delivered { background: rgba(16, 185, 129, 0.12); color: #34D399; border-color: rgba(16, 185, 129, 0.3); }
.status-select--cancelled { background: rgba(239, 68, 68, 0.12); color: #F87171; border-color: rgba(239, 68, 68, 0.3); }

.covers-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  background: rgba(255, 255, 255, 0.05);
  padding: 4px 8px;
  border-radius: var(--r-sm);
  font-size: 11px;
  font-weight: 600;
  color: var(--text-2);
}

.status-badge {
  padding: 3px 8px;
  border-radius: var(--r-sm);
  font-size: 11px;
  font-weight: 700;
}

.status-badge--confirmed { background: var(--success-bg); color: var(--success); }
.status-badge--pending   { background: var(--warning-bg); color: var(--warning); }

.btn-detail {
  display: inline-flex;
  align-items: center;
  gap: 3px;
  background: transparent;
  border: none;
  color: var(--text-3);
  font-size: 11px;
  font-weight: 600;
  cursor: pointer;
  padding: 4px 8px;
  border-radius: var(--r-sm);
  transition: all var(--t-fast);
}

.btn-detail:hover {
  color: var(--gold);
  background: rgba(212, 175, 55, 0.08);
}

.res-action-group {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 6px;
}

.btn-res-confirm {
  background: rgba(16, 185, 129, 0.15);
  border: 1px solid rgba(16, 185, 129, 0.3);
  color: #34D399;
  font-size: 11px;
  font-weight: 700;
  padding: 4px 9px;
  border-radius: var(--r-sm);
  cursor: pointer;
  transition: all var(--t-fast);
}

.btn-res-confirm:hover {
  background: rgba(16, 185, 129, 0.3);
}

.btn-res-view {
  background: rgba(255, 255, 255, 0.05);
  border: none;
  color: var(--text-3);
  padding: 5px;
  border-radius: var(--r-sm);
  cursor: pointer;
}

.panel-table-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.05);
}

.footer-count {
  font-size: 12px;
  color: var(--text-3);
}

.link-full-view {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  color: var(--gold);
  font-size: 12px;
  font-weight: 700;
  text-decoration: none;
  transition: all var(--t-fast);
}

.link-full-view:hover {
  text-decoration: underline;
  transform: translateX(2px);
}

/* Empty State */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem 1rem;
  color: var(--text-3);
}

.empty-icon {
  margin-bottom: 0.5rem;
  opacity: 0.4;
}

.empty-title {
  font-weight: 600;
  color: var(--text-2);
}

.empty-sub {
  font-size: 12px;
}

/* Right Column: Alerts & Live Activity Timeline */
.live-status-chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: rgba(16, 185, 129, 0.12);
  border: 1px solid rgba(16, 185, 129, 0.3);
  color: #34D399;
  font-size: 10px;
  font-weight: 800;
  padding: 3px 8px;
  border-radius: var(--r-full);
  font-family: var(--font-mono);
}

.live-dot-pulse {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #34D399;
  animation: pulse-live 1.5s infinite;
}

.alert-stream-box {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
}

.alert-card {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  padding: 12px;
  border-radius: var(--r-md);
  border: 1px solid transparent;
}

.alert-card--gold {
  background: rgba(212, 175, 55, 0.08);
  border-color: rgba(212, 175, 55, 0.25);
  color: var(--gold);
}

.alert-card--warning {
  background: rgba(245, 158, 11, 0.08);
  border-color: rgba(245, 158, 11, 0.25);
  color: #FBBF24;
}

.alert-card__headline {
  font-size: 12px;
  font-weight: 700;
  color: var(--text);
}

.alert-card__sub {
  font-size: 11px;
  color: var(--text-3);
  margin-top: 2px;
}

.activity-timeline {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.activity-item {
  display: flex;
  gap: 12px;
  position: relative;
}

.activity-item__track {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 22px;
  flex-shrink: 0;
}

.activity-item__pip {
  width: 22px;
  height: 22px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.06);
  color: var(--text-2);
  z-index: 1;
}

.activity-item__pip--order       { background: rgba(59, 130, 246, 0.15); color: #60A5FA; }
.activity-item__pip--kitchen     { background: rgba(245, 158, 11, 0.15); color: #FBBF24; }
.activity-item__pip--reservation { background: rgba(16, 185, 129, 0.15); color: #34D399; }
.activity-item__pip--review      { background: rgba(212, 175, 55, 0.15); color: var(--gold); }
.activity-item__pip--payment     { background: rgba(168, 85, 247, 0.15); color: #C084FC; }

.activity-item__line {
  flex: 1;
  width: 2px;
  background: rgba(255, 255, 255, 0.06);
  margin: 4px 0;
}

.activity-item__body {
  padding-bottom: 1rem;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.activity-item__text {
  font-size: 12px;
  color: var(--text-2);
  line-height: 1.4;
}

.activity-item__time {
  font-size: 10px;
  color: var(--text-3);
  font-family: var(--font-mono);
}

/* Luxury Toast Feedback */
.luxury-toast {
  position: fixed;
  bottom: 24px;
  right: 24px;
  background: rgba(19, 19, 23, 0.95);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(212, 175, 55, 0.4);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.6), 0 0 20px rgba(212, 175, 55, 0.2);
  padding: 12px 18px;
  border-radius: var(--r-md);
  color: var(--text);
  font-size: 13px;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 10px;
  z-index: 9999;
}

.toast-fade-enter-active,
.toast-fade-leave-active {
  transition: all 0.3s ease;
}

.toast-fade-enter-from,
.toast-fade-leave-to {
  opacity: 0;
  transform: translateY(12px);
}
</style>
