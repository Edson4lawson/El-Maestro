<?php
header("Content-Type: text/plain; charset=UTF-8");

include_once './config/database.php';
include_once './models/Plate.php';

$database = new Database();
$db = $database->getConnection();

echo "=== CORRECTION IMAGES DE BOISSONS AVEC FICHIERS EXISTANTS ===\n\n";

// Récupérer les boissons
$query = "SELECT id, name FROM plates WHERE category = 'Boissons' ORDER BY id";
$stmt = $db->prepare($query);
$stmt->execute();

$boissons = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Images disponibles pour les boissons (utiliser des images existantes)
$drinkImages = [
    'image1.jpg', 'image2.jpg', 'image3.jpg', 'image4.jpg', 'image5.jpg',
    'image6.jpg', 'image7.jpg', 'image8.jpg', 'image9.jpg', 'image10.jpg',
    'image11.jpg', 'image12.jpg', 'image13.jpg', 'image14.jpg', 'image15.jpg',
    'image16.jpg', 'image17.jpg', 'image18.jpg'
];

// Mettre à jour les URLs des boissons avec des images existantes
$updateQuery = "UPDATE plates SET image_url = :image_url WHERE id = :id";
$updateStmt = $db->prepare($updateQuery);

$updatedCount = 0;
$imageIndex = 0;

foreach ($boissons as $boisson) {
    $imageName = $drinkImages[$imageIndex % count($drinkImages)];
    $newImageUrl = "http://localhost:8080/api/images?file=$imageName";
    
    $updateStmt->bindParam(":image_url", $newImageUrl);
    $updateStmt->bindParam(":id", $boisson['id']);
    
    if ($updateStmt->execute()) {
        echo "✅ Mis à jour: {$boisson['name']} → $imageName\n";
        $updatedCount++;
    } else {
        echo "❌ Erreur mise à jour: {$boisson['name']}\n";
    }
    
    $imageIndex++;
}

echo "\n=== RÉSUMÉ ===\n";
echo "$updatedCount boissons mises à jour sur " . count($boissons) . "\n";
echo "Les images de boissons utilisent maintenant des fichiers existants.\n";
echo "Les boissons devraient s'afficher correctement dans le menu.\n";
?>
