<?php
// Script to create admin user if not exists
include_once __DIR__ . '/config/database.php';
include_once __DIR__ . '/models/Admin.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $admin = new Admin($db);
    
    // Check if admin already exists
    $existingAdmin = $admin->findByEmail('admin@elmaestro.bj');
    
    if ($existingAdmin) {
        echo json_encode([
            'status' => 'exists',
            'message' => 'Admin user already exists',
            'admin' => [
                'id' => $existingAdmin['id'],
                'email' => $existingAdmin['email'],
                'name' => $existingAdmin['name'] ?? 'Admin',
                'is_active' => $existingAdmin['is_active']
            ]
        ]);
    } else {
        // Create admin user
        $adminData = [
            'name' => 'Admin EL MAESTRO',
            'email' => 'admin@elmaestro.bj',
            'phone' => '+229 00 00 00 00',
            'password' => 'admin123',
            'role' => 'super_admin'
        ];
        
        $adminId = $admin->create($adminData);
        
        if ($adminId) {
            echo json_encode([
                'status' => 'created',
                'message' => 'Admin user created successfully',
                'admin_id' => $adminId,
                'credentials' => [
                    'email' => 'admin@elmaestro.bj',
                    'password' => 'admin123'
                ]
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to create admin user'
            ]);
        }
    }
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
