-- ========================================================
-- SCRIPT DE CRÉATION DE LA BASE DE DONNÉES "EL MAESTRO"
-- Compatible : MySQL / PostgreSQL
-- ========================================================

-- 1. Table des Plats (Plates)
CREATE TABLE IF NOT EXISTS plates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    category VARCHAR(100),
    image_url VARCHAR(255),
    base_rating DECIMAL(2, 1) DEFAULT 5.0, -- Note initiale prestigieuse
    is_signature BOOLEAN DEFAULT FALSE,
    prep_time VARCHAR(20) DEFAULT '20-30 min',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Table des Avis / Notations (6 étoiles)
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plate_id INT NOT NULL,
    user_name VARCHAR(100),
    rating INT CHECK (rating >= 1 AND rating <= 6),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (plate_id) REFERENCES plates(id) ON DELETE CASCADE
);

-- 3. Table des Commandes (Orders)
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    customer_address TEXT NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    payment_method ENUM('mtn', 'moov', 'card', 'cash') NOT NULL,
    payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    delivery_status ENUM('preparing', 'on_route', 'delivered') DEFAULT 'preparing',
    tracking_number VARCHAR(50) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. Éléments de Commande (Order Items)
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    plate_id INT NOT NULL,
    quantity INT DEFAULT 1,
    price_at_time DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (plate_id) REFERENCES plates(id)
);

-- 5. Utilisateurs Fidélité (Loyalty)
CREATE TABLE IF NOT EXISTS loyalty_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(20) UNIQUE NOT NULL,
    points INT DEFAULT 0,
    tier ENUM('Bronze', 'Argent', 'Or', 'Platine') DEFAULT 'Bronze',
    last_order_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 6. Table des Réservations
CREATE TABLE IF NOT EXISTS reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(100),
    reservation_date DATE NOT NULL,
    reservation_time TIME NOT NULL,
    people_count VARCHAR(20),
    special_request TEXT,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 7. Insertion de quelques plats de démo (Facultatif)
INSERT INTO plates (name, description, price, category, image_url, is_signature, base_rating)
VALUES 
('Poulet Braisé Maestro', 'Mariné 24h, épices secrètes du Bénin.', 5500.00, 'Plats Résistants', 'grilled_chicken.png', TRUE, 5.8),
('Poisson Grillé Royal', 'Capitaine frais de Cotonou.', 8500.00, 'Plats Résistants', 'fish.png', TRUE, 5.9),
('Mousse Chocolat Gold', 'Chocolat 70% et éclats dorés.', 3000.00, 'Desserts', 'mousse.png', FALSE, 6.0);
