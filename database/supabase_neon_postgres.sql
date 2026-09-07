-- ========================================================
-- BASE DE DONNÉES COMPLÈTE POUR SUPABASE / NEON (POSTGRESQL)
-- PROJET : EL MAESTRO
-- Compatible : Supabase, Neon.tech, PostgreSQL 14+
-- ========================================================

-- Nettoyage si besoin (optionnel)
-- DROP TABLE IF EXISTS admin_sessions CASCADE;
-- DROP TABLE IF EXISTS admins CASCADE;
-- DROP TABLE IF EXISTS order_items CASCADE;
-- DROP TABLE IF EXISTS orders CASCADE;
-- DROP TABLE IF EXISTS reviews CASCADE;
-- DROP TABLE IF EXISTS loyalty_users CASCADE;
-- DROP TABLE IF EXISTS reservations CASCADE;
-- DROP TABLE IF EXISTS plates CASCADE;

-- --------------------------------------------------------
-- 1. Table des Plats (plates)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS plates (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price NUMERIC(10, 2) NOT NULL,
    category VARCHAR(100),
    image_url VARCHAR(255),
    base_rating NUMERIC(2, 1) DEFAULT 5.0,
    is_signature BOOLEAN DEFAULT FALSE,
    is_available BOOLEAN DEFAULT TRUE,
    prep_time VARCHAR(50) DEFAULT '20-30 min',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- --------------------------------------------------------
-- 2. Table des Avis (reviews)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS reviews (
    id SERIAL PRIMARY KEY,
    plate_id INT NOT NULL REFERENCES plates(id) ON DELETE CASCADE,
    user_name VARCHAR(100),
    rating INT CHECK (rating >= 1 AND rating <= 6),
    comment TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- --------------------------------------------------------
-- 3. Table des Commandes (orders)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS orders (
    id SERIAL PRIMARY KEY,
    customer_name VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(50) NOT NULL,
    customer_address TEXT NOT NULL,
    total_price NUMERIC(10, 2) NOT NULL,
    payment_method VARCHAR(20) NOT NULL CHECK (payment_method IN ('mtn', 'moov', 'card', 'cash')),
    payment_status VARCHAR(20) DEFAULT 'pending' CHECK (payment_status IN ('pending', 'paid', 'failed')),
    delivery_status VARCHAR(20) DEFAULT 'preparing' CHECK (delivery_status IN ('preparing', 'on_route', 'delivered')),
    tracking_number VARCHAR(100) UNIQUE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- --------------------------------------------------------
-- 4. Éléments de Commande (order_items)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS order_items (
    id SERIAL PRIMARY KEY,
    order_id INT NOT NULL REFERENCES orders(id) ON DELETE CASCADE,
    plate_id INT NOT NULL REFERENCES plates(id),
    quantity INT DEFAULT 1,
    price_at_time NUMERIC(10, 2) NOT NULL
);

-- --------------------------------------------------------
-- 5. Utilisateurs Fidélité (loyalty_users)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS loyalty_users (
    id SERIAL PRIMARY KEY,
    phone VARCHAR(50) UNIQUE NOT NULL,
    points INT DEFAULT 0,
    tier VARCHAR(20) DEFAULT 'Bronze' CHECK (tier IN ('Bronze', 'Argent', 'Or', 'Platine')),
    last_order_at TIMESTAMP WITH TIME ZONE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- --------------------------------------------------------
-- 6. Table des Réservations (reservations)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS reservations (
    id SERIAL PRIMARY KEY,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(150),
    reservation_date DATE NOT NULL,
    reservation_time TIME NOT NULL,
    people_count VARCHAR(50),
    special_request TEXT,
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'confirmed', 'cancelled')),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- --------------------------------------------------------
-- 7. Table des Administrateurs (admins)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS admins (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(30) DEFAULT 'admin' CHECK (role IN ('super_admin', 'admin', 'manager')),
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP WITH TIME ZONE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- --------------------------------------------------------
-- 8. Table des Sessions Administrateurs (admin_sessions)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS admin_sessions (
    id SERIAL PRIMARY KEY,
    admin_id INT NOT NULL REFERENCES admins(id) ON DELETE CASCADE,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    otp_code VARCHAR(10),
    otp_expires_at TIMESTAMP WITH TIME ZONE,
    is_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP WITH TIME ZONE NOT NULL
);

-- --------------------------------------------------------
-- Index d'optimisation
-- --------------------------------------------------------
CREATE INDEX IF NOT EXISTS idx_admins_email ON admins (email);
CREATE INDEX IF NOT EXISTS idx_admin_sessions_token ON admin_sessions (session_token);
CREATE INDEX IF NOT EXISTS idx_plates_category ON plates (category);
CREATE INDEX IF NOT EXISTS idx_orders_tracking ON orders (tracking_number);

-- --------------------------------------------------------
-- Compte Administrateur par défaut
-- Email: admin@elmaestro.bj | Mot de passe: admin123
-- --------------------------------------------------------
INSERT INTO admins (name, email, phone, password, role)
VALUES ('Super Admin', 'admin@elmaestro.bj', '+2290154047392', '$2y$10$6akjqp8vXdAwShko.7sLqujmO0TX0URTf/YE3h2adoEGP9Jxp06by', 'super_admin')
ON CONFLICT (email) DO UPDATE SET phone = '+2290154047392', password = '$2y$10$6akjqp8vXdAwShko.7sLqujmO0TX0URTf/YE3h2adoEGP9Jxp06by';

-- --------------------------------------------------------
-- Insertion du Menu Initial (Plats, Desserts, Boissons, Spécialités)
-- --------------------------------------------------------
INSERT INTO plates (name, description, price, category, image_url, is_signature, base_rating, is_available)
VALUES 
('Poulet Yassa', 'Poulet mariné au citron vert, oignons caramélisés et moutarde de Dijon, servi avec du riz parfumé.', 6500.00, 'Plats Résistants', 'Poulet Yassa.jpg', TRUE, 5.9, TRUE),
('Sushi Mix', 'Assortiment raffiné de 12 pièces de sushis et makis préparés avec du poisson frais du jour.', 9500.00, 'Entrées', 'Sushi Mix.jpg', FALSE, 5.8, TRUE),
('Attiéké Poisson', 'Capitaine frais braisé aux herbes africaines accompagné d''attiéké et sauce pimentée douce.', 7000.00, 'Plats Résistants', 'Attiéké Poisson.jpg', TRUE, 6.0, TRUE),
('Pâtes Carbonara Classique', 'Pâtes fraîches al dente, guanciale croustillant, pecorino romano et jaune d''œuf crémeux.', 5500.00, 'Plats Résistants', 'Pâtes Carbonara Classique.jpg', FALSE, 5.7, TRUE),
('Pâtes Carbonara Truffe', 'Notre recette classique sublimée par de la truffe noire d''été et crème de parmesan.', 8500.00, 'Plats Résistants', 'Pâtes Carbonara Truffe.jpg', TRUE, 6.0, TRUE),
('Pâtes Carbonara Végétarienne', 'Variation gourmande aux champignons des bois sautés, courgettes grillées et parmesan.', 5000.00, 'Plats Résistants', 'Pâtes Carbonara Végétarienne.jpg', FALSE, 5.5, TRUE),
('Pâtes Carbonara Pancetta', 'Préparation traditionnelle italienne avec de la pancetta affinée et poivre noir concassé.', 6000.00, 'Plats Résistants', 'Pâtes Carbonara Pancetta.jpg', FALSE, 5.8, TRUE),
('Pâtes Carbonara Seafood', 'Mélange terre et mer avec crevettes royales, calamars sautés et crème légère safranée.', 8000.00, 'Plats Résistants', 'Pâtes Carbonara Seafood.jpg', TRUE, 5.9, TRUE),
('Poulet Braisé Maestro', 'Poulet fermier mariné pendant 24h avec notre bouquet d''épices secrètes du chef.', 6000.00, 'Plats Résistants', 'Poulet Braisé Maestro.jpg', TRUE, 6.0, TRUE),
('Poisson Grillé Royal', 'Gros bar grillé au feu de bois avec alloco fondant et sauce tomate épicée.', 9000.00, 'Plats Résistants', 'Poisson Grillé Royal.jpg', TRUE, 6.0, TRUE),
('Eau Minérale', 'Bouteille d''eau minérale naturelle fraîche 1.5L.', 1000.00, 'Boissons', 'Eau Minérale.jpg', FALSE, 5.0, TRUE),
('Eau Pétillante', 'Eau minérale gazeuse avec quartier de citron vert.', 1200.00, 'Boissons', 'Eau Pétillante.jpg', FALSE, 5.0, TRUE),
('Jus d''Orange Frais', 'Jus pur pressé à la minute avec oranges locales mûres.', 2000.00, 'Boissons', 'Jus d''Orange Frais.jpg', FALSE, 5.6, TRUE),
('Mojito Sans Alcool', 'Menthe fraîche pilée, citron vert, sucre de canne et eau gazeuse.', 2500.00, 'Boissons', 'Mojito Sans Alcool.jpg', FALSE, 5.8, TRUE),
('Fondant au Chocolat', 'Cœur coulant au chocolat noir 70% accompagné d''une boule de glace vanille bourbon.', 3000.00, 'Desserts', 'Brownie Chaud.jpg', FALSE, 5.9, TRUE),
('Tiramisu Classique', 'Biscuits savoiardi imbibés d''espresso et crème onctueuse au mascarpone.', 3500.00, 'Desserts', 'Tiramisu Classique.jpg', FALSE, 6.0, TRUE),
('Cheesecake Fruits Rouges', 'Cheesecake crémeux façon New-Yorkaise sur coulis de framboises fraîches.', 3500.00, 'Desserts', 'Cheesecake.jpg', FALSE, 5.9, TRUE);
