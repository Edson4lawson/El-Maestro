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
        
        // Nettoyage et validation
        $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
        $password = $data['password'];
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Email invalide']);
            return;
        }
        
        // Vérification admin
        $admin = $this->adminModel->findByEmail($email);
        
        if (!$admin || !password_verify($password, $admin['password'])) {
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
        $otpService = new OTPService();
        $otpCode = $otpService->generateCode();
        $otpExpires = date('Y-m-d H:i:s', strtotime('+1 minute'));
        
        // Sauvegarde session
        $sessionId = $this->adminModel->createSession($admin['id'], $sessionToken, $otpCode, $otpExpires);
        
        if (!$sessionId) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur session']);
            return;
        }
        
        // Envoi OTP
        $otpService->sendOTP($admin['phone'], $otpCode);
        
        // Mise à jour dernier login
        $this->adminModel->updateLastLogin($admin['id']);
        
        echo json_encode([
            'success' => true,
            'session_token' => $sessionToken,
            'message' => 'OTP envoyé sur votre téléphone',
            'expires_in' => 60 // secondes
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
        
        // Validation format OTP
        $otpCode = trim($data['otp_code']);
        if (!preg_match('/^\d{6}$/', $otpCode)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Code OTP invalide']);
            return;
        }
        
        $session = $this->adminModel->findSession($data['session_token']);
        
        if (!$session || $session['otp_code'] !== $otpCode) {
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
                'email' => $session['admin_email'],
                'role' => $session['admin_role']
            ]
        ]);
    }
    
    // Déconnexion
    public function logout() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['session_token'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Token requis']);
            return;
        }
        
        $this->adminModel->invalidateSession($data['session_token']);
        
        echo json_encode([
            'success' => true,
            'message' => 'Déconnexion réussie'
        ]);
    }
}

// Router
$action = $_GET['action'] ?? '';
$controller = new AuthController();

switch ($action) {
    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->login();
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
        }
        break;
    case 'verify-otp':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->verifyOTP();
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
        }
        break;
    case 'logout':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->logout();
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
        }
        break;
    default:
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Action non trouvée']);
}
?>
