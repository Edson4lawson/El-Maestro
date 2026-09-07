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
-- Insertion du Menu Initial Complet (58 Éléments)
-- --------------------------------------------------------
INSERT INTO plates (name, description, price, category, image_url, is_signature, base_rating, is_available)
VALUES 
-- 1. ENTRÉES (9 éléments)
('Sushi Mix', 'Assortiment raffiné de 12 pièces de sushis et makis préparés avec du poisson frais du jour.', 9500.00, 'Entrées', 'Sushi Mix.jpg', FALSE, 5.8, TRUE),
('Foie Gras Poêlé', 'Foie gras de canard poêlé à la perfection, chutney de mangue caramélisée et pain brioché tiède.', 12000.00, 'Entrées', 'Foie Gras Poêlé.jpg', TRUE, 5.9, TRUE),
('Carpaccio de Bœuf', 'Fines lamelles de filet de bœuf mariné, huile d''olive extra vierge, copeaux de parmesan et roquette.', 7000.00, 'Entrées', 'Carpaccio de Bœuf.jpg', FALSE, 5.6, TRUE),
('Tartare de Saumon', 'Saumon frais coupé au couteau, avocat crémeux, zeste de citron vert et herbes fraîches.', 7500.00, 'Entrées', 'Tartare de Saumon.jpg', FALSE, 5.7, TRUE),
('Bruschetta Italienne', 'Pain de campagne grillé frotté à l''ail, concassé de tomates fraîches, basilic et mozzarella di bufala.', 4000.00, 'Entrées', 'Bruschetta.jpg', FALSE, 5.4, TRUE),
('Crevettes Grillées', 'Crevettes géantes marinées à l''ail et au piment doux, saisies à la plancha avec sauce tartare.', 8000.00, 'Entrées', 'Crevettes Grillées.jpg', TRUE, 5.8, TRUE),
('Salade Composée Royale', 'Mesclun de saison, tomates cerises, cœurs de palmier, noix torréfiées et vinaigrette balsamique.', 4500.00, 'Entrées', 'Salade Composée.jpg', FALSE, 5.3, TRUE),
('Soupe du Jour du Chef', 'Soupe artisanale mijotée avec des légumes frais du marché et croûtons dorés à l''huile d''olive.', 3500.00, 'Entrées', 'Soupe du Jour.jpg', FALSE, 5.2, TRUE),
('Velouté de Potiron', 'Onctueux velouté de potiron parfumé aux épices douces et crème fraîche battue.', 4000.00, 'Entrées', 'Velouté de Potiron.jpg', FALSE, 5.5, TRUE),

-- 2. PLATS RÉSISTANTS (17 éléments)
('Poulet Braisé Maestro', 'Poulet fermier mariné 24h aux épices secrètes du Bénin, braisé sur charbon ardent.', 6000.00, 'Plats Résistants', 'Poulet Braisé Maestro.jpg', TRUE, 6.0, TRUE),
('Poisson Grillé Royal', 'Capitaine frais du littoral de Cotonou braisé, sauce vierge pimentée et alloco doré.', 9000.00, 'Plats Résistants', 'Poisson Grillé Royal.jpg', TRUE, 6.0, TRUE),
('Attiéké Poisson Braisé', 'Semoule de manioc vapeur de qualité supérieure avec bar grillé et piment doux écrasé.', 7000.00, 'Plats Résistants', 'Attiéké Poisson.jpg', TRUE, 6.0, TRUE),
('Poulet Yassa Royal', 'Poulet mariné au citron vert, oignons caramélisés et moutarde de Dijon, servi avec riz parfumé.', 6500.00, 'Plats Résistants', 'Poulet Yassa.jpg', TRUE, 5.9, TRUE),
('Pâtes Carbonara Truffe', 'Pâtes fraîches al dente sublimées par de la truffe noire d''été, guanciale et crème de parmesan.', 8500.00, 'Plats Résistants', 'Pâtes Carbonara Truffe.jpg', TRUE, 6.0, TRUE),
('Pâtes Carbonara Classique', 'Recette romaine traditionnelle au guanciale croustillant, pecorino romano et jaune d''œuf crémeux.', 5500.00, 'Plats Résistants', 'Pâtes Carbonara Classique.jpg', FALSE, 5.7, TRUE),
('Pâtes Carbonara Pancetta', 'Préparation authentique avec de la pancetta affinée, crème onctueuse et poivre noir concassé.', 6000.00, 'Plats Résistants', 'Pâtes Carbonara Pancetta.jpg', FALSE, 5.8, TRUE),
('Pâtes Carbonara Seafood', 'Mélange terre et mer gourmand avec gambas royales, calamars sautés et crème légère safranée.', 8000.00, 'Plats Résistants', 'Pâtes Carbonara Seafood.jpg', TRUE, 5.9, TRUE),
('Pâtes Carbonara Végétarienne', 'Variation aux champignons des bois sautés, courgettes grillées, tomates confites et parmesan.', 5000.00, 'Plats Résistants', 'Pâtes Carbonara Végétarienne.jpg', FALSE, 5.5, TRUE),
('Brochette de Viande Grillée', 'Brochettes de filet de bœuf et d''agneau grillées aux épices, poivrons croquants et alloco.', 4500.00, 'Plats Résistants', 'brochette de viande.jpg', FALSE, 5.6, TRUE),
('Burger Maison & Frites', 'Steak haché pur bœuf façon bouchère, cheddar affiné, oignons caramélisés et frites maison.', 5000.00, 'Plats Résistants', 'buger & frite.jpg', FALSE, 5.5, TRUE),
('Chawama Viande Gourmet', 'Pain pita libanais farci d''émincé de bœuf mariné, sauce tahini et crudités croquantes.', 4000.00, 'Plats Résistants', 'chawama viande.jpg', FALSE, 5.4, TRUE),
('Chawama Traditionnel', 'Chawama authentique au poulet tendre, sauce à l''ail maison et frites dorées.', 3500.00, 'Plats Résistants', 'chawama.jpg', FALSE, 5.3, TRUE),
('Come & Mowo Spécialité', 'Spécialité béninoise traditionnelle de pâte fermentée accompagnée de poisson frit et sauce tomate pimentée.', 4000.00, 'Plats Résistants', 'come & mowo.jpg', FALSE, 5.5, TRUE),
('Fillet de Poulet Mariné', 'Suprême de poulet grillé aux herbes de Provence, sauce forestière aux champignons.', 5500.00, 'Plats Résistants', 'fillet de poulet.jpg', FALSE, 5.7, TRUE),
('Pizza Artisanale Maestro', 'Pâte à pizza fine maison, sauce tomate mijotée, mozzarella fondante, jambon braisé et basilic.', 6000.00, 'Plats Résistants', 'pizza.jpg', FALSE, 5.8, TRUE),
('Riz Cantonaise au Poulet', 'Riz sauté au wok avec émincé de poulet, petits pois, œufs brouillés et sauce soja.', 4500.00, 'Plats Résistants', 'riz cantonaise.jpg', FALSE, 5.6, TRUE),

-- 3. DESSERTS (13 éléments)
('Mousse au Chocolat Gold 24k', 'Chocolat noir 70% intense de São Tomé et éclats d''or comestible 24 carats.', 4500.00, 'Desserts', 'Mousse au Chocolat.jpg', TRUE, 6.0, TRUE),
('Tiramisu Classique Maestro', 'Biscuits savoiardi imbibés d''espresso grand cru, crème mascarpone vanillée et cacao amer.', 4000.00, 'Desserts', 'Tiramisu Classique.jpg', TRUE, 6.0, TRUE),
('Fondant Brownie Chaud', 'Brownie coulant aux noix de pécan servi chaud avec sa boule de glace vanille artisanale.', 3500.00, 'Desserts', 'Brownie Chaud.jpg', FALSE, 5.9, TRUE),
('Cheesecake Fruits Rouges', 'Cheesecake crémeux façon New-Yorkaise sur biscuit croustillant et coulis de framboises.', 3500.00, 'Desserts', 'Cheesecake.jpg', FALSE, 5.9, TRUE),
('Crème Brûlée Vanille Bourbon', 'Crème onctueuse infusée à la vanille de Madagascar sous sa fine coque de caramel croquant.', 3500.00, 'Desserts', 'Crème Brûlée.jpg', FALSE, 5.8, TRUE),
('Crêpes Suzette Flambées', 'Crêpes fines au beurre d''orange et zestes confits, flambées au Grand Marnier.', 4000.00, 'Desserts', 'Crêpes Suzette.jpg', FALSE, 5.7, TRUE),
('Glace Artisanale Trio', 'Sélection de 3 boules de glace maison (Vanille Bourbon, Chocolat Noir, Pistache de Sicile).', 3000.00, 'Desserts', 'Glace Artisanale.jpg', FALSE, 5.5, TRUE),
('Macarons Assortis Paris', 'Coffret de 6 macarons fins aux parfums variés (framboise, pistache, caramel beurre salé, chocolat).', 4000.00, 'Desserts', 'Macarons.jpg', FALSE, 5.8, TRUE),
('Panna Cotta Coulis Framboise', 'Crème cuite vanillée traditionnelle avec un coulis acidulé de fruits rouges frais.', 3500.00, 'Desserts', 'Panna Cotta.jpg', FALSE, 5.6, TRUE),
('Profiteroles au Chocolat Noir', 'Choux maison garnis de glace vanille et nappés d''une sauce chaude au chocolat noir.', 4000.00, 'Desserts', 'Profiteroles.jpg', FALSE, 5.8, TRUE),
('Salade de Fruits Frais de Saison', 'Mélange rafraîchissant d''ananas pain de sucre, mangue, papaye et fruits de la passion.', 2500.00, 'Desserts', 'Salade de Fruits.jpg', FALSE, 5.4, TRUE),
('Tarte au Citron Meringuée', 'Pâte sablée croustillante, crème au citron jaune et meringue italienne dorée au chalumeau.', 3500.00, 'Desserts', 'Tarte au Citron.jpg', FALSE, 5.7, TRUE),
('Tarte aux Pommes Fine', 'Tarte fine feuilletée aux pommes caramélisées au four avec pointe de cannelle.', 3500.00, 'Desserts', 'Tarte aux Pommes.jpg', FALSE, 5.6, TRUE),

-- 4. BOISSONS (19 éléments)
('Cocktail Tropical Ananas & Passion', 'Fruits frais du Bénin pressés minute, sirop de canne infusé à la vanille bourbon.', 3500.00, 'Boissons', 'Cocktail Tropical.jpg', TRUE, 5.9, TRUE),
('Mojito Prestige Maestro', 'Menthe fraîche froissée, citron vert bio, eau pétillante et touche florale.', 4000.00, 'Boissons', 'Mojito Sans Alcool.jpg', TRUE, 6.0, TRUE),
('Cocktail Ananas Frais', 'Jus d''ananas pain de sucre pressé, pointe de coco et zeste de citron vert.', 3000.00, 'Boissons', 'Cocktail Ananas.jpg', FALSE, 5.7, TRUE),
('Cocktail Fruits Rouges', 'Mélange vitaminé de fraises, framboises, myrtilles et jus de canneberge.', 3500.00, 'Boissons', 'Cocktail Fruits Rouges.jpg', FALSE, 5.8, TRUE),
('Jus d''Orange Pressé Frais', 'Jus pur pressé à la minute avec des oranges locales gorgées de soleil.', 2000.00, 'Boissons', 'Jus d''Orange Frais.jpg', FALSE, 5.6, TRUE),
('Jus de Grenadine Pur', 'Nectar doux de grenade rafraîchissant et antioxydant.', 2000.00, 'Boissons', 'Jus de Grenadine.jpg', FALSE, 5.5, TRUE),
('Jus de Pomme Bio', 'Pur jus de pomme 100% naturel sans sucre ajouté.', 2000.00, 'Boissons', 'Jus de Pomme.jpg', FALSE, 5.5, TRUE),
('Limonade Maison Menthe', 'Citron jaune pressé, sucre de canne brut, menthe fraîche et eau gazéifiée.', 2500.00, 'Boissons', 'Limonade Maison.jpg', FALSE, 5.7, TRUE),
('Limonade Rose Parfumée', 'Limonade artisanale parfumée à l''eau de rose et hibiscus frais.', 2500.00, 'Boissons', 'Limonade Rose.jpg', FALSE, 5.6, TRUE),
('Smoothie Berry Fruits Rouges', 'Smoothie onctueux aux fruits rouges frais et yaourt fermier velouté.', 3000.00, 'Boissons', 'Smoothie Berry.jpg', FALSE, 5.8, TRUE),
('Smoothie Mangue Passion', 'Mangue bien mûre mixée avec fruit de la passion et lait de coco.', 3000.00, 'Boissons', 'Smoothie Mangue.jpg', FALSE, 5.8, TRUE),
('Thé Glacé Pêche Blanche', 'Infusion de thé noir parfumé à la pêche blanche et tranche de pêche fraîche.', 2500.00, 'Boissons', 'Thé Glacé Pêche.jpg', FALSE, 5.6, TRUE),
('Thé Vert Glacé Japonais', 'Thé vert Sencha bio infusé à froid avec feuilles de menthe.', 2500.00, 'Boissons', 'Thé Vert Glacé.jpg', FALSE, 5.5, TRUE),
('Thé à la Menthe Fraîche', 'Thé vert Gunpowder traditionnel servi brûlant avec bouquet de menthe fraîche.', 2000.00, 'Boissons', 'Thé à la Menthe.jpg', FALSE, 5.7, TRUE),
('Café Glacé Onctueux', 'Double espresso corsé servi frappé sur glaçons avec nuage de lait condensé.', 2500.00, 'Boissons', 'Café Glacé.jpg', FALSE, 5.6, TRUE),
('Café Froid Infusion Lente', 'Cold brew café grand cru infusé à froid pendant 16 heures.', 2500.00, 'Boissons', 'Café Froid.jpg', FALSE, 5.5, TRUE),
('Lait Caillé Frais Traditionnel', 'Lait caillé artisanal frais, riche et savoureux selon la recette locale.', 1500.00, 'Boissons', 'lait caillé.jpg', FALSE, 5.4, TRUE),
('Eau Minérale Naturelle 1.5L', 'Bouteille d''eau minérale de source pure servie bien fraîche.', 1000.00, 'Boissons', 'Eau Minérale.jpg', FALSE, 5.0, TRUE),
('Eau Pétillante Citron Vert', 'Eau gazeuse naturelle accompagnée d''un quartier de citron vert frais.', 1200.00, 'Boissons', 'Eau Pétillante.jpg', FALSE, 5.0, TRUE);
