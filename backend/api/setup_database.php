<?php
header("Content-Type: text/plain; charset=UTF-8");

// Connect to MySQL without database first
try {
    $conn = new PDO("mysql:host=localhost", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Connexion à MySQL établie\n";
    
    // Create database
    $conn->exec("CREATE DATABASE IF NOT EXISTS el_maestro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Base de données 'el_maestro' créée\n";
    
    // Select the database
    $conn->exec("USE el_maestro");
    
    // Create plates table
    $create_plates = "CREATE TABLE IF NOT EXISTS plates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        category VARCHAR(100) NOT NULL,
        image_url VARCHAR(500),
        rating DECIMAL(3,2) DEFAULT 4.0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($create_plates);
    echo "✓ Table 'plates' créée\n";
    
    // Create reviews table
    $create_reviews = "CREATE TABLE IF NOT EXISTS reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        plate_id INT NOT NULL,
        user_name VARCHAR(100) NOT NULL,
        rating DECIMAL(3,2) NOT NULL,
        comment TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (plate_id) REFERENCES plates(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($create_reviews);
    echo "✓ Table 'reviews' créée\n";
    
    // Create orders table
    $create_orders = "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tracking_number VARCHAR(50) UNIQUE NOT NULL,
        customer_name VARCHAR(255) NOT NULL,
        customer_phone VARCHAR(20) NOT NULL,
        items JSON NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        status ENUM('pending', 'confirmed', 'preparing', 'ready', 'delivered') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($create_orders);
    echo "✓ Table 'orders' créée\n";
    
    // Create reservations table
    $create_reservations = "CREATE TABLE IF NOT EXISTS reservations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        date DATE NOT NULL,
        time TIME NOT NULL,
        guests INT NOT NULL,
        special_requests TEXT,
        status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($create_reservations);
    echo "✓ Table 'reservations' créée\n";
    
    // Create loyalty_users table
    $create_loyalty = "CREATE TABLE IF NOT EXISTS loyalty_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        phone VARCHAR(20) UNIQUE NOT NULL,
        points INT DEFAULT 0,
        tier ENUM('Bronze', 'Silver', 'Gold', 'Platinum') DEFAULT 'Bronze',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($create_loyalty);
    echo "✓ Table 'loyalty_users' créée\n";
    
    echo "\n=== Base de données configurée avec succès ===\n";
    
} catch(PDOException $exception) {
    echo "Erreur: " . $exception->getMessage() . "\n";
}
?>
