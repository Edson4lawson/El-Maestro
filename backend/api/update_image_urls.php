<?php
header("Content-Type: text/plain; charset=UTF-8");

include_once './config/database.php';
$database = new Database();
$db = $database->getConnection();

echo "=== Mise à jour des URLs d'images ===\n\n";

// Update image paths to use the API endpoint
$update_query = "UPDATE plates SET image_url = CONCAT('http://localhost:8000/api/images?file=', REPLACE(image_url, '/src/assets/', ''))";

$stmt = $db->prepare($update_query);
if ($stmt->execute()) {
    $affected = $stmt->rowCount();
    echo "✓ $affected URLs d'images mises à jour\n\n";
    
    // Show some examples
    $select_query = "SELECT id, name, image_url FROM plates LIMIT 5";
    $result = $db->query($select_query);
    
    echo "=== Exemples d'URLs mises à jour ===\n";
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo 'ID: ' . $row['id'] . ' | Name: ' . $row['name'] . ' | Image: ' . $row['image_url'] . "\n";
    }
} else {
    echo "✗ Erreur lors de la mise à jour\n";
}
?>
