<?php
include_once './config/database.php';
$database = new Database();
$db = $database->getConnection();

echo "=== Diagnostic des chemins d'images ===\n\n";

$query = 'SELECT id, name, image_url FROM plates LIMIT 10';
$stmt = $db->prepare($query);
$stmt->execute();

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo 'ID: ' . $row['id'] . ' | Name: ' . $row['name'] . ' | Image: ' . $row['image_url'] . "\n";
}

echo "\n=== Test d'accès aux images ===\n";

// Test if image files exist
$image_paths = [
    'src/assets/images/image1.jpg',
    'src/assets/images/dessert1.jpg',
    'src/assets/images/imageA.jpg'
];

foreach ($image_paths as $path) {
    $full_path = __DIR__ . '/../' . $path;
    echo "Path: $path -> ";
    echo file_exists($full_path) ? "✓ EXISTS" : "✗ NOT FOUND";
    echo "\n";
}
?>
