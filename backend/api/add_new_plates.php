<?php
header("Content-Type: text/plain; charset=UTF-8");

include_once './config/database.php';
include_once './models/Plate.php';

$database = new Database();
$db = $database->getConnection();

// Nouveaux plats basés sur les noms des images
$newPlates = [
    [
        'name' => 'Brochette de Viande',
        'description' => 'Brochettes de viande grillées aux épices, accompagnées de légumes frais.',
        'category' => 'Plats Résistants',
        'price' => 4500.00,
        'image_url' => 'http://localhost:5173/src/assets/brochette de viande.jpg',
        'rating' => 4.6
    ],
    [
        'name' => 'Burger & Frites',
        'description' => 'Délicieux burger maison avec frites croustillantes.',
        'category' => 'Plats Résistants',
        'price' => 5000.00,
        'image_url' => 'http://localhost:5173/src/assets/buger & frite.jpg',
        'rating' => 4.5
    ],
    [
        'name' => 'Chawama Viande',
        'description' => 'Chawama traditionnel avec viande tendre et sauce savoureuse.',
        'category' => 'Plats Résistants',
        'price' => 4000.00,
        'image_url' => 'http://localhost:5173/src/assets/chawama viande.jpg',
        'rating' => 4.4
    ],
    [
        'name' => 'Chawama',
        'description' => 'Chawama authentique, plat local très apprécié.',
        'category' => 'Plats Résistants',
        'price' => 3500.00,
        'image_url' => 'http://localhost:5173/src/assets/chawama.jpg',
        'rating' => 4.3
    ],
    [
        'name' => 'Come & Mowo',
        'description' => 'Spécialité locale Come & Mowo, mélange de saveurs uniques.',
        'category' => 'Plats Résistants',
        'price' => 4000.00,
        'image_url' => 'http://localhost:5173/src/assets/come & mowo.jpg',
        'rating' => 4.5
    ],
    [
        'name' => 'Fillet de Poulet',
        'description' => 'Fillet de poulet grillé avec marinade aux herbes.',
        'category' => 'Plats Résistants',
        'price' => 5500.00,
        'image_url' => 'http://localhost:5173/src/assets/fillet de poulet.jpg',
        'rating' => 4.7
    ],
    [
        'name' => 'Lait Caillé',
        'description' => 'Lait caillé frais et nature, riche en probiotiques.',
        'category' => 'Boissons',
        'price' => 1000.00,
        'image_url' => 'http://localhost:5173/src/assets/lait caillé.jpg',
        'rating' => 4.2
    ],
    [
        'name' => 'Pizza',
        'description' => 'Pizza artisanale avec fromage fondant et ingrédients frais.',
        'category' => 'Plats Résistants',
        'price' => 6000.00,
        'image_url' => 'http://localhost:5173/src/assets/pizza.jpg',
        'rating' => 4.8
    ],
    [
        'name' => 'Riz Cantonaise',
        'description' => 'Riz cantonaise aux légumes et poulet, recette asiatique.',
        'category' => 'Plats Résistants',
        'price' => 4500.00,
        'image_url' => 'http://localhost:5173/src/assets/riz cantonaise.jpg',
        'rating' => 4.6
    ]
];

$insert_query = "INSERT INTO plates (name, description, price, category, image_url, base_rating) VALUES (:name, :description, :price, :category, :image_url, :rating)";
$insert_stmt = $db->prepare($insert_query);

$added_count = 0;
$duplicate_count = 0;

echo "=== Ajout des nouveaux plats ===\n";
foreach ($newPlates as $plate) {
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
           echo "✓ Ajouté: " . $plate['name'] . " (" . $plate['category'] . ")\n";
            $added_count++;
        } else {
            echo "✗ Erreur: " . $plate['name'] . "\n";
        }
    } else {
        echo "⚠ Déjà existant: " . $plate['name'] . "\n";
        $duplicate_count++;
    }
}

echo "\n=== Ajout terminé ===\n";
echo "$added_count nouveaux plats ajoutés\n";
echo "$duplicate_count plats déjà existants\n";

// Afficher le résumé final
echo "\n=== Résumé du menu ===\n";
$select_query = "SELECT category, COUNT(*) as count FROM plates GROUP BY category";
$select_stmt = $db->prepare($select_query);
$select_stmt->execute();
$categories = $select_stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($categories as $cat) {
    echo "{$cat['category']}: {$cat['count']} éléments\n";
}

$total_query = "SELECT COUNT(*) as total FROM plates";
$total_stmt = $db->prepare($total_query);
$total_stmt->execute();
$total = $total_stmt->fetch(PDO::FETCH_ASSOC);
echo "\nTotal: {$total['total']} éléments\n";
?>
