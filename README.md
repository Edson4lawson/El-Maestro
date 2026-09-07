# 🍽️ Restaurant EL MAESTRO

Application web gastronomique moderne pour restaurant avec menu dynamique, réservations en ligne, système de commande en temps réel, programme de fidélité et tableau de bord d'administration sécurisé.

---

## 🏗️ Architecture & Technologies

- **Frontend** : Vue.js 3, Vite, Tailwind CSS, Pinia, Vue Router, Lucide Icons, GSAP, Chart.js
- **Backend API** : PHP 8.2 (RESTful), Architecture MVC, PDO multi-SGBD
- **Base de Données** : Compatible PostgreSQL (Supabase / Neon) & MySQL (Aiven / MariaDB / Laragon)
- **Déploiement Cloud** :
  - **Frontend** : [Vercel](https://vercel.com) (SPA, CDN mondial, HTTPS)
  - **Backend** : [Render](https://render.com) (Web Service Docker)
  - **Base de Données** : [Supabase](https://supabase.com) ou [Neon](https://neon.tech)

---

## 📁 Structure du Projet

```text
El-Maestro/
├── frontend/                     # Application Vue.js 3 (Vite + Tailwind)
│   ├── src/                     # Code source (composants, vues, stores, assets)
│   ├── public/                  # Fichiers statiques
│   ├── vercel.json              # Configuration de redirection SPA Vercel
│   ├── package.json             # Dépendances NPM
│   └── vite.config.js           # Configuration Vite
├── backend/                     # API REST PHP
│   ├── Dockerfile               # Conteneur Docker optimisé pour Render
│   └── api/                     # Points de terminaison API
│       ├── config/              # Configuration universelle BDD (PostgreSQL / MySQL)
│       ├── controllers/         # Contrôleurs API (Menu, Commandes, Réservations, Auth)
│       ├── models/              # Modèles de données
│       ├── assets/              # Images des plats servies par l'API
│       └── index.php            # Routeur principal & CORS
├── database/                    # Scripts SQL de migration
│   ├── supabase_neon_postgres.sql # Schéma complet pour Supabase & Neon (PostgreSQL)
│   └── el_maestro_full_production.sql # Schéma complet pour MySQL
├── tests/                       # Scripts de tests et utilitaires de diagnostic
├── GUIDE_DEPLOIEMENT_VERCEL_RENDER_SUPABASE.md # Guide pas-à-pas pour la production
└── README.md
```

---

## 🚀 Démarrage en Développement Local (Laragon)

### 1. Démarrer le Backend
```bash
cd backend/api
php -S localhost:8080
```
*L'API est accessible sur `http://localhost:8080/api`*

### 2. Démarrer le Frontend
```bash
cd frontend
npm install
npm run dev
```
*Le site est accessible sur `http://localhost:5173`*

---

## 🌐 Déploiement en Production Cloud

Consultez le guide détaillé : **[GUIDE_DEPLOIEMENT_VERCEL_RENDER_SUPABASE.md](GUIDE_DEPLOIEMENT_VERCEL_RENDER_SUPABASE.md)**

### Résumé des variables d'environnement

#### Backend sur Render :
| Variable | Exemple |
|---|---|
| `DATABASE_URL` | `postgresql://postgres:[PASSWORD]@[HOST]:5432/postgres?sslmode=require` |
| `FRONTEND_URL` | `https://votre-app.vercel.app` |

#### Frontend sur Vercel :
| Variable | Exemple |
|---|---|
| `VITE_API_URL` | `https://votre-backend-render.onrender.com/api` |

---

## 🔐 Identifiants d'Administration par Défaut

- **URL d'accès** : `/admin/login`
- **Email** : `admin@elmaestro.bj`
- **Mot de passe** : `admin123`
- **Rôle** : `super_admin`
- **Processus** : Authentification 2FA par code OTP temporaire avec session sécurisée par token.

---

## ✨ Fonctionnalités Principales

- 🍽️ **Menu Interactif & Recherche** : Filtrage par catégorie (Plats résistants, Entrées, Desserts, Boissons), notes sur 6 étoiles, fiches détaillées.
- 🛒 **Panier & Commandes** : Calcul automatique, suivi de livraison avec numéro de tracking unique.
- 📅 **Réservations en Ligne** : Choix de la date, de l'heure et du nombre de couverts.
- 💎 **Programme de Fidélité** : Consultation des points et statuts (Bronze, Argent, Or, Platine) par numéro de téléphone.
- 📊 **Dashboard Administrateur** :
  - Statistiques en direct sur les commandes, réservations et chiffre d'affaires.
  - Gestion complète du menu (ajout, modification, suppression, disponibilité).
  - Gestion des statuts de commande et validation des réservations.
