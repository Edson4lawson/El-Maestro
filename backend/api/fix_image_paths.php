<?php
header("Content-Type: text/plain; charset=UTF-8");

include_once './config/database.php';
$database = new Database();
$db = $database->getConnection();

echo "=== Correction des chemins d'images ===\n\n";

// Update image paths to remove /src/assets/images/ and use correct paths
$update_query = "UPDATE plates SET image_url = REPLACE(image_url, '/src/assets/images/', '/src/assets/')";

$stmt = $db->prepare($update_query);
if ($stmt->execute()) {
    $affected = $stmt->rowCount();
    echo "✓ $affected chemins d'images corrigés\n\n";
    
    // Show some examples
    $select_query = "SELECT id, name, image_url FROM plates LIMIT 10";
    $result = $db->query($select_query);
    
    echo "=== Exemples de chemins corrigés ===\n";
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo 'ID: ' . $row['id'] . ' | Name: ' . $row['name'] . ' | Image: ' . $row['image_url'] . "\n";
    }
} else {
    echo "✗ Erreur lors de la mise à jour\n";
}
?>
