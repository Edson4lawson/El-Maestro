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
        <!-- 1. PAGE HEADER & ACTIONS                                       -->
        <!-- ══════════════════════════════════════════════════════════════ -->
        <header class="cmd-header">
          <div class="cmd-header__left">
            <span class="panel-eyebrow">Catalogue Culinaire & Créations</span>
            <h1 class="cmd-header__title">
              Gestion du <span class="text-gold-gradient">Menu</span>
            </h1>
            <p class="cmd-header__subtitle">
              {{ filteredPlates.length }} créations gastronomiques disponibles • {{ activePlatesCount }} plats actifs en salle
            </p>
          </div>

          <div class="cmd-header__right">
            <!-- Search -->
            <div class="menu-search-box">
              <Search :size="15" class="search-icon" />
              <input 
                v-model="searchQuery" 
                type="text" 
                placeholder="Rechercher une recette, ingrédient..." 
                class="menu-search-input" 
              />
              <button v-if="searchQuery" @click="searchQuery = ''" class="search-clear-btn">✕</button>
            </div>

            <!-- New Dish Button -->
            <button class="btn-action-gold" @click="openCreateModal">
              <Plus :size="16" />
              <span>Nouveau Plat</span>
            </button>
          </div>
        </header>

        <!-- ══════════════════════════════════════════════════════════════ -->
        <!-- 2. CATALOG KPI SUMMARY STRIP                                   -->
        <!-- ══════════════════════════════════════════════════════════════ -->
        <section class="menu-stats-strip">
          <div class="menu-kpi-item">
            <span class="menu-kpi-lbl">Total Recettes</span>
            <span class="menu-kpi-val">{{ plates.length }}</span>
          </div>
          <div class="menu-kpi-sep"></div>
          <div class="menu-kpi-item">
            <span class="menu-kpi-lbl">En Service (Disponibles)</span>
            <span class="menu-kpi-val text-success">{{ activePlatesCount }}</span>
          </div>
          <div class="menu-kpi-sep"></div>
          <div class="menu-kpi-item">
            <span class="menu-kpi-lbl">Plats Signatures</span>
            <span class="menu-kpi-val text-gold">{{ signaturePlatesCount }}</span>
          </div>
          <div class="menu-kpi-sep"></div>
          <div class="menu-kpi-item">
            <span class="menu-kpi-lbl">Prix Moyen Carte</span>
            <span class="menu-kpi-val font-mono">{{ formatCurrency(averagePrice) }}</span>
          </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════════ -->
        <!-- 3. FILTERS BAR & VIEW MODE                                     -->
        <!-- ══════════════════════════════════════════════════════════════ -->
        <div class="menu-filters-bar">
          <!-- Category Pills -->
          <div class="cat-pills-wrap">
            <button 
              class="cat-pill" 
              :class="{ 'cat-pill--active': selectedCategory === '' }"
              @click="selectedCategory = ''"
            >
              Tous ({{ plates.length }})
            </button>
            <button 
              v-for="cat in availableCategories" 
              :key="cat" 
              class="cat-pill" 
              :class="{ 'cat-pill--active': selectedCategory === cat }"
              @click="selectedCategory = cat"
            >
              {{ cat }} ({{ getCategoryCount(cat) }})
            </button>
          </div>

          <div class="filters-right-group">
            <!-- Availability Select -->
            <div class="filter-select-wrap">
              <label>Statut</label>
              <select v-model="statusFilter" class="f-select-mini">
                <option value="">Tous les statuts</option>
                <option value="available">Disponible</option>
                <option value="unavailable">Épuisé / Masqué</option>
              </select>
            </div>

            <!-- Price Sort Select -->
            <div class="filter-select-wrap">
              <label>Tri</label>
              <select v-model="priceSort" class="f-select-mini">
                <option value="none">Défaut</option>
                <option value="asc">Prix Croissant</option>
                <option value="desc">Prix Décroissant</option>
                <option value="rating">Meilleures Notes</option>
              </select>
            </div>

            <!-- Grid / List Switcher -->
            <div class="view-mode-switch">
              <button 
                class="view-mode-btn" 
                :class="{ 'view-mode-btn--active': viewMode === 'grid' }"
                @click="viewMode = 'grid'"
                title="Affichage Grille"
              >
                <LayoutGrid :size="15" />
              </button>
              <button 
                class="view-mode-btn" 
                :class="{ 'view-mode-btn--active': viewMode === 'list' }"
                @click="viewMode = 'list'"
                title="Affichage Liste"
              >
                <List :size="15" />
              </button>
            </div>
          </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════════ -->
        <!-- 4. MENU ITEMS (GRID & LIST VIEWS)                              -->
        <!-- ══════════════════════════════════════════════════════════════ -->

        <!-- GRID VIEW -->
        <div v-if="viewMode === 'grid'" class="menu-cards-grid">
          <div 
            v-for="dish in filteredPlates" 
            :key="dish.id" 
            class="dish-card"
            @click="viewDishDetail(dish)"
          >
            <!-- Image Area with Glass Badges -->
            <div class="dish-card__img-container">
              <img :src="resolveImage(dish.image || dish.image_url)" :alt="dish.name" class="dish-card__img" />
              
              <div class="dish-card__badges-overlay">
                <span v-if="dish.is_signature" class="badge-signature">
                  <Star :size="11" class="fill-gold" /> Signature
                </span>
                <span class="badge-price">{{ formatCurrency(dish.price) }}</span>
              </div>

              <!-- Quick Status Dot -->
              <div class="dish-card__status-dot" :class="dish.status === 'available' ? 'status-dot--on' : 'status-dot--off'" :title="dish.status === 'available' ? 'Disponible' : 'Épuisé'"></div>
            </div>

            <!-- Dish Content -->
            <div class="dish-card__body">
              <div class="dish-card__top">
                <span class="dish-card__cat">{{ dish.category || 'Gastronomie' }}</span>
                <div class="dish-card__rating">
                  <Star :size="12" class="fill-gold text-gold" />
                  <span>{{ Number(dish.rating || dish.base_rating || 5.8).toFixed(1) }}</span>
                </div>
              </div>

              <h3 class="dish-card__name">{{ dish.name }}</h3>
              <p class="dish-card__desc">{{ dish.description || 'Création raffinée du Chef Maestro préparée à la commande.' }}</p>

              <!-- Footer Actions -->
              <div class="dish-card__foot" @click.stop>
                <button 
                  class="btn-toggle-status" 
                  :class="dish.status === 'available' ? 'btn-status--on' : 'btn-status--off'"
                  @click="toggleAvailability(dish)"
                >
                  <span class="status-indicator"></span>
                  {{ dish.status === 'available' ? 'En Cuisine' : 'Épuisé' }}
                </button>

                <div class="card-action-btns">
                  <button class="card-act-btn" @click="openEditModal(dish)" title="Modifier">
                    <Edit3 :size="14" />
                  </button>
                  <button class="card-act-btn card-act-btn--del" @click="confirmDeleteDish(dish)" title="Supprimer">
                    <Trash2 :size="14" />
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Empty Search State -->
          <div v-if="filteredPlates.length === 0" class="empty-menu-box">
            <ChefHat :size="42" class="empty-icon" />
            <h3 class="empty-title">Aucun plat trouvé dans cette sélection</h3>
            <p class="empty-sub">Essayez de réinitialiser vos filtres ou ajoutez une nouvelle recette au menu.</p>
            <button class="btn-action-gold mt-4" @click="openCreateModal">
              <Plus :size="16" />
              <span>Créer un Plat</span>
            </button>
          </div>
        </div>

        <!-- LIST VIEW -->
        <div v-else class="menu-list-panel">
          <div class="table-wrapper">
            <table class="luxury-table">
              <thead>
                <tr>
                  <th>Plat & Recette</th>
                  <th>Catégorie</th>
                  <th>Prix Unitaire</th>
                  <th>Préparation</th>
                  <th>Note & Prestige</th>
                  <th>Disponibilité</th>
                  <th class="text-right">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="dish in filteredPlates" :key="dish.id" class="table-row">
                  <td>
                    <div class="list-dish-cell">
                      <img :src="resolveImage(dish.image || dish.image_url)" :alt="dish.name" class="list-dish-thumb" />
                      <div class="list-dish-info">
                        <span class="list-dish-name">
                          {{ dish.name }}
                          <span v-if="dish.is_signature" class="list-sig-badge">★ Sig</span>
                        </span>
                        <span class="list-dish-desc">{{ dish.description }}</span>
                      </div>
                    </div>
                  </td>

                  <td><span class="dish-cat-pill">{{ dish.category }}</span></td>

                  <td class="font-mono font-bold text-gold">{{ formatCurrency(dish.price) }}</td>

                  <td class="font-mono text-muted">{{ dish.prep_time || '20-30 min' }}</td>

                  <td>
                    <div class="list-rating">
                      <Star :size="12" class="fill-gold text-gold" />
                      <span class="font-bold">{{ Number(dish.rating || dish.base_rating || 5.8).toFixed(1) }} / 6</span>
                    </div>
                  </td>

                  <td>
                    <button 
                      class="btn-toggle-status" 
                      :class="dish.status === 'available' ? 'btn-status--on' : 'btn-status--off'"
                      @click="toggleAvailability(dish)"
                    >
                      <span class="status-indicator"></span>
                      {{ dish.status === 'available' ? 'Disponible' : 'Épuisé' }}
                    </button>
                  </td>

                  <td class="text-right">
                    <div class="list-actions-group">
                      <button class="card-act-btn" @click="openEditModal(dish)" title="Modifier">
                        <Edit3 :size="14" />
                      </button>
                      <button class="card-act-btn card-act-btn--del" @click="confirmDeleteDish(dish)" title="Supprimer">
                        <Trash2 :size="14" />
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </main>

    <!-- ══════════════════════════════════════════════════════════════ -->
    <!-- 5. DISH INSPECTION MODAL                                       -->
    <!-- ══════════════════════════════════════════════════════════════ -->
    <transition name="modal-fade">
      <div v-if="inspectedDish" class="modal-backdrop" @click="inspectedDish = null">
        <div class="modal-card modal-card--detail" @click.stop>
          <button class="modal-close-btn" @click="inspectedDish = null">✕</button>

          <div class="dish-detail-layout">
            <div class="dish-detail-img-wrap">
              <img :src="resolveImage(inspectedDish.image || inspectedDish.image_url)" :alt="inspectedDish.name" class="dish-detail-img" />
              <span v-if="inspectedDish.is_signature" class="detail-sig-tag">
                <Star :size="13" class="fill-gold" /> Plat Signature Exclusif
              </span>
            </div>

            <div class="dish-detail-content">
              <div class="detail-header">
                <span class="dish-card__cat">{{ inspectedDish.category }}</span>
                <h2 class="detail-title">{{ inspectedDish.name }}</h2>
                <div class="detail-price-rating">
                  <span class="detail-price">{{ formatCurrency(inspectedDish.price) }}</span>
                  <div class="detail-rating">
                    <Star v-for="s in 6" :key="s" :size="14" :class="s <= Math.round(inspectedDish.rating || 5.8) ? 'fill-gold text-gold' : 'text-muted'" />
                    <span class="rating-num">{{ inspectedDish.rating || 5.8 }} / 6</span>
                  </div>
                </div>
              </div>

              <div class="detail-desc-box">
                <p>{{ inspectedDish.description }}</p>
              </div>

              <div class="detail-meta-grid">
                <div class="detail-meta-item">
                  <span class="meta-lbl">Temps de Préparation</span>
                  <span class="meta-val font-mono">{{ inspectedDish.prep_time || '20-30 min' }}</span>
                </div>
                <div class="detail-meta-item">
                  <span class="meta-lbl">Statut en Cuisine</span>
                  <span class="meta-val" :class="inspectedDish.status === 'available' ? 'text-success' : 'text-danger'">
                    {{ inspectedDish.status === 'available' ? 'Actif en Salle' : 'Masqué / Épuisé' }}
                  </span>
                </div>
              </div>

              <div class="detail-actions">
                <button class="btn-action-ghost" @click="toggleAvailability(inspectedDish)">
                  {{ inspectedDish.status === 'available' ? 'Rendre Épuisé' : 'Rendre Disponible' }}
                </button>
                <button class="btn-action-gold" @click="openEditModal(inspectedDish)">
                  <Edit3 :size="15" />
                  <span>Modifier la Fiche</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </transition>

    <!-- ══════════════════════════════════════════════════════════════ -->
    <!-- 6. CREATE / EDIT DISH MODAL                                    -->
    <!-- ══════════════════════════════════════════════════════════════ -->
    <transition name="modal-fade">
      <div v-if="showEditModal" class="modal-backdrop" @click="showEditModal = false">
        <div class="modal-card modal-card--form" @click.stop>
          <div class="modal-head">
            <h2 class="modal-head-title">
              {{ editingDish ? 'Modifier la Création' : 'Ajouter un Nouveau Plat' }}
            </h2>
            <button class="modal-close-btn" @click="showEditModal = false">✕</button>
          </div>

          <form @submit.prevent="saveDishForm" class="dish-form">
            <div class="form-row">
              <div class="form-group">
                <label>Nom du Plat *</label>
                <input v-model="dishForm.name" type="text" class="f-input" placeholder="ex: Filet de Capitaine Royal" required />
              </div>
              <div class="form-group">
                <label>Prix (FCFA) *</label>
                <input v-model.number="dishForm.price" type="number" class="f-input font-mono" placeholder="7500" required />
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Catégorie</label>
                <select v-model="dishForm.category" class="f-select">
                  <option v-for="cat in availableCategories" :key="cat" :value="cat">{{ cat }}</option>
                </select>
              </div>
              <div class="form-group">
                <label>Temps de Préparation</label>
                <input v-model="dishForm.prep_time" type="text" class="f-input" placeholder="ex: 20-30 min" />
              </div>
            </div>

            <div class="form-group">
              <label>Nom du fichier Image ou URL</label>
              <div class="input-with-icon">
                <ImageIcon :size="16" class="field-icon" />
                <input v-model="dishForm.image_url" type="text" class="f-input f-input--icon" placeholder="ex: Poulet Braisé Maestro.jpg ou URL https://..." />
              </div>
            </div>

            <div class="form-group">
              <label>Description & Notes Gastronomiques</label>
              <textarea v-model="dishForm.description" class="f-textarea" rows="3" placeholder="Ingrédients nobles, assaisonnements et accords mets-vins..."></textarea>
            </div>

            <div class="form-row form-row--checks">
              <label class="custom-checkbox">
                <input v-model="dishForm.is_signature" type="checkbox" />
                <span class="checkmark"></span>
                <span class="cb-label">Marquer comme <strong>Plat Signature</strong></span>
              </label>

              <label class="custom-checkbox">
                <input v-model="dishForm.is_available" type="checkbox" />
                <span class="checkmark"></span>
                <span class="cb-label">Disponible immédiatement</span>
              </label>
            </div>

            <div class="form-actions">
              <button type="button" class="btn-action-ghost" @click="showEditModal = false">Annuler</button>
              <button type="submit" class="btn-action-gold">
                <Save :size="16" />
                <span>{{ editingDish ? 'Sauvegarder les Modifications' : 'Créer le Plat' }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </transition>

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
import { ref, computed, onMounted } from 'vue'
import { 
  Search, Plus, LayoutGrid, List, Star, Edit3, Trash2, 
  ChefHat, Save, Image as ImageIcon, CheckCircle2 
} from 'lucide-vue-next'

import AdminSidebar from '../../components/admin/AdminSidebar.vue'
import AdminTopBar from '../../components/admin/AdminTopBar.vue'
import { adminMenuService, plateService } from '../../services/api'
import { getImageUrl } from '../../utils/imageUtils'

const sidebarCollapsed = ref(false)
const searchQuery = ref('')
const selectedCategory = ref('')
const statusFilter = ref('')
const priceSort = ref('none')
const viewMode = ref('grid')

const toastMessage = ref('')
const showToast = (msg) => {
  toastMessage.value = msg
  setTimeout(() => { toastMessage.value = '' }, 3500)
}

const availableCategories = ['Plats Résistants', 'Entrées', 'Desserts', 'Boissons']

// ── Initial Fallback Catalog Data ───────────────────────────
const initialPlatesCatalog = [
  { id: 1, name: 'Poulet Braisé Maestro', category: 'Plats Résistants', price: 6500, prep_time: '25 min', rating: 5.9, is_signature: true, status: 'available', image_url: 'Poulet Braisé Maestro.jpg', description: 'Poulet fermier mariné 24h aux épices secrètes du Bénin, braisé sur charbon ardent.' },
  { id: 2, name: 'Poisson Grillé Royal', category: 'Plats Résistants', price: 9500, prep_time: '30 min', rating: 6.0, is_signature: true, status: 'available', image_url: 'Poisson Grillé Royal.jpg', description: 'Capitaine frais du littoral de Cotonou, sauce vierge pimentée et alloco doré.' },
  { id: 3, name: 'Pâtes Carbonara Truffe Noire', category: 'Plats Résistants', price: 8500, prep_time: '20 min', rating: 5.8, is_signature: true, status: 'available', image_url: 'Pâtes Carbonara Truffe.jpg', description: 'Guanciale croustillant, jaunes d\'œufs bio, pecorino romano et copeaux de truffe.' },
  { id: 4, name: 'Attiéké Poisson Braisé', category: 'Plats Résistants', price: 5500, prep_time: '20 min', rating: 5.7, is_signature: false, status: 'available', image_url: 'Attiéké Poisson.jpg', description: 'Semoule de manioc vapeur de qualité supérieure avec bar grillé et piment écrasé.' },
  { id: 5, name: 'Foie Gras Poêlé aux Épices', category: 'Entrées', price: 12000, prep_time: '15 min', rating: 5.9, is_signature: true, status: 'available', image_url: 'Foie Gras Poêlé.jpg', description: 'Foie gras de canard poêlé, chutney de mangue caramélisée et pain brioché.' },
  { id: 6, name: 'Carpaccio de Bœuf Angus', category: 'Entrées', price: 7000, prep_time: '10 min', rating: 5.6, is_signature: false, status: 'available', image_url: 'Carpaccio de Bœuf.jpg', description: 'Fines lamelles de filet de bœuf, huile d\'olive extra vierge, parmesan affiné 24 mois.' },
  { id: 7, name: 'Mousse au Chocolat Gold 24k', category: 'Desserts', price: 4500, prep_time: '10 min', rating: 6.0, is_signature: true, status: 'available', image_url: 'Mousse au Chocolat.jpg', description: 'Chocolat noir 70% intense de São Tomé et éclats d\'or comestible 24 carats.' },
  { id: 8, name: 'Tiramisu Classique Maestro', category: 'Desserts', price: 4000, prep_time: '10 min', rating: 5.8, is_signature: false, status: 'available', image_url: 'Tiramisu Classique.jpg', description: 'Biscuits savoiardi imbibés d\'espresso grand cru, crème mascarpone onctueuse.' },
  { id: 9, name: 'Cocktail Tropical Ananas & Passion', category: 'Boissons', price: 3500, prep_time: '5 min', rating: 5.7, is_signature: false, status: 'available', image_url: 'Cocktail Tropical.jpg', description: 'Fruits frais pressés, sirop de canne infusé à la vanille de Madagascar.' },
  { id: 10, name: 'Mojito Prestige Maestro', category: 'Boissons', price: 4000, prep_time: '5 min', rating: 5.9, is_signature: true, status: 'available', image_url: 'Mojito Sans Alcool.jpg', description: 'Menthe fraîche froissée, citron vert bio, eau pétillante et touche florale.' },
]

const plates = ref([...initialPlatesCatalog])
const loading = ref(false)

// ── Modals & State ──────────────────────────────────────────
const inspectedDish = ref(null)
const showEditModal = ref(false)
const editingDish = ref(null)

const dishForm = ref({
  name: '',
  category: 'Plats Résistants',
  price: 5000,
  prep_time: '20 min',
  image_url: '',
  description: '',
  is_signature: false,
  is_available: true
})

// ── Fetch Plates from Backend ───────────────────────────────
const fetchPlates = async () => {
  try {
    loading.value = true
    const res = await adminMenuService.getAll()
    const rawData = res.data?.data || res.data
    if (Array.isArray(rawData) && rawData.length > 0) {
      plates.value = rawData.map(p => ({
        ...p,
        status: p.status || (p.is_available === 0 ? 'unavailable' : 'available'),
        rating: p.rating || p.base_rating || 5.8
      }))
    }
  } catch (e) {
    console.warn("Backend API offline or loading fallback:", e.message)
  } finally {
    loading.value = false
  }
}

onMounted(fetchPlates)

// ── Image Resolver ──────────────────────────────────────────
const resolveImage = (img) => {
  return getImageUrl(img)
}

// ── Metrics Calculations ────────────────────────────────────
const activePlatesCount = computed(() => plates.value.filter(p => p.status === 'available').length)
const signaturePlatesCount = computed(() => plates.value.filter(p => p.is_signature).length)
const averagePrice = computed(() => {
  if (!plates.value.length) return 0
  const sum = plates.value.reduce((acc, p) => acc + Number(p.price || 0), 0)
  return Math.round(sum / plates.value.length)
})

const getCategoryCount = (cat) => plates.value.filter(p => p.category === cat).length

// ── Filtered & Sorted Plates ────────────────────────────────
const filteredPlates = computed(() => {
  let list = [...plates.value]

  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase()
    list = list.filter(p => p.name.toLowerCase().includes(q) || (p.description && p.description.toLowerCase().includes(q)))
  }

  if (selectedCategory.value) {
    list = list.filter(p => p.category === selectedCategory.value)
  }

  if (statusFilter.value) {
    list = list.filter(p => p.status === statusFilter.value)
  }

  if (priceSort.value === 'asc') {
    list.sort((a, b) => a.price - b.price)
  } else if (priceSort.value === 'desc') {
    list.sort((a, b) => b.price - a.price)
  } else if (priceSort.value === 'rating') {
    list.sort((a, b) => (b.rating || 0) - (a.rating || 0))
  }

  return list
})

const formatCurrency = (val) => Number(val || 0).toLocaleString('fr-FR') + ' FCFA'

// ── Actions: Toggle Status, View Detail, Edit, Delete ───────
const viewDishDetail = (dish) => {
  inspectedDish.value = dish
}

const toggleAvailability = async (dish) => {
  const newStatus = dish.status === 'available' ? 'unavailable' : 'available'
  dish.status = newStatus
  try {
    if (dish.id) await adminMenuService.toggleStatus(dish.id)
  } catch (e) {
    console.warn("Status toggle error:", e)
  }
  showToast(`${dish.name} est maintenant marqué comme ${newStatus === 'available' ? 'disponible en salle' : 'épuisé'}.`)
}

const openCreateModal = () => {
  editingDish.value = null
  dishForm.value = {
    name: '',
    category: 'Plats Résistants',
    price: 5000,
    prep_time: '20-30 min',
    image_url: '',
    description: '',
    is_signature: false,
    is_available: true
  }
  showEditModal.value = true
}

const openEditModal = (dish) => {
  editingDish.value = dish
  dishForm.value = {
    name: dish.name,
    category: dish.category || 'Plats Résistants',
    price: dish.price,
    prep_time: dish.prep_time || '20-30 min',
    image_url: dish.image || dish.image_url || '',
    description: dish.description || '',
    is_signature: !!dish.is_signature,
    is_available: dish.status === 'available'
  }
  if (inspectedDish.value) inspectedDish.value = null
  showEditModal.value = true
}

const saveDishForm = async () => {
  if (!dishForm.value.name || !dishForm.value.price) return

  if (editingDish.value) {
    // Update existing dish
    Object.assign(editingDish.value, {
      ...dishForm.value,
      status: dishForm.value.is_available ? 'available' : 'unavailable'
    })
    try {
      await adminMenuService.update(editingDish.value.id, dishForm.value)
    } catch(e) {
      console.warn("Update error:", e)
    }
    showToast(`La recette "${dishForm.value.name}" a été mise à jour.`)
  } else {
    // Create new dish
    const newDish = {
      id: Date.now(),
      ...dishForm.value,
      status: dishForm.value.is_available ? 'available' : 'unavailable',
      rating: 5.8
    }
    plates.value.unshift(newDish)
    try {
      await adminMenuService.create(newDish)
    } catch(e) {
      console.warn("Create error:", e)
    }
    showToast(`Le plat "${dishForm.value.name}" a été ajouté au menu !`)
  }

  showEditModal.value = false
}

const confirmDeleteDish = async (dish) => {
  if (confirm(`Confirmez-vous le retrait définitif de "${dish.name}" de la carte ?`)) {
    plates.value = plates.value.filter(p => p.id !== dish.id)
    if (inspectedDish.value?.id === dish.id) inspectedDish.value = null
    try {
      await adminMenuService.delete(dish.id)
    } catch(e) {
      console.warn("Delete error:", e)
    }
    showToast(`"${dish.name}" a été supprimé de la carte.`)
  }
}
</script>

<style scoped>
@import '../../styles/admin-tokens.css';

.dash-content { gap: 1.75rem; }

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
  gap: 0.85rem;
}

.menu-search-box {
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(19, 19, 23, 0.8);
  border: 1px solid var(--border);
  padding: 8px 14px;
  border-radius: var(--r-md);
  width: 260px;
}

.menu-search-box:focus-within {
  border-color: var(--gold);
  box-shadow: 0 0 14px rgba(212, 175, 55, 0.25);
}

.search-icon { color: var(--text-3); }
.menu-search-input {
  background: transparent;
  border: none;
  outline: none;
  font-size: 12px;
  color: var(--text);
  width: 100%;
}
.menu-search-input::placeholder { color: var(--text-4); }
.search-clear-btn { background: transparent; border: none; color: var(--text-3); cursor: pointer; font-size: 11px; }

.btn-action-gold {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  background: var(--gold-gradient);
  border: none;
  color: #09090B;
  padding: 9px 18px;
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

/* KPI Summary Strip */
.menu-stats-strip {
  display: flex;
  align-items: center;
  background: rgba(19, 19, 23, 0.65);
  backdrop-filter: blur(20px);
  border: 1px solid var(--border);
  border-radius: var(--r-lg);
  padding: 0.9rem 1.6rem;
  gap: 2rem;
  box-shadow: var(--sh-sm);
  flex-wrap: wrap;
}

.menu-kpi-item {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.menu-kpi-lbl {
  font-size: 11px;
  color: var(--text-3);
  text-transform: uppercase;
  font-weight: 600;
  letter-spacing: 0.04em;
}

.menu-kpi-val {
  font-size: 20px;
  font-weight: 800;
  color: var(--text);
}

.menu-kpi-sep {
  width: 1px;
  height: 28px;
  background: rgba(255, 255, 255, 0.08);
}

/* Filters Bar */
.menu-filters-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 1rem;
}

.cat-pills-wrap {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.cat-pill {
  background: rgba(19, 19, 23, 0.7);
  border: 1px solid var(--border);
  color: var(--text-2);
  padding: 6px 14px;
  border-radius: var(--r-full);
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all var(--t-fast);
}

.cat-pill:hover {
  color: var(--text);
  border-color: var(--border-hover);
}

.cat-pill--active {
  background: rgba(212, 175, 55, 0.15);
  color: var(--gold);
  border-color: rgba(212, 175, 55, 0.35);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.filters-right-group {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.filter-select-wrap {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 11px;
  color: var(--text-3);
  font-weight: 600;
}

.f-select-mini {
  background: rgba(19, 19, 23, 0.8);
  border: 1px solid var(--border);
  color: var(--text);
  font-size: 11px;
  font-weight: 600;
  padding: 6px 10px;
  border-radius: var(--r-sm);
  outline: none;
  cursor: pointer;
}

.view-mode-switch {
  display: flex;
  background: rgba(0, 0, 0, 0.4);
  padding: 3px;
  border-radius: var(--r-sm);
  border: 1px solid var(--border);
}

.view-mode-btn {
  background: transparent;
  border: none;
  color: var(--text-3);
  padding: 5px 8px;
  border-radius: var(--r-xs);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.view-mode-btn--active {
  background: rgba(255, 255, 255, 0.08);
  color: var(--gold);
}

/* ══════════════════════════════════════════════════════════════ */
/* DISH CARDS GRID                                                */
/* ══════════════════════════════════════════════════════════════ */
.menu-cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1.5rem;
}

.dish-card {
  background: rgba(19, 19, 23, 0.7);
  backdrop-filter: blur(20px);
  border: 1px solid var(--border);
  border-radius: var(--r-xl);
  overflow: hidden;
  box-shadow: var(--sh);
  display: flex;
  flex-direction: column;
  transition: all var(--t);
  cursor: pointer;
}

.dish-card:hover {
  transform: translateY(-4px);
  border-color: var(--border-gold);
  box-shadow: var(--sh-gold);
}

.dish-card__img-container {
  width: 100%;
  height: 180px;
  position: relative;
  background: rgba(0, 0, 0, 0.3);
  overflow: hidden;
}

.dish-card__img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.4s ease;
}

.dish-card:hover .dish-card__img {
  transform: scale(1.06);
}

.dish-card__badges-overlay {
  position: absolute;
  top: 10px;
  left: 10px;
  right: 10px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  pointer-events: none;
}

.badge-signature {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  background: rgba(0, 0, 0, 0.75);
  backdrop-filter: blur(8px);
  color: var(--gold);
  border: 1px solid rgba(212, 175, 55, 0.4);
  font-size: 10px;
  font-weight: 800;
  padding: 3px 8px;
  border-radius: var(--r-full);
}

.badge-price {
  background: rgba(19, 19, 23, 0.85);
  backdrop-filter: blur(8px);
  color: var(--text);
  border: 1px solid var(--border);
  font-size: 11px;
  font-weight: 800;
  font-family: var(--font-mono);
  padding: 3px 8px;
  border-radius: var(--r-sm);
}

.dish-card__status-dot {
  position: absolute;
  bottom: 10px;
  right: 10px;
  width: 10px;
  height: 10px;
  border-radius: 50%;
  border: 2px solid #131317;
}

.status-dot--on  { background: var(--success); box-shadow: 0 0 6px var(--success); }
.status-dot--off { background: var(--error); }

.dish-card__body {
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  flex: 1;
}

.dish-card__top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.4rem;
}

.dish-card__cat {
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--gold);
}

.dish-card__rating {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 11px;
  font-weight: 700;
  color: var(--text);
}

.dish-card__name {
  font-family: var(--font-display);
  font-size: 1.15rem;
  font-weight: 700;
  color: var(--text);
  line-height: 1.3;
  margin-bottom: 0.5rem;
}

.dish-card__desc {
  font-size: 12px;
  color: var(--text-3);
  line-height: 1.45;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  margin-bottom: 1rem;
  flex: 1;
}

.dish-card__foot {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-top: 0.85rem;
  border-top: 1px solid rgba(255, 255, 255, 0.05);
}

.btn-toggle-status {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  border-radius: var(--r-full);
  font-size: 11px;
  font-weight: 700;
  border: 1px solid transparent;
  cursor: pointer;
  transition: all var(--t-fast);
}

.btn-status--on {
  background: rgba(16, 185, 129, 0.12);
  color: #34D399;
  border-color: rgba(16, 185, 129, 0.3);
}

.btn-status--off {
  background: rgba(239, 68, 68, 0.12);
  color: #F87171;
  border-color: rgba(239, 68, 68, 0.3);
}

.status-indicator {
  width: 5px;
  height: 5px;
  border-radius: 50%;
  background: currentColor;
}

.card-action-btns {
  display: flex;
  align-items: center;
  gap: 4px;
}

.card-act-btn {
  background: rgba(255, 255, 255, 0.05);
  border: none;
  color: var(--text-2);
  width: 28px;
  height: 28px;
  border-radius: var(--r-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all var(--t-fast);
}

.card-act-btn:hover { background: rgba(255, 255, 255, 0.12); color: var(--text); }
.card-act-btn--del:hover { background: rgba(239, 68, 68, 0.2); color: #F87171; }

/* Empty Menu */
.empty-menu-box {
  grid-column: 1 / -1;
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

.empty-icon { color: var(--gold); opacity: 0.6; margin-bottom: 0.75rem; }
.empty-title { font-family: var(--font-display); font-size: 1.25rem; font-weight: 700; margin-bottom: 0.25rem; }
.empty-sub { font-size: 12px; color: var(--text-3); max-width: 380px; }

/* List View */
.menu-list-panel {
  background: rgba(19, 19, 23, 0.7);
  backdrop-filter: blur(20px);
  border: 1px solid var(--border);
  border-radius: var(--r-xl);
  padding: 1.25rem;
}

.list-dish-cell { display: flex; align-items: center; gap: 12px; }
.list-dish-thumb { width: 44px; height: 44px; border-radius: var(--r-md); object-fit: cover; flex-shrink: 0; }
.list-dish-info { display: flex; flex-direction: column; }
.list-dish-name { font-weight: 700; color: var(--text); display: flex; align-items: center; gap: 6px; }
.list-sig-badge { font-size: 9px; background: rgba(212, 175, 55, 0.2); color: var(--gold); padding: 1px 5px; border-radius: var(--r-xs); }
.list-dish-desc { font-size: 11px; color: var(--text-3); max-width: 340px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.dish-cat-pill { font-size: 11px; background: rgba(255, 255, 255, 0.05); padding: 3px 9px; border-radius: var(--r-full); color: var(--text-2); }
.list-rating { display: flex; align-items: center; gap: 4px; font-size: 12px; }
.list-actions-group { display: flex; align-items: center; justify-content: flex-end; gap: 6px; }

/* ══════════════════════════════════════════════════════════════ */
/* MODALS                                                         */
/* ══════════════════════════════════════════════════════════════ */
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.75);
  backdrop-filter: blur(10px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 999;
  padding: 1.5rem;
}

.modal-card {
  background: #131317;
  border: 1px solid var(--border-gold);
  box-shadow: var(--sh-gold-hover);
  border-radius: var(--r-xl);
  position: relative;
  overflow: hidden;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-close-btn {
  position: absolute;
  top: 16px;
  right: 16px;
  background: rgba(255, 255, 255, 0.08);
  border: none;
  color: var(--text);
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  z-index: 10;
}

.modal-card--detail { width: 100%; max-width: 780px; }
.dish-detail-layout { display: grid; grid-template-columns: 1fr 1.25fr; }
@media (max-width: 680px) { .dish-detail-layout { grid-template-columns: 1fr; } }

.dish-detail-img-wrap {
  position: relative;
  height: 100%;
  min-height: 280px;
  background: #09090B;
}

.dish-detail-img { width: 100%; height: 100%; object-fit: cover; }
.detail-sig-tag {
  position: absolute;
  bottom: 16px;
  left: 16px;
  background: rgba(0, 0, 0, 0.85);
  border: 1px solid var(--gold);
  color: var(--gold);
  font-size: 11px;
  font-weight: 800;
  padding: 4px 10px;
  border-radius: var(--r-full);
  display: inline-flex;
  align-items: center;
  gap: 5px;
}

.dish-detail-content {
  padding: 2rem;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.detail-title {
  font-family: var(--font-display);
  font-size: 1.65rem;
  font-weight: 700;
  margin: 4px 0 8px;
}

.detail-price-rating {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.detail-price {
  font-size: 1.5rem;
  font-weight: 800;
  color: var(--gold);
  font-family: var(--font-mono);
}

.detail-rating { display: flex; align-items: center; gap: 4px; }
.rating-num { font-weight: 800; font-size: 12px; margin-left: 4px; }

.detail-desc-box {
  background: rgba(255, 255, 255, 0.03);
  border-radius: var(--r-md);
  padding: 1rem;
  font-size: 13px;
  color: var(--text-2);
  line-height: 1.5;
}

.detail-meta-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  padding: 0.85rem 0;
  border-top: 1px solid rgba(255, 255, 255, 0.06);
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}

.detail-meta-item { display: flex; flex-direction: column; gap: 3px; }
.meta-lbl { font-size: 10px; color: var(--text-3); text-transform: uppercase; font-weight: 700; }
.meta-val { font-size: 13px; font-weight: 700; }

.detail-actions {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 0.75rem;
  margin-top: auto;
}

/* Form Modal */
.modal-card--form { width: 100%; max-width: 620px; padding: 2rem; }
.modal-head { margin-bottom: 1.5rem; }
.modal-head-title { font-family: var(--font-display); font-size: 1.5rem; font-weight: 700; }

.dish-form { display: flex; flex-direction: column; gap: 1.25rem; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
@media (max-width: 540px) { .form-row { grid-template-columns: 1fr; } }

.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-group label { font-size: 12px; font-weight: 600; color: var(--text-2); }

.f-input, .f-select, .f-textarea {
  background: rgba(0, 0, 0, 0.4);
  border: 1px solid var(--border);
  border-radius: var(--r-md);
  padding: 10px 14px;
  color: var(--text);
  font-size: 13px;
  outline: none;
  transition: all var(--t-fast);
}

.f-input:focus, .f-select:focus, .f-textarea:focus {
  border-color: var(--gold);
  box-shadow: 0 0 12px rgba(212, 175, 55, 0.25);
}

.input-with-icon { position: relative; display: flex; align-items: center; }
.field-icon { position: absolute; left: 12px; color: var(--text-3); }
.f-input--icon { padding-left: 38px; width: 100%; }

.form-row--checks { display: flex; align-items: center; gap: 1.5rem; margin: 0.5rem 0; }
.custom-checkbox { display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 12px; color: var(--text-2); }

.form-actions {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 0.75rem;
  margin-top: 1rem;
}

/* Modal Transitions */
.modal-fade-enter-active, .modal-fade-leave-active { transition: all 0.25s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; transform: scale(0.96); }

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
