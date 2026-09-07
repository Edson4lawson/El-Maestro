<?php
header("Content-Type: text/plain; charset=UTF-8");

include_once './config/database.php';
include_once './models/Plate.php';

$database = new Database();
$db = $database->getConnection();

// Plats d'entrée
$entreesData = [
    [
        'name' => 'Salade Composée',
        'description' => 'Salade fraîche avec légumes de saison et vinaigrette maison.',
        'category' => 'Entrées',
        'price' => 2500.00,
        'image_url' => 'http://localhost:5173/src/assets/image10.jpg',
        'rating' => 4.3
    ],
    [
        'name' => 'Soupe du Jour',
        'description' => 'Soupe maison préparée avec des ingrédients frais du jour.',
        'category' => 'Entrées',
        'price' => 2000.00,
        'image_url' => 'http://localhost:5173/src/assets/image11.jpg',
        'rating' => 4.2
    ],
    [
        'name' => 'Carpaccio de Bœuf',
        'description' => ' fines tranches de bœuf cru avec parmesan et huile d\'olive.',
        'category' => 'Entrées',
        'price' => 4500.00,
        'image_url' => 'http://localhost:5173/src/assets/image12.jpg',
        'rating' => 4.7
    ],
    [
        'name' => 'Bruschetta',
        'description' => 'Pain grillé à l\'ail avec tomates fraîches et basilic.',
        'category' => 'Entrées',
        'price' => 2500.00,
        'image_url' => 'http://localhost:5173/src/assets/image13.jpg',
        'rating' => 4.4
    ],
    [
        'name' => 'Crevettes Grillées',
        'description' => 'Crevettes fraîches grillées aux herbes aromatiques.',
        'category' => 'Entrées',
        'price' => 4000.00,
        'image_url' => 'http://localhost:5173/src/assets/image14.jpg',
        'rating' => 4.6
    ],
    [
        'name' => 'Tartare de Saumon',
        'description' => 'Saumon frais mariné avec citron et aneth.',
        'category' => 'Entrées',
        'price' => 4500.00,
        'image_url' => 'http://localhost:5173/src/assets/image15.jpg',
        'rating' => 4.5
    ],
    [
        'name' => 'Foie Gras Poêlé',
        'description' => 'Foie gras poêlé accompagné de toast et de figues.',
        'category' => 'Entrées',
        'price' => 6500.00,
        'image_url' => 'http://localhost:5173/src/assets/image16.jpg',
        'rating' => 4.8
    ],
    [
        'name' => 'Velouté de Potiron',
        'description' => 'Velouté onctueux de potiron avec crème fraîche.',
        'category' => 'Entrées',
        'price' => 2500.00,
        'image_url' => 'http://localhost:5173/src/assets/image2.jpg',
        'rating' => 4.3
    ]
];

$insert_query = "INSERT INTO plates (name, description, price, category, image_url, base_rating) VALUES (:name, :description, :price, :category, :image_url, :rating)";
$insert_stmt = $db->prepare($insert_query);

$added_count = 0;

echo "=== Ajout des entrées ===\n";
foreach ($entreesData as $entree) {
    // Vérifier si le plat existe déjà
    $check_query = "SELECT id FROM plates WHERE name = ?";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->execute([$entree['name']]);
    
    if ($check_stmt->rowCount() == 0) {
        $insert_stmt->bindParam(":name", $entree['name']);
        $insert_stmt->bindParam(":description", $entree['description']);
        $insert_stmt->bindParam(":price", $entree['price']);
        $insert_stmt->bindParam(":category", $entree['category']);
        $insert_stmt->bindParam(":image_url", $entree['image_url']);
        $insert_stmt->bindParam(":rating", $entree['rating']);
        
        if ($insert_stmt->execute()) {
            echo "✓ Ajouté: " . $entree['name'] . "\n";
            $added_count++;
        } else {
            echo "✗ Erreur: " . $entree['name'] . "\n";
        }
    } else {
        echo "⚠ Déjà existant: " . $entree['name'] . "\n";
    }
}

echo "\n=== Ajout terminé ===\n";
echo "$added_count entrées ajoutées\n";

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
