<template>
  <aside class="sidebar" :class="{ 'sidebar--collapsed': collapsed }">

    <!-- Ambient glow top -->
    <div class="sidebar__glow" aria-hidden="true"></div>

    <!-- Logo -->
    <div class="sidebar__header">
      <div class="sidebar__brand">
        <div class="sidebar__gem">
          <svg width="18" height="18" viewBox="0 0 36 36" fill="none">
            <path d="M18 3L33 11.5V24.5L18 33L3 24.5V11.5L18 3Z" stroke="#D4AF37" stroke-width="1.5"/>
            <path d="M18 10L27 15V25L18 30L9 25V15L18 10Z" fill="rgba(212,175,55,0.15)"/>
            <circle cx="18" cy="18" r="3.5" fill="#D4AF37"/>
          </svg>
        </div>
        <transition name="fade-x">
          <div v-if="!collapsed" class="sidebar__brand-text">
            <span class="sidebar__name">El Maestro</span>
            <span class="sidebar__sub">Administration</span>
          </div>
        </transition>
      </div>
      <button class="sidebar__toggle" @click="$emit('toggle')" :title="collapsed ? 'Développer' : 'Réduire'">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
          <path v-if="!collapsed" d="M11 17l-5-5 5-5M17 17l-5-5 5-5"/>
          <path v-else d="M13 7l5 5-5 5M7 7l5 5-5 5"/>
        </svg>
      </button>
    </div>

    <!-- Nav -->
    <nav class="sidebar__nav">
      <div v-for="group in navGroups" :key="group.label" class="nav-group">
        <transition name="fade-x">
          <p v-if="!collapsed" class="nav-group__label">{{ group.label }}</p>
        </transition>
        <ul>
          <li v-for="item in group.items" :key="item.to">
            <RouterLink :to="item.to" class="nav-link" :class="{ 'nav-link--active': isActive(item.to) }" :title="collapsed ? item.label : ''">
              <span class="nav-link__active-bar" v-if="isActive(item.to)"></span>
              <span class="nav-link__icon" v-html="item.icon"></span>
              <transition name="fade-x">
                <span v-if="!collapsed" class="nav-link__label">{{ item.label }}</span>
              </transition>
              <transition name="fade-x">
                <span v-if="!collapsed && item.badge" class="nav-link__badge">{{ item.badge }}</span>
              </transition>
              <span v-if="collapsed && item.badge" class="nav-link__dot"></span>
            </RouterLink>
          </li>
        </ul>
      </div>
    </nav>

    <!-- Footer user -->
    <div class="sidebar__footer">
      <div class="sidebar__user-row">
        <div class="sidebar__avatar">{{ userInitial }}</div>
        <transition name="fade-x">
          <div v-if="!collapsed" class="sidebar__user-meta">
            <span class="sidebar__user-name">{{ adminName }}</span>
            <span class="sidebar__user-badge">Super Admin</span>
          </div>
        </transition>
      </div>
      <transition name="fade-x">
        <button v-if="!collapsed" class="sidebar__logout" @click="handleLogout" title="Déconnexion">
          <svg width="13" height="13" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M3 3a1 1 0 011 1v12a1 1 0 11-2 0V4a1 1 0 011-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/>
          </svg>
        </button>
      </transition>
    </div>
  </aside>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'

defineProps({ collapsed: { type: Boolean, default: false } })
defineEmits(['toggle'])

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const adminName = computed(() => authStore.user?.name || 'Admin')
const userInitial = computed(() => adminName.value.charAt(0).toUpperCase())
const isActive = (p) => route.path === p || route.path.startsWith(p + '/')

const handleLogout = async () => {
  await authStore.logout?.()
  router.push('/admin/login')
}

const ic = {
  dash: `<svg width="15" height="15" viewBox="0 0 20 20" fill="currentColor"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/></svg>`,
  orders: `<svg width="15" height="15" viewBox="0 0 20 20" fill="currentColor"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 1 1 0 000 2H6a2 2 0 00-2 2v6a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2a1 1 0 100-2 2 2 0 012 2v8a2 2 0 01-2 2H6a2 2 0 01-2-2V5z" clip-rule="evenodd"/></svg>`,
  resa: `<svg width="15" height="15" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/></svg>`,
  menu: `<svg width="15" height="15" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h6a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/></svg>`,
  stats: `<svg width="15" height="15" viewBox="0 0 20 20" fill="currentColor"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5H3v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9H8V7zM14 4a1 1 0 011-1h2v13h-3V4z"/></svg>`,
  set: `<svg width="15" height="15" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.532 1.532 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.532 1.532 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/></svg>`,
  notif: `<svg width="15" height="15" viewBox="0 0 20 20" fill="currentColor"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/></svg>`,
}

const navGroups = [
  { label: 'PRINCIPAL', items: [
    { to: '/admin/dashboard',     label: 'Tableau de bord', icon: ic.dash },
    { to: '/admin/orders',        label: 'Commandes',       icon: ic.orders, badge: 12 },
    { to: '/admin/reservations',  label: 'Réservations',    icon: ic.resa,   badge: 8 },
    { to: '/admin/menu',          label: 'Menu',            icon: ic.menu },
  ]},
  { label: 'ANALYSE & VEILLE', items: [
    { to: '/admin/analytics',     label: 'Statistiques',    icon: ic.stats },
    { to: '/admin/notifications', label: 'Notifications',   icon: ic.notif,  badge: 3 },
    { to: '/admin/settings',      label: 'Paramètres',      icon: ic.set },
  ]},
]
</script>

<style scoped>
@import '../../styles/admin-tokens.css';

.sidebar {
  position: sticky;
  top: 0;
  align-self: flex-start;
  width: var(--sidebar-w);
  height: 100vh;
  background: #0D0D0D;
  border-right: 1px solid var(--border);
  display: flex;
  flex-direction: column;
  transition: width var(--t-l);
  z-index: var(--z-sticky);
  overflow: hidden;
  flex-shrink: 0;
}
.sidebar--collapsed { width: var(--sidebar-c); }

/* Ambient glow */
.sidebar__glow {
  position: absolute; top: -60px; left: -40px;
  width: 200px; height: 200px; border-radius: 50%;
  background: radial-gradient(circle, rgba(212,175,55,0.08) 0%, transparent 70%);
  pointer-events: none;
}

/* Header */
.sidebar__header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 0 var(--s4);
  height: var(--topbar-h);
  border-bottom: 1px solid var(--border);
  flex-shrink: 0;
}
.sidebar__brand { display: flex; align-items: center; gap: 12px; min-width: 0; }

.sidebar__gem {
  width: 36px; height: 36px; flex-shrink: 0;
  border-radius: var(--r);
  background: linear-gradient(145deg, rgba(212,175,55,0.12), rgba(212,175,55,0.04));
  border: 1px solid rgba(212,175,55,0.3);
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 0 20px rgba(212,175,55,0.1);
}

.sidebar__brand-text { min-width: 0; overflow: hidden; }
.sidebar__name {
  display: block;
  font-family: var(--font-display);
  font-size: 15px; font-weight: 700;
  color: var(--text);
  white-space: nowrap; letter-spacing: -0.01em;
}
.sidebar__sub {
  display: block; font-size: 9px;
  color: var(--gold); letter-spacing: 0.14em;
  text-transform: uppercase; margin-top: 1px;
}

.sidebar__toggle {
  width: 24px; height: 24px; flex-shrink: 0;
  border-radius: var(--r-sm);
  border: 1px solid var(--border);
  background: transparent; color: var(--text-3);
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; transition: all var(--t);
}
.sidebar__toggle:hover { border-color: var(--border-gold); color: var(--gold); }

/* Nav */
.sidebar__nav {
  flex: 1; overflow-y: auto; overflow-x: hidden;
  padding: var(--s5) var(--s3);
  display: flex; flex-direction: column; gap: var(--s6);
}
.nav-group ul { list-style: none; display: flex; flex-direction: column; gap: 2px; }

.nav-group__label {
  font-size: 9px; font-weight: 700;
  color: var(--text-4); letter-spacing: 0.15em;
  text-transform: uppercase;
  padding: 0 var(--s3); margin-bottom: var(--s2);
  white-space: nowrap; overflow: hidden;
}

.nav-link {
  position: relative;
  display: flex; align-items: center; gap: 11px;
  padding: 9px 10px;
  border-radius: var(--r);
  color: var(--text-2); text-decoration: none;
  transition: all var(--t); overflow: hidden;
  white-space: nowrap;
}
.nav-link:hover { background: rgba(255,255,255,0.04); color: var(--text); }

.nav-link--active {
  background: linear-gradient(135deg, rgba(212,175,55,0.14), rgba(212,175,55,0.06));
  color: var(--gold);
  border: 1px solid rgba(212,175,55,0.2);
}
.nav-link--active:hover { background: linear-gradient(135deg, rgba(212,175,55,0.18), rgba(212,175,55,0.08)); }

.nav-link__active-bar {
  position: absolute; left: 0; top: 20%; bottom: 20%;
  width: 2px; border-radius: 99px;
  background: var(--gold);
  box-shadow: 0 0 8px rgba(212,175,55,0.6);
}

.nav-link__icon { display: flex; align-items: center; flex-shrink: 0; }
.nav-link__label { font-size: 13px; font-weight: 500; flex: 1; }

.nav-link__badge {
  font-size: 9px; font-weight: 700;
  background: rgba(239,68,68,0.15);
  color: #ef4444;
  border: 1px solid rgba(239,68,68,0.25);
  border-radius: 99px; padding: 1px 6px; flex-shrink: 0;
}

.nav-link__dot {
  position: absolute; top: 6px; right: 6px;
  width: 5px; height: 5px; border-radius: 50%;
  background: #ef4444;
  animation: pulse-gold 2s infinite;
}

/* Footer */
.sidebar__footer {
  border-top: 1px solid var(--border);
  padding: var(--s3) var(--s3);
  display: flex; align-items: center; gap: var(--s3);
  flex-shrink: 0;
}
.sidebar__user-row { display: flex; align-items: center; gap: 10px; flex: 1; min-width: 0; }

.sidebar__avatar {
  width: 30px; height: 30px; flex-shrink: 0; border-radius: 50%;
  background: linear-gradient(135deg, #D4AF37, #B8860B);
  color: #0A0A0A; font-size: 11px; font-weight: 800;
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 0 12px rgba(212,175,55,0.3);
}
.sidebar__user-meta { min-width: 0; overflow: hidden; }
.sidebar__user-name { display: block; font-size: 12px; font-weight: 600; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sidebar__user-badge { display: inline-block; font-size: 8px; font-weight: 700; color: var(--gold); letter-spacing: 0.1em; text-transform: uppercase; margin-top: 1px; }

.sidebar__logout {
  width: 26px; height: 26px; flex-shrink: 0;
  border: 1px solid var(--border);
  border-radius: var(--r-sm);
  background: transparent; color: var(--text-3);
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; transition: all var(--t);
}
.sidebar__logout:hover { border-color: rgba(239,68,68,0.3); color: #ef4444; background: rgba(239,68,68,0.06); }

/* Transitions */
.fade-x-enter-active, .fade-x-leave-active { transition: opacity 150ms ease, transform 150ms ease; }
.fade-x-enter-from { opacity: 0; transform: translateX(-8px); }
.fade-x-leave-to { opacity: 0; transform: translateX(-8px); }

@media (max-width: 768px) {
  .sidebar {
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    z-index: var(--z-fixed);
    transform: translateX(calc(-1 * var(--sidebar-w)));
    transition: transform var(--t-l), width var(--t-l);
  }
  .sidebar:not(.sidebar--collapsed) { transform: translateX(0); }
  .sidebar--collapsed { width: var(--sidebar-w); transform: translateX(calc(-1 * var(--sidebar-w))); }
}
</style>
