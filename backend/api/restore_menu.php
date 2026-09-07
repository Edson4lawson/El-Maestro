<?php
header("Content-Type: text/plain; charset=UTF-8");

include_once './config/database.php';
include_once './models/Plate.php';

$database = new Database();
$db = $database->getConnection();

// Garder les 10 plats principaux déjà présents
// Ajouter les boissons
$drinksData = [
    ["Jus d'Orange Frais", "Jus d'orange pressé à la minute, plein de vitamines.", 1500, "http://localhost:5173/src/assets/A.jpg"],
    ["Cocktail Tropical", "Mélange exotique de fruits tropicaux frais.", 2500, "http://localhost:5173/src/assets/B.jpg"],
    ["Limonade Maison", "Limonade artisanale avec menthe fraîche.", 2000, "http://localhost:5173/src/assets/C.jpg"],
    ["Thé Glacé Pêche", "Thé glacé infusé à la pêche blanche.", 2000, "http://localhost:5173/src/assets/D.jpg"],
    ["Smoothie Berry", "Smoothie aux fruits rouges et yaourt.", 2500, "http://localhost:5173/src/assets/E.jpg"],
    ["Eau Pétillante", "Eau minérale pétillante avec tranche de citron.", 1000, "http://localhost:5173/src/assets/ImageF.jpg"],
    ["Café Glacé", "Café expresso refroidi avec glaçons et lait.", 1500, "http://localhost:5173/src/assets/G.jpg"],
    ["Mojito Sans Alcool", "Mojito traditionnel sans alcool.", 2500, "http://localhost:5173/src/assets/H.jpg"],
    ["Jus de Grenadine", "Jus de grenadine frais et sucré.", 1500, "http://localhost:5173/src/assets/I.jpg"],
    ["Cocktail Ananas", "Jus d'ananas frais avec une touche de coco.", 2000, "http://localhost:5173/src/assets/J.jpg"],
    ["Thé Vert Glacé", "Thé vert japonais refroidi.", 1500, "http://localhost:5173/src/assets/K.jpg"],
    ["Smoothie Mangue", "Smoothie crémeux à la mangue.", 2500, "http://localhost:5173/src/assets/L.jpg"],
    ["Eau Minérale", "Eau de source pure.", 1000, "http://localhost:5173/src/assets/M.jpg"],
    ["Jus de Pomme", "Jus de pomme bio 100%.", 1500, "http://localhost:5173/src/assets/N.jpg"],
    ["Café Froid", "Café froid infusé lentement.", 2000, "http://localhost:5173/src/assets/O.jpg"],
    ["Limonade Rose", "Limonade parfumée à la rose.", 2000, "http://localhost:5173/src/assets/P.jpg"],
    ["Cocktail Fruits Rouges", "Mélange de fruits rouges frais.", 2500, "http://localhost:5173/src/assets/Q.jpg"],
    ["Thé à la Menthe", "Thé vert à la menthe fraîche.", 1500, "http://localhost:5173/src/assets/R.jpg"]
];

// Ajouter les desserts
$dessertsData = [
    ["Tiramisu Classique", "Tiramisu italien traditionnel au café et mascarpone.", 3000, "http://localhost:5173/src/assets/dessert1.jpg"],
    ["Crème Brûlée", "Crème vanillée avec une croûte de sucre caramélisé.", 2500, "http://localhost:5173/src/assets/dessert2.jpg"],
    ["Mousse au Chocolat", "Mousse au chocolat noir belge.", 2000, "http://localhost:5173/src/assets/dessert3.jpg"],
    ["Tarte aux Pommes", "Tarte fine aux pommes caramelisées.", 2500, "http://localhost:5173/src/assets/dessert4.jpg"],
    ["Panna Cotta", "Dessert italien à la vanille avec coulis de fruits.", 2500, "http://localhost:5173/src/assets/dessert5.jpg"],
    ["Cheesecake", "Cheesecake new-yorkais avec coulis de fruits rouges.", 3000, "http://localhost:5173/src/assets/dessert6.jpg"],
    ["Glace Artisanale", "Glace maison parfum vanille ou chocolat.", 2000, "http://localhost:5173/src/assets/dessert7.jpg"],
    ["Brownie Chaud", "Brownie au chocolat tiède avec glace vanille.", 2500, "http://localhost:5173/src/assets/dessert8.jpg"],
    ["Salade de Fruits", "Salade de fruits de saison frais.", 1500, "http://localhost:5173/src/assets/dessert9.jpg"],
    ["Crêpes Suzette", "Crêpes flambées au Grand Marnier.", 3500, "http://localhost:5173/src/assets/dessert10.jpg"],
    ["Profiteroles", "Choux à la crème glacée et chocolat chaud.", 3000, "http://localhost:5173/src/assets/dessert11.jpg"],
    ["Macarons", "Assortiment de macarons français.", 2500, "http://localhost:5173/src/assets/dessert12.jpg"],
    ["Tarte au Citron", "Tarte au citron meringuée.", 2500, "http://localhost:5173/src/assets/dessert13.jpg"]
];

$insert_query = "INSERT INTO plates (name, description, price, category, image_url, base_rating) VALUES (:name, :description, :price, :category, :image_url, :rating)";
$insert_stmt = $db->prepare($insert_query);

$added_count = 0;

// Insérer les boissons
echo "=== Ajout des boissons ===\n";
foreach ($drinksData as $drink) {
    $insert_stmt->bindParam(":name", $drink[0]);
    $insert_stmt->bindParam(":description", $drink[1]);
    $insert_stmt->bindParam(":price", $drink[2]);
    $category = "Boissons";
    $insert_stmt->bindParam(":category", $category);
    $insert_stmt->bindParam(":image_url", $drink[3]);
    $rating = 4.0 + (rand(0, 8) / 10);
    $insert_stmt->bindParam(":rating", $rating);
    
    if ($insert_stmt->execute()) {
        echo "✓ " . $drink[0] . "\n";
        $added_count++;
    }
}

// Insérer les desserts
echo "\n=== Ajout des desserts ===\n";
foreach ($dessertsData as $dessert) {
    $insert_stmt->bindParam(":name", $dessert[0]);
    $insert_stmt->bindParam(":description", $dessert[1]);
    $insert_stmt->bindParam(":price", $dessert[2]);
    $category = "Desserts";
    $insert_stmt->bindParam(":category", $category);
    $insert_stmt->bindParam(":image_url", $dessert[3]);
    $rating = 4.0 + (rand(0, 8) / 10);
    $insert_stmt->bindParam(":rating", $rating);
    
    if ($insert_stmt->execute()) {
        echo "✓ " . $dessert[0] . "\n";
        $added_count++;
    }
}

echo "\n=== Restauration terminée ===\n";
echo "$added_count éléments ajoutés (boissons + desserts)\n";

// Afficher le résumé
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
