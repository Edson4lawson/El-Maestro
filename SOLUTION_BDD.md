# 🔧 **SOLUTION CONFIGURATION BASE DE DONNÉES**

## ❌ **Problème MySQL non trouvé**

Le script `setup_database.bat` ne trouve pas MySQL car :
- MySQL n'est pas dans le PATH Windows
- Laragon utilise sa propre configuration MySQL

---

## ✅ **SOLUTIONS RECOMMANDÉES**

### **Option 1 : Via Laragon (Recommandé)**
1. **Ouvrir Laragon**
2. **Cliquer sur "Database"** → **phpMyAdmin**
3. **Créer la base** `el_maestro`
4. **Importer les fichiers** :
   - `database/admin_tables.sql`
   - `database/database.sql`

### **Option 2 : Via HeidiSQL**
1. **Télécharger HeidiSQL** (gratuit)
2. **Se connecter** avec :
   - Hôte : `localhost` ou `127.0.0.1`
   - Utilisateur : `root`
   - Mot de passe : (vide)
   - Port : `3306`
3. **Créer base** `el_maestro`
4. **Importer les fichiers SQL**

### **Option 3 : Via ligne de commande Laragon**
```bash
# Ouvrir terminal Laragon
# Se déplacer dans le dossier
cd c:/laragon/www/El maestro

# Importer avec le binaire Laragon MySQL
"c:/laragon/bin/mysql/mysql.exe" -u root -p el_maestro < database/admin_tables.sql
"c:/laragon/bin/mysql/mysql.exe" -u root -p el_maestro < database/database.sql
```

---

## 🗄️ **CONTENU DES FICHIERS SQL**

### **admin_tables.sql**
- Table `admins` : administrateurs avec rôles
- Table `admin_sessions` : sessions OTP sécurisées
- Admin par défaut : `admin@elmaestro.bj` / `admin123`

### **database.sql**
- Table `plates` : plats du restaurant
- Table `reviews` : avis clients
- Table `orders` : commandes
- Table `reservations` : réservations
- Table `loyalty_users` : programme fidélité

---

## 🚀 **ÉTAPES SUIVANTES**

### **1. Base de données configurée**
✅ Importer les deux fichiers SQL dans `el_maestro`

### **2. Démarrer les serveurs**
```bash
# Frontend (déjà démarré sur :5177)
npm run dev

# Backend PHP
php -S localhost:8000 -t backend/api
```

### **3. Accéder admin**
- **URL** : http://localhost:5177/admin/login
- **Email** : admin@elmaestro.bj
- **Mot de passe** : admin123

---

## 📊 **VÉRIFICATION**

Une fois la base configurée :
1. ✅ **Tester l'API** : http://localhost:8000/backend/api/index.php
2. ✅ **Vérifier les tables** dans phpMyAdmin
3. ✅ **Se connecter** à l'interface admin

---

## 🎯 **RÉSULTAT ATTENDU**

- **Dashboard admin** 100% fonctionnel
- **Double authentification** (email + OTP)
- **Gestion complète** du restaurant
- **Interface responsive** et professionnelle

---

*Le dashboard admin est prêt, il ne manque plus que la configuration de la base de données !* 🎉
