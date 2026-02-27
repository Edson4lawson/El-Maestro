<?php
header("Content-Type: text/plain; charset=UTF-8");

include_once './config/database.php';
include_once './models/Plate.php';

$database = new Database();
$db = $database->getConnection();

// Clear existing plates
$clear_query = "DELETE FROM plates";
$clear_stmt = $db->prepare($clear_query);
$clear_stmt->execute();

// Reset auto increment
$reset_query = "ALTER TABLE plates AUTO_INCREMENT = 1";
$reset_stmt = $db->prepare($reset_query);
$reset_stmt->execute();

// Base dishes data
$baseDishes = [
    [
        'name' => "Poulet Yassa",
        'description' => "Un plat sénégalais savoureux à base de poulet mariné au citron et aux oignons.",
        'category' => "Plats Résistants",
        'price' => 12.50,
        'image_url' => "/src/assets/images/image1.jpg",
        'rating' => 4.5
    ],
    [
        'name' => "Sushi Mix",
        'description' => "Une sélection raffinée de sushis japonais frais et colorés.",
        'category' => "Plats Résistants", 
        'price' => 15.00,
        'image_url' => "/src/assets/images/image2.jpg",
        'rating' => 4.8
    ],
    [
        'name' => "Attiéké Poisson",
        'description' => "Un plat ivoirien à base de semoule de manioc accompagnée de poisson grillé.",
        'category' => "Plats Résistants",
        'price' => 10.50,
        'image_url' => "/src/assets/images/image3.jpg",
        'rating' => 4.3
    ]
];

// Add pasta dishes with different variations
$pastaVariations = [
    ["Pâtes Carbonara Classique", "Des pâtes italiennes classiques avec une sauce onctueuse aux œufs et au parmesan.", 11.00],
    ["Pâtes Carbonara Truffe", "Des pâtes carbonara enrichies à la truffe pour une saveur intense.", 14.50],
    ["Pâtes Carbonara Végétarienne", "Version végétarienne avec des légumes grillés et sauce crémeuse.", 10.50],
    ["Pâtes Carbonara Pancetta", "Des pâtes avec pancetta croquante et sauce parmesan.", 12.00],
    ["Pâtes Carbonara Seafood", "Variante aux fruits de mer avec crevettes et calamars.", 16.00]
];

for ($i = 4; $i <= 17; $i++) {
    $variation = $pastaVariations[($i - 4) % count($pastaVariations)];
    $baseDishes[] = [
        'name' => $variation[0],
        'description' => $variation[1],
        'category' => "Plats Résistants",
        'price' => $variation[2],
        'image_url' => "/src/assets/images/image$i.jpg",
        'rating' => 4.0 + (rand(0, 8) / 10)
    ];
}

// Drinks data
$drinksData = [
    ["Jus d'Orange Frais", "Jus d'orange pressé à la minute, plein de vitamines.", 3.50],
    ["Cocktail Tropical", "Mélange exotique de fruits tropicaux frais.", 5.50],
    ["Limonade Maison", "Limonade artisanale avec menthe fraîche.", 4.00],
    ["Thé Glacé Pêche", "Thé glacé infusé à la pêche blanche.", 4.50],
    ["Smoothie Berry", "Smoothie aux fruits rouges et yaourt.", 5.00],
    ["Eau Pétillante", "Eau minérale pétillante avec tranche de citron.", 2.50],
    ["Café Glacé", "Café expresso refroidi avec glaçons et lait.", 3.50],
    ["Mojito Sans Alcool", "Mojito traditionnel sans alcool.", 5.50],
    ["Jus de Grenadine", "Jus de grenadine frais et sucré.", 3.00],
    ["Cocktail Ananas", "Jus d'ananas frais avec une touche de coco.", 4.50],
    ["Thé Vert Glacé", "Thé vert japonais refroidi.", 3.50],
    ["Smoothie Mangue", "Smoothie crémeux à la mangue.", 5.50],
    ["Eau Minérale", "Eau de source pure.", 2.00],
    ["Jus de Pomme", "Jus de pomme bio 100%.", 3.00],
    ["Café Froid", "Café froid infusé lentement.", 4.00],
    ["Limonade Rose", "Limonade parfumée à la rose.", 4.50],
    ["Cocktail Fruits Rouges", "Mélange de fruits rouges frais.", 5.00],
    ["Thé à la Menthe", "Thé vert à la menthe fraîche.", 3.50]
];

$baseDrinks = [];
for ($i = 1; $i <= 18; $i++) {
    $drink = $drinksData[$i - 1];
    $baseDrinks[] = [
        'name' => $drink[0],
        'description' => $drink[1],
        'category' => "Boissons",
        'price' => $drink[2],
        'image_url' => "/src/assets/images/image" . chr(64 + $i) . ".jpg", // A, B, C, etc.
        'rating' => 4.0 + (rand(0, 8) / 10)
    ];
}

// Desserts data
$dessertsData = [
    ["Tiramisu Classique", "Tiramisu italien traditionnel au café et mascarpone.", 6.50],
    ["Crème Brûlée", "Crème vanillée avec une croûte de sucre caramélisé.", 5.50],
    ["Mousse au Chocolat", "Mousse au chocolat noir belge.", 4.50],
    ["Tarte aux Pommes", "Tarte fine aux pommes caramelisées.", 5.00],
    ["Panna Cotta", "Dessert italien à la vanille avec coulis de fruits.", 5.50],
    ["Cheesecake", "Cheesecake new-yorkais avec coulis de fruits rouges.", 6.00],
    ["Glace Artisanale", "Glace maison parfum vanille ou chocolat.", 4.00],
    ["Brownie Chaud", "Brownie au chocolat tiède avec glace vanille.", 5.50],
    ["Salade de Fruits", "Salade de fruits de saison frais.", 3.50],
    ["Crêpes Suzette", "Crêpes flambées au Grand Marnier.", 7.00],
    ["Profiteroles", "Choux à la crème glacée et chocolat chaud.", 6.50],
    ["Macarons", "Assortiment de macarons français.", 5.00],
    ["Tarte au Citron", "Tarte au citron meringuée.", 5.50]
];

$baseDesserts = [];
for ($i = 1; $i <= 13; $i++) {
    $dessert = $dessertsData[$i - 1];
    $baseDesserts[] = [
        'name' => $dessert[0],
        'description' => $dessert[1],
        'category' => "Desserts",
        'price' => $dessert[2],
        'image_url' => "/src/assets/images/dessert$i.jpg",
        'rating' => 4.0 + (rand(0, 8) / 10)
    ];
}

// Combine all plates
$allPlates = array_merge($baseDishes, $baseDrinks, $baseDesserts);

// Insert all plates
$insert_query = "INSERT INTO plates (name, description, price, category, image_url, rating) VALUES (:name, :description, :price, :category, :image_url, :rating)";
$insert_stmt = $db->prepare($insert_query);

$success_count = 0;
foreach ($allPlates as $plate) {
    $insert_stmt->bindParam(":name", $plate['name']);
    $insert_stmt->bindParam(":description", $plate['description']);
    $insert_stmt->bindParam(":price", $plate['price']);
    $insert_stmt->bindParam(":category", $plate['category']);
    $insert_stmt->bindParam(":image_url", $plate['image_url']);
    $insert_stmt->bindParam(":rating", $plate['rating']);
    
    if ($insert_stmt->execute()) {
        $success_count++;
        echo "✓ Ajouté: " . $plate['name'] . " (" . $plate['category'] . ")\n";
    } else {
        echo "✗ Erreur: " . $plate['name'] . "\n";
    }
}

echo "\n=== Migration terminée ===\n";
echo "$success_count plats sur " . count($allPlates) . " ont été ajoutés avec succès.\n";
echo "Catégories: " . count($baseDishes) . " plats, " . count($baseDrinks) . " boissons, " . count($baseDesserts) . " desserts.\n";
?>
