<template>
  <div class="dash">
    <AdminSidebar :collapsed="sidebarCollapsed" @toggle="sidebarCollapsed = !sidebarCollapsed" />
    <main class="dash__main">
      <AdminTopBar :sidebar-collapsed="sidebarCollapsed" @toggle-sidebar="sidebarCollapsed = !sidebarCollapsed" />
      <!-- Ambient background orbs -->
      <div class="orb orb--1" aria-hidden="true"></div>
      <div class="orb orb--2" aria-hidden="true"></div>

      <div class="dash__inner">
        <!-- ── PAGE HEADER ── -->
        <div class="ph">
          <div class="ph__left">
            <p class="ph__greet">Gestion des clients</p>
            <h1 class="ph__title">Réservations</h1>
            <p class="ph__date">{{ filteredReservations.length }} réservations trouvées</p>
          </div>
          <div class="ph__right">
            <div class="search-bar">
              <Search class="search-icon" />
              <input v-model="searchQuery" type="text" placeholder="Nom, email ou téléphone..." class="search-input" />
            </div>
            <button class="btn-ghost">
              <Download class="btn-icon" />
              Exporter CSV
            </button>
            <button class="btn-gold-sm">
              <Plus class="btn-icon" />
              Nouvelle Réservation
            </button>
          </div>
        </div>

        <!-- ── FILTERS ── -->
        <div class="filters">
          <div class="filter-item">
            <label>Statut</label>
            <select v-model="statusFilter">
              <option value="">Tous les statuts</option>
              <option value="pending">En attente</option>
              <option value="confirmed">Confirmée</option>
              <option value="cancelled">Annulée</option>
              <option value="completed">Terminée</option>
            </select>
          </div>
          <div class="filter-item">
            <label>Période</label>
            <select v-model="dateFilter">
              <option value="today">Aujourd'hui</option>
              <option value="tomorrow">Demain</option>
              <option value="week">Cette semaine</option>
              <option value="month">Ce mois</option>
              <option value="all">Historique complet</option>
            </select>
          </div>
          <div class="filter-item">
            <label>Taille du groupe</label>
            <select v-model="guestsFilter">
              <option value="">Tous</option>
              <option value="1-2">1-2 pers.</option>
              <option value="3-4">3-4 pers.</option>
              <option value="5-8">5-8 pers.</option>
              <option value="8+">8+ pers.</option>
            </select>
          </div>
          <div class="filter-spacer"></div>
          <div class="view-toggles">
            <button :class="{ active: viewMode === 'list' }" @click="viewMode = 'list'"><List class="v-icon" /></button>
            <button :class="{ active: viewMode === 'calendar' }" @click="viewMode = 'calendar'"><Calendar class="v-icon" /></button>
          </div>
        </div>

        <!-- ── STATS ROW ── -->
        <div class="stats-mini-grid">
          <div class="mini-card">
            <div class="mini-card__icon mini-card__icon--blue"><Users class="m-icon" /></div>
            <div>
              <p class="mini-card__label">Aujourd'hui</p>
              <h3 class="mini-card__value">{{ todayCount }}</h3>
            </div>
          </div>
          <div class="mini-card">
            <div class="mini-card__icon mini-card__icon--green"><CheckCircle class="m-icon" /></div>
            <div>
              <p class="mini-card__label">Confirmées</p>
              <h3 class="mini-card__value">{{ confirmedCount }}</h3>
            </div>
          </div>
          <div class="mini-card">
            <div class="mini-card__icon mini-card__icon--gold"><Star class="m-icon" /></div>
            <div>
              <p class="mini-card__label">Total Couverts</p>
              <h3 class="mini-card__value">{{ totalGuests }}</h3>
            </div>
          </div>
          <div class="mini-card">
            <div class="mini-card__icon mini-card__icon--purple"><Clock class="m-icon" /></div>
            <div>
              <p class="mini-card__label">À venir</p>
              <h3 class="mini-card__value">{{ upcomingCount }}</h3>
            </div>
          </div>
        </div>

        <!-- ── MAIN CONTENT ── -->
        <div class="panel panel--table">
          <div v-if="viewMode === 'list'" class="table-container">
            <table class="rt">
              <thead>
                <tr>
                  <th @click="sortBy('date')">Date & Heure <ArrowUpDown class="sort-ico" /></th>
                  <th @click="sortBy('customer')">Client <ArrowUpDown class="sort-ico" /></th>
                  <th>Couverts</th>
                  <th>Statut</th>
                  <th class="txt-right">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="r in filteredReservations" :key="r.id" class="rt__row">
                  <td>
                    <div class="rt__date">
                      <span class="rt__day">{{ formatDate(r.date) }}</span>
                      <span class="rt__time">{{ r.time }}</span>
                    </div>
                  </td>
                  <td>
                    <div class="rt__client">
                      <div class="rt__avatar">{{ r.customer_name.charAt(0) }}</div>
                      <div>
                        <div class="rt__name">{{ r.customer_name }}</div>
                        <div class="rt__contact">{{ r.phone }}</div>
                      </div>
                    </div>
                  </td>
                  <td>
                    <div class="rt__guests">
                      <Users class="g-icon" />
                      <span>{{ r.guests }}</span>
                    </div>
                  </td>
                  <td>
                    <span class="badge" :class="`badge--${r.status}`">{{ statusLabels[r.status] }}</span>
                  </td>
                  <td>
                    <div class="rt__actions">
                      <button class="a-btn" @click="viewDetail(r)" title="Détails"><Eye class="a-icon" /></button>
                      <button class="a-btn" @click="editItem(r)" title="Modifier"><Edit3 class="a-icon" /></button>
                      <button class="a-btn a-btn--danger" @click="cancelItem(r)" title="Annuler"><XCircle class="a-icon" /></button>
                    </div>
                  </td>
                </tr>
                <tr v-if="filteredReservations.length === 0">
                  <td colspan="5" class="empty-state">
                    <Inbox class="empty-icon" />
                    <p>Aucune réservation ne correspond à vos critères.</p>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-else class="calendar-placeholder">
            <div class="glass-alert">
              <Calendar class="alert-icon" />
              <h3>Mode Calendrier en cours d'optimisation</h3>
              <p>L'affichage interactif des réservations par mois arrive très bientôt.</p>
              <button class="btn-ghost" @click="viewMode = 'list'">Retour à la liste</button>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- ── DETAIL MODAL ── -->
    <transition name="fade">
      <div v-if="selectedItem" class="modal-overlay" @click="selectedItem = null">
        <div class="modal" @click.stop>
          <div class="modal__head">
            <h2 class="modal__title">Détails Réservation</h2>
            <button class="close-btn" @click="selectedItem = null"><X /></button>
          </div>
          <div class="modal__body">
            <div class="m-grid">
              <div class="m-section">
                <label>Client</label>
                <div class="m-val">{{ selectedItem.customer_name }}</div>
                <div class="m-sub">{{ selectedItem.email }}</div>
                <div class="m-sub">{{ selectedItem.phone }}</div>
              </div>
              <div class="m-section">
                <label>Planification</label>
                <div class="m-val">{{ formatDate(selectedItem.date) }} à {{ selectedItem.time }}</div>
                <div class="m-val">{{ selectedItem.guests }} personnes</div>
              </div>
            </div>
            <div class="m-section mt-6" v-if="selectedItem.special_requests">
              <label>Demandes spéciales</label>
              <div class="m-quote">{{ selectedItem.special_requests }}</div>
            </div>
          </div>
          <div class="modal__foot">
            <button class="btn-ghost" @click="selectedItem = null">Fermer</button>
            <button v-if="selectedItem.status === 'pending'" class="btn-gold-sm" @click="confirmItem(selectedItem)">Confirmer la venue</button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { 
  Search, Plus, Download, Users, CheckCircle, Star, Clock, 
  List, Calendar, ArrowUpDown, Eye, Edit3, XCircle, X, Inbox 
} from 'lucide-vue-next'
import AdminSidebar from '../../components/admin/AdminSidebar.vue'
import AdminTopBar from '../../components/admin/AdminTopBar.vue'

const sidebarCollapsed = ref(false)
const searchQuery = ref('')
const statusFilter = ref('')
const dateFilter = ref('all')
const guestsFilter = ref('')
const viewMode = ref('list')
const selectedItem = ref(null)
const sortField = ref('date')
const sortDir = ref('desc')

const reservations = ref([
  { id: 1, customer_name: 'Marie Konaté', phone: '+229 97 12 34 56', email: 'marie.k@gmail.com', date: '2025-05-14', time: '19:30', guests: 4, status: 'confirmed', special_requests: 'Table près de la fenêtre, allergie aux fruits de mer' },
  { id: 2, customer_name: 'Jean Boco', phone: '+229 95 98 76 54', email: 'jean.b@outlook.com', date: '2025-05-14', time: '20:00', guests: 2, status: 'pending', special_requests: 'Anniversaire de mariage' },
  { id: 3, customer_name: 'Aline Soglo', phone: '+229 91 45 67 89', email: 'aline.s@yahoo.fr', date: '2025-05-15', time: '12:30', guests: 6, status: 'confirmed' },
  { id: 4, customer_name: 'Marc Lawson', phone: '+229 60 11 22 33', email: 'marc.l@gmail.com', date: '2025-05-16', time: '21:00', guests: 3, status: 'cancelled' },
  { id: 5, customer_name: 'Sophie DOSSOU', phone: '+229 90 44 55 66', email: 'sophie.d@gmail.com', date: '2025-05-14', time: '13:00', guests: 8, status: 'completed' }
])

const statusLabels = { pending: 'En attente', confirmed: 'Confirmée', cancelled: 'Annulée', completed: 'Terminée' }

const filteredReservations = computed(() => {
  let list = [...reservations.value]
  
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase()
    list = list.filter(r => r.customer_name.toLowerCase().includes(q) || r.email.toLowerCase().includes(q) || r.phone.includes(q))
  }
  
  if (statusFilter.value) list = list.filter(r => r.status === statusFilter.value)
  
  if (guestsFilter.value) {
    list = list.filter(r => {
      if (guestsFilter.value === '1-2') return r.guests <= 2
      if (guestsFilter.value === '3-4') return r.guests >= 3 && r.guests <= 4
      if (guestsFilter.value === '5-8') return r.guests >= 5 && r.guests <= 8
      if (guestsFilter.value === '8+') return r.guests > 8
      return true
    })
  }

  list.sort((a, b) => {
    let av = a[sortField.value], bv = b[sortField.value]
    if (sortField.value === 'date') {
      av = new Date(a.date + ' ' + a.time); bv = new Date(b.date + ' ' + b.time)
    }
    return sortDir.value === 'asc' ? (av > bv ? 1 : -1) : (av < bv ? 1 : -1)
  })

  return list
})

const todayCount = computed(() => reservations.value.filter(r => r.date === new Date().toISOString().split('T')[0]).length)
const confirmedCount = computed(() => reservations.value.filter(r => r.status === 'confirmed').length)
const totalGuests = computed(() => reservations.value.reduce((s, r) => s + r.guests, 0))
const upcomingCount = computed(() => reservations.value.filter(r => new Date(r.date) >= new Date()).length)

const formatDate = (d) => new Date(d).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short' })
const sortBy = (f) => { if (sortField.value === f) sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'; else { sortField.value = f; sortDir.value = 'asc' } }

const viewDetail = (r) => selectedItem.value = r
const confirmItem = (r) => { r.status = 'confirmed'; selectedItem.value = null }
const cancelItem = (r) => { if (confirm('Annuler cette réservation ?')) r.status = 'cancelled' }
</script>

<style scoped>
@import '../../styles/admin-tokens.css';

.dash { font-family: var(--font-sans); background: #0A0A0A; }

/* Ambient orbs */
.orb { position: fixed; border-radius: 50%; pointer-events: none; filter: blur(100px); z-index: 0; opacity: 0.4; }
.orb--1 { width: 600px; height: 600px; top: -200px; left: 10%; background: radial-gradient(circle, rgba(212,175,55,0.06), transparent 70%); }
.orb--2 { width: 500px; height: 500px; bottom: -100px; right: 5%; background: radial-gradient(circle, rgba(99,102,241,0.04), transparent 70%); }

/* Page Header */
.ph { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: var(--s8); position: relative; z-index: 1; }
.ph__greet { font-size: 11px; color: var(--text-3); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 4px; }
.ph__title { font-family: var(--font-display); font-size: 36px; font-weight: 700; color: var(--text); letter-spacing: -0.02em; line-height: 1; }
.ph__date { font-size: 13px; color: var(--text-3); margin-top: 6px; }
.ph__right { display: flex; gap: var(--s3); align-items: center; }

/* Search Bar */
.search-bar { position: relative; width: 280px; }
.search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 14px; color: var(--text-4); }
.search-input { width: 100%; background: var(--card); border: 1px solid var(--border); border-radius: var(--r); padding: 9px 12px 9px 36px; color: var(--text); font-size: 13px; outline: none; transition: all var(--t); }
.search-input:focus { border-color: var(--gold); box-shadow: 0 0 0 3px rgba(212,175,55,0.05); }

/* Buttons */
.btn-ghost { display: inline-flex; align-items: center; gap: 7px; padding: 9px 16px; border-radius: var(--r); border: 1px solid var(--border); background: transparent; font-size: 12px; font-weight: 500; color: var(--text-2); cursor: pointer; transition: all var(--t); }
.btn-ghost:hover { border-color: var(--gold); color: var(--text); background: rgba(212,175,55,0.04); }
.btn-gold-sm { display: inline-flex; align-items: center; gap: 7px; padding: 9px 18px; border-radius: var(--r); background: linear-gradient(135deg, #D4AF37, #B8860B); border: none; font-size: 12px; font-weight: 700; color: #0A0A0A; cursor: pointer; transition: all var(--t); box-shadow: 0 4px 20px rgba(212,175,55,0.3); }
.btn-gold-sm:hover { transform: translateY(-1px); box-shadow: 0 8px 32px rgba(212,175,55,0.45); }
.btn-icon { width: 14px; height: 14px; }

/* Filters */
.filters { display: flex; align-items: center; gap: 15px; background: var(--card); border: 1px solid var(--border); padding: 12px 20px; border-radius: var(--r-lg); margin-bottom: var(--s6); margin-top: 10px; margin-left: 5px; margin-right: 4px; position: relative; z-index: 1; }
.filter-item { display: flex; flex-direction: column; gap: 4px; }
.filter-item label { font-size: 9px; font-weight: 700; color: var(--text-4); text-transform: uppercase; letter-spacing: 0.1em; }
.filter-item select { background: transparent; border: none; color: var(--text-2); font-size: 13px; font-weight: 600; outline: none; cursor: pointer; padding: 2px 0; border-bottom: 1px solid transparent; transition: all var(--t); }
.filter-item select:hover { color: var(--gold); }
.filter-spacer { flex: 1; }

.view-toggles { display: flex; background: rgba(255,255,255,0.03); padding: 4px; border-radius: var(--r); border: 1px solid var(--border); }
.view-toggles button { width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; background: transparent; border: none; border-radius: 6px; color: var(--text-4); cursor: pointer; transition: all var(--t); }
.view-toggles button.active { background: var(--card-hover); color: var(--gold); box-shadow: 0 2px 8px rgba(0,0,0,0.3); }
.v-icon { width: 16px; height: 16px; }

/* Stats Mini Grid */
.stats-mini-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: var(--s4); margin-bottom: var(--s6); margin-left: 5px; margin-right: 4px; position: relative; z-index: 1; }
.mini-card { background: var(--card); border: 1px solid var(--border); padding: 16px; border-radius: var(--r-lg); display: flex; align-items: center; gap: 16px; transition: border-color var(--t); }
.mini-card:hover { border-color: rgba(212,175,55,0.2); }
.mini-card__icon { width: 44px; height: 44px; border-radius: var(--r); display: flex; align-items: center; justify-content: center; }
.mini-card__icon--blue { background: rgba(59,130,246,0.1); color: #3b82f6; }
.mini-card__icon--green { background: rgba(34,197,94,0.1); color: #22c55e; }
.mini-card__icon--gold { background: rgba(212,175,55,0.1); color: var(--gold); }
.mini-card__icon--purple { background: rgba(168,85,247,0.1); color: #a855f7; }
.m-icon { width: 20px; height: 20px; }
.mini-card__label { font-size: 11px; color: var(--text-3); margin-bottom: 2px; }
.mini-card__value { font-size: 20px; font-weight: 700; color: var(--text); }

/* Table */
.panel--table { position: relative; z-index: 1; overflow: hidden; border-radius: var(--r-xl); background: var(--card); border: 1px solid var(--border); margin-left: 5px; margin-right: 4px; }
.table-container { overflow-x: auto; }
.rt { width: 100%; border-collapse: collapse; }
.rt th { text-align: left; padding: 16px 24px; font-size: 10px; font-weight: 700; color: var(--text-4); text-transform: uppercase; letter-spacing: 0.12em; border-bottom: 1px solid var(--border); cursor: pointer; transition: color var(--t); }
.rt th:hover { color: var(--text-2); }
.sort-ico { width: 12px; height: 12px; display: inline-block; vertical-align: middle; margin-left: 4px; opacity: 0.5; }

.rt__row td { padding: 16px 24px; border-bottom: 1px solid rgba(255,255,255,0.03); background: transparent; transition: background var(--t); }
.rt__row:hover td { background: rgba(255,255,255,0.015); }

.rt__date { display: flex; flex-direction: column; }
.rt__day { font-size: 13px; font-weight: 600; color: var(--text); }
.rt__time { font-size: 11px; color: var(--gold); font-weight: 700; }

.rt__client { display: flex; align-items: center; gap: 12px; }
.rt__avatar { width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, rgba(212,175,55,0.2), rgba(212,175,55,0.05)); display: flex; align-items: center; justify-content: center; color: var(--gold); font-weight: 800; font-size: 12px; }
.rt__name { font-size: 13px; font-weight: 600; color: var(--text); }
.rt__contact { font-size: 11px; color: var(--text-3); }

.rt__guests { display: flex; align-items: center; gap: 6px; font-weight: 700; color: var(--text-2); }
.g-icon { width: 14px; height: 14px; color: var(--text-4); }

.rt__actions { display: flex; gap: 8px; justify-content: flex-end; }
.a-btn { width: 32px; height: 32px; border-radius: 8px; border: 1px solid var(--border); background: rgba(255,255,255,0.02); color: var(--text-3); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all var(--t); }
.a-btn:hover { border-color: var(--gold); color: var(--gold); background: rgba(212,175,55,0.05); }
.a-btn--danger:hover { border-color: #ef4444; color: #ef4444; background: rgba(239,68,68,0.05); }
.a-icon { width: 14px; height: 14px; }

.txt-right { text-align: right; }

.badge { padding: 4px 10px; border-radius: 99px; font-size: 10px; font-weight: 700; display: inline-flex; align-items: center; }
.badge--pending { background: rgba(245,158,11,0.1); color: #f59e0b; }
.badge--confirmed { background: rgba(34,197,94,0.1); color: #22c55e; }
.badge--cancelled { background: rgba(239,68,68,0.1); color: #ef4444; }
.badge--completed { background: rgba(59,130,246,0.1); color: #3b82f6; }

.empty-state { text-align: center; padding: 60px 0; color: var(--text-4); }
.empty-icon { width: 40px; height: 40px; margin: 0 auto 12px; opacity: 0.3; }

/* Calendar Placeholder */
.calendar-placeholder { padding: 80px 40px; display: flex; align-items: center; justify-content: center; }
.glass-alert { background: rgba(255,255,255,0.02); border: 1px dashed var(--border); border-radius: var(--r-xl); padding: 40px; text-align: center; max-width: 400px; }
.alert-icon { width: 48px; height: 48px; color: var(--gold); margin-bottom: var(--s4); opacity: 0.6; }
.glass-alert h3 { font-size: 18px; color: var(--text); margin-bottom: 12px; }
.glass-alert p { font-size: 14px; color: var(--text-3); margin-bottom: 24px; line-height: 1.5; }

/* Modal */
.modal-overlay { position: fixed; inset: 0; z-index: 1000; background: rgba(0,0,0,0.8); backdrop-blur: 8px; display: flex; align-items: center; justify-content: center; padding: 20px; }
.modal { background: var(--bg); border: 1px solid var(--border); border-radius: var(--r-xl); width: 100%; max-width: 600px; overflow: hidden; box-shadow: 0 50px 100px rgba(0,0,0,0.8); }
.modal__head { padding: 24px 32px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
.modal__title { font-family: var(--font-display); font-size: 24px; color: var(--text); }
.close-btn { background: transparent; border: none; color: var(--text-4); cursor: pointer; transition: color var(--t); }
.close-btn:hover { color: var(--text); }

.modal__body { padding: 32px; }
.m-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 32px; }
.m-section label { display: block; font-size: 10px; font-weight: 700; color: var(--gold); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px; }
.m-val { font-size: 16px; font-weight: 600; color: var(--text); margin-bottom: 4px; }
.m-sub { font-size: 13px; color: var(--text-3); }
.m-quote { background: rgba(255,255,255,0.03); border-left: 3px solid var(--gold); padding: 16px; font-style: italic; font-size: 14px; color: var(--text-2); border-radius: 0 8px 8px 0; }

.modal__foot { padding: 24px 32px; background: rgba(255,255,255,0.02); border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 12px; }

/* Responsive */
@media (max-width: 1200px) { .stats-mini-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 1024px) { 
  .ph { flex-direction: column; align-items: flex-start; gap: var(--s4); }
  .ph__right { width: 100%; justify-content: space-between; }
  .search-bar { flex: 1; }
  .filters { flex-wrap: wrap; }
}
@media (max-width: 768px) {
  .dash__main { margin-left: 0; }
  .stats-mini-grid { grid-template-columns: 1fr; }
  .rt th:nth-child(3) { display: none; }
  .rt td:nth-child(3) { display: none; }
}
</style>
