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
            <p class="ph__greet">Configuration</p>
            <h1 class="ph__title">Paramètres</h1>
            <p class="ph__date">Gérez les préférences de votre restaurant</p>
          </div>
          <div class="ph__right">
            <button class="btn-ghost">
              <RefreshCw class="btn-icon" />
              Réinitialiser
            </button>
            <button class="btn-gold-sm">
              <Save class="btn-icon" />
              Enregistrer
            </button>
          </div>
        </div>

        <!-- ── SETTINGS LAYOUT ── -->
        <div class="settings-layout">
          <!-- Sidebar Settings Nav -->
          <div class="settings-nav">
            <button 
              class="s-nav-item" 
              :class="{ 's-nav-item--active': activeSection === 'general' }"
              @click="activeSection = 'general'"
            >
              <Store class="s-nav-icon" />
              Général
            </button>
            <button 
              class="s-nav-item" 
              :class="{ 's-nav-item--active': activeSection === 'payment' }"
              @click="activeSection = 'payment'"
            >
              <CreditCard class="s-nav-icon" />
              Paiement & Taxes
            </button>
            <button 
              class="s-nav-item" 
              :class="{ 's-nav-item--active': activeSection === 'notifications' }"
              @click="activeSection = 'notifications'"
            >
              <Bell class="s-nav-icon" />
              Notifications
            </button>
            <button 
              class="s-nav-item" 
              :class="{ 's-nav-item--active': activeSection === 'security' }"
              @click="activeSection = 'security'"
            >
              <Shield class="s-nav-icon" />
              Sécurité
            </button>
            <button 
              class="s-nav-item" 
              :class="{ 's-nav-item--active': activeSection === 'team' }"
              @click="activeSection = 'team'"
            >
              <Users class="s-nav-icon" />
              Équipe
            </button>
          </div>

          <!-- Settings Content -->
          <div class="settings-content">
            <!-- Section Général -->
            <div v-show="activeSection === 'general'">
              <div class="panel">
                <div class="panel__head">
                  <div>
                    <h2 class="panel__title">Informations du Restaurant</h2>
                    <p class="panel__sub">Ces informations sont affichées publiquement aux clients.</p>
                  </div>
                </div>
                <div class="panel__body">
                  <form class="s-form" @submit.prevent>
                    <div class="form-row">
                      <div class="form-group">
                        <label>Nom du restaurant</label>
                        <input type="text" class="form-input" value="El Maestro" />
                      </div>
                      <div class="form-group">
                        <label>Numéro de téléphone</label>
                        <input type="text" class="form-input" value="+229 97 00 00 00" />
                      </div>
                    </div>

                    <div class="form-group">
                      <label>Adresse complète</label>
                      <input type="text" class="form-input" value="Haie Vive, Cotonou, Bénin" />
                    </div>

                    <div class="form-group">
                      <label>Description (Bio)</label>
                      <textarea class="form-textarea" rows="3">Le goût de l'excellence culinaire au cœur de Cotonou.</textarea>
                    </div>

                    <div class="form-group">
                      <label>Devise principale</label>
                      <select class="form-select">
                        <option value="XOF">FCFA (XOF)</option>
                        <option value="EUR">Euro (€)</option>
                        <option value="USD">Dollar ($)</option>
                      </select>
                    </div>
                  </form>
                </div>
              </div>

              <div class="panel mt-6">
                <div class="panel__head">
                  <div>
                    <h2 class="panel__title">Heures d'ouverture</h2>
                    <p class="panel__sub">Définissez vos horaires pour les commandes et réservations.</p>
                  </div>
                </div>
                <div class="panel__body">
                  <div class="hours-list">
                    <div class="hour-row" v-for="day in ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche']" :key="day">
                      <div class="hour-day">
                        <label class="toggle">
                          <input type="checkbox" checked />
                          <span class="toggle-slider"></span>
                        </label>
                        <span>{{ day }}</span>
                      </div>
                      <div class="hour-times">
                        <input type="time" class="form-input form-input--time" value="11:00" />
                        <span>à</span>
                        <input type="time" class="form-input form-input--time" value="23:30" />
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Section Paiement & Taxes -->
            <div v-show="activeSection === 'payment'">
              <div class="panel">
                <div class="panel__head">
                  <div>
                    <h2 class="panel__title">Méthodes de Paiement</h2>
                    <p class="panel__sub">Configurez les options de paiement acceptées.</p>
                  </div>
                </div>
                <div class="panel__body">
                  <form class="s-form">
                    <div class="payment-methods">
                      <div class="payment-method">
                        <label class="toggle">
                          <input type="checkbox" checked />
                          <span class="toggle-slider"></span>
                        </label>
                        <div class="payment-info">
                          <h4>Mobile Money - MTN</h4>
                          <p>Accepter les paiements via MTN Mobile Money</p>
                        </div>
                      </div>
                      <div class="payment-method">
                        <label class="toggle">
                          <input type="checkbox" checked />
                          <span class="toggle-slider"></span>
                        </label>
                        <div class="payment-info">
                          <h4>Mobile Money - Moov</h4>
                          <p>Accepter les paiements via Moov Africa</p>
                        </div>
                      </div>
                      <div class="payment-method">
                        <label class="toggle">
                          <input type="checkbox" />
                          <span class="toggle-slider"></span>
                        </label>
                        <div class="payment-info">
                          <h4>Cartes Bancaires</h4>
                          <p>Visa, Mastercard, etc.</p>
                        </div>
                      </div>
                      <div class="payment-method">
                        <label class="toggle">
                          <input type="checkbox" checked />
                          <span class="toggle-slider"></span>
                        </label>
                        <div class="payment-info">
                          <h4>Paiement en espèces</h4>
                          <p>Paiement à la livraison</p>
                        </div>
                      </div>
                    </div>
                  </form>
                </div>
              </div>

              <div class="panel mt-6">
                <div class="panel__head">
                  <div>
                    <h2 class="panel__title">Configuration des Taxes</h2>
                    <p class="panel__sub">Définissez les taux de taxes applicables.</p>
                  </div>
                </div>
                <div class="panel__body">
                  <form class="s-form">
                    <div class="form-group">
                      <label>TVA (%)</label>
                      <input type="number" class="form-input" value="18" step="0.1" />
                    </div>
                    <div class="form-group">
                      <label>Taxe de service (%)</label>
                      <input type="number" class="form-input" value="10" step="0.1" />
                    </div>
                  </form>
                </div>
              </div>
            </div>

            <!-- Section Notifications -->
            <div v-show="activeSection === 'notifications'">
              <div class="panel">
                <div class="panel__head">
                  <div>
                    <h2 class="panel__title">Préférences de Notification</h2>
                    <p class="panel__sub">Gérez comment et quand vous recevez des notifications.</p>
                  </div>
                </div>
                <div class="panel__body">
                  <form class="s-form">
                    <div class="notification-group">
                      <h4>Nouvelles commandes</h4>
                      <div class="notification-item">
                        <label class="toggle">
                          <input type="checkbox" checked />
                          <span class="toggle-slider"></span>
                        </label>
                        <span>Notification par email</span>
                      </div>
                      <div class="notification-item">
                        <label class="toggle">
                          <input type="checkbox" checked />
                          <span class="toggle-slider"></span>
                        </label>
                        <span>Notification SMS</span>
                      </div>
                      <div class="notification-item">
                        <label class="toggle">
                          <input type="checkbox" />
                          <span class="toggle-slider"></span>
                        </label>
                        <span>Notification push</span>
                      </div>
                    </div>

                    <div class="notification-group">
                      <h4>Nouvelles réservations</h4>
                      <div class="notification-item">
                        <label class="toggle">
                          <input type="checkbox" checked />
                          <span class="toggle-slider"></span>
                        </label>
                        <span>Notification par email</span>
                      </div>
                      <div class="notification-item">
                        <label class="toggle">
                          <input type="checkbox" />
                          <span class="toggle-slider"></span>
                        </label>
                        <span>Notification SMS</span>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>

            <!-- Section Sécurité -->
            <div v-show="activeSection === 'security'">
              <div class="panel">
                <div class="panel__head">
                  <div>
                    <h2 class="panel__title">Sécurité du Compte</h2>
                    <p class="panel__sub">Protégez votre compte et vos données.</p>
                  </div>
                </div>
                <div class="panel__body">
                  <form class="s-form">
                    <div class="form-group">
                      <label>Changer le mot de passe</label>
                      <input type="password" class="form-input" placeholder="Mot de passe actuel" />
                    </div>
                    <div class="form-group">
                      <label>Nouveau mot de passe</label>
                      <input type="password" class="form-input" placeholder="Nouveau mot de passe" />
                    </div>
                    <div class="form-group">
                      <label>Confirmer le nouveau mot de passe</label>
                      <input type="password" class="form-input" placeholder="Confirmer le mot de passe" />
                    </div>

                    <div class="form-group">
                      <label>Authentification à deux facteurs</label>
                      <div class="security-option">
                        <label class="toggle">
                          <input type="checkbox" checked />
                          <span class="toggle-slider"></span>
                        </label>
                        <div class="security-info">
                          <h4>Activer 2FA</h4>
                          <p>Recevoir un code par SMS pour vous connecter</p>
                        </div>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>

            <!-- Section Équipe -->
            <div v-show="activeSection === 'team'">
              <div class="panel">
                <div class="panel__head">
                  <div>
                    <h2 class="panel__title">Gestion de l'Équipe</h2>
                    <p class="panel__sub">Ajoutez et gérez les membres de votre équipe.</p>
                  </div>
                  <button class="btn-gold-sm">
                    <Plus class="btn-icon" />
                    Ajouter un membre
                  </button>
                </div>
                <div class="panel__body">
                  <div class="team-list">
                    <div class="team-member">
                      <div class="member-avatar">
                        <span>SA</span>
                      </div>
                      <div class="member-info">
                        <h4>Super Admin</h4>
                        <p>admin@elmaestro.bj</p>
                        <span class="role-badge super-admin">Super Admin</span>
                      </div>
                      <div class="member-actions">
                        <button class="btn-ghost">Modifier</button>
                        <button class="btn-ghost text-red-400">Supprimer</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </main>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Save, RefreshCw, Store, CreditCard, Bell, Shield, Users, Plus } from 'lucide-vue-next'
import AdminSidebar from '../../components/admin/AdminSidebar.vue'
import AdminTopBar from '../../components/admin/AdminTopBar.vue'

const sidebarCollapsed = ref(false)
const activeSection = ref('general')
</script>

<style scoped>
@import '../../styles/admin-tokens.css';

.dash { font-family: var(--font-sans); background: #0A0A0A; }

.orb { position: fixed; border-radius: 50%; pointer-events: none; filter: blur(100px); z-index: 0; opacity: 0.2; }
.orb--1 { width: 600px; height: 600px; top: -100px; right: -100px; background: radial-gradient(circle, rgba(212,175,55,0.08), transparent 70%); }
.orb--2 { width: 500px; height: 500px; bottom: -100px; left: -100px; background: radial-gradient(circle, rgba(99,102,241,0.05), transparent 70%); }

.ph { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: var(--s8); position: relative; z-index: 1; }
.ph__greet { font-size: 11px; color: var(--text-3); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 4px; }
.ph__title { font-family: var(--font-display); font-size: 36px; font-weight: 700; color: var(--text); letter-spacing: -0.02em; line-height: 1; }
.ph__date { font-size: 13px; color: var(--text-3); margin-top: 6px; }
.ph__right { display: flex; gap: var(--s3); align-items: center; }

.btn-ghost { display: inline-flex; align-items: center; gap: 7px; padding: 9px 16px; border-radius: var(--r); border: 1px solid rgba(255,255,255,0.08); background: rgba(255,255,255,0.02); font-size: 12px; font-weight: 600; color: var(--text-2); cursor: pointer; transition: all var(--t); }
.btn-ghost:hover { border-color: rgba(255,255,255,0.15); color: var(--text); background: rgba(255,255,255,0.06); }
.btn-gold-sm { display: inline-flex; align-items: center; gap: 7px; padding: 9px 18px; border-radius: var(--r); background: linear-gradient(135deg, #D4AF37, #B8860B); border: none; font-size: 12px; font-weight: 700; color: #0A0A0A; cursor: pointer; transition: all var(--t); box-shadow: 0 4px 20px rgba(212,175,55,0.3); }
.btn-gold-sm:hover { transform: translateY(-1px); box-shadow: 0 8px 32px rgba(212,175,55,0.45); }
.btn-icon { width: 14px; height: 14px; }

/* Layout */
.settings-layout { display: grid; grid-template-columns: 240px 1fr; gap: 6px; margin-left: 5px; margin-right: 4px; margin-top: 12px; position: relative; z-index: 1; }

/* Nav */
.settings-nav { display: flex; flex-direction: column; gap: 4px; }
.s-nav-item { display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: var(--r-md); background: transparent; border: none; color: var(--text-3); font-size: 14px; font-weight: 600; cursor: pointer; transition: all var(--t); text-align: left; }
.s-nav-item:hover { background: rgba(255,255,255,0.03); color: var(--text-2); }
.s-nav-item--active { background: rgba(212,175,55,0.1) !important; color: var(--gold) !important; box-shadow: inset 3px 0 0 var(--gold); }
.s-nav-icon { width: 18px; height: 18px; }

/* Content */
.settings-content { display: flex; flex-direction: column; gap: var(--s6); }

.panel { background: rgba(22, 22, 22, 0.4); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.06); border-radius: var(--r-xl); overflow: hidden; }
.panel__head { padding: 24px 32px; border-bottom: 1px solid rgba(255,255,255,0.03); }
.panel__title { font-size: 18px; font-weight: 600; color: var(--text); margin-bottom: 4px; }
.panel__sub { font-size: 13px; color: var(--text-4); }
.panel__body { padding: 32px; }

/* Form */
.s-form { display: flex; flex-direction: column; gap: 24px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
.form-group { display: flex; flex-direction: column; gap: 8px; }
.form-group label { font-size: 12px; font-weight: 600; color: var(--text-2); }

.form-input, .form-select, .form-textarea { width: 100%; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); border-radius: var(--r-md); padding: 12px 16px; color: var(--text); font-size: 14px; font-family: var(--font-sans); transition: all var(--t); outline: none; }
.form-input:focus, .form-select:focus, .form-textarea:focus { border-color: var(--gold); box-shadow: 0 0 0 3px rgba(212,175,55,0.1); background: rgba(0,0,0,0.4); }
.form-textarea { resize: vertical; min-height: 80px; }
.form-select { appearance: none; cursor: pointer; }

/* Hours List */
.hours-list { display: flex; flex-direction: column; gap: 16px; }
.hour-row { display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; background: rgba(255,255,255,0.02); border-radius: var(--r-md); border: 1px solid rgba(255,255,255,0.03); }
.hour-day { display: flex; align-items: center; gap: 16px; font-size: 14px; font-weight: 600; color: var(--text-2); width: 150px; }
.hour-times { display: flex; align-items: center; gap: 12px; color: var(--text-4); font-size: 13px; }
.form-input--time { width: auto; padding: 8px 12px; }

/* Toggle */
.toggle { position: relative; display: inline-block; width: 44px; height: 24px; }
.toggle input { opacity: 0; width: 0; height: 0; }
.toggle-slider { position: absolute; cursor: pointer; inset: 0; background-color: rgba(255,255,255,0.1); transition: .4s; border-radius: 34px; }
.toggle-slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: var(--text-3); transition: .4s; border-radius: 50%; }
.toggle input:checked + .toggle-slider { background-color: rgba(212,175,55,0.3); }
.toggle input:checked + .toggle-slider:before { transform: translateX(20px); background-color: var(--gold); }

/* Payment Methods */
.payment-methods { display: flex; flex-direction: column; gap: 20px; }
.payment-method { display: flex; align-items: center; gap: 16px; padding: 20px; background: rgba(255,255,255,0.02); border-radius: var(--r-md); border: 1px solid rgba(255,255,255,0.03); }
.payment-info h4 { font-size: 16px; font-weight: 600; color: var(--text); margin-bottom: 4px; }
.payment-info p { font-size: 13px; color: var(--text-4); }

/* Notifications */
.notification-group { margin-bottom: 32px; }
.notification-group h4 { font-size: 14px; font-weight: 600; color: var(--text-2); margin-bottom: 16px; text-transform: uppercase; letter-spacing: 0.05em; }
.notification-item { display: flex; align-items: center; gap: 16px; margin-bottom: 12px; }
.notification-item span { font-size: 14px; color: var(--text-3); }

/* Security */
.security-option { display: flex; align-items: center; gap: 16px; padding: 16px; background: rgba(255,255,255,0.02); border-radius: var(--r-md); border: 1px solid rgba(255,255,255,0.03); }
.security-info h4 { font-size: 14px; font-weight: 600; color: var(--text); margin-bottom: 4px; }
.security-info p { font-size: 13px; color: var(--text-4); }

/* Team */
.team-list { display: flex; flex-direction: column; gap: 16px; }
.team-member { display: flex; align-items: center; gap: 16px; padding: 20px; background: rgba(255,255,255,0.02); border-radius: var(--r-md); border: 1px solid rgba(255,255,255,0.03); }
.member-avatar { width: 48px; height: 48px; background: var(--gold); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #0A0A0A; font-size: 14px; }
.member-info { flex: 1; }
.member-info h4 { font-size: 16px; font-weight: 600; color: var(--text); margin-bottom: 4px; }
.member-info p { font-size: 13px; color: var(--text-4); margin-bottom: 8px; }
.role-badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
.role-badge.super-admin { background: rgba(212,175,55,0.2); color: var(--gold); }
.member-actions { display: flex; gap: 8px; }

/* Utility */
.text-red-400 { color: #ef4444; }
.mt-6 { margin-top: 24px; }

@media (max-width: 1024px) {
  .settings-layout { grid-template-columns: 1fr; }
  .settings-nav { flex-direction: row; overflow-x: auto; padding-bottom: 12px; }
  .s-nav-item { white-space: nowrap; }
}
@media (max-width: 768px) {
  .dash__main { margin-left: 0; }
  .form-row { grid-template-columns: 1fr; }
  .hour-row { flex-direction: column; align-items: flex-start; gap: 16px; }
  .payment-method, .notification-item, .security-option, .team-member { flex-direction: column; align-items: flex-start; gap: 12px; }
  .member-actions { width: 100%; justify-content: flex-end; }
}
</style>
