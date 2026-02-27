-- ========================================================
-- TABLE ADMINISTRATEURS POUR EL MAESTRO
-- ========================================================

-- 1. Table des administrateurs
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL, -- hashé avec password_hash()
    role ENUM('super_admin', 'admin', 'manager') DEFAULT 'admin',
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2. Table des sessions administrateurs
CREATE TABLE IF NOT EXISTS admin_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    otp_code VARCHAR(6) NULL,
    otp_expires_at TIMESTAMP NULL,
    is_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE,
    INDEX idx_session_token (session_token),
    INDEX idx_admin_id (admin_id)
);

-- 3. Index pour optimisation
CREATE INDEX idx_admin_email ON admins(email);
CREATE INDEX idx_admin_active ON admins(is_active);

-- 4. Admin par défaut (mot de passe: admin123)
-- IMPORTANT: Changer ce mot de passe en production!
INSERT INTO admins (name, email, phone, password, role) VALUES 
('Super Admin', 'admin@elmaestro.bj', '+22912345678', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin');

-- 5. Procédure pour nettoyer les sessions expirées
DELIMITER //
CREATE PROCEDURE CleanExpiredSessions()
BEGIN
    DELETE FROM admin_sessions 
    WHERE expires_at < NOW() 
    OR (otp_expires_at IS NOT NULL AND otp_expires_at < NOW());
END //
DELIMITER ;

-- 6. Événement pour nettoyer automatiquement toutes les heures
CREATE EVENT IF NOT EXISTS clean_admin_sessions
ON SCHEDULE EVERY 1 HOUR
DO CALL CleanExpiredSessions();
