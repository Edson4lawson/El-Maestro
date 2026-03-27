# Restaurant EL MAESTRO

## 📁 Structure du Projet

```text
El-Maestro/
├── frontend/          # Application Vue.js
│   ├── src/          # Code source Vue.js
│   ├── public/       # Fichiers statiques
│   ├── package.json  # Dépendances frontend
│   └── vite.config.js # Configuration Vite
├── backend/          # API PHP
│   └── api/          # Code source API
│       ├── config/   # Configuration BDD
│       ├── models/   # Modèles PHP
│       └── *.php     # Fichiers API
├── database/         # Base de données
│   └── database.sql  # Structure SQL
└── README.md         # Documentation
```

## 🚀 Installation

### Frontend (Vue.js)

```bash
cd frontend
npm install
npm run dev
```

### Backend (PHP)
- Serveur PHP requis (Apache/Nginx)
- Base de données MySQL/MariaDB
- Importer `database/database.sql`

## � Démarrage du Projet

### Lancer le Backend (API PHP)
```bash
cd backend/api
php -S localhost:8080
```
*Le backend sera accessible sur http://localhost:8080*

### Lancer le Frontend (Vue.js)
```bash
cd frontend
npm install
npm run dev
```
*Le frontend sera accessible sur http://localhost:5173*

### Ordre de démarrage recommandé
1. **D'abord le backend** : `cd backend/api && php -S localhost:8080`
2. **Ensuite le frontend** : `cd frontend && npm run dev`
3. **Accès au site** : http://localhost:5173
4. **Accès admin** : http://localhost:5173/admin/login

### Identifiants Admin
- **Email** : admin@elmaestro.bj
- **Mot de passe** : admin123
- **Processus** : Login → OTP → Dashboard

## �📋 Fonctionnalités

- 🍽️ Menu restaurant interactif
- 🛒 Panier d'achats
- 📱 Design responsive
- 🌗 Mode sombre/clair
- 📧 Newsletter
- 📸 Réseaux sociaux

## 🎨 Technologies

- **Frontend**: Vue 3, Vite, TailwindCSS
- **Backend**: PHP, MySQL
- **Animations**: GSAP, CSS3
