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
            <p class="ph__greet">Flux opérationnel</p>
            <h1 class="ph__title">Commandes</h1>
            <p class="ph__date">{{ filteredOrders.length }} transactions trouvées</p>
          </div>
          <div class="ph__right">
            <div class="search-bar">
              <Search class="search-icon" />
              <input v-model="searchQuery" type="text" placeholder="ID, Client ou Tracking..." class="search-input" />
            </div>
            <button class="btn-ghost">
              <Download class="btn-icon" />
              Rapport financier
            </button>
          </div>
        </div>

        <!-- ── FILTERS ── -->
        <div class="filters">
          <div class="filter-item">
            <label>Statut</label>
            <select v-model="statusFilter">
              <option value="">Tous les flux</option>
              <option value="pending">En attente</option>
              <option value="preparing">En cuisine</option>
              <option value="ready">Prête</option>
              <option value="delivered">Livrée</option>
              <option value="cancelled">Annulée</option>
            </select>
          </div>
          <div class="filter-item">
            <label>Période</label>
            <select v-model="periodFilter">
              <option value="today">Aujourd'hui</option>
              <option value="week">7 derniers jours</option>
              <option value="month">30 derniers jours</option>
              <option value="all">Historique global</option>
            </select>
          </div>
          <div class="filter-item">
            <label>Montant</label>
            <select v-model="amountFilter">
              <option value="">Tous montants</option>
              <option value="low">Moins de 10k</option>
              <option value="mid">10k - 25k</option>
              <option value="high">Plus de 25k</option>
            </select>
          </div>
          <div class="filter-spacer"></div>
          <div class="revenue-chip">
            <TrendingUp class="rev-icon" />
            <div class="rev-content">
              <span class="rev-label">Chiffre d'affaires filtré</span>
              <span class="rev-value">{{ formatCurrency(totalRevenue) }}</span>
            </div>
          </div>
        </div>

        <!-- ── ORDERS CONTENT ── -->
        <div class="panel panel--table">
          <div class="table-container">
            <table class="ot">
              <thead>
                <tr>
                  <th @click="sortBy('id')">ID & Tracking <ArrowUpDown class="sort-ico" /></th>
                  <th @click="sortBy('customer')">Client <ArrowUpDown class="sort-ico" /></th>
                  <th>Articles</th>
                  <th @click="sortBy('total')">Montant <ArrowUpDown class="sort-ico" /></th>
                  <th>Statut</th>
                  <th class="txt-right">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="o in paginatedOrders" :key="o.id" class="ot__row">
                  <td>
                    <div class="ot__id-wrap">
                      <span class="ot__number">#{{ o.id }}</span>
                      <span class="ot__tracking">{{ o.tracking }}</span>
                    </div>
                  </td>
                  <td>
                    <div class="ot__client">
                      <div class="ot__avatar">{{ o.customer.name.charAt(0) }}</div>
                      <div>
                        <div class="ot__name">{{ o.customer.name }}</div>
                        <div class="ot__time">{{ formatDate(o.date) }}</div>
                      </div>
                    </div>
                  </td>
                  <td>
                    <div class="ot__items">
                      <span class="ot__count">{{ o.items.length }} articles</span>
                      <span class="ot__preview">{{ o.items.map(i => i.name).join(', ').slice(0, 30) }}...</span>
                    </div>
                  </td>
                  <td>
                    <div class="ot__total">{{ formatCurrency(o.total) }}</div>
                  </td>
                  <td>
                    <span class="badge" :class="`badge--${o.status}`">{{ statusLabels[o.status] }}</span>
                  </td>
                  <td>
                    <div class="ot__actions">
                      <button class="a-btn" @click="viewDetail(o)"><Eye class="a-icon" /></button>
                      <button class="a-btn" @click="updateStatus(o)" v-if="o.status !== 'delivered' && o.status !== 'cancelled'"><ChevronRight class="a-icon" /></button>
                      <button class="a-btn a-btn--danger" @click="cancelOrder(o)" v-if="o.status === 'pending'"><XCircle class="a-icon" /></button>
                    </div>
                  </td>
                </tr>
                <tr v-if="filteredOrders.length === 0">
                  <td colspan="6" class="empty-state">
                    <ShoppingBag class="empty-icon" />
                    <p>Aucune commande trouvée.</p>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="pagination" v-if="totalPages > 1">
            <button :disabled="currentPage === 1" @click="currentPage--" class="pg-btn"><ChevronLeft /></button>
            <div class="pg-pages">
              <button 
                v-for="p in totalPages" :key="p" 
                :class="{ active: currentPage === p }"
                @click="currentPage = p"
                class="pg-num"
              >{{ p }}</button>
            </div>
            <button :disabled="currentPage === totalPages" @click="currentPage++" class="pg-btn"><ChevronRight /></button>
          </div>
        </div>
      </div>
    </main>

    <!-- ── DETAIL MODAL ── -->
    <transition name="fade">
      <div v-if="selectedItem" class="modal-overlay" @click="selectedItem = null">
        <div class="modal modal--order" @click.stop>
          <div class="modal__head">
            <div>
              <h2 class="modal__title">Commande #{{ selectedItem.id }}</h2>
              <p class="modal__sub">Tracking: {{ selectedItem.tracking }}</p>
            </div>
            <button class="close-btn" @click="selectedItem = null"><X /></button>
          </div>
          <div class="modal__body">
            <div class="order-grid">
              <div class="order-main">
                <h4 class="section-label">Articles commandés</h4>
                <div class="order-list">
                  <div v-for="i in selectedItem.items" :key="i.id" class="order-item">
                    <div class="order-item__info">
                      <span class="order-item__qty">{{ i.quantity }}x</span>
                      <span class="order-item__name">{{ i.name }}</span>
                    </div>
                    <span class="order-item__price">{{ formatCurrency(i.price * i.quantity) }}</span>
                  </div>
                </div>
                <div class="order-total-box">
                  <div class="total-row"><span>Sous-total</span><span>{{ formatCurrency(selectedItem.subtotal) }}</span></div>
                  <div class="total-row"><span>Livraison</span><span>{{ formatCurrency(selectedItem.delivery_fee) }}</span></div>
                  <div class="total-row total-row--final"><span>Total</span><span>{{ formatCurrency(selectedItem.total) }}</span></div>
                </div>
              </div>
              <div class="order-side">
                <div class="side-block">
                  <label>Client</label>
                  <p class="side-val">{{ selectedItem.customer.name }}</p>
                  <p class="side-sub">{{ selectedItem.customer.phone }}</p>
                  <p class="side-sub">{{ selectedItem.customer.address }}</p>
                </div>
                <div class="side-block">
                  <label>Statut actuel</label>
                  <div class="status-indicator">
                    <span class="badge" :class="`badge--${selectedItem.status}`">{{ statusLabels[selectedItem.status] }}</span>
                  </div>
                </div>
                <div class="side-block" v-if="selectedItem.notes">
                  <label>Notes</label>
                  <p class="side-note">{{ selectedItem.notes }}</p>
                </div>
              </div>
            </div>
          </div>
          <div class="modal__foot">
            <button class="btn-ghost" @click="selectedItem = null">Fermer</button>
            <div class="foot-actions">
              <button v-if="selectedItem.status === 'pending'" class="btn-gold-sm" @click="updateStatus(selectedItem, 'preparing')">Lancer la préparation</button>
              <button v-if="selectedItem.status === 'preparing'" class="btn-gold-sm" @click="updateStatus(selectedItem, 'ready')">Prête pour livraison</button>
              <button v-if="selectedItem.status === 'ready'" class="btn-gold-sm" @click="updateStatus(selectedItem, 'delivered')">Confirmer la livraison</button>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { 
  Search, Download, TrendingUp, ShoppingBag, ArrowUpDown, 
  Eye, ChevronRight, XCircle, ChevronLeft, X 
} from 'lucide-vue-next'
import AdminSidebar from '../../components/admin/AdminSidebar.vue'
import AdminTopBar from '../../components/admin/AdminTopBar.vue'

const sidebarCollapsed = ref(false)
const searchQuery = ref('')
const statusFilter = ref('')
const periodFilter = ref('all')
const amountFilter = ref('')
const currentPage = ref(1)
const itemsPerPage = 8
const selectedItem = ref(null)
const sortField = ref('date')
const sortDir = ref('desc')

const statusLabels = { 
  pending: 'En attente', preparing: 'En cuisine', 
  ready: 'Prête', delivered: 'Livrée', cancelled: 'Annulée' 
}

const orders = ref([
  { id: 1248, tracking: 'MAESTRO-9821', customer: { name: 'Marie Konaté', phone: '+229 97 12 34 56', email: 'marie.k@gmail.com', address: 'Haie Vive, Villa 45' }, items: [{ id: 1, name: 'Poulet Yassa', price: 12000, quantity: 2 }, { id: 2, name: 'Riz Blanc', price: 1500, quantity: 2 }], subtotal: 27000, delivery_fee: 2000, total: 29000, status: 'preparing', date: new Date('2025-05-14T10:30:00'), notes: 'Sans piment' },
  { id: 1247, tracking: 'MAESTRO-9820', customer: { name: 'Jean Boco', phone: '+229 95 98 76 54', address: 'Agla, Rue 120' }, items: [{ id: 3, name: 'Thieboudienne', price: 15000, quantity: 1 }], subtotal: 15000, delivery_fee: 1500, total: 16500, status: 'pending', date: new Date('2025-05-14T11:45:00') },
  { id: 1246, tracking: 'MAESTRO-9819', customer: { name: 'Aline Soglo', phone: '+229 91 45 67 89', address: 'Zongo, Face Pharmacie' }, items: [{ id: 5, name: 'Dibi d\'Agneau', price: 8500, quantity: 3 }], subtotal: 25500, delivery_fee: 2000, total: 27500, status: 'delivered', date: new Date('2025-05-14T09:15:00') },
  { id: 1245, tracking: 'MAESTRO-9818', customer: { name: 'Marc Lawson', phone: '+229 60 11 22 33', address: 'Cotonou, Zone Portuaire' }, items: [{ id: 6, name: 'Mousse au Chocolat', price: 4500, quantity: 1 }], subtotal: 4500, delivery_fee: 1000, total: 5500, status: 'cancelled', date: new Date('2025-05-13T18:20:00') }
])

const filteredOrders = computed(() => {
  let list = [...orders.value]
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase()
    list = list.filter(o => o.id.toString().includes(q) || o.tracking.toLowerCase().includes(q) || o.customer.name.toLowerCase().includes(q))
  }
  if (statusFilter.value) list = list.filter(o => o.status === statusFilter.value)
  if (amountFilter.value) {
    list = list.filter(o => {
      if (amountFilter.value === 'low') return o.total < 10000
      if (amountFilter.value === 'mid') return o.total >= 10000 && o.total <= 25000
      if (amountFilter.value === 'high') return o.total > 25000
      return true
    })
  }
  list.sort((a, b) => {
    let av = a[sortField.value], bv = b[sortField.value]
    return sortDir.value === 'asc' ? (av > bv ? 1 : -1) : (av < bv ? 1 : -1)
  })
  return list
})

const paginatedOrders = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  return filteredOrders.value.slice(start, start + itemsPerPage)
})

const totalPages = computed(() => Math.ceil(filteredOrders.value.length / itemsPerPage))
const totalRevenue = computed(() => filteredOrders.value.reduce((s, o) => s + o.total, 0))

const formatCurrency = (amt) => new Intl.NumberFormat('fr-FR').format(amt) + ' FCFA'
const formatDate = (d) => new Intl.DateTimeFormat('fr-FR', { hour: '2-digit', minute: '2-digit', day: '2-digit', month: 'short' }).format(d)

const viewDetail = (o) => selectedItem.value = o
const updateStatus = (o, next) => {
  if (next) { o.status = next; selectedItem.value = null; return }
  const flow = ['pending', 'preparing', 'ready', 'delivered']
  const idx = flow.indexOf(o.status)
  if (idx < flow.length - 1) o.status = flow[idx + 1]
}
const cancelOrder = (o) => { if (confirm('Annuler cette commande ?')) o.status = 'cancelled' }

const sortBy = (f) => { if (sortField.value === f) sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'; else { sortField.value = f; sortDir.value = 'asc' } }
</script>

<style scoped>
@import '../../styles/admin-tokens.css';

.dash { font-family: var(--font-sans); background: #0A0A0A; }

/* Ambient orbs */
.orb { position: fixed; border-radius: 50%; pointer-events: none; filter: blur(100px); z-index: 0; opacity: 0.3; }
.orb--1 { width: 600px; height: 600px; top: -100px; left: 10%; background: radial-gradient(circle, rgba(212,175,55,0.06), transparent 70%); }
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
.filters { display: flex; align-items: center; gap: 30px; background: var(--card); border: 1px solid var(--border); padding: 12px 20px; border-radius: var(--r-lg); margin-bottom: var(--s8); margin-top: 12px; margin-left: 5px; margin-right: 4px; position: relative; z-index: 1; }
.filter-item { display: flex; flex-direction: column; gap: 4px;  }
.filter-item label { font-size: 9px; font-weight: 700; color: var(--text-4); text-transform: uppercase; letter-spacing: 0.1em; }
.filter-item select { background: black; border: none; color: var(--text-2); font-size: 13px; font-weight: 600; outline: none; cursor: pointer; border-bottom: 1px solid transparent; transition: all var(--t); }
.filter-item select:hover { color: var(--gold); }
.filter-spacer { flex: 1; }

.revenue-chip { display: flex; align-items: center; gap: 12px; padding: 8px 16px; background: rgba(212,175,55,0.05); border: 1px solid rgba(212,175,55,0.1); border-radius: var(--r-md); }
.rev-icon { width: 18px; height: 18px; color: var(--gold); }
.rev-label { display: block; font-size: 9px; color: var(--text-4); text-transform: uppercase; font-weight: 700; }
.rev-value { display: block; font-size: 15px; font-weight: 700; color: var(--text); }

/* Table */
.panel--table { border-radius: var(--r-xl); background: var(--card); border: 1px solid var(--border); overflow: hidden; position: relative; z-index: 1; margin-left: 5px; margin-right: 4px; }
.table-container { overflow-x: auto; }
.ot { width: 100%; border-collapse: collapse; margin-top: 5px; }
.ot th { text-align: left; padding: 16px 24px; font-size: 10px; font-weight: 700; color: var(--text-4); text-transform: uppercase; border-bottom: 1px solid var(--border); cursor: pointer; }
.sort-ico { width: 12px; height: 12px; display: inline-block; vertical-align: middle; margin-left: 4px; opacity: 0.5; }

.ot__row td { padding: 16px 24px; border-bottom: 1px solid rgba(255,255,255,0.03); transition: background var(--t); }
.ot__row:hover td { background: rgba(255,255,255,0.015); }

.ot__id-wrap { display: flex; flex-direction: column; }
.ot__number { font-size: 13px; font-weight: 700; color: var(--text); }
.ot__tracking { font-size: 10px; color: var(--text-4); font-family: monospace; }

.ot__client { display: flex; align-items: center; gap: 12px; }
.ot__avatar { width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, rgba(212,175,55,0.2), rgba(212,175,55,0.05)); display: flex; align-items: center; justify-content: center; color: var(--gold); font-weight: 800; font-size: 12px; }
.ot__name { font-size: 13px; font-weight: 600; color: var(--text); }
.ot__time { font-size: 11px; color: var(--text-4); }

.ot__items { display: flex; flex-direction: column; }
.ot__count { font-size: 12px; color: var(--text-2); font-weight: 600; }
.ot__preview { font-size: 11px; color: var(--text-4); }

.ot__total { font-weight: 700; color: var(--text); font-size: 14px; }

.badge { padding: 4px 10px; border-radius: 99px; font-size: 10px; font-weight: 700; display: inline-flex; align-items: center; }
.badge--pending { background: rgba(99,102,241,0.1); color: #6366f1; }
.badge--preparing { background: rgba(245,158,11,0.1); color: #f59e0b; }
.badge--ready { background: rgba(168,85,247,0.1); color: #a855f7; }
.badge--delivered { background: rgba(34,197,94,0.1); color: #22c55e; }
.badge--cancelled { background: rgba(239,68,68,0.1); color: #ef4444; }

.ot__actions { display: flex; gap: 8px; justify-content: flex-end; }
.a-btn { width: 32px; height: 32px; border-radius: 8px; border: 1px solid var(--border); background: transparent; color: var(--text-3); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all var(--t); }
.a-btn:hover { color: var(--gold); border-color: var(--gold); background: rgba(212,175,55,0.05); }

/* Pagination */
.pagination { display: flex; align-items: center; justify-content: center; gap: 16px; padding: 24px; border-top: 1px solid var(--border); }
.pg-btn { background: transparent; border: 1px solid var(--border); color: var(--text-3); border-radius: 8px; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all var(--t); }
.pg-btn:hover:not(:disabled) { border-color: var(--gold); color: var(--gold); }
.pg-btn:disabled { opacity: 0.3; cursor: not-allowed; }
.pg-num { background: transparent; border: none; color: var(--text-4); font-size: 13px; font-weight: 600; cursor: pointer; width: 32px; height: 32px; border-radius: 8px; transition: all var(--t); }
.pg-num.active { background: var(--card-hover); color: var(--gold); }

/* Modal Order */
.modal-overlay { position: fixed; inset: 0; z-index: 1000; background: rgba(0,0,0,0.85); backdrop-blur: 8px; display: flex; align-items: center; justify-content: center; padding: 20px; }
.modal--order { max-width: 800px; width: 100%; background: var(--bg); border: 1px solid var(--border); border-radius: var(--r-xl); overflow: hidden; }

.modal__head { padding: 32px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: flex-start; }
.modal__title { font-family: var(--font-display); font-size: 28px; color: var(--text); margin-bottom: 4px; }
.modal__sub { font-size: 11px; color: var(--text-4); font-family: monospace; }
.close-btn { background: transparent; border: none; color: var(--text-4); cursor: pointer; }

.modal__body { padding: 32px; }
.order-grid { display: grid; grid-template-columns: 1.5fr 1fr; gap: 40px; }

.section-label { font-size: 10px; font-weight: 700; color: var(--gold); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 16px; }

.order-list { display: flex; flex-direction: column; gap: 12px; margin-bottom: 24px; }
.order-item { display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; background: rgba(255,255,255,0.02); border-radius: var(--r-md); }
.order-item__qty { color: var(--gold); font-weight: 800; margin-right: 12px; }
.order-item__name { color: var(--text-2); font-size: 14px; }
.order-item__price { font-weight: 700; color: var(--text); }

.order-total-box { border-top: 1px solid var(--border); padding-top: 20px; }
.total-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px; color: var(--text-3); }
.total-row--final { margin-top: 16px; font-size: 20px; font-weight: 800; color: var(--gold); }

.side-block { margin-bottom: 24px; }
.side-block label { display: block; font-size: 9px; font-weight: 700; color: var(--text-4); text-transform: uppercase; margin-bottom: 8px; }
.side-val { font-size: 16px; font-weight: 700; color: var(--text); margin-bottom: 4px; }
.side-sub { font-size: 13px; color: var(--text-3); margin-bottom: 2px; }
.side-note { font-size: 13px; font-style: italic; color: #f59e0b; background: rgba(245,158,11,0.05); padding: 12px; border-radius: 8px; border-left: 3px solid #f59e0b; }

.modal__foot { padding: 24px 32px; background: rgba(255,255,255,0.02); border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
.foot-actions { display: flex; gap: 12px; }

@media (max-width: 1024px) {
  .order-grid { grid-template-columns: 1fr; gap: 32px; }
  .ph { flex-direction: column; align-items: flex-start; gap: 20px; }
  .ph__right { width: 100%; justify-content: space-between; }
}

@media (max-width: 768px) {
  .filters { flex-wrap: wrap; }
  .search-bar { width: 100%; }
}
</style>
