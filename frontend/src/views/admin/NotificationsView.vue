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
        <!-- 1. HEADER                                                      -->
        <!-- ══════════════════════════════════════════════════════════════ -->
        <header class="cmd-header">
          <div class="cmd-header__left">
            <span class="panel-eyebrow">Centre de Vigilance & Alertes</span>
            <h1 class="cmd-header__title">
              Notifications & <span class="text-gold-gradient">Alertes Directes</span>
            </h1>
            <p class="cmd-header__subtitle">
              {{ unreadCount }} alertes non traitées • Flux synchronisé en temps réel
            </p>
          </div>

          <div class="cmd-header__right">
            <button class="btn-action-ghost" @click="markAllAsRead" :disabled="unreadCount === 0">
              <CheckCheck :size="16" />
              <span>Tout marquer comme lu</span>
            </button>
            <button class="btn-action-ghost btn-action-ghost--danger" @click="clearAllNotifications" :disabled="notifications.length === 0">
              <Trash2 :size="16" />
              <span>Effacer l'historique</span>
            </button>
          </div>
        </header>

        <!-- ══════════════════════════════════════════════════════════════ -->
        <!-- 2. SUMMARY COUNTERS                                            -->
        <!-- ══════════════════════════════════════════════════════════════ -->
        <section class="notif-summary-grid">
          <div class="notif-card notif-card--unread" @click="filterType = 'unread'">
            <div class="notif-card__icon"><Bell :size="18" /></div>
            <div class="notif-card__info">
              <span class="notif-card__val">{{ unreadCount }}</span>
              <span class="notif-card__lbl">Non lues</span>
            </div>
          </div>

          <div class="notif-card notif-card--order" @click="filterType = 'order'">
            <div class="notif-card__icon"><ShoppingBag :size="18" /></div>
            <div class="notif-card__info">
              <span class="notif-card__val">{{ getCountByType('order') }}</span>
              <span class="notif-card__lbl">Commandes VIP</span>
            </div>
          </div>

          <div class="notif-card notif-card--res" @click="filterType = 'reservation'">
            <div class="notif-card__icon"><CalendarDays :size="18" /></div>
            <div class="notif-card__info">
              <span class="notif-card__val">{{ getCountByType('reservation') }}</span>
              <span class="notif-card__lbl">Réservations</span>
            </div>
          </div>

          <div class="notif-card notif-card--alert" @click="filterType = 'kitchen'">
            <div class="notif-card__icon"><AlertTriangle :size="18" /></div>
            <div class="notif-card__info">
              <span class="notif-card__val">{{ getCountByType('kitchen') }}</span>
              <span class="notif-card__lbl">Alertes Cuisine & Stocks</span>
            </div>
          </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════ -->
        <!-- 3. FILTERS & SEARCH                                            -->
        <!-- ══════════════════════════════════════════════════════════════ -->
        <div class="notif-filter-bar">
          <div class="filter-pills-wrap">
            <button 
              class="filter-pill" 
              :class="{ 'filter-pill--active': filterType === 'all' }"
              @click="filterType = 'all'"
            >
              Toutes ({{ notifications.length }})
            </button>
            <button 
              class="filter-pill" 
              :class="{ 'filter-pill--active': filterType === 'unread' }"
              @click="filterType = 'unread'"
            >
              Non lues ({{ unreadCount }})
            </button>
            <button 
              class="filter-pill" 
              :class="{ 'filter-pill--active': filterType === 'order' }"
              @click="filterType = 'order'"
            >
              Commandes
            </button>
            <button 
              class="filter-pill" 
              :class="{ 'filter-pill--active': filterType === 'reservation' }"
              @click="filterType = 'reservation'"
            >
              Réservations
            </button>
            <button 
              class="filter-pill" 
              :class="{ 'filter-pill--active': filterType === 'kitchen' }"
              @click="filterType = 'kitchen'"
            >
              Cuisine & Stocks
            </button>
            <button 
              class="filter-pill" 
              :class="{ 'filter-pill--active': filterType === 'review' }"
              @click="filterType = 'review'"
            >
              Avis Clients
            </button>
          </div>

          <div class="notif-search-box">
            <Search :size="14" class="search-icon" />
            <input 
              v-model="searchQuery" 
              type="text" 
              placeholder="Rechercher une alerte..." 
              class="notif-search-input" 
            />
            <button v-if="searchQuery" @click="searchQuery = ''" class="search-clear-btn">✕</button>
          </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════════ -->
        <!-- 4. NOTIFICATIONS LIST                                          -->
        <!-- ══════════════════════════════════════════════════════════════ -->
        <div class="notif-list-container">
          <div 
            v-for="item in filteredNotifications" 
            :key="item.id" 
            class="notif-row"
            :class="{ 'notif-row--unread': !item.read }"
            @click="markAsRead(item)"
          >
            <!-- Type Icon Pip -->
            <div class="notif-row__icon-box" :class="`notif-icon--${item.type}`">
              <component :is="getIconComponent(item.type)" :size="16" />
            </div>

            <!-- Body -->
            <div class="notif-row__body">
              <div class="notif-row__head">
                <h3 class="notif-row__title">{{ item.title }}</h3>
                <span class="notif-row__time">{{ item.time }}</span>
              </div>
              <p class="notif-row__text">{{ item.message }}</p>

              <!-- Optional Action Bar in Notification -->
              <div v-if="item.actionLink" class="notif-row__actions" @click.stop>
                <RouterLink :to="item.actionLink" class="btn-notif-action">
                  {{ item.actionLabel || 'Consulter' }}
                  <ChevronRight :size="13" />
                </RouterLink>
              </div>
            </div>

            <!-- Status Indicator & Dismiss -->
            <div class="notif-row__tools" @click.stop>
              <button 
                class="btn-notif-status" 
                :title="item.read ? 'Marquer comme non lu' : 'Marquer comme lu'"
                @click="toggleItemRead(item)"
              >
                <span class="read-dot" :class="{ 'read-dot--unread': !item.read }"></span>
              </button>
              <button class="btn-notif-del" @click="removeNotification(item.id)" title="Supprimer">
                <X :size="14" />
              </button>
            </div>
          </div>

          <!-- Empty State -->
          <div v-if="filteredNotifications.length === 0" class="empty-notif-box">
            <Inbox :size="40" class="empty-icon" />
            <h3 class="empty-title">Aucune notification dans cette vue</h3>
            <p class="empty-sub">Toutes les alertes ont été traitées ou correspondent à vos filtres.</p>
          </div>
        </div>

      </div>
    </main>

    <!-- Toast Feedback -->
    <transition name="toast-fade">
      <div v-if="toastMessage" class="luxury-toast">
        <CheckCircle2 :size="18" class="text-gold" />
        <span>{{ toastMessage }}</span>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { RouterLink } from 'vue-router'
import { 
  Bell, ShoppingBag, CalendarDays, AlertTriangle, Star, CheckCheck, 
  Trash2, Search, ChevronRight, X, Inbox, CheckCircle2, DollarSign, Utensils 
} from 'lucide-vue-next'

import AdminSidebar from '../../components/admin/AdminSidebar.vue'
import AdminTopBar from '../../components/admin/AdminTopBar.vue'

const sidebarCollapsed = ref(false)
const filterType = ref('all')
const searchQuery = ref('')
const toastMessage = ref('')

const showToast = (msg) => {
  toastMessage.value = msg
  setTimeout(() => { toastMessage.value = '' }, 3500)
}

// ── Notifications Data ──────────────────────────────────────
const notifications = ref([
  {
    id: 1,
    type: 'order',
    title: 'Nouvelle Commande VIP #CMD-1048',
    message: 'Dr. Aurel Dossou a validé une commande de 32 000 FCFA (Poulet Braisé Maestro ×2, Cocktail Ananas ×2). Livraison demandée à Cadjèhoun.',
    time: 'Il y a 4 min',
    read: false,
    actionLink: '/admin/orders',
    actionLabel: 'Traiter la Commande'
  },
  {
    id: 2,
    type: 'reservation',
    title: 'Table VIP n°4 Confirmée',
    message: 'Réservation pour 6 couverts enregistrée pour le Général Bio à 19h30. Protocole Dégustation Prestige.',
    time: 'Il y a 18 min',
    read: false,
    actionLink: '/admin/reservations',
    actionLabel: 'Voir la Réservation'
  },
  {
    id: 3,
    type: 'kitchen',
    title: 'Alerte Stock : Filet de Capitaine Royal',
    message: 'Le stock en chambre froide est descendu à 4 portions. Pensez à réapprovisionner auprès du fournisseur de pêche artisanale.',
    time: 'Il y a 35 min',
    read: false,
    actionLink: '/admin/menu',
    actionLabel: 'Gérer la Carte'
  },
  {
    id: 4,
    type: 'review',
    title: 'Nouvel Avis d\'Excellence 6★',
    message: 'Mme Sophie Lawson a noté 6/6 : "Un dîner féérique, le poisson braisé était d\'une tendreté absolue !"',
    time: 'Il y a 1h 15min',
    read: true,
    actionLink: '/admin/dashboard',
    actionLabel: 'Voir le Tableau de Bord'
  },
  {
    id: 5,
    type: 'payment',
    title: 'Encaissement Validé (+45 000 FCFA)',
    message: 'Paiement mobile MTN MoMo reçu avec succès pour la commande #CMD-1046 (Carlos Mensah).',
    time: 'Il y a 2h',
    read: true,
    actionLink: '/admin/orders',
    actionLabel: 'Vérifier la Facture'
  },
  {
    id: 6,
    type: 'kitchen',
    title: 'Mise en Place Terminée',
    message: 'La brigade de cuisine a clôturé la préparation des marinades et sauces pour le Service du Soir.',
    time: 'Il y a 3h',
    read: true,
    actionLink: null
  }
])

const unreadCount = computed(() => notifications.value.filter(n => !n.read).length)

const getCountByType = (type) => notifications.value.filter(n => n.type === type).length

const filteredNotifications = computed(() => {
  return notifications.value.filter(item => {
    // Filter Type
    let matchesType = true
    if (filterType.value === 'unread') matchesType = !item.read
    else if (filterType.value !== 'all') matchesType = item.type === filterType.value

    // Search
    let matchesSearch = true
    if (searchQuery.value) {
      const q = searchQuery.value.toLowerCase()
      matchesSearch = item.title.toLowerCase().includes(q) || item.message.toLowerCase().includes(q)
    }

    return matchesType && matchesSearch
  })
})

const getIconComponent = (type) => {
  switch (type) {
    case 'order': return ShoppingBag
    case 'reservation': return CalendarDays
    case 'kitchen': return AlertTriangle
    case 'review': return Star
    case 'payment': return DollarSign
    default: return Bell
  }
}

// ── Actions ─────────────────────────────────────────────────
const markAsRead = (item) => {
  if (!item.read) {
    item.read = true
  }
}

const toggleItemRead = (item) => {
  item.read = !item.read
  showToast(item.read ? 'Alerte marquée comme lue' : 'Alerte marquée comme non lue')
}

const markAllAsRead = () => {
  notifications.value.forEach(n => n.read = true)
  showToast('Toutes les notifications ont été marquées comme lues.')
}

const removeNotification = (id) => {
  notifications.value = notifications.value.filter(n => n.id !== id)
  showToast('Notification supprimée.')
}

const clearAllNotifications = () => {
  if (confirm('Voulez-vous vraiment effacer tout l\'historique des notifications ?')) {
    notifications.value = []
    showToast('Historique des alertes effacé.')
  }
}
</script>

<style scoped>
@import '../../styles/admin-tokens.css';

.dash-content {
  max-width: 1440px;
  gap: 1.75rem;
}

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

.cmd-header__subtitle { font-size: 13px; color: var(--text-3); }

.cmd-header__right {
  display: flex;
  align-items: center;
  gap: 0.75rem;
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

.btn-action-ghost:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.06);
  color: var(--text);
  border-color: var(--border-hover);
}

.btn-action-ghost:disabled { opacity: 0.4; cursor: not-allowed; }

.btn-action-ghost--danger:hover:not(:disabled) {
  background: rgba(239, 68, 68, 0.12);
  color: #F87171;
  border-color: rgba(239, 68, 68, 0.3);
}

/* Summary Grid */
.notif-summary-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.25rem;
}
@media (max-width: 900px) { .notif-summary-grid { grid-template-columns: repeat(2, 1fr); } }

.notif-card {
  background: rgba(19, 19, 23, 0.7);
  backdrop-filter: blur(20px);
  border: 1px solid var(--border);
  border-radius: var(--r-xl);
  padding: 1.15rem 1.4rem;
  display: flex;
  align-items: center;
  gap: 14px;
  box-shadow: var(--sh);
  cursor: pointer;
  transition: all var(--t);
}

.notif-card:hover {
  transform: translateY(-2px);
  border-color: var(--border-hover);
}

.notif-card__icon {
  width: 40px;
  height: 40px;
  border-radius: var(--r-md);
  display: flex;
  align-items: center;
  justify-content: center;
}

.notif-card--unread .notif-card__icon { background: rgba(212, 175, 55, 0.15); color: var(--gold); border: 1px solid rgba(212, 175, 55, 0.35); }
.notif-card--order .notif-card__icon  { background: rgba(59, 130, 246, 0.15); color: #60A5FA; border: 1px solid rgba(59, 130, 246, 0.35); }
.notif-card--res .notif-card__icon    { background: rgba(16, 185, 129, 0.15); color: #34D399; border: 1px solid rgba(16, 185, 129, 0.35); }
.notif-card--alert .notif-card__icon  { background: rgba(245, 158, 11, 0.15); color: #FBBF24; border: 1px solid rgba(245, 158, 11, 0.35); }

.notif-card__info { display: flex; flex-direction: column; gap: 2px; }
.notif-card__val  { font-size: 20px; font-weight: 800; color: var(--text); font-variant-numeric: tabular-nums; }
.notif-card__lbl  { font-size: 11px; color: var(--text-3); font-weight: 600; text-transform: uppercase; }

/* Filter Bar */
.notif-filter-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 1rem;
}

.filter-pills-wrap {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.filter-pill {
  background: rgba(19, 19, 23, 0.7);
  border: 1px solid var(--border);
  color: var(--text-3);
  padding: 6px 13px;
  border-radius: var(--r-full);
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all var(--t-fast);
}

.filter-pill:hover { color: var(--text); }

.filter-pill--active {
  background: rgba(212, 175, 55, 0.15);
  color: var(--gold);
  border-color: rgba(212, 175, 55, 0.35);
}

.notif-search-box {
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(19, 19, 23, 0.8);
  border: 1px solid var(--border);
  padding: 7px 12px;
  border-radius: var(--r-md);
  width: 240px;
}

.notif-search-input {
  background: transparent;
  border: none;
  outline: none;
  font-size: 12px;
  color: var(--text);
  width: 100%;
}
.notif-search-input::placeholder { color: var(--text-4); }
.search-clear-btn { background: transparent; border: none; color: var(--text-3); cursor: pointer; font-size: 10px; }

/* Notifications List */
.notif-list-container {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.notif-row {
  background: rgba(19, 19, 23, 0.6);
  backdrop-filter: blur(20px);
  border: 1px solid var(--border);
  border-radius: var(--r-lg);
  padding: 1.25rem 1.4rem;
  display: flex;
  align-items: flex-start;
  gap: 16px;
  box-shadow: var(--sh-sm);
  transition: all var(--t-fast);
  cursor: pointer;
}

.notif-row:hover {
  background: rgba(255, 255, 255, 0.03);
  border-color: var(--border-hover);
}

.notif-row--unread {
  border-color: rgba(212, 175, 55, 0.3);
  background: rgba(212, 175, 55, 0.03);
}

.notif-row__icon-box {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  margin-top: 2px;
}

.notif-icon--order       { background: rgba(59, 130, 246, 0.15); color: #60A5FA; }
.notif-icon--reservation { background: rgba(16, 185, 129, 0.15); color: #34D399; }
.notif-icon--kitchen     { background: rgba(245, 158, 11, 0.15); color: #FBBF24; }
.notif-icon--review      { background: rgba(212, 175, 55, 0.15); color: var(--gold); }
.notif-icon--payment     { background: rgba(168, 85, 247, 0.15); color: #C084FC; }

.notif-row__body {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.notif-row__head {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.notif-row__title {
  font-size: 14px;
  font-weight: 700;
  color: var(--text);
}

.notif-row__time {
  font-size: 11px;
  color: var(--text-3);
  font-family: var(--font-mono);
}

.notif-row__text {
  font-size: 13px;
  color: var(--text-2);
  line-height: 1.45;
}

.notif-row__actions {
  margin-top: 6px;
}

.btn-notif-action {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 11px;
  font-weight: 700;
  color: var(--gold);
  text-decoration: none;
}

.btn-notif-action:hover { text-decoration: underline; }

.notif-row__tools {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-top: 4px;
}

.btn-notif-status {
  background: transparent;
  border: none;
  padding: 4px;
  cursor: pointer;
}

.read-dot {
  display: block;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.15);
}

.read-dot--unread {
  background: var(--gold);
  box-shadow: 0 0 8px var(--gold);
}

.btn-notif-del {
  background: transparent;
  border: none;
  color: var(--text-3);
  padding: 4px;
  border-radius: var(--r-sm);
  cursor: pointer;
}

.btn-notif-del:hover { color: #F87171; background: rgba(239, 68, 68, 0.1); }

/* Empty */
.empty-notif-box {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 1rem;
  background: rgba(19, 19, 23, 0.5);
  border-radius: var(--r-xl);
  border: 1px dashed var(--border);
  text-align: center;
}

.empty-icon { color: var(--gold); opacity: 0.5; margin-bottom: 0.75rem; }
.empty-title { font-family: var(--font-display); font-size: 1.25rem; font-weight: 700; margin-bottom: 0.25rem; }
.empty-sub { font-size: 12px; color: var(--text-3); }

/* Luxury Toast */
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
.toast-fade-enter-active, .toast-fade-leave-active { transition: all 0.3s ease; }
.toast-fade-enter-from, .toast-fade-leave-to { opacity: 0; transform: translateY(12px); }
</style>
