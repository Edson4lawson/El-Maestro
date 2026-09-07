<template>
  <header class="topbar" :class="{ 'topbar--collapsed': sidebarCollapsed }">
    <div class="topbar__inner">

      <!-- Left -->
      <div class="topbar__left">
        <button class="icon-btn" @click="$emit('toggle-sidebar')">
          <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
          </svg>
        </button>
        <div class="topbar__breadcrumb">
          <span class="bc-root">Admin</span>
          <svg width="10" height="10" viewBox="0 0 20 20" fill="currentColor" style="opacity:.3"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
          <span class="bc-page">{{ pageLabel }}</span>
        </div>
      </div>

      <!-- Center: search -->
      <div class="topbar__search">
        <div class="search" :class="{ 'search--active': focused }">
          <svg class="search__ico" width="13" height="13" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
          </svg>
          <input v-model="q" type="text" placeholder="Rechercher une commande, un client…"
            class="search__input" @focus="focused=true" @blur="focused=false" @keydown.esc="q=''"/>
          <div class="search__hint"><kbd>⌘</kbd><kbd>K</kbd></div>
        </div>
      </div>

      <!-- Right -->
      <div class="topbar__right">

        <!-- Live indicator -->
        <div class="live-pill">
          <span class="live-dot"></span>
          <span>Live</span>
        </div>

        <!-- Notifications -->
        <div class="tb-action" ref="notifRef">
          <button class="icon-btn icon-btn--rel" @click="toggleNotif">
            <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
              <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/>
            </svg>
            <span class="notif-count">3</span>
          </button>
          <transition name="dd">
            <div v-if="showNotif" class="dropdown">
              <div class="dd-head">
                <span class="dd-title">Notifications</span>
                <button class="dd-clear">Tout lu</button>
              </div>
              <div class="dd-body">
                <div v-for="n in notifs" :key="n.id" class="notif" :class="{'notif--unread':n.unread}">
                  <span class="notif__pip" :style="{background: n.color}"></span>
                  <div>
                    <p class="notif__title">{{ n.title }}</p>
                    <p class="notif__sub">{{ n.sub }}</p>
                    <p class="notif__time">{{ n.time }}</p>
                  </div>
                </div>
              </div>
              <div class="dd-foot">
                <RouterLink to="/admin/notifications" class="dd-more">Voir toutes →</RouterLink>
              </div>
            </div>
          </transition>
        </div>

        <!-- Profile -->
        <div class="tb-action" ref="profileRef">
          <button class="profile-chip" @click="toggleProfile">
            <div class="profile-chip__av">{{ init }}</div>
            <div class="profile-chip__info">
              <span class="profile-chip__name">{{ name }}</span>
              <span class="profile-chip__role">Super Admin</span>
            </div>
            <svg width="10" height="10" viewBox="0 0 20 20" fill="currentColor"
              :style="{ transform: showProfile ? 'rotate(180deg)' : 'rotate(0)', transition: 'transform 200ms' }">
              <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
          </button>
          <transition name="dd">
            <div v-if="showProfile" class="dropdown dropdown--profile">
              <div class="dd-profile-head">
                <div class="profile-av-lg">{{ init }}</div>
                <div>
                  <p class="profile-nm">{{ name }}</p>
                  <p class="profile-em">admin@elmaestro.bj</p>
                </div>
              </div>
              <div class="dd-body">
                <RouterLink to="/admin/profile" class="dd-item">
                  <svg width="13" height="13" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                  Mon profil
                </RouterLink>
                <RouterLink to="/admin/settings" class="dd-item">
                  <svg width="13" height="13" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.532 1.532 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.532 1.532 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/></svg>
                  Paramètres
                </RouterLink>
                <div class="dd-sep"></div>
                <button class="dd-item dd-item--red" @click="logout">
                  <svg width="13" height="13" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 011 1v12a1 1 0 11-2 0V4a1 1 0 011-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/></svg>
                  Déconnexion
                </button>
              </div>
            </div>
          </transition>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'

defineProps({ sidebarCollapsed: Boolean })
defineEmits(['toggle-sidebar'])

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const q = ref(''); const focused = ref(false)
const showNotif = ref(false); const showProfile = ref(false)
const notifRef = ref(null); const profileRef = ref(null)

const name = computed(() => authStore.user?.name || 'Admin')
const init = computed(() => name.value.charAt(0).toUpperCase())

const labels = { 
  '/admin/dashboard': 'Tableau de bord', 
  '/admin/orders': 'Commandes', 
  '/admin/menu': 'Menu', 
  '/admin/reservations': 'Réservations', 
  '/admin/analytics': 'Statistiques', 
  '/admin/notifications': 'Notifications', 
  '/admin/settings': 'Paramètres' 
}
const pageLabel = computed(() => labels[route.path] || 'Dashboard')

const toggleNotif = () => { showNotif.value = !showNotif.value; showProfile.value = false }
const toggleProfile = () => { showProfile.value = !showProfile.value; showNotif.value = false }
const logout = async () => { await authStore.logout?.(); router.push('/admin/login') }

const notifs = [
  { id:1, title:'Commande #1248', sub:'Marie K. — 25 000 FCFA', time:'Il y a 2 min', color:'#22c55e', unread:true },
  { id:2, title:'Stock faible', sub:'Poulet (~3 portions)', time:'Il y a 18 min', color:'#f59e0b', unread:true },
  { id:3, title:'Rapport mensuel', sub:'Disponible en téléchargement', time:'Il y a 1h', color:'#6366f1', unread:false },
]

const outside = (e) => {
  if (notifRef.value && !notifRef.value.contains(e.target)) showNotif.value = false
  if (profileRef.value && !profileRef.value.contains(e.target)) showProfile.value = false
}
onMounted(() => document.addEventListener('mousedown', outside))
onUnmounted(() => document.removeEventListener('mousedown', outside))
</script>

<style scoped>
@import '../../styles/admin-tokens.css';

.topbar {
  position: sticky;
  top: 0;
  width: 100%;
  height: var(--topbar-h);
  background: rgba(14, 14, 18, 0.92);
  backdrop-filter: blur(24px) saturate(1.5);
  -webkit-backdrop-filter: blur(24px) saturate(1.5);
  border-bottom: 1px solid var(--border);
  z-index: 40;
  font-family: var(--font-sans);
}

.topbar__inner {
  display: flex; align-items: center; gap: var(--s4);
  height: 100%; padding: 0 var(--s6);
}

/* Left */
.topbar__left { display: flex; align-items: center; gap: var(--s3); flex-shrink: 0; }

.icon-btn {
  width: 32px; height: 32px; border-radius: var(--r);
  border: 1px solid var(--border);
  background: transparent; color: var(--text-3);
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; transition: all var(--t);
}
.icon-btn:hover { border-color: var(--border-gold); color: var(--gold); background: rgba(212,175,55,0.04); }

.topbar__breadcrumb { display: flex; align-items: center; gap: 6px; font-size: 12px; }
.bc-root { color: var(--text-3); }
.bc-page { color: var(--text); font-weight: 600; }

/* Search */
.topbar__search { flex: 1; max-width: 420px; }
.search {
  display: flex; align-items: center; gap: 8px;
  padding: 0 12px; height: 36px;
  border-radius: var(--r);
  border: 1px solid var(--border);
  background: rgba(255,255,255,0.03);
  transition: all var(--t);
}
.search--active { border-color: var(--border-gold); background: rgba(212,175,55,0.04); box-shadow: 0 0 0 3px rgba(212,175,55,0.07); }
.search__ico { color: var(--text-3); flex-shrink: 0; }
.search__input { flex: 1; background: transparent; border: none; outline: none; font-family: var(--font-sans); font-size: 12px; color: var(--text); }
.search__input::placeholder { color: var(--text-4); }
.search__hint { display: flex; gap: 3px; flex-shrink: 0; }
kbd { background: rgba(255,255,255,0.06); border: 1px solid var(--border); border-radius: 4px; padding: 1px 5px; font-size: 9px; color: var(--text-3); font-family: var(--font-sans); }

/* Right */
.topbar__right { display: flex; align-items: center; gap: var(--s2); margin-left: auto; }

/* Live pill */
.live-pill {
  display: flex; align-items: center; gap: 6px;
  padding: 4px 10px; border-radius: 99px;
  border: 1px solid rgba(34,197,94,0.2);
  background: rgba(34,197,94,0.06);
  font-size: 10px; font-weight: 700; color: #22c55e;
  letter-spacing: 0.05em; text-transform: uppercase;
}
.live-dot {
  width: 6px; height: 6px; border-radius: 50%;
  background: #22c55e;
  animation: pulse-gold 1.5s infinite;
}

.tb-action { position: relative; }

.icon-btn--rel { position: relative; }
.notif-count {
  position: absolute; top: 3px; right: 3px;
  min-width: 14px; height: 14px; padding: 0 3px;
  background: #ef4444; border-radius: 99px;
  border: 1.5px solid #0A0A0A;
  font-size: 8px; font-weight: 800; color: #fff;
  display: flex; align-items: center; justify-content: center; line-height: 1;
}

/* Profile chip */
.profile-chip {
  display: flex; align-items: center; gap: 8px;
  padding: 4px 10px 4px 4px;
  border-radius: 99px;
  border: 1px solid var(--border);
  background: transparent; cursor: pointer;
  transition: all var(--t);
  color: var(--text-2);
}
.profile-chip:hover { border-color: var(--border-gold); background: rgba(212,175,55,0.04); }

.profile-chip__av {
  width: 26px; height: 26px; border-radius: 50%;
  background: linear-gradient(135deg, #D4AF37, #B8860B);
  color: #0A0A0A; font-size: 10px; font-weight: 800;
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 0 10px rgba(212,175,55,0.35);
}
.profile-chip__info { text-align: left; line-height: 1; }
.profile-chip__name { display: block; font-size: 12px; font-weight: 600; color: var(--text); }
.profile-chip__role { display: block; font-size: 9px; color: var(--text-3); margin-top: 2px; }

/* Dropdown */
.dropdown {
  position: absolute; top: calc(100% + 8px); right: 0;
  width: 290px;
  background: #141414;
  border: 1px solid var(--border);
  border-radius: var(--r-lg);
  box-shadow: 0 24px 60px rgba(0,0,0,0.8), 0 0 1px rgba(255,255,255,0.08);
  overflow: hidden;
  z-index: var(--z-drop);
}
.dropdown--profile { width: 240px; }

.dd-head { display: flex; justify-content: space-between; align-items: center; padding: 12px 14px; border-bottom: 1px solid var(--border); }
.dd-title { font-size: 12px; font-weight: 700; color: var(--text); }
.dd-clear { font-size: 10px; color: var(--gold); background: none; border: none; cursor: pointer; font-weight: 600; transition: opacity var(--t); }
.dd-clear:hover { opacity: 0.7; }
.dd-body { }
.dd-foot { padding: 8px 14px; border-top: 1px solid var(--border); }
.dd-more { font-size: 11px; color: var(--gold); text-decoration: none; font-weight: 600; transition: opacity var(--t); }
.dd-more:hover { opacity: 0.75; }

/* Notif */
.notif { display: flex; gap: 10px; padding: 10px 14px; border-bottom: 1px solid var(--border); cursor: pointer; transition: background var(--t); align-items: flex-start; }
.notif:hover { background: rgba(255,255,255,0.03); }
.notif--unread { background: rgba(212,175,55,0.02); }
.notif__pip { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; margin-top: 5px; }
.notif__title { font-size: 12px; font-weight: 600; color: var(--text); margin-bottom: 2px; }
.notif__sub { font-size: 11px; color: var(--text-2); margin-bottom: 2px; }
.notif__time { font-size: 9px; color: var(--text-3); }

/* Profile head */
.dd-profile-head { display: flex; gap: 10px; align-items: center; padding: 12px 14px; border-bottom: 1px solid var(--border); }
.profile-av-lg { width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, #D4AF37, #B8860B); color: #0A0A0A; font-size: 14px; font-weight: 800; display: flex; align-items: center; justify-content: center; box-shadow: 0 0 14px rgba(212,175,55,0.3); flex-shrink: 0; }
.profile-nm { font-size: 13px; font-weight: 700; color: var(--text); }
.profile-em { font-size: 10px; color: var(--text-3); margin-top: 2px; }

.dd-item { display: flex; align-items: center; gap: 10px; padding: 10px 14px; font-size: 12px; font-weight: 500; color: var(--text-2); text-decoration: none; background: none; border: none; cursor: pointer; width: 100%; text-align: left; transition: all var(--t); }
.dd-item:hover { background: rgba(255,255,255,0.04); color: var(--text); }
.dd-item--red { color: #ef4444; }
.dd-item--red:hover { background: rgba(239,68,68,0.06); }
.dd-sep { height: 1px; background: var(--border); margin: 4px 0; }

/* Anim */
.dd-enter-active { animation: float-up 180ms var(--ease) both; }
.dd-leave-active { animation: float-up 120ms var(--ease) reverse both; }

@media (max-width: 768px) {
  .topbar { left: 0 !important; }
  .topbar__breadcrumb { display: none; }
  .topbar__search { max-width: 160px; }
  .profile-chip__info { display: none; }
  .search__hint { display: none; }
  .live-pill { display: none; }
}
</style>
