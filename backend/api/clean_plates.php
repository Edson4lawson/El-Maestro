<?php
header("Content-Type: text/plain; charset=UTF-8");

include_once './config/database.php';
include_once './models/Plate.php';

$database = new Database();
$db = $database->getConnection();

// Les 10 plats à garder avec leurs vraies images
$platesToKeep = [
    'Poulet Yassa',
    'Sushi Mix', 
    'Attiéké Poisson',
    'Pâtes Carbonara Classique',
    'Pâtes Carbonara Truffe',
    'Pâtes Carbonara Végétarienne',
    'Pâtes Carbonara Pancetta',
    'Pâtes Carbonara Seafood',
    'Poulet Braisé Maestro',
    'Poisson Grillé Royal'
];

// Supprimer tous les plats qui ne sont pas dans la liste
$placeholders = implode(',', array_fill(0, count($platesToKeep), '?'));
$delete_query = "DELETE FROM plates WHERE name NOT IN ($placeholders)";
$delete_stmt = $db->prepare($delete_query);

$deleted_count = 0;
if ($delete_stmt->execute($platesToKeep)) {
    $deleted_count = $delete_stmt->rowCount();
    echo "✓ $deleted_count plats supprimés\n";
} else {
    echo "✗ Erreur lors de la suppression\n";
}

// Réinitialiser l'auto increment
$reset_query = "ALTER TABLE plates AUTO_INCREMENT = 1";
$reset_stmt = $db->prepare($reset_query);
$reset_stmt->execute();

// Ajouter les 2 plats manquants s'ils n'existent pas
$missingPlates = [
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
foreach ($missingPlates as $plate) {
    // Vérifier si le plat existe déjà
    $check_query = "SELECT id FROM plates WHERE name = ?";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->execute([$plate['name']]);
    
    if ($check_stmt->rowCount() == 0) {
        $insert_stmt->bindParam(":name", $plate['name']);
        $insert_stmt->bindParam(":description", $plate['description']);
        $insert_stmt->bindParam(":price", $plate['price']);
        $insert_stmt->bindParam(":category", $plate['category']);
        $insert_stmt->bindParam(":image_url", $plate['image_url']);
        $insert_stmt->bindParam(":rating", $plate['rating']);
        
        if ($insert_stmt->execute()) {
            echo "✓ Ajouté: " . $plate['name'] . "\n";
            $added_count++;
        }
    }
}

echo "\n=== Nettoyage terminé ===\n";
echo "$deleted_count plats supprimés, $added_count plats ajoutés\n";

// Afficher les plats restants
echo "\n=== Plats restants dans la base de données ===\n";
$select_query = "SELECT id, name, category, image_url FROM plates ORDER BY id";
$select_stmt = $db->prepare($select_query);
$select_stmt->execute();
$plates = $select_stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($plates as $plate) {
    echo "ID: {$plate['id']} | {$plate['name']} ({$plate['category']})\n";
}

echo "\nTotal: " . count($plates) . " plats\n";
?>
