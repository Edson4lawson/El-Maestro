<?php
// Create admin user if not exists
header("Content-Type: application/json; charset=UTF-8");

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    include_once './config/database.php';
    include_once './models/Admin.php';
    
    $database = new Database();
    $db = $database->getConnection();
    
    // Check if admin table exists
    $tableCheck = $db->query("SHOW TABLES LIKE 'admins'");
    if ($tableCheck->rowCount() === 0) {
        // Create admins table
        $createTable = "
        CREATE TABLE admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            phone VARCHAR(20),
            password VARCHAR(255) NOT NULL,
            role ENUM('admin', 'super_admin') DEFAULT 'admin',
            is_active BOOLEAN DEFAULT 1,
            last_login TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $db->exec($createTable);
        echo json_encode(['table_created' => true]);
    }
    
    $admin = new Admin($db);
    
    // Check if admin exists
    $existingAdmin = $admin->findByEmail('admin@elmaestro.bj');
    
    if ($existingAdmin) {
        echo json_encode([
            'success' => true,
            'message' => 'Admin user already exists',
            'admin' => [
                'id' => $existingAdmin['id'],
                'email' => $existingAdmin['email'],
                'name' => $existingAdmin['name'] ?? 'Admin',
                'is_active' => $existingAdmin['is_active']
            ]
        ]);
    } else {
        // Create admin user directly
        $password = 'admin123';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $query = "INSERT INTO admins (name, email, phone, password, role, is_active) 
                  VALUES (:name, :email, :phone, :password, :role, :is_active)";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':is_active', $is_active);
        
        $name = 'Admin EL MAESTRO';
        $email = 'admin@elmaestro.bj';
        $phone = '+229 00 00 00 00';
        $role = 'super_admin';
        $is_active = 1;
        
        if ($stmt->execute()) {
            $adminId = $db->lastInsertId();
            echo json_encode([
                'success' => true,
                'message' => 'Admin user created successfully',
                'admin_id' => $adminId,
                'credentials' => [
                    'email' => 'admin@elmaestro.bj',
                    'password' => 'admin123'
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to create admin user',
                'error' => $stmt->errorInfo()
            ]);
        }
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'debug' => [
            'error_type' => get_class($e),
            'error_file' => $e->getFile(),
            'error_line' => $e->getLine()
        ]
    ]);
}
?>
