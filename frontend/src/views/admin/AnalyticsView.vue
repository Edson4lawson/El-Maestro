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
        <!-- 1. HEADER & PERIOD SELECTOR                                    -->
        <!-- ══════════════════════════════════════════════════════════════ -->
        <header class="cmd-header">
          <div class="cmd-header__left">
            <span class="panel-eyebrow">Intelligence Financière & Big Data</span>
            <h1 class="cmd-header__title">
              Analytique & <span class="text-gold-gradient">Performances</span>
            </h1>
            <p class="cmd-header__subtitle">
              Indicateurs de rentabilité, mix-produit et prévisions de croissance
            </p>
          </div>

          <div class="cmd-header__right">
            <!-- Period Selector Tabs -->
            <div class="period-switcher">
              <button 
                v-for="p in periods" 
                :key="p.id" 
                class="period-btn" 
                :class="{ 'period-btn--active': activePeriod === p.id }"
                @click="setPeriod(p.id)"
              >
                {{ p.label }}
              </button>
            </div>

            <!-- Export -->
            <button class="btn-action-ghost" @click="exportAnalytics">
              <Download :size="16" />
              <span>Exporter Rapport BI</span>
            </button>
          </div>
        </header>

        <!-- ══════════════════════════════════════════════════════════════ -->
        <!-- 2. EXECUTIVE METRICS GRID                                      -->
        <!-- ══════════════════════════════════════════════════════════════ -->
        <section class="kpi-grid">
          <div class="ana-kpi-card ana-kpi-card--gold">
            <div class="ana-kpi-card__head">
              <div class="ana-kpi-icon"><TrendingUp :size="20" /></div>
              <div class="ana-trend ana-trend--up">
                <ArrowUpRight :size="14" />
                <span>+16.8%</span>
              </div>
            </div>
            <div class="ana-kpi-card__body">
              <span class="ana-kpi-label">Chiffre d'Affaires Brut</span>
              <div class="ana-kpi-val">
                {{ formatFCFA(currentMetrics.grossRevenue) }} <span class="cur">FCFA</span>
              </div>
            </div>
            <div class="ana-kpi-card__foot">
              <span>Marge nette estimée : <strong>68.4%</strong></span>
            </div>
          </div>

          <div class="ana-kpi-card ana-kpi-card--blue">
            <div class="ana-kpi-card__head">
              <div class="ana-kpi-icon"><ShoppingBag :size="20" /></div>
              <div class="ana-trend ana-trend--up">
                <ArrowUpRight :size="14" />
                <span>+8.5%</span>
              </div>
            </div>
            <div class="ana-kpi-card__body">
              <span class="ana-kpi-label">Volume Total Commandes</span>
              <div class="ana-kpi-val">
                {{ currentMetrics.totalOrders.toLocaleString('fr-FR') }} <span class="cur">commandes</span>
              </div>
            </div>
            <div class="ana-kpi-card__foot">
              <span>Taux de conversion : <strong>14.2%</strong></span>
            </div>
          </div>

          <div class="ana-kpi-card ana-kpi-card--purple">
            <div class="ana-kpi-card__head">
              <div class="ana-kpi-icon"><DollarSign :size="20" /></div>
              <div class="ana-trend ana-trend--up">
                <ArrowUpRight :size="14" />
                <span>+4.2%</span>
              </div>
            </div>
            <div class="ana-kpi-card__body">
              <span class="ana-kpi-label">Ticket Moyen Par Table</span>
              <div class="ana-kpi-val">
                {{ formatFCFA(currentMetrics.avgTicket) }} <span class="cur">FCFA</span>
              </div>
            </div>
            <div class="ana-kpi-card__foot">
              <span>Objectif cible : <strong>25 000 FCFA</strong></span>
            </div>
          </div>

          <div class="ana-kpi-card ana-kpi-card--green">
            <div class="ana-kpi-card__head">
              <div class="ana-kpi-icon"><Users :size="20" /></div>
              <div class="ana-trend ana-trend--up">
                <ArrowUpRight :size="14" />
                <span>+24.0%</span>
              </div>
            </div>
            <div class="ana-kpi-card__body">
              <span class="ana-kpi-label">Nouveaux Clients Fidélité</span>
              <div class="ana-kpi-val">
                {{ currentMetrics.newCustomers }} <span class="cur">membres</span>
              </div>
            </div>
            <div class="ana-kpi-card__foot">
              <span>Rétention à 30j : <strong>78%</strong></span>
            </div>
          </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════ -->
        <!-- 3. ADVANCED INTERACTIVE CHARTS                                 -->
        <!-- ══════════════════════════════════════════════════════════════ -->
        <section class="analytics-charts-grid">
          
          <!-- Revenue & Growth Bar/Line Chart -->
          <div class="analytics-panel analytics-panel--growth">
            <div class="panel-header">
              <div class="panel-header__info">
                <span class="panel-eyebrow">Trajectoire Financière</span>
                <h2 class="panel-title">Revenus Comparatifs & Objectifs</h2>
              </div>
              
              <div class="chart-legend-custom">
                <span class="legend-item"><span class="legend-dot" style="background:#D4AF37;"></span> Réel (FCFA)</span>
                <span class="legend-item"><span class="legend-dot" style="background:rgba(255,255,255,0.25);"></span> Objectif Prévisionnel</span>
              </div>
            </div>

            <div class="panel-body panel-body--chart">
              <div class="chart-canvas-container">
                <canvas ref="growthChartRef"></canvas>
              </div>
            </div>
          </div>

          <!-- Acquisition Channels Donut -->
          <div class="analytics-panel analytics-panel--channels">
            <div class="panel-header">
              <div class="panel-header__info">
                <span class="panel-eyebrow">Canaux de Vente</span>
                <h2 class="panel-title">Origine des Commandes</h2>
              </div>
            </div>

            <div class="panel-body panel-body--channels-flex">
              <div class="donut-wrap">
                <canvas ref="channelDonutRef"></canvas>
                <div class="donut-center-info">
                  <span class="donut-center-num">100%</span>
                  <span class="donut-center-sub">Couverture</span>
                </div>
              </div>

              <div class="channel-stats-list">
                <div v-for="(ch, idx) in channelsData" :key="idx" class="channel-stat-row">
                  <div class="channel-info">
                    <span class="channel-color-dot" :style="{ backgroundColor: ch.color }"></span>
                    <span class="channel-name">{{ ch.name }}</span>
                  </div>
                  <div class="channel-nums">
                    <span class="channel-amount">{{ formatFCFA(ch.amount) }} FCFA</span>
                    <span class="channel-pct">{{ ch.pct }}%</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </section>

        <!-- ══════════════════════════════════════════════════════════════ -->
        <!-- 4. MENU PROFITABILITY MATRIX & HOURLY HEATMAP                  -->
        <!-- ══════════════════════════════════════════════════════════════ -->
        <section class="bottom-analytics-grid">
          
          <!-- Profitability Leaderboard -->
          <div class="analytics-panel">
            <div class="panel-header">
              <div class="panel-header__info">
                <span class="panel-eyebrow">Matrice de Rentabilité</span>
                <h2 class="panel-title">Plats Générateurs de Valeur</h2>
              </div>
              <span class="badge-gold">Top 5 Rentabilité</span>
            </div>

            <div class="profit-table-wrap">
              <table class="profit-table">
                <thead>
                  <tr>
                    <th>Plat / Recette</th>
                    <th>Catégorie</th>
                    <th>Prix Vente</th>
                    <th>Marge Brute</th>
                    <th>Ventes</th>
                    <th class="text-right">Total Généré</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(dish, dIdx) in profitableDishes" :key="dIdx" class="profit-row">
                    <td>
                      <div class="profit-dish-cell">
                        <span class="profit-rank">#{{ dIdx + 1 }}</span>
                        <span class="profit-dish-name">{{ dish.name }}</span>
                      </div>
                    </td>
                    <td><span class="dish-cat-pill">{{ dish.category }}</span></td>
                    <td class="font-mono">{{ formatFCFA(dish.price) }} FCFA</td>
                    <td>
                      <div class="margin-bar-cell">
                        <span class="margin-pct text-gold">{{ dish.margin }}%</span>
                        <div class="margin-bar-track">
                          <div class="margin-bar-fill" :style="{ width: `${dish.margin}%` }"></div>
                        </div>
                      </div>
                    </td>
                    <td class="font-mono font-bold">{{ dish.sales }}</td>
                    <td class="text-right font-mono font-bold text-gold">{{ formatFCFA(dish.total) }} FCFA</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Peak Hours Analysis -->
          <div class="analytics-panel">
            <div class="panel-header">
              <div class="panel-header__info">
                <span class="panel-eyebrow">Flux Temporel</span>
                <h2 class="panel-title">Affluence par Créneau Horaire</h2>
              </div>
            </div>

            <div class="peak-hours-visual">
              <div v-for="slot in peakHoursSlots" :key="slot.hour" class="peak-slot">
                <div class="peak-slot__bar-wrap">
                  <div class="peak-slot__bar" :style="{ height: `${slot.intensity}%` }" :class="{ 'peak-slot__bar--rush': slot.isRush }">
                    <span class="peak-slot__tooltip">{{ slot.orders }} cmd</span>
                  </div>
                </div>
                <span class="peak-slot__label" :class="{ 'text-gold font-bold': slot.isRush }">{{ slot.hour }}</span>
              </div>
            </div>
            
            <div class="peak-summary-foot">
              <div class="peak-insight">
                <Flame :size="16" class="text-gold" />
                <span>Pic d'affluence maximal : <strong>20h00 - 21h30</strong> (Moyenne 42 commandes/heure).</span>
              </div>
            </div>
          </div>

        </section>

      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Chart, registerables } from 'chart.js'
import { 
  TrendingUp, ShoppingBag, Users, DollarSign, Download, ArrowUpRight, 
  Flame 
} from 'lucide-vue-next'

import AdminSidebar from '../../components/admin/AdminSidebar.vue'
import AdminTopBar from '../../components/admin/AdminTopBar.vue'

Chart.register(...registerables)

const sidebarCollapsed = ref(false)
const activePeriod = ref('month')

const periods = [
  { id: 'day',     label: "Aujourd'hui" },
  { id: 'week',    label: 'Cette Semaine' },
  { id: 'month',   label: 'Ce Mois' },
  { id: 'quarter', label: 'Trimestre' },
]

const setPeriod = (pId) => {
  activePeriod.value = pId
  renderGrowthChart()
}

const formatFCFA = (val) => Number(val || 0).toLocaleString('fr-FR')

const exportAnalytics = () => {
  alert('Export du rapport analytique exécutif au format PDF / Excel en cours de téléchargement.')
}

// ── Metrics Data ────────────────────────────────────────────
const currentMetrics = computed(() => {
  const map = {
    day:     { grossRevenue: 845000,   totalOrders: 32,  avgTicket: 26400, newCustomers: 12 },
    week:    { grossRevenue: 5120000,  totalOrders: 218, avgTicket: 23480, newCustomers: 45 },
    month:   { grossRevenue: 18450000, totalOrders: 780, avgTicket: 23650, newCustomers: 142 },
    quarter: { grossRevenue: 54200000, totalOrders: 2340,avgTicket: 23160, newCustomers: 410 },
  }
  return map[activePeriod.value] || map.month
})

// ── Channels Donut Data ─────────────────────────────────────
const channelsData = [
  { name: 'Commande En Ligne (Site Web)', amount: 9600000, pct: 52, color: '#D4AF37' },
  { name: 'Sur Place & Réservations',     amount: 5500000, pct: 30, color: '#3B82F6' },
  { name: 'Livraison Express Maestro',    amount: 3350000, pct: 18, color: '#10B981' },
]

// ── Profitable Dishes Leaderboard ───────────────────────────
const profitableDishes = [
  { name: 'Poulet Braisé Maestro Prestige', category: 'Plats Résistants', price: 6500, margin: 74, sales: 342, total: 2223000 },
  { name: 'Poisson Grillé Royal (Capitaine)', category: 'Plats Résistants', price: 9500, margin: 71, sales: 228, total: 2166000 },
  { name: 'Pâtes Carbonara Truffe Noire', category: 'Plats Résistants', price: 8500, margin: 68, sales: 184, total: 1564000 },
  { name: 'Foie Gras Poêlé aux Épices', category: 'Entrées Nobles', price: 12000, margin: 65, sales: 98, total: 1176000 },
  { name: 'Mousse au Chocolat Gold 24k', category: 'Desserts', price: 4500, margin: 82, sales: 215, total: 967500 },
]

// ── Peak Hours Slots ────────────────────────────────────────
const peakHoursSlots = [
  { hour: '11h', intensity: 25, orders: 8, isRush: false },
  { hour: '12h', intensity: 75, orders: 28, isRush: true },
  { hour: '13h', intensity: 88, orders: 34, isRush: true },
  { hour: '14h', intensity: 45, orders: 16, isRush: false },
  { hour: '15h', intensity: 15, orders: 4, isRush: false },
  { hour: '18h', intensity: 35, orders: 12, isRush: false },
  { hour: '19h', intensity: 70, orders: 26, isRush: true },
  { hour: '20h', intensity: 98, orders: 42, isRush: true },
  { hour: '21h', intensity: 92, orders: 38, isRush: true },
  { hour: '22h', intensity: 55, orders: 20, isRush: false },
]

// ── Chart.js Instances ──────────────────────────────────────
const growthChartRef = ref(null)
const channelDonutRef = ref(null)
let growthChartInstance = null
let channelDonutInstance = null

const renderGrowthChart = () => {
  if (!growthChartRef.value) return
  if (growthChartInstance) growthChartInstance.destroy()

  const labelsMap = {
    day:     ['11h', '12h', '13h', '14h', '15h', '18h', '19h', '20h', '21h', '22h'],
    week:    ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
    month:   ['Semaine 1', 'Semaine 2', 'Semaine 3', 'Semaine 4'],
    quarter: ['Mois 1', 'Mois 2', 'Mois 3'],
  }

  const dataMap = {
    day:     [45000, 120000, 185000, 95000, 40000, 80000, 190000, 260000, 210000, 120000],
    week:    [520000, 680000, 610000, 740000, 920000, 1180000, 960000],
    month:   [3800000, 4500000, 4900000, 5250000],
    quarter: [16500000, 18200000, 19500000],
  }

  const targetMap = {
    day:     [40000, 100000, 160000, 80000, 30000, 70000, 170000, 240000, 190000, 100000],
    week:    [500000, 600000, 600000, 700000, 850000, 1000000, 900000],
    month:   [3500000, 4200000, 4600000, 5000000],
    quarter: [15000000, 17000000, 18000000],
  }

  const labels = labelsMap[activePeriod.value] || labelsMap.month
  const actuals = dataMap[activePeriod.value] || dataMap.month
  const targets = targetMap[activePeriod.value] || targetMap.month

  growthChartInstance = new Chart(growthChartRef.value, {
    type: 'bar',
    data: {
      labels,
      datasets: [
        {
          label: 'Chiffre d\'Affaires Réel',
          data: actuals,
          backgroundColor: '#D4AF37',
          borderRadius: 6,
          barPercentage: 0.55
        },
        {
          label: 'Objectif Budgétaire',
          data: targets,
          backgroundColor: 'rgba(255, 255, 255, 0.12)',
          borderRadius: 6,
          barPercentage: 0.55
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: 'rgba(19, 19, 23, 0.95)',
          titleColor: '#F3E5AB',
          bodyColor: '#FBF9F5',
          borderColor: 'rgba(212, 175, 55, 0.35)',
          borderWidth: 1,
          padding: 12,
          cornerRadius: 10,
          callbacks: {
            label: (ctx) => ` ${ctx.dataset.label} : ${ctx.parsed.y.toLocaleString('fr-FR')} FCFA`
          }
        }
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: { color: 'rgba(251, 249, 245, 0.5)', font: { family: 'Inter', size: 11 } }
        },
        y: {
          grid: { color: 'rgba(255, 255, 255, 0.05)' },
          ticks: {
            color: 'rgba(251, 249, 245, 0.5)',
            font: { family: 'Inter', size: 11 },
            callback: (v) => `${(v / 1000).toFixed(0)}k`
          },
          beginAtZero: true
        }
      }
    }
  })
}

const renderChannelDonut = () => {
  if (!channelDonutRef.value) return
  if (channelDonutInstance) channelDonutInstance.destroy()

  channelDonutInstance = new Chart(channelDonutRef.value, {
    type: 'doughnut',
    data: {
      labels: channelsData.map(c => c.name),
      datasets: [{
        data: channelsData.map(c => c.pct),
        backgroundColor: channelsData.map(c => c.color),
        borderColor: '#131317',
        borderWidth: 3,
        hoverOffset: 6
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '75%',
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

onMounted(() => {
  setTimeout(() => {
    renderGrowthChart()
    renderChannelDonut()
  }, 100)
})

onUnmounted(() => {
  if (growthChartInstance) growthChartInstance.destroy()
  if (channelDonutInstance) channelDonutInstance.destroy()
})
</script>

<style scoped>
@import '../../styles/admin-tokens.css';

.ambient-glow {
  position: fixed;
  pointer-events: none;
  border-radius: 50%;
  filter: blur(140px);
  z-index: 0;
  opacity: 0.12;
}
.ambient-glow--gold { width: 550px; height: 550px; top: -100px; right: 5%; background: radial-gradient(circle, var(--gold) 0%, transparent 70%); }
.ambient-glow--blue { width: 450px; height: 450px; bottom: 10%; left: 15%; background: radial-gradient(circle, #3B82F6 0%, transparent 70%); opacity: 0.08; }

/* Header */
.cmd-header {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 1.5rem;
  padding-bottom: 0.5rem;
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

.period-btn:hover { color: var(--text); }
.period-btn--active {
  background: rgba(255, 255, 255, 0.08);
  color: var(--gold);
  border: 1px solid rgba(212, 175, 55, 0.25);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
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
  color: var(--text);
  border-color: var(--border-hover);
}

/* KPI Grid */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.25rem;
}
@media (max-width: 1380px) { .kpi-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 720px)  { .kpi-grid { grid-template-columns: 1fr; } }

.ana-kpi-card {
  background: rgba(19, 19, 23, 0.7);
  backdrop-filter: blur(20px);
  border: 1px solid var(--border);
  border-radius: var(--r-xl);
  padding: 1.4rem;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  min-height: 150px;
  box-shadow: var(--sh);
  transition: all var(--t);
}

.ana-kpi-card:hover {
  transform: translateY(-3px);
  border-color: var(--border-hover);
}

.ana-kpi-card__head {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.ana-kpi-icon {
  width: 38px;
  height: 38px;
  border-radius: var(--r-md);
  display: flex;
  align-items: center;
  justify-content: center;
}

.ana-kpi-card--gold .ana-kpi-icon   { background: rgba(212, 175, 55, 0.12); color: var(--gold); border: 1px solid rgba(212, 175, 55, 0.3); }
.ana-kpi-card--blue .ana-kpi-icon   { background: rgba(59, 130, 246, 0.12); color: #60A5FA; border: 1px solid rgba(59, 130, 246, 0.3); }
.ana-kpi-card--purple .ana-kpi-icon { background: rgba(168, 85, 247, 0.12); color: #C084FC; border: 1px solid rgba(168, 85, 247, 0.3); }
.ana-kpi-card--green .ana-kpi-icon  { background: rgba(16, 185, 129, 0.12); color: #34D399; border: 1px solid rgba(16, 185, 129, 0.3); }

.ana-trend {
  display: inline-flex;
  align-items: center;
  gap: 3px;
  font-size: 11px;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: var(--r-full);
}
.ana-trend--up { background: rgba(16, 185, 129, 0.12); color: #34D399; border: 1px solid rgba(16, 185, 129, 0.25); }

.ana-kpi-card__body { margin: 0.85rem 0 0.4rem; }
.ana-kpi-label { font-size: 11px; color: var(--text-3); text-transform: uppercase; font-weight: 600; letter-spacing: 0.04em; }
.ana-kpi-val { font-size: 26px; font-weight: 800; color: var(--text); font-variant-numeric: tabular-nums; margin-top: 4px; }
.ana-kpi-val .cur { font-size: 13px; font-weight: 600; color: var(--gold); }

.ana-kpi-card__foot {
  padding-top: 0.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.04);
  font-size: 11px;
  color: var(--text-3);
}

/* Charts Grid */
.analytics-charts-grid {
  display: grid;
  grid-template-columns: 1.75fr 1fr;
  gap: 1.5rem;
}
@media (max-width: 1280px) { .analytics-charts-grid { grid-template-columns: 1fr; } }

.analytics-panel {
  background: rgba(19, 19, 23, 0.7);
  backdrop-filter: blur(24px);
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
}

.panel-title {
  font-family: var(--font-display);
  font-size: 1.35rem;
  font-weight: 700;
}

.chart-legend-custom {
  display: flex;
  align-items: center;
  gap: 1rem;
  font-size: 11px;
  color: var(--text-2);
}

.legend-item { display: flex; align-items: center; gap: 6px; }
.legend-dot { width: 8px; height: 8px; border-radius: 50%; }

.chart-canvas-container {
  width: 100%;
  height: 290px;
  position: relative;
}

/* Donut & Channels */
.panel-body--channels-flex {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.donut-wrap {
  width: 140px;
  height: 140px;
  margin: 0 auto;
  position: relative;
}

.donut-center-info {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.donut-center-num { font-size: 20px; font-weight: 800; color: var(--text); }
.donut-center-sub { font-size: 9px; color: var(--text-3); text-transform: uppercase; font-weight: 600; }

.channel-stats-list {
  display: flex;
  flex-direction: column;
  gap: 0.65rem;
}

.channel-stat-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 12px;
  padding: 8px 12px;
  background: rgba(255, 255, 255, 0.02);
  border-radius: var(--r-sm);
  border: 1px solid rgba(255, 255, 255, 0.03);
}

.channel-info { display: flex; align-items: center; gap: 8px; }
.channel-color-dot { width: 8px; height: 8px; border-radius: 50%; }
.channel-name { color: var(--text-2); }
.channel-nums { display: flex; align-items: center; gap: 10px; font-family: var(--font-mono); }
.channel-amount { font-weight: 700; color: var(--text); }
.channel-pct { color: var(--gold); font-weight: 800; }

/* Bottom Analytics Grid */
.bottom-analytics-grid {
  display: grid;
  grid-template-columns: 1.65fr 1fr;
  gap: 1.5rem;
}
@media (max-width: 1280px) { .bottom-analytics-grid { grid-template-columns: 1fr; } }

.badge-gold {
  background: rgba(212, 175, 55, 0.12);
  border: 1px solid rgba(212, 175, 55, 0.3);
  color: var(--gold);
  font-size: 11px;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: var(--r-full);
}

.profit-table-wrap { overflow-x: auto; }
.profit-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0 4px;
  font-size: 12px;
}
.profit-table th {
  padding: 8px 12px;
  color: var(--text-3);
  font-size: 11px;
  text-transform: uppercase;
  font-weight: 700;
  text-align: left;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}
.profit-row td {
  padding: 10px 12px;
  background: rgba(255, 255, 255, 0.02);
  vertical-align: middle;
}
.profit-row td:first-child { border-radius: var(--r-sm) 0 0 var(--r-sm); }
.profit-row td:last-child  { border-radius: 0 var(--r-sm) var(--r-sm) 0; }

.profit-dish-cell { display: flex; align-items: center; gap: 8px; }
.profit-rank { font-weight: 800; color: var(--gold); font-family: var(--font-mono); font-size: 11px; }
.profit-dish-name { font-weight: 600; color: var(--text); }

.dish-cat-pill {
  font-size: 10px;
  background: rgba(255, 255, 255, 0.05);
  padding: 2px 8px;
  border-radius: var(--r-full);
  color: var(--text-3);
}

.margin-bar-cell { display: flex; align-items: center; gap: 8px; }
.margin-pct { font-weight: 700; font-family: var(--font-mono); width: 32px; }
.margin-bar-track { width: 60px; height: 4px; background: rgba(255, 255, 255, 0.08); border-radius: var(--r-full); overflow: hidden; }
.margin-bar-fill { height: 100%; background: var(--gold); border-radius: var(--r-full); }

/* Peak Hours Heatmap */
.peak-hours-visual {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 8px;
  height: 180px;
  padding: 1rem 0 0.5rem;
}

.peak-slot {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  flex: 1;
  height: 100%;
  justify-content: flex-end;
}

.peak-slot__bar-wrap {
  width: 100%;
  height: 140px;
  display: flex;
  align-items: flex-end;
  justify-content: center;
}

.peak-slot__bar {
  width: 80%;
  max-width: 22px;
  background: rgba(255, 255, 255, 0.12);
  border-radius: 4px 4px 0 0;
  transition: all var(--t);
  position: relative;
  cursor: pointer;
}

.peak-slot__bar--rush {
  background: var(--gold-gradient);
  box-shadow: 0 0 12px rgba(212, 175, 55, 0.35);
}

.peak-slot__bar:hover {
  filter: brightness(1.2);
}

.peak-slot__tooltip {
  position: absolute;
  top: -24px;
  left: 50%;
  transform: translateX(-50%);
  background: #09090B;
  color: var(--gold);
  border: 1px solid var(--border-gold);
  font-size: 9px;
  font-weight: 700;
  padding: 2px 5px;
  border-radius: var(--r-xs);
  white-space: nowrap;
  opacity: 0;
  pointer-events: none;
  transition: opacity var(--t-fast);
}

.peak-slot__bar:hover .peak-slot__tooltip {
  opacity: 1;
}

.peak-slot__label {
  font-size: 11px;
  color: var(--text-3);
  font-family: var(--font-mono);
}

.peak-summary-foot {
  margin-top: 1rem;
  padding-top: 0.85rem;
  border-top: 1px solid rgba(255, 255, 255, 0.05);
}

.peak-insight {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12px;
  color: var(--text-2);
}
</style>
