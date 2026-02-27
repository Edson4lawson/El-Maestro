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
    
    // Protéger les routes admin
    public function protect() {
        // Récupérer token depuis header Authorization ou session
        $token = $this->getAuthToken();
        
        if (!$token) {
            $this->sendError(401, 'Non authentifié', 'Token manquant');
            return null;
        }
        
        $session = $this->adminModel->findSession($token);
        
        if (!$session) {
            $this->sendError(401, 'Session invalide', 'Token non trouvé');
            return null;
        }
        
        if (!$session['is_verified']) {
            $this->sendError(401, 'OTP non vérifié', 'Vérification OTP requise');
            return null;
        }
        
        if (strtotime($session['expires_at']) < time()) {
            $this->sendError(401, 'Session expirée', 'Reconnectez-vous');
            return null;
        }
        
        // Ajouter infos admin dans session PHP
        $_SESSION['admin_id'] = $session['admin_id'];
        $_SESSION['admin_name'] = $session['admin_name'];
        $_SESSION['admin_email'] = $session['admin_email'];
        $_SESSION['admin_role'] = $session['admin_role'];
        $_SESSION['admin_token'] = $token;
        
        return $session;
    }
    
    // Vérifier si admin est connecté
    public function isAuthenticated() {
        $token = $this->getAuthToken();
        return $token && $this->adminModel->findSession($token);
    }
    
    // Récupérer role admin
    public function getAdminRole() {
        return $_SESSION['admin_role'] ?? null;
    }
    
    // Vérifier permissions
    public function hasPermission($requiredRole) {
        $currentRole = $this->getAdminRole();
        
        $roles = [
            'super_admin' => ['super_admin', 'admin', 'manager'],
            'admin' => ['admin', 'manager'],
            'manager' => ['manager']
        ];
        
        return in_array($currentRole, $roles[$requiredRole] ?? []);
    }
    
    // Récupérer token depuis header ou session
    private function getAuthToken() {
        // Priorité: Header Authorization > Session
        $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        
        if (empty($token)) {
            $token = $_SESSION['admin_token'] ?? '';
        }
        
        // Nettoyer le token (enlever "Bearer ")
        return str_replace('Bearer ', '', $token);
    }
    
    // Envoyer erreur JSON
    private function sendError($code, $message, $details = null) {
        http_response_code($code);
        $response = [
            'success' => false,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        if ($details) {
            $response['details'] = $details;
        }
        
        echo json_encode($response);
        exit;
    }
    
    // Rediriger vers login si non authentifié
    public function redirectToLogin() {
        header('Location: /admin/login');
        exit;
    }
    
    // Nettoyer sessions expirées
    public function cleanup() {
        return $this->adminModel->cleanExpiredSessions();
    }
}
?>
