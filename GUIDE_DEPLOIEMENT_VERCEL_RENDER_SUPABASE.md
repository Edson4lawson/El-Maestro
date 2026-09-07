# 🚀 Guide Complet de Déploiement : El Maestro

Ce guide vous accompagne pas à pas pour déployer l'intégralité de l'application **El Maestro** sur le cloud en production avec :

1. **Base de Données** : Supabase ou Neon (PostgreSQL) ou Aiven (MySQL)
2. **Backend API (PHP)** : Render (Web Service Docker)
3. **Frontend (Vue 3 / Vite)** : Vercel

---

## 📋 Table des Matières

1. [Étape 1 : Déployer la Base de Données (Supabase ou Neon)](#étape-1--déployer-la-base-de-données)
2. [Étape 2 : Déployer le Backend sur Render](#étape-2--déployer-le-backend-sur-render)
3. [Étape 3 : Déployer le Frontend sur Vercel](#étape-3--déployer-le-frontend-sur-vercel)
4. [Étape 4 : Vérification Finale & Connexion](#étape-4--vérification-finale)

---

## 🗄️ Étape 1 : Déployer la Base de Données

Vous avez le choix entre **Supabase** (recommandé), **Neon**, ou un hébergeur **MySQL (Aiven / Railway)**.

### Option A : Supabase (PostgreSQL - Recommandé)

1. Rendez-vous sur [supabase.com](https://supabase.com) et créez un compte.
2. Cliquez sur **"New project"** :
   - **Name** : `el-maestro`
   - **Database Password** : Définissez un mot de passe fort (notez-le précieusement).
   - **Region** : Choisissez la région la plus proche (ex: `eu-central-1` Francfort ou Paris).
3. Une fois le projet créé, rendez-vous dans le menu **SQL Editor** (icône `>_` à gauche).
4. Cliquez sur **"New query"**, ouvrez le fichier [`database/supabase_neon_postgres.sql`](file:///c:/laragon/www/El%20maestro/database/supabase_neon_postgres.sql), copiez l'intégralité de son contenu et collez-le dans l'éditeur.
5. Cliquez sur **"Run"** (ou Ctrl+Entrée). Toutes les tables, index, plats et l'administrateur par défaut sont créés !
6. Récupérez votre chaîne de connexion :
   - Allez dans **Project Settings** > **Database** > **Connection string** > onglet **URI**.
   - Copiez l'URL (format: `postgresql://postgres.[ref]:[PASSWORD]@aws-0-[region].pooler.supabase.com:6543/postgres`).
   - Remplacez `[PASSWORD]` par votre vrai mot de passe.

---

### Option B : Neon (PostgreSQL Serverless)

1. Rendez-vous sur [neon.tech](https://neon.tech) et créez un projet.
2. Allez dans l'onglet **SQL Editor**, collez le contenu de [`database/supabase_neon_postgres.sql`](file:///c:/laragon/www/El%20maestro/database/supabase_neon_postgres.sql) et cliquez sur **Run**.
3. Dans votre tableau de bord Neon, copiez la chaîne **Connection Details** (format: `postgresql://neondb_owner:password@ep-xyz.eu-central-1.aws.neon.tech/neondb?sslmode=require`).

---

## ⚙️ Étape 2 : Déployer le Backend sur Render

1. Rendez-vous sur [render.com](https://render.com) et connectez votre compte GitHub.
2. Cliquez sur **"New +"** > **"Web Service"**.
3. Sélectionnez votre repository GitHub `El-Maestro`.
4. Configurez les champs du service :
   - **Name** : `el-maestro-api`
   - **Region** : Même région que votre BDD (ex: `Frankfurt (EU Central)`).
   - **Branch** : `main`
   - **Root Directory** : `backend`
   - **Runtime** : **Docker** (Render détectera automatiquement le fichier `Dockerfile` dans le dossier `backend`).
   - **Instance Type** : **Free**
5. Dans la section **Environment Variables**, ajoutez :

| Clé            | Valeur                                       | Description                  |
| -------------- | -------------------------------------------- | ---------------------------- |
| `DATABASE_URL` | `postgresql://...` (votre URL Supabase/Neon) | Chaîne de connexion complète |
| `FRONTEND_URL` | `https://votre-app.vercel.app`               | URL de votre frontend Vercel |

_(Note : Si vous utilisez des variables MySQL individuelles : `DB_DRIVER=mysql`, `DB_HOST=...`, `DB_PORT=3306`, `DB_NAME=...`, `DB_USER=...`, `DB_PASS=...`)_

6. Cliquez sur **"Create Web Service"**.
7. Render va construire l'image Docker et démarrer le serveur. Une fois déployé, notez l'URL fournie par Render (ex: `https://el-maestro-api.onrender.com`).

---

## 💻 Étape 3 : Déployer le Frontend sur Vercel

1. Rendez-vous sur [vercel.com](https://vercel.com) et connectez-vous avec GitHub.
2. Cliquez sur **"Add New..."** > **"Project"**.
3. Importez le repository `El-Maestro`.
4. Configurez le projet :
   - **Framework Preset** : **Vite**
   - **Root Directory** : Cliquez sur **Edit** et sélectionnez le dossier `frontend`.
   - **Build Command** : `npm run build` (par défaut)
   - **Output Directory** : `dist` (par défaut)
5. Dans la section **Environment Variables**, ajoutez :

| Nom            | Valeur                                                                  |
| -------------- | ----------------------------------------------------------------------- |
| `VITE_API_URL` | `https://el-maestro-api.onrender.com/api` (URL de votre backend Render) |

6. Cliquez sur **"Deploy"**.
7. En quelques secondes, votre application est en ligne avec HTTPS et CDN mondial !

---

## ✅ Étape 4 : Vérification Finale & Connexion

1. **Vérifier l'API** :
   - Rendez-vous sur `https://votre-backend-render.onrender.com/api`
   - Vous devez recevoir le JSON listant tous les plats du menu.
2. **Accéder à l'interface d'administration** :
   - Sur votre site Vercel, accédez à `/admin/login`
   - **Identifiant par défaut** : `admin@elmaestro.bj`
   - **Mot de passe** : `admin123`
3. **Tester les fonctionnalités** :
   - Passer une commande test.
   - Effectuer une réservation.
   - Consulter les stats dans le dashboard admin.

---

🎉 **Votre application El Maestro est 100% opérationnelle en production !**
