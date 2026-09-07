<?php
header("Content-Type: text/plain; charset=UTF-8");

include_once __DIR__ . '/config/database.php';

$database = new Database();
$db = $database->getConnection();

$imageMapping = [
    // Entrées
    'Salade Composée' => 'http://localhost:5173/src/assets/Salade Composée.jpg',
    'Soupe du Jour' => 'http://localhost:5173/src/assets/Soupe du Jour.jpg',
    'Carpaccio de Bœuf' => 'http://localhost:5173/src/assets/Carpaccio de Bœuf.jpg',
    'Bruschetta' => 'http://localhost:5173/src/assets/Bruschetta.jpg',
    'Crevettes Grillées' => 'http://localhost:5173/src/assets/Crevettes Grillées.jpg',
    'Tartare de Saumon' => 'http://localhost:5173/src/assets/Tartare de Saumon.jpg',
    'Foie Gras Poêlé' => 'http://localhost:5173/src/assets/Foie Gras Poêlé.jpg',
    'Velouté de Potiron' => 'http://localhost:5173/src/assets/Velouté de Potiron.jpg',

    // Desserts
    'Tiramisu Classique' => 'http://localhost:5173/src/assets/Tiramisu Classique.jpg',
    'Crème Brûlée' => 'http://localhost:5173/src/assets/Crème Brûlée.jpg',
    'Mousse au Chocolat' => 'http://localhost:5173/src/assets/Mousse au Chocolat.jpg',
    'Tarte aux Pommes' => 'http://localhost:5173/src/assets/Tarte aux Pommes.jpg',
    'Panna Cotta' => 'http://localhost:5173/src/assets/Panna Cotta.jpg',
    'Cheesecake' => 'http://localhost:5173/src/assets/Cheesecake.jpg',
    'Glace Artisanale' => 'http://localhost:5173/src/assets/Glace Artisanale.jpg',
    'Brownie Chaud' => 'http://localhost:5173/src/assets/Brownie Chaud.jpg',
    'Salade de Fruits' => 'http://localhost:5173/src/assets/Salade de Fruits.jpg',
    'Crêpes Suzette' => 'http://localhost:5173/src/assets/Crêpes Suzette.jpg',
    'Profiteroles' => 'http://localhost:5173/src/assets/Profiteroles.jpg',
    'Macarons' => 'http://localhost:5173/src/assets/Macarons.jpg',
    'Tarte au Citron' => 'http://localhost:5173/src/assets/Tarte au Citron.jpg',

    // Boissons
    'Jus d\'Orange Frais' => 'http://localhost:5173/src/assets/Jus d\'Orange Frais.jpg',
    'Cocktail Tropical' => 'http://localhost:5173/src/assets/Cocktail Tropical.jpg',
    'Limonade Maison' => 'http://localhost:5173/src/assets/Limonade Maison.jpg',
    'Thé Glacé Pêche' => 'http://localhost:5173/src/assets/Thé Glacé Pêche.jpg',
    'Smoothie Berry' => 'http://localhost:5173/src/assets/Smoothie Berry.jpg',
    'Eau Pétillante' => 'http://localhost:5173/src/assets/Eau Pétillante.jpg',
    'Café Glacé' => 'http://localhost:5173/src/assets/Café Glacé.jpg',
    'Mojito Sans Alcool' => 'http://localhost:5173/src/assets/Mojito Sans Alcool.jpg',
    'Jus de Grenadine' => 'http://localhost:5173/src/assets/Jus de Grenadine.jpg',
    'Cocktail Ananas' => 'http://localhost:5173/src/assets/Cocktail Ananas.jpg',
    'Thé Vert Glacé' => 'http://localhost:5173/src/assets/Thé Vert Glacé.jpg',
    'Smoothie Mangue' => 'http://localhost:5173/src/assets/Smoothie Mangue.jpg',
    'Eau Minérale' => 'http://localhost:5173/src/assets/Eau Minérale.jpg',
    'Jus de Pomme' => 'http://localhost:5173/src/assets/Jus de Pomme.jpg',
    'Café Froid' => 'http://localhost:5173/src/assets/Café Froid.jpg',
    'Limonade Rose' => 'http://localhost:5173/src/assets/Limonade Rose.jpg',
    'Cocktail Fruits Rouges' => 'http://localhost:5173/src/assets/Cocktail Fruits Rouges.jpg',
    'Thé à la Menthe' => 'http://localhost:5173/src/assets/Thé à la Menthe.jpg',
    'Lait Caillé' => 'http://localhost:5173/src/assets/Lait Caillé.jpg',

    // Plats Résistants
    'Poulet Yassa' => 'http://localhost:5173/src/assets/Poulet Yassa.jpg',
    'Sushi Mix' => 'http://localhost:5173/src/assets/Sushi Mix.jpg',
    'Attiéké Poisson' => 'http://localhost:5173/src/assets/Attiéké Poisson.jpg',
    'Pâtes Carbonara Classique' => 'http://localhost:5173/src/assets/Pâtes Carbonara Classique.jpg',
    'Pâtes Carbonara Truffe' => 'http://localhost:5173/src/assets/Pâtes Carbonara Truffe.jpg',
    'Pâtes Carbonara Végétarienne' => 'http://localhost:5173/src/assets/Pâtes Carbonara Végétarienne.jpg',
    'Pâtes Carbonara Pancetta' => 'http://localhost:5173/src/assets/Pâtes Carbonara Pancetta.jpg',
    'Pâtes Carbonara Seafood' => 'http://localhost:5173/src/assets/Pâtes Carbonara Seafood.jpg',
    'Poulet Braisé Maestro' => 'http://localhost:5173/src/assets/Poulet Braisé Maestro.jpg',
    'Poisson Grillé Royal' => 'http://localhost:5173/src/assets/Poisson Grillé Royal.jpg',
    'Brochette de Viande' => 'http://localhost:5173/src/assets/brochette de viande.jpg',
    'Burger & Frites' => 'http://localhost:5173/src/assets/buger & frite.jpg',
    'Chawama' => 'http://localhost:5173/src/assets/chawama.jpg',
    'Chawama Viande' => 'http://localhost:5173/src/assets/chawama viande.jpg',
    'Come & Mowo' => 'http://localhost:5173/src/assets/come & mowo.jpg',
    'Fillet de Poulet' => 'http://localhost:5173/src/assets/fillet de poulet.jpg',
    'Pizza' => 'http://localhost:5173/src/assets/pizza.jpg',
    'Riz Cantonaise' => 'http://localhost:5173/src/assets/riz cantonaise.jpg'
];

$update_query = "UPDATE plates SET image_url = :image_url WHERE name = :name";
$update_stmt = $db->prepare($update_query);

$success_count = 0;
foreach ($imageMapping as $name => $image_url) {
    $update_stmt->bindParam(":image_url", $image_url);
    $update_stmt->bindParam(":name", $name);
    
    if ($update_stmt->execute()) {
        $rows_affected = $update_stmt->rowCount();
        if ($rows_affected > 0) {
            echo "✓ Mis à jour: $name -> $image_url\n";
            $success_count++;
        } else {
            echo "ℹ Déjà à jour: $name\n";
        }
    } else {
        echo "✗ Erreur: $name\n";
    }
}

echo "\n=== Synchronisation terminée ===\n";
echo "$success_count plats modifiés sur " . count($imageMapping) . " plats totaux.\n";
