<?php
header("Content-Type: text/plain; charset=UTF-8");

include_once './config/database.php';
include_once './models/Plate.php';

$database = new Database();
$db = $database->getConnection();

// Supprimer TOUT les plats
$delete_query = "DELETE FROM plates";
$delete_stmt = $db->prepare($delete_query);
$delete_stmt->execute();

// Réinitialiser l'auto increment
$reset_query = "ALTER TABLE plates AUTO_INCREMENT = 1";
$reset_stmt = $db->prepare($reset_query);
$reset_stmt->execute();

// Réinsérer uniquement les 10 plats avec vraies images
$finalPlates = [
    [
        'name' => 'Poulet Yassa',
        'description' => 'Un plat sénégalais savoureux à base de poulet mariné au citron et aux oignons.',
        'category' => 'Plats Résistants',
        'price' => 5500.00,
        'image_url' => 'http://localhost:5173/src/assets/Poulet Yassa.jpg',
        'rating' => 4.5
    ],
    [
        'name' => 'Sushi Mix',
        'description' => 'Une sélection raffinée de sushis japonais frais et colorés.',
        'category' => 'Plats Résistants',
        'price' => 6500.00,
        'image_url' => 'http://localhost:5173/src/assets/Sushi Mix.jpg',
        'rating' => 4.8
    ],
    [
        'name' => 'Attiéké Poisson',
        'description' => 'Un plat ivoirien à base de semoule de manioc accompagnée de poisson grillé.',
        'category' => 'Plats Résistants',
        'price' => 4500.00,
        'image_url' => 'http://localhost:5173/src/assets/Attiéké Poisson.jpg',
        'rating' => 4.3
    ],
    [
        'name' => 'Pâtes Carbonara Classique',
        'description' => 'Des pâtes italiennes classiques avec une sauce onctueuse aux œufs et au parmesan.',
        'category' => 'Plats Résistants',
        'price' => 5000.00,
        'image_url' => 'http://localhost:5173/src/assets/Pâtes Carbonara Classique.jpg',
        'rating' => 4.5
    ],
    [
        'name' => 'Pâtes Carbonara Truffe',
        'description' => 'Des pâtes carbonara enrichies à la truffe pour une saveur intense.',
        'category' => 'Plats Résistants',
        'price' => 6500.00,
        'image_url' => 'http://localhost:5173/src/assets/Pâtes Carbonara Truffe.jpg',
        'rating' => 4.7
    ],
    [
        'name' => 'Pâtes Carbonara Végétarienne',
        'description' => 'Version végétarienne avec des légumes grillés et sauce crémeuse.',
        'category' => 'Plats Résistants',
        'price' => 4500.00,
        'image_url' => 'http://localhost:5173/src/assets/Pâtes Carbonara Végétarienne.jpg',
        'rating' => 4.2
    ],
    [
        'name' => 'Pâtes Carbonara Pancetta',
        'description' => 'Des pâtes avec pancetta croquante et sauce parmesan.',
        'category' => 'Plats Résistants',
        'price' => 5500.00,
        'image_url' => 'http://localhost:5173/src/assets/Pâtes Carbonara Pancetta.jpg',
        'rating' => 4.6
    ],
    [
        'name' => 'Pâtes Carbonara Seafood',
        'description' => 'Variante aux fruits de mer avec crevettes et calamars.',
        'category' => 'Plats Résistants',
        'price' => 7000.00,
        'image_url' => 'http://localhost:5173/src/assets/Pâtes Carbonara Seafood.jpg',
        'rating' => 4.8
    ],
    [
        'name' => 'Poulet Braisé Maestro',
        'description' => 'Mariné 24h, épices secrètes du Bénin.',
        'category' => 'Plats Résistants',
        'price' => 5500.00,
        'image_url' => 'http://localhost:5173/src/assets/Poulet Braisé Maestro.jpg',
        'rating' => 5.8
    ],
    [
        'name' => 'Poisson Grillé Royal',
        'description' => 'Capitaine frais de Cotonou.',
        'category' => 'Plats Résistants',
        'price' => 8500.00,
        'image_url' => 'http://localhost:5173/src/assets/Poisson Grillé Royal.jpg',
        'rating' => 5.9
    ]
];

$insert_query = "INSERT INTO plates (name, description, price, category, image_url, base_rating) VALUES (:name, :description, :price, :category, :image_url, :rating)";
$insert_stmt = $db->prepare($insert_query);

$added_count = 0;
foreach ($finalPlates as $plate) {
    $insert_stmt->bindParam(":name", $plate['name']);
    $insert_stmt->bindParam(":description", $plate['description']);
    $insert_stmt->bindParam(":price", $plate['price']);
    $insert_stmt->bindParam(":category", $plate['category']);
    $insert_stmt->bindParam(":image_url", $plate['image_url']);
    $insert_stmt->bindParam(":rating", $plate['rating']);
    
    if ($insert_stmt->execute()) {
        echo "✓ Ajouté: " . $plate['name'] . "\n";
        $added_count++;
    } else {
        echo "✗ Erreur: " . $plate['name'] . "\n";
    }
}

echo "\n=== Nettoyage final terminé ===\n";
echo "$added_count plats ajoutés (sur 10 attendus)\n";

// Afficher les plats finaux
echo "\n=== Plats finaux dans la base de données ===\n";
$select_query = "SELECT id, name, category, price, image_url FROM plates ORDER BY id";
$select_stmt = $db->prepare($select_query);
$select_stmt->execute();
$plates = $select_stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($plates as $plate) {
    echo "ID: {$plate['id']} | {$plate['name']} | {$plate['price']} FCFA\n";
    echo "   Image: {$plate['image_url']}\n";
}

echo "\nTotal: " . count($plates) . " plats\n";
?>
