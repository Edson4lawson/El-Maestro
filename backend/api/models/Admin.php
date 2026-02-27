<?php
include_once '../config/database.php';

class Admin {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    // Trouver admin par email
    public function findByEmail($email) {
        $query = "SELECT * FROM admins WHERE email = :email AND is_active = 1 LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Trouver admin par ID
    public function findById($id) {
        $query = "SELECT * FROM admins WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Créer session admin
    public function createSession($adminId, $sessionToken, $otpCode, $otpExpires) {
        $query = "INSERT INTO admin_sessions (admin_id, session_token, otp_code, otp_expires_at, expires_at) 
                  VALUES (:admin_id, :session_token, :otp_code, :otp_expires_at, DATE_ADD(NOW(), INTERVAL 24 HOUR))";
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':admin_id', $adminId);
        $stmt->bindParam(':session_token', $sessionToken);
        $stmt->bindParam(':otp_code', $otpCode);
        $stmt->bindParam(':otp_expires_at', $otpExpires);
        
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }
    
    // Trouver session par token
    public function findSession($sessionToken) {
        $query = "SELECT s.*, a.name as admin_name, a.email as admin_email, a.role as admin_role 
                  FROM admin_sessions s 
                  JOIN admins a ON s.admin_id = a.id 
                  WHERE s.session_token = :session_token AND s.expires_at > NOW() 
                  LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':session_token', $sessionToken);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Valider session (OTP vérifié)
    public function verifySession($sessionToken) {
        $query = "UPDATE admin_sessions SET is_verified = 1 WHERE session_token = :session_token";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':session_token', $sessionToken);
        
        return $stmt->execute();
    }
    
    // Invalider session
    public function invalidateSession($sessionToken) {
        $query = "UPDATE admin_sessions SET expires_at = NOW() WHERE session_token = :session_token";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':session_token', $sessionToken);
        
        return $stmt->execute();
    }
    
    // Mettre à jour dernier login
    public function updateLastLogin($adminId) {
        $query = "UPDATE admins SET last_login = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $adminId);
        
        return $stmt->execute();
    }
    
    // Nettoyer anciennes sessions
    public function cleanExpiredSessions() {
        $query = "DELETE FROM admin_sessions 
                  WHERE expires_at < NOW() 
                  OR (otp_expires_at IS NOT NULL AND otp_expires_at < NOW())";
        $stmt = $this->db->prepare($query);
        
        return $stmt->execute();
    }
    
    // Lister tous les admins
    public function getAll($limit = 50, $offset = 0) {
        $query = "SELECT id, name, email, role, is_active, last_login, created_at 
                  FROM admins 
                  ORDER BY created_at DESC 
                  LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Créer nouvel admin
    public function create($data) {
        $query = "INSERT INTO admins (name, email, phone, password, role) 
                  VALUES (:name, :email, :phone, :password, :role)";
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':phone', $data['phone']);
        $stmt->bindParam(':password', password_hash($data['password'], PASSWORD_DEFAULT));
        $stmt->bindParam(':role', $data['role'] ?? 'admin');
        
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }
    
    // Mettre à jour admin
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        if (isset($data['name'])) {
            $fields[] = "name = :name";
            $params[':name'] = $data['name'];
        }
        if (isset($data['email'])) {
            $fields[] = "email = :email";
            $params[':email'] = $data['email'];
        }
        if (isset($data['phone'])) {
            $fields[] = "phone = :phone";
            $params[':phone'] = $data['phone'];
        }
        if (isset($data['role'])) {
            $fields[] = "role = :role";
            $params[':role'] = $data['role'];
        }
        if (isset($data['is_active'])) {
            $fields[] = "is_active = :is_active";
            $params[':is_active'] = $data['is_active'];
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $query = "UPDATE admins SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindParam($key, $value);
        }
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
    
    // Supprimer admin
    public function delete($id) {
        $query = "DELETE FROM admins WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
}
?>
