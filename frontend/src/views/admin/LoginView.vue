<template>
  <div class="login">

    <!-- ── LEFT PANEL — Branding ── -->
    <div class="brand">
      <!-- Grid pattern -->
      <div class="brand__grid" aria-hidden="true"></div>
      <!-- Gold orb -->
      <div class="brand__orb" aria-hidden="true"></div>

      <div class="brand__body">
        <!-- Logo -->
        <div class="brand__logo">
          <svg width="22" height="22" viewBox="0 0 36 36" fill="none">
            <path d="M18 3L33 11.5V24.5L18 33L3 24.5V11.5L18 3Z" stroke="#D4AF37" stroke-width="1.5"/>
            <path d="M18 10L27 15V25L18 30L9 25V15L18 10Z" fill="rgba(212,175,55,0.18)"/>
            <circle cx="18" cy="18" r="3.5" fill="#D4AF37"/>
          </svg>
        </div>

        <!-- Brand text -->
        <h1 class="brand__name">El Maestro</h1>
        <p class="brand__tagline">L'art de la gastronomie</p>

        <div class="brand__divider"></div>

        <!-- Quote -->
        <blockquote class="brand__quote">
          "L'excellence n'est pas un acte,<br>c'est une habitude."
          <cite>— Admin Portal</cite>
        </blockquote>

        <!-- Stats -->
        <div class="brand__stats">
          <div class="bstat"><span class="bstat__v">2 400+</span><span class="bstat__l">Commandes/mois</span></div>
          <div class="bstat__sep"></div>
          <div class="bstat"><span class="bstat__v">98%</span><span class="bstat__l">Satisfaction</span></div>
          <div class="bstat__sep"></div>
          <div class="bstat"><span class="bstat__v">5★</span><span class="bstat__l">Classement</span></div>
        </div>
      </div>

      <p class="brand__copy">© 2025 El Maestro — Tous droits réservés</p>
    </div>

    <!-- ── RIGHT PANEL — Form ── -->
    <div class="form-side">
      <div class="form-wrap">

        <!-- Header -->
        <div class="fh">
          <p class="fh__eyebrow">Espace Administration</p>
          <h2 class="fh__title">Connexion</h2>
          <p class="fh__sub">Accédez à votre tableau de bord sécurisé</p>
        </div>

        <!-- Error -->
        <transition name="slide-down">
          <div v-if="error" class="err-box" role="alert">
            <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            {{ error }}
          </div>
        </transition>

        <!-- Form -->
        <form @submit.prevent="submit" novalidate>

          <!-- Email -->
          <div class="field" :class="{ 'field--err': errs.email, 'field--focus': focused.email }">
            <label for="email" class="field__lbl">Adresse e-mail</label>
            <div class="field__wrap">
              <svg class="field__ico" width="14" height="14" viewBox="0 0 20 20" fill="currentColor">
                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
              </svg>
              <input id="email" v-model="form.email" type="email" 
              placeholder="mail@gmail.com"
                autocomplete="email" class="field__input"
                @focus="focused.email=true" @blur="focused.email=false; vEmail()" />
            </div>
            <span v-if="errs.email" class="field__err">{{ errs.email }}</span>
          </div>

          <!-- Password -->
          <div class="field" :class="{ 'field--err': errs.pw, 'field--focus': focused.pw }">
            <label for="pw" class="field__lbl">Mot de passe</label>
            <div class="field__wrap">
              <svg class="field__ico" width="14" height="14" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
              </svg>
              <input id="pw" v-model="form.pw" :type="showPw ? 'text' : 'password'"
                placeholder="••••••••••"
                 autocomplete="current-password" class="field__input field__input--pr"
                @focus="focused.pw=true" @blur="focused.pw=false" />
              <button type="button" class="field__eye" @click="showPw=!showPw">
                <svg v-if="!showPw" width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/></svg>
                <svg v-else width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd"/><path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.064 7 9.542 7 .847 0 1.669-.105 2.454-.303z"/></svg>
              </button>
            </div>
            <span v-if="errs.pw" class="field__err">{{ errs.pw }}</span>
          </div>

          <!-- Remember -->
          <label class="check-row">
            <input type="checkbox" v-model="form.remember" class="check-native" />
            <span class="check-box"><svg v-if="form.remember" width="9" height="9" viewBox="0 0 12 12" fill="none"><path d="M2 6l3 3 5-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
            <span class="check-text">Se souvenir de moi</span>
          </label>

          <!-- Submit -->
          <button type="submit" class="submit" :disabled="loading">
            <span v-if="!loading"  >
              Se connecter
              <!-- <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/></svg> -->
            </span>
            <span v-else class="submit__loader">
              <svg class="spin" width="16" height="16" viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2.5" stroke-dasharray="32" stroke-dashoffset="12" stroke-linecap="round"/>
              </svg>
              Connexion…
            </span>
          </button>
        </form>

        <p class="form-foot">Problème d'accès ? <a href="mailto:Edson4lawson@gmail.com">Contacter le support</a></p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const form = reactive({ email: '', pw: '', remember: false })
const loading = ref(false); const error = ref(''); const showPw = ref(false)
const focused = reactive({ email: false, pw: false })
const errs = reactive({ email: '', pw: '' })

const vEmail = () => {
  if (!form.email) { errs.email = 'Email requis'; return false }
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) { errs.email = 'Format invalide'; return false }
  errs.email = ''; return true
}

const submit = async () => {
  if (loading.value) return
  error.value = ''
  if (!vEmail()) return
  if (!form.pw) { errs.pw = 'Mot de passe requis'; return }
  errs.pw = ''
  loading.value = true
  try {
    const r = await authStore.login({ email: form.email, password: form.pw })
    if (r.success) router.push('/admin/otp')
    else error.value = r.message || 'Identifiants incorrects'
  } catch { error.value = 'Erreur réseau. Réessayez.' }
  finally { loading.value = false }
}
</script>

<style scoped>
@import '../../styles/admin-tokens.css';

.login { min-height: 100vh; display: grid; grid-template-columns: 1fr 1fr; font-family: var(--font-sans); 
  background:linear-gradient(to bottom right, black, rgba(255, 217, 0, 0.377));
   }

/* ── Brand Panel ── */
.brand {
  position: relative; overflow: hidden;
 
  border-right: 1px solid var(--border);
  display: flex; flex-direction: column;
}

.brand__grid {
  position: absolute; inset: 0; pointer-events: none;
  background-image:
    linear-gradient(rgba(212,175,55,0.05) 1px, transparent 1px),
    linear-gradient(90deg, rgba(212,175,55,0.05) 1px, transparent 1px);
  background-size: 52px 52px;
  mask-image: radial-gradient(ellipse at 40% 40%, black 20%, transparent 72%);
}

.brand__orb {
  position: absolute; width: 380px; height: 380px;
  top: -80px; left: -100px; border-radius: 50%; pointer-events: none;
  background: radial-gradient(circle, rgba(212,175,55,0.1) 0%, transparent 70%);
  filter: blur(40px);
}

.brand__body {
  flex: 1; display: flex; flex-direction: column;
  justify-content: center; padding: 60px 52px;
  position: relative; z-index: 1;
}

.brand__logo {
  width: 50px; height: 50px; border-radius: var(--r-md);
  border: 1px solid rgba(212,175,55,0.3);
  background: rgba(212,175,55,0.06);
  display: flex; align-items: center; justify-content: center;
  margin-bottom: var(--s6);
  box-shadow: 0 0 28px rgba(212,175,55,0.15);
  margin-bottom: 15px;
}

.brand__name {
  font-family: var(--font-display);
  font-size: 42px; font-weight: 700;
  color: var(--gold); letter-spacing: -0.02em; line-height: 1;
  margin-bottom: 10px;
}

.brand__tagline {
  font-size: 12px; font-weight: 500;
  color: var(--gold); letter-spacing: 0.12em;
  text-transform: uppercase; margin-top: var(--s2);
}

.brand__divider {
  width: 40px; height: 1px;
  background: linear-gradient(90deg, var(--gold), transparent);
  margin: var(--s8) 0;
}

.brand__quote {
  font-style: italic; font-size: 14px; color: var(--text-2);
  line-height: 1.7; border-left: 2px solid rgba(212,175,55,0.4);
  padding-left: var(--s4); margin-bottom: var(--s8);
}
.brand__quote cite { display: block; font-size: 10px; color: var(--text-3); font-style: normal; margin-top: var(--s2); letter-spacing: 0.05em; }

.brand__stats { display: flex; align-items: center; gap: var(--s5); }
.bstat__v { display: block; font-size: 22px; font-weight: 800; color: var(--text); letter-spacing: -0.02em; }
.bstat__l { display: block; font-size: 9px; color: var(--text-3); text-transform: uppercase; letter-spacing: 0.08em; margin-top: 2px; }
.bstat__sep { width: 1px; height: 32px; background: var(--border); }

.brand__copy { padding: 20px 52px; font-size: 10px; color: var(--text-4); border-top: 1px solid var(--border); position: relative; z-index: 1; }

/* ── Form Side ── */
.form-side { display: flex; align-items: center; justify-content: center; padding: var(--s8);  }
.form-wrap { width: 100%; max-width: 380px; }

.fh { margin-bottom: var(--s8); }
.fh__eyebrow { font-size: 9px; font-weight: 700; color: var(--gold); letter-spacing: 0.15em; text-transform: uppercase; margin-bottom: var(--s3); }
.fh__title { font-family: var(--font-display); font-size: 32px; font-weight: 700; color: var(--text); letter-spacing: -0.02em; line-height: 1; margin-bottom:5px; }
.fh__sub { font-size: 12px; color: var(--text-3); margin-bottom: 10px; }

/* Error */
.err-box {
  display: flex; align-items: center; gap: 8px;
  padding: 10px 14px; margin-bottom: var(--s5);
  background: rgba(239,68,68,0.08);
  border: 1px solid rgba(239,68,68,0.2);
  border-radius: var(--r); font-size: 12px; color: #ef4444;
}

/* Field */
.field { display: flex; flex-direction: column; gap: 6px; margin-bottom: var(--s5); }
.field__lbl {
  font-size: 10px; font-weight: 700;
  color: var(--text-3); letter-spacing: 0.1em; text-transform: uppercase;
  transition: color var(--t);
}
.field--focus .field__lbl { color: var(--gold); }

.field__wrap { position: relative; display: flex; align-items: center; margin-bottom: 10px; }
.field__ico { position: absolute; left: 13px; color: var(--text-3); pointer-events: none; transition: color var(--t); }
.field--focus .field__ico { color: var(--gold); }

.field__input {
  width: 100%; padding: 12px 13px 12px 40px;
  background: var(--card); border: 1px solid var(--border);
  border-radius: var(--r); font-family: var(--font-sans);
  font-size: 13px; color: var(--text); outline: none;
  transition: all var(--t);
}
.field__input::placeholder { color: var(--text-4); }
.field__input:focus { border-color: rgba(212,175,55,0.4); background: var(--card-hover); box-shadow: 0 0 0 3px rgba(212,175,55,0.07); }
.field--err .field__input { border-color: rgba(239,68,68,0.4); }
.field__input--pr { padding-right: 42px; }

.field__eye { position: absolute; right: 12px; background: none; border: none; cursor: pointer; color: var(--text-3); display: flex; align-items: center; transition: color var(--t); }
.field__eye:hover { color: var(--text-2); }
.field__err { font-size: 10px; color: #ef4444; margin-bottom: 10px; }

/* Checkbox */
.check-row { display: flex; align-items: center; gap: 8px; cursor: pointer; user-select: none; margin-bottom: var(--s6); }
.check-native { position: absolute; opacity: 0; width: 0; height: 0; }
.check-box {
  width: 15px; height: 15px; border-radius: 4px; flex-shrink: 0;
  border: 1px solid var(--border); background: var(--card);
  display: flex; align-items: center; justify-content: center;
  transition: all var(--t); color: #0A0A0A;
}
.check-native:checked ~ .check-box { background: var(--gold); border-color: var(--gold); }
.check-text { font-size: 12px; color: var(--text-2); margin-bottom: 10px; }

/* Submit */
.submit {
  width: 70%; padding: 13px; margin-left: 50px;
  background: linear-gradient(135deg, #D4AF37 0%, #C5A028 50%, #B8860B 100%);
  border: none; border-radius:15px; font-family: var(--font-sans);
  font-size: 13px; font-weight: 700; color: #0A0A0A;
  cursor: pointer; transition: all var(--t);
  box-shadow: 0 4px 20px rgba(212,175,55,0.3);
  display: flex; align-items: center; justify-content: center; gap: 8px;
}
.submit:hover:not(:disabled) { box-shadow: 0 8px 32px rgba(212,175,55,0.5); transform: translateY(-1px); }
.submit:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
.submit__loader { display: flex; align-items: center; gap: 8px; }
.spin { animation: spin 0.8s linear infinite; }

.form-foot { margin-top:10px; text-align: center; font-size: 11px; color: var(--text-3); }
.form-foot a { color: var(--gold); text-decoration: none; font-weight: 600; margin-left: 4px; transition: opacity var(--t); }
.form-foot a:hover { opacity: 0.75; }

/* Anim */
.slide-down-enter-active { animation: float-up 200ms var(--ease); }
.slide-down-leave-active { animation: float-up 150ms var(--ease) reverse; }

@media (max-width: 768px) {
  .login { grid-template-columns: 1fr; }
  .brand { display: none; }
  .form-side { padding: var(--s6); }
}
</style>
