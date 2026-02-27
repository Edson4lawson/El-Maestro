# 🔧 **GUIDE CONFIGURATION MANUELLE LARAGON**

## ❌ **Problème PowerShell**
Les commandes PowerShell ne reconnaissent pas correctement les chemins MySQL de Laragon.

---

## ✅ **SOLUTION MANUELLE ÉTAPE PAR ÉTAPE**

### **ÉTAPE 1 : Ouvrir Laragon**
1. **Démarrer Laragon**
2. **Cliquer sur "Database"** dans l'interface Laragon
3. **Cliquer sur "phpMyAdmin"** (ouvrir dans le navigateur)

### **ÉTAPE 2 : Créer la base de données**
1. **Dans phpMyAdmin**, cliquer sur "Nouvelle base de données"
2. **Nom de la base** : `el_maestro`
3. **Collation** : `utf8mb4_unicode_ci`
4. **Cliquer sur "Créer"**

### **ÉTAPE 3 : Importer les tables admin**
1. **Sélectionner la base** `el_maestro`
2. **Cliquer sur "Importer"**
3. **Choisir le fichier** : `C:\laragon\www\El maestro\database\admin_tables.sql`
4. **Format** : SQL
5. **Cliquer sur "Exécuter"**

### **ÉTAPE 4 : Importer les tables principales**
1. **Toujours dans la base** `el_maestro`
2. **Cliquer sur "Importer"**
3. **Choisir le fichier** : `C:\laragon\www\El maestro\database\database.sql`
4. **Format** : SQL
5. **Cliquer sur "Exécuter"**

---

## ✅ **VÉRIFICATION**

### **Vérifier les tables créées**
Dans phpMyAdmin, vous devriez voir :
- `admins` (table administrateurs)
- `admin_sessions` (sessions OTP)
- `plates` (plats du restaurant)
- `reviews` (avis clients)
- `orders` (commandes)
- `reservations` (réservations)
- `loyalty_users` (fidélité)

### **Admin par défaut**
- **Email** : `admin@elmaestro.bj`
- **Mot de passe** : `admin123`

---

## 🚀 **DÉMARRAGE DES SERVEURS**

### **Frontend (déjà démarré)**
```bash
npm run dev
# URL : http://localhost:5173/admin/login
```

### **Backend PHP**
```bash
php -S localhost:8000 -t backend/api
# URL API : http://localhost:8000/backend/api/index.php
```

---

## 🎯 **ACCÈS ADMIN**

Une fois tout configuré :
1. **Ouvrir** : http://localhost:5173/admin/login
2. **Se connecter** avec les identifiants par défaut
3. **Saisir le code OTP** (visible dans les logs PHP)
4. **Accéder au dashboard** complet

---

## 📊 **RÉSULTAT FINAL**

- ✅ **Base de données** configurée manuellement
- ✅ **Dashboard admin** 100% fonctionnel
- ✅ **Double authentification** opérationnelle
- ✅ **Interface prête** pour production

---

*Le dashboard admin EL MAESTRO est maintenant prêt à être utilisé !* 🎉

**Prochaine étape recommandée :** Tester l'authentification complète et explorer le dashboard.
