<?php
header("Content-Type: text/plain; charset=UTF-8");

include_once './config/database.php';
include_once './models/Plate.php';

$database = new Database();
$db = $database->getConnection();

// Vérifier les images de boissons dans la base
echo "=== VÉRIFICATION DES IMAGES DE BOISSONS ===\n\n";

$query = "SELECT id, name, image_url FROM plates WHERE category = 'Boissons' ORDER BY id";
$stmt = $db->prepare($query);
$stmt->execute();

$boissons = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Boissons dans la base de données:\n";
foreach ($boissons as $boisson) {
    echo "ID: {$boisson['id']} - Nom: {$boisson['name']}\n";
    echo "Image URL: {$boisson['image_url']}\n";
    
    // Vérifier si le fichier image existe
    $imageFile = '';
    if (strpos($boisson['image_url'], 'file=') !== false) {
        parse_str(parse_url($boisson['image_url'], PHP_URL_QUERY), $query);
        $imageFile = $query['file'] ?? '';
    }
    
    if ($imageFile) {
        $imagePath = '../frontend/src/assets/' . $imageFile;
        if (file_exists($imagePath)) {
            echo "✅ Image trouvée: $imagePath\n";
        } else {
            echo "❌ Image manquante: $imagePath\n";
        }
    }
    echo "---\n";
}

echo "\n=== VÉRIFICATION DES FICHIERS IMAGES DISPONIBLES ===\n\n";

// Vérifier les fichiers images A-R
for ($letter = ord('A'); $letter <= ord('R'); $letter++) {
    $filename = 'image' . chr($letter) . '.jpg';
    $path = '../frontend/src/assets/' . $filename;
    
    if (file_exists($path)) {
        echo "✅ $filename - Existe\n";
    } else {
        echo "❌ $filename - Manquant\n";
    }
}

echo "\n=== CORRECTION DES URLs DE BOISSONS ===\n\n";

// Mettre à jour les URLs des boissons avec les bons fichiers
$updateQuery = "UPDATE plates SET image_url = :image_url WHERE id = :id";
$updateStmt = $db->prepare($updateQuery);

$updatedCount = 0;
$letterIndex = 0;

foreach ($boissons as $boisson) {
    $newImageUrl = "http://localhost:8080/api/images?file=" . chr(65 + $letterIndex) . ".jpg"; // A, B, C, etc.
    
    $updateStmt->bindParam(":image_url", $newImageUrl);
    $updateStmt->bindParam(":id", $boisson['id']);
    
    if ($updateStmt->execute()) {
        echo "✅ Mis à jour: {$boisson['name']} → $newImageUrl\n";
        $updatedCount++;
    } else {
        echo "❌ Erreur mise à jour: {$boisson['name']}\n";
    }
    
    $letterIndex++;
}

echo "\n=== RÉSUMÉ ===\n";
echo "$updatedCount boissons mises à jour sur " . count($boissons) . "\n";
echo "Les images de boissons devraient maintenant s'afficher correctement.\n";
?>
