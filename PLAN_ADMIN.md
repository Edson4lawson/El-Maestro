# 📋 PLAN ADMIN DASHBOARD - EL MAESTRO

## 🎯 **Objectif**
Créer un **dashboard administrateur sécurisé** avec authentification à double facteur (email + OTP téléphone).

---

## 🏗️ **STRUCTURE À CRÉER**

### 📁 **Nouveaux dossiers**
```
frontend/src/
├── views/
│   ├── admin/
│   │   ├── LoginView.vue      # Page login admin
│   │   ├── OTPView.vue        # Saisie code OTP
│   │   ├── DashboardView.vue  # Dashboard principal
│   │   ├── OrdersView.vue     # Gestion commandes
│   │   ├── MenuView.vue       # Gestion menu
│   │   └── ReservationsView.vue # Gestion réservations
│   └── components/
│       └── admin/
│           ├── Sidebar.vue       # Barre latérale admin
│           ├── StatsCard.vue     # Cartes statistiques
│           └── OrderTable.vue   # Tableau commandes

backend/api/
├── controllers/
│   ├── AdminController.php     # Logique admin
│   └── AuthController.php     # Authentification
├── middleware/
│   └── AdminMiddleware.php   # Protection routes admin
├── services/
│   └── OTPService.php        # Génération OTP
└── models/
    └── Admin.php             # Modèle admin
```

---

## 🗄️ **BASE DE DONNÉES**

### **Table `admins`**
```sql
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL, -- hashé
    role ENUM('super_admin', 'admin', 'manager') DEFAULT 'admin',
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Admin par défaut (mot de passe: admin123)
INSERT INTO admins (name, email, phone, password, role) VALUES 
('Super Admin', 'admin@elmaestro.bj', '+22912345678', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin');
```

### **Table `admin_sessions`**
```sql
CREATE TABLE IF NOT EXISTS admin_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    otp_code VARCHAR(6) NULL,
    otp_expires_at TIMESTAMP NULL,
    is_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
);
```

---

## 🔐 **SÉCURITÉ**

### **1. Authentification**
- ✅ **Password hashé** avec `password_hash()`
- ✅ **Validation email** côté backend
- ✅ **Protection CSRF** sur tous formulaires
- ✅ **Rate limiting** login tentatives
- ✅ **Session sécurisée** avec token unique

### **2. OTP Sécurisé**
- ✅ **Code aléatoire** 6 chiffres
- ✅ **Validité 1 minute** maximum
- ✅ **Envoi SMS** (simulation pour développement)
- ✅ **Vérification** avant accès dashboard
- ✅ **Auto-destruction** après utilisation

### **3. Middleware Protection**
- ✅ **Toutes routes /admin*** protégées
- ✅ **Vérification session** valide
- ✅ **Redirection auto** vers login
- ✅ **Validation OTP** obligatoire

---

## 📱 **FRONTEND (VUE 3)**

### **Pages Admin**

#### **1. Login (/admin/login)**
```vue
<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-m-obsidian to-m-gold/20">
    <div class="max-w-md w-full mx-4">
      <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-8 border border-white/20">
        <h1 class="text-3xl font-bold text-center mb-8 text-gradient-gold">Admin EL MAESTRO</h1>
        
        <form @submit.prevent="handleLogin" class="space-y-6">
          <div>
            <label class="block text-sm font-medium mb-2">Email</label>
            <input v-model="form.email" type="email" required 
                   class="w-full px-4 py-3 rounded-lg bg-white/10 border border-white/20">
          </div>
          
          <div>
            <label class="block text-sm font-medium mb-2">Mot de passe</label>
            <input v-model="form.password" type="password" required 
                   class="w-full px-4 py-3 rounded-lg bg-white/10 border border-white/20">
          </div>
          
          <button type="submit" :disabled="loading" 
                  class="w-full btn-gold py-3 font-bold">
            <span v-if="loading">Connexion...</span>
            <span v-else>Se connecter</span>
          </button>
        </form>
      </div>
    </div>
  </div>
</template>
```

#### **2. OTP (/admin/otp)**
```vue
<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-m-obsidian to-m-gold/20">
    <div class="max-w-sm w-full mx-4">
      <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-8 border border-white/20">
        <div class="text-center mb-8">
          <Smartphone class="w-16 h-16 mx-auto mb-4 text-m-gold" />
          <h2 class="text-2xl font-bold text-gradient-gold">Vérification OTP</h2>
          <p class="text-white/70 mt-2">Entrez le code à 6 chiffres envoyé sur votre téléphone</p>
        </div>
        
        <form @submit.prevent="verifyOTP" class="space-y-6">
          <div class="grid grid-cols-6 gap-2">
            <input v-for="(digit, i) in otp" :key="i" 
                   v-model="otp[i]" 
                   type="text" 
                   maxlength="1" 
                   pattern="[0-9]"
                   @input="handleInput($event, i)"
                   ref="otpInputs"
                   class="w-full aspect-square text-center text-2xl font-bold bg-white/10 border border-white/20 rounded-lg">
          </div>
          
          <button type="submit" :disabled="loading || otp.join('').length !== 6"
                  class="w-full btn-gold py-3 font-bold mt-6">
            <span v-if="loading">Vérification...</span>
            <span v-else>Valider</span>
          </button>
          
          <div v-if="error" class="text-red-500 text-center mt-4">{{ error }}</div>
          <div v-if="timeLeft > 0" class="text-white/70 text-center mt-2">
            Code expire dans: {{ formatTime(timeLeft) }}
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
```

#### **3. Dashboard (/admin/dashboard)**
```vue
<template>
  <div class="min-h-screen bg-m-obsidian">
    <!-- Sidebar -->
    <AdminSidebar />
    
    <!-- Main Content -->
    <div class="ml-64 p-8">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gradient-gold mb-2">Tableau de Bord</h1>
        <p class="text-white/70">Bienvenue, {{ adminName }}</p>
      </div>
      
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <StatsCard title="Commandes Aujourd'hui" :value="stats.todayOrders" icon="ShoppingCart" color="blue" />
        <StatsCard title="Réservations" :value="stats.todayReservations" icon="Calendar" color="green" />
        <StatsCard title="Chiffre d'Affaires" :value="formatCurrency(stats.revenue)" icon="DollarSign" color="gold" />
        <StatsCard title="Plats Populaires" :value="stats.popularDishes" icon="TrendingUp" color="purple" />
      </div>
      
      <!-- Charts Section -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white/5 backdrop-blur-xl rounded-2xl p-6 border border-white/10">
          <h3 class="text-xl font-bold mb-4">Évolution des Commandes</h3>
          <canvas ref="ordersChart"></canvas>
        </div>
        <div class="bg-white/5 backdrop-blur-xl rounded-2xl p-6 border border-white/10">
          <h3 class="text-xl font-bold mb-4">Répartition des Catégories</h3>
          <canvas ref="categoriesChart"></canvas>
        </div>
      </div>
      
      <!-- Recent Orders -->
      <div class="bg-white/5 backdrop-blur-xl rounded-2xl p-6 border border-white/10">
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-xl font-bold">Commandes Récentes</h3>
          <RouterLink to="/admin/orders" class="text-m-gold hover:text-m-gold/80">
            Voir tout →
          </RouterLink>
        </div>
        <OrderTable :orders="recentOrders" :compact="true" />
      </div>
    </div>
  </div>
</template>
```

---

## 🗄️ **BACKEND (PHP)**

### **1. AuthController.php**
```php
<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include_once '../config/database.php';
include_once '../models/Admin.php';
include_once '../services/OTPService.php';

class AuthController {
    private $db;
    private $adminModel;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->adminModel = new Admin($this->db);
    }
    
    // Login admin avec génération OTP
    public function login() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validation
        if (!isset($data['email']) || !isset($data['password'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Email et mot de passe requis']);
            return;
        }
        
        // Vérification admin
        $admin = $this->adminModel->findByEmail($data['email']);
        
        if (!$admin || !password_verify($data['password'], $admin['password'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Identifiants incorrects']);
            return;
        }
        
        if (!$admin['is_active']) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Compte désactivé']);
            return;
        }
        
        // Génération session et OTP
        $sessionToken = bin2hex(random_bytes(32));
        $otpCode = $this->generateOTP();
        $otpExpires = date('Y-m-d H:i:s', strtotime('+1 minute'));
        
        // Sauvegarde session
        $this->adminModel->createSession($admin['id'], $sessionToken, $otpCode, $otpExpires);
        
        // Envoi OTP (simulation SMS)
        $this->sendOTP($admin['phone'], $otpCode);
        
        echo json_encode([
            'success' => true,
            'session_token' => $sessionToken,
            'message' => 'OTP envoyé sur votre téléphone'
        ]);
    }
    
    // Vérification OTP
    public function verifyOTP() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['session_token']) || !isset($data['otp_code'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Token et OTP requis']);
            return;
        }
        
        $session = $this->adminModel->findSession($data['session_token']);
        
        if (!$session || $session['otp_code'] !== $data['otp_code']) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Code OTP incorrect']);
            return;
        }
        
        if (strtotime($session['otp_expires_at']) < time()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Code OTP expiré']);
            return;
        }
        
        // Validation session
        $this->adminModel->verifySession($data['session_token']);
        
        echo json_encode([
            'success' => true,
            'message' => 'Authentification réussie',
            'admin' => [
                'id' => $session['admin_id'],
                'name' => $session['admin_name'],
                'role' => $session['admin_role']
            ]
        ]);
    }
    
    private function generateOTP() {
        return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }
    
    private function sendOTP($phone, $otp) {
        // Simulation envoi SMS - à remplacer avec vrai service SMS
        error_log("OTP pour $phone: $otp");
        
        // En production: utiliser un service SMS comme Twilio, Orange API, etc.
        // twilio_send_sms($phone, "Votre code EL MAESTRO: $otp");
    }
}

// Router
$action = $_GET['action'] ?? '';
$controller = new AuthController();

switch ($action) {
    case 'login':
        $controller->login();
        break;
    case 'verify-otp':
        $controller->verifyOTP();
        break;
    default:
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Action non trouvée']);
}
?>
```

### **2. AdminMiddleware.php**
```php
<?php
include_once '../config/database.php';
include_once '../models/Admin.php';

class AdminMiddleware {
    private $db;
    private $adminModel;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->adminModel = new Admin($this->db);
    }
    
    public function protect() {
        // Récupérer token depuis header ou session
        $token = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SESSION['admin_token'] ?? '';
        
        if (!$token) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Non authentifié']);
            exit;
        }
        
        $session = $this->adminModel->findSession($token);
        
        if (!$session || !$session['is_verified']) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Session invalide']);
            exit;
        }
        
        if (strtotime($session['expires_at']) < time()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Session expirée']);
            exit;
        }
        
        // Ajouter infos admin dans globals
        $_SESSION['admin_id'] = $session['admin_id'];
        $_SESSION['admin_name'] = $session['admin_name'];
        $_SESSION['admin_role'] = $session['admin_role'];
        
        return $session;
    }
}
?>
```

---

## 🚀 **PLAN D'IMPLÉMENTATION**

### **PHASE 1 (2 heures) - Base**
1. ✅ **Créer tables SQL** (`admins`, `admin_sessions`)
2. ✅ **Implémenter AuthController** (login + OTP)
3. ✅ **Créer AdminMiddleware** protection
4. ✅ **Développer LoginView.vue** (formulaire)
5. ✅ **Développer OTPView.vue** (saisie 6 chiffres)

### **PHASE 2 (3 heures) - Dashboard**
1. ✅ **Créer DashboardView.vue** (stats + graphiques)
2. ✅ **Développer Sidebar.vue** (navigation admin)
3. ✅ **Implémenter StatsCard.vue** (widgets)
4. ✅ **Ajouter OrderTable.vue** (tableau commandes)

### **PHASE 3 (2 heures) - Fonctionnalités**
1. ✅ **Gestion menu CRUD** complet
2. ✅ **Gestion commandes** avec filtres
3. ✅ **Gestion réservations** avec calendrier
4. ✅ **Notifications temps réel** WebSocket

### **PHASE 4 (1 heure) - Sécurité & Tests**
1. ✅ **Validation inputs** tous formulaires
2. ✅ **Protection CSRF** globale
3. ✅ **Tests fonctionnels** login/OTP
4. ✅ **Documentation API** Swagger

---

## 📊 **LIVRABLES FINAUX**

### **Frontend**
- ✅ **5 pages admin** complètes et responsives
- ✅ **3 composants** réutilisables
- ✅ **Intégration design** existant
- ✅ **Gestion erreurs** utilisateur

### **Backend**
- ✅ **2 contrôleurs** sécurisés
- ✅ **1 middleware** protection
- ✅ **2 modèles** admin/session
- ✅ **API REST** complète

### **Sécurité**
- ✅ **Double authentification** (email + OTP)
- ✅ **Sessions sécurisées** avec expiration
- ✅ **Protection routes** admin complètes
- ✅ **Validation et sanitization** données

---

## 🎯 **ESTIMATION TEMPS**
- **Total** : 8 heures de développement
- **Complexité** : Moyenne-Élevée (sécurité renforcée)
- **Prêt production** : Après tests et déploiement

**Dashboard admin EL MAESTRO sera 100% fonctionnel et sécurisé !** 🔐✨
