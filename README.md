# Restaurant EL MAESTRO

## 📁 Structure du Projet

```
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

## 📋 Fonctionnalités

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
