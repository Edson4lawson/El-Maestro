# 🔧 **DIAGNOSTIC : PLATS NON VISIBLES**

## ❌ **PROBLÈME IDENTIFIÉ**

### **Le problème :**
- **Frontend** : Utilise port 8000 pour l'API
- **Backend** : Tourne sur port 8080
- **Résultat** : Les plats ne se chargent pas

---

## 🔍 **CAUSE DU PROBLÈME**

### **Dans `frontend/src/services/api.js` :**
```javascript
// AVANT (incorrect)
const API_URL = 'http://localhost:8000/api'

// APRÈS (corrigé)
const API_URL = 'http://localhost:8080/api'
```

### **Le frontend essayait de contacter :**
```
http://localhost:8000/api/plates
```

**Mais le backend tourne sur :**
```
http://localhost:8080/api/plates
```

---

## 🚀 **SOLUTION APPLIQUÉE**

### **J'ai corrigé l'URL de l'API :**
- ✅ **Port changé** : 8000 → 8080
- ✅ **Services mis à jour** : Tous les services utilisent le bon port

---

## 🎯 **ÉTAPES POUR VOIR LES PLATS**

### **1. Démarrer le backend**
```bash
cd backend/api
php -S localhost:8080
```

### **2. Démarrer le frontend**
```bash
cd frontend
npm run dev
```

### **3. Accéder au site**
```
http://localhost:5173
```

---

## 📊 **VÉRIFICATION**

### **Les plats devraient maintenant apparaître :**
- ✅ **Page d'accueil** : 3 plats signature
- ✅ **Page menu** : Tous les plats disponibles
- ✅ **Images** : Affichées correctement

---

## 🎉 **TESTEZ MAINTENANT**

### **Après avoir corrigé l'URL :**
1. **Rafraîchir** la page d'accueil
2. **Vérifier** que les plats s'affichent
3. **Tester** l'ajout au panier

---

**Le problème était simplement une incompatibilité de ports entre le frontend et le backend !** 🔧

**Maintenant les plats devraient s'afficher correctement.** 🎯
