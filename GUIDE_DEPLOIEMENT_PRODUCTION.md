# 🚀 Guide de Déploiement en Production - El Maestro

Ce guide détaille les étapes exactes pour mettre en ligne le projet **El Maestro** (Frontend Vue 3 + Backend PHP REST API + MySQL).

---

## 📋 Table des Matières
1. [Bilan du Diagnostic Pré-Déploiement](#1-bilan-du-diagnostic-pré-déploiement)
2. [Structure des Dossiers à Déployer](#2-structure-des-dossiers-à-déployer)
3. [Étape 1 : Déploiement de la Base de Données](#3-étape-1--déploiement-de-la-base-de-données)
4. [Étape 2 : Configuration & Déploiement du Backend PHP](#4-étape-2--configuration--déploiement-du-backend-php)
5. [Étape 3 : Build & Déploiement du Frontend Vue 3](#5-étape-3--build--déploiement-du-frontend-vue-3)
6. [Méthodes d'Hébergement Recommandées](#6-méthodes-dhébergement-recommandées)
7. [Checklist Sécurité & Post-Déploiement](#7-checklist-sécurité--post-déploiement)

---

## 1. Bilan du Diagnostic Pré-Déploiement

| Composant | Statut | Détails & Correctifs apportés |
| :--- | :--- | :--- |
| **Frontend (Vue 3 / Vite)** | ✅ Prêt | Erreur d'asset manquant résolue (`HomeView.vue`), build Vite validé à 100%. |
| **Routage SPA (.htaccess)** | ✅ Prêt | Fichier `.htaccess` ajouté pour éviter les erreurs 404 lors du rechargement de page. |
| **Backend API (PHP 8)** | ✅ Prêt | Contrôleurs REST, CORS configurés, gestion des variables d'environnement renforcée. |
| **Sécurité Backend** | ✅ Prêt | `.htaccess` de protection créé pour bloquer l'accès public aux fichiers `.env` et `.sql`. |
| **Base de Données (MySQL)** | ✅ Prêt | Fichier SQL unifié disponible : `database/el_maestro_full_production.sql`. |

---

## 2. Structure des Dossiers à Déployer

### En hébergement classique (cPanel / Apache / Hostinger / OVH) :
```
public_html/ (ou www/)
│
├── index.html              <-- Fichiers du dossier frontend/dist/
├── assets/                 <-- Fichiers JS, CSS, images compilés du dossier frontend/dist/assets/
├── .htaccess               <-- Redirection SPA Vue Router
│
└── backend/
    └── api/                <-- Fichiers du dossier backend/api/
        ├── .env            <-- Vos identifiants de BDD de production
        ├── .htaccess       <-- Sécurisation des fichiers sensibles
        ├── index.php       <-- Point d'entrée de l'API
        ├── config/
        ├── controllers/
        ├── models/
        └── services/
```

---

## 3. Étape 1 : Déploiement de la Base de Données

1. Rendez-vous sur votre hébergeur (ex: **cPanel** > **Bases de données MySQL** ou **phpMyAdmin**).
2. Créez une nouvelle base de données (ex: `u123456_el_maestro`).
3. Créez un utilisateur MySQL avec tous les privilèges et générez un mot de passe fort.
4. Ouvrez **phpMyAdmin**, sélectionnez votre base de données et cliquez sur **Importer**.
5. Sélectionnez le fichier :
   ```
   database/el_maestro_full_production.sql
   ```
6. Exécutez l'importation.

> ℹ️ **Compte Administrateur par défaut après import :**
> - **Email** : `admin@elmaestro.bj`
> - **Mot de passe** : `admin123` *(À changer impérativement après votre 1ère connexion)*

---

## 4. Étape 2 : Configuration & Déploiement du Backend PHP

1. Créez le fichier `backend/api/.env` sur votre serveur avec vos vrais identifiants de production :
   ```env
   DB_HOST=localhost
   DB_NAME=u123456_el_maestro
   DB_USER=u123456_user
   DB_PASS=VotreMotDePasseFort123!
   ```
2. Téléversez le dossier `backend/api/` sur votre hébergeur dans le répertoire `/public_html/backend/api/`.
3. Testez l'API dans votre navigateur :
   ```
   https://votre-domaine.com/backend/api/
   ```
   Elle doit renvoyer les données des plats au format JSON.

---

## 5. Étape 3 : Build & Déploiement du Frontend Vue 3

1. Dans le dossier `frontend/`, créez ou modifiez `.env.production` :
   ```env
   VITE_API_URL=https://votre-domaine.com/backend/api
   ```
2. Compilez les fichiers pour la production :
   ```bash
   cd frontend
   npm run build
   ```
3. Tous les fichiers prêts pour la production sont générés dans `frontend/dist/`.
4. Téléversez tout le contenu de `frontend/dist/` à la racine de votre site (`public_html/`).

---

## 6. Méthodes d'Hébergement Recommandées

### Option A : Hébergement Mutualisé / cPanel (Hostinger, OVH, LWS, etc.)
- **Frontend + Backend** : Dans le même espace `public_html/`.
- **Avantage** : Économique, simple, 1 seul nom de domaine, HTTPS inclus.

### Option B : Découplé (Vercel / Netlify + Backend PHP)
- **Frontend** : Déployez le dossier `frontend/` sur **Vercel** ou **Netlify** (commande `npm run build`, output `dist`).
- **Backend** : Déployez `backend/api/` sur un hébergeur PHP / VPS.
- **Variable Vercel** : Définissez `VITE_API_URL=https://api.votre-domaine.com` dans le tableau de bord Vercel.

---

## 7. Checklist Sécurité & Post-Déploiement

- [ ] **HTTPS Actif** : Assurez-vous que le certificat SSL (Let's Encrypt) est activé.
- [ ] **Mot de passe Admin modifié** : Changez le mot de passe du compte `admin@elmaestro.bj`.
- [ ] **Fichiers de test non uploadés** : Ne pas téléverser les scripts de debug locaux (`check_plats.php`, `cookie.txt`, `test_admin_login.php`).
- [ ] **Fichier `.env` protégé** : Vérifiez que `https://votre-domaine.com/backend/api/.env` renvoie bien une erreur 403 Forbidden (bloqué par le `.htaccess`).
- [ ] **Navigation SPA testée** : Actualisez la page sur `/menu` ou `/fidelite` pour vérifier l'absence d'erreur 404.
