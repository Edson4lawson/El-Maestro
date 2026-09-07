-- ========================================================
-- BASE DE DONNÉES COMPLÈTE POUR PRODUCTION : EL MAESTRO
-- Compatible MySQL 5.7+ / MySQL 8.0+ / MariaDB 10.3+
-- Encodage : UTF-8 (utf8mb4)
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- 1. Table des Plats (plates)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `plates` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `price` DECIMAL(10, 2) NOT NULL,
    `category` VARCHAR(100),
    `image_url` VARCHAR(255),
    `base_rating` DECIMAL(2, 1) DEFAULT 5.0,
    `is_signature` BOOLEAN DEFAULT FALSE,
    `is_available` BOOLEAN DEFAULT TRUE,
    `prep_time` VARCHAR(20) DEFAULT '20-30 min',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 2. Table des Avis (reviews)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `reviews` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `plate_id` INT NOT NULL,
    `user_name` VARCHAR(100),
    `rating` INT CHECK (`rating` >= 1 AND `rating` <= 6),
    `comment` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`plate_id`) REFERENCES `plates`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 3. Table des Commandes (orders)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `customer_name` VARCHAR(255) NOT NULL,
    `customer_phone` VARCHAR(20) NOT NULL,
    `customer_address` TEXT NOT NULL,
    `total_price` DECIMAL(10, 2) NOT NULL,
    `payment_method` ENUM('mtn', 'moov', 'card', 'cash') NOT NULL,
    `payment_status` ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    `delivery_status` ENUM('preparing', 'on_route', 'delivered') DEFAULT 'preparing',
    `tracking_number` VARCHAR(50) UNIQUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 4. Éléments de Commande (order_items)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `order_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `plate_id` INT NOT NULL,
    `quantity` INT DEFAULT 1,
    `price_at_time` DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`plate_id`) REFERENCES `plates`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 5. Utilisateurs Fidélité (loyalty_users)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `loyalty_users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `phone` VARCHAR(20) UNIQUE NOT NULL,
    `points` INT DEFAULT 0,
    `tier` ENUM('Bronze', 'Argent', 'Or', 'Platine') DEFAULT 'Bronze',
    `last_order_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 6. Table des Réservations (reservations)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `reservations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `customer_name` VARCHAR(100) NOT NULL,
    `customer_email` VARCHAR(100),
    `reservation_date` DATE NOT NULL,
    `reservation_time` TIME NOT NULL,
    `people_count` VARCHAR(20),
    `special_request` TEXT,
    `status` ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 7. Table des Administrateurs (admins)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admins` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255) UNIQUE NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('super_admin', 'admin', 'manager') DEFAULT 'admin',
    `is_active` BOOLEAN DEFAULT TRUE,
    `last_login` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_admin_email` (`email`),
    INDEX `idx_admin_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 8. Table des Sessions Administrateurs (admin_sessions)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin_sessions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `admin_id` INT NOT NULL,
    `session_token` VARCHAR(255) UNIQUE NOT NULL,
    `otp_code` VARCHAR(6) NULL,
    `otp_expires_at` TIMESTAMP NULL,
    `is_verified` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `expires_at` TIMESTAMP NOT NULL,
    FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE CASCADE,
    INDEX `idx_session_token` (`session_token`),
    INDEX `idx_admin_id` (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Compte administrateur par défaut
-- Email: admin@elmaestro.bj / Mot de passe: admin123
-- --------------------------------------------------------
INSERT INTO `admins` (`name`, `email`, `phone`, `password`, `role`) 
VALUES ('Super Admin', 'admin@elmaestro.bj', '+2290154047392', '$2y$10$6akjqp8vXdAwShko.7sLqujmO0TX0URTf/YE3h2adoEGP9Jxp06by', 'super_admin')
ON DUPLICATE KEY UPDATE `phone` = '+2290154047392', `password` = '$2y$10$6akjqp8vXdAwShko.7sLqujmO0TX0URTf/YE3h2adoEGP9Jxp06by';

SET FOREIGN_KEY_CHECKS = 1;
