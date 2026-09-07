<?php
header("Content-Type: text/plain; charset=UTF-8");

include_once './config/database.php';
include_once './models/Plate.php';

$database = new Database();
$db = $database->getConnection();

echo "=== Vérification des correspondances images/noms ===\n\n";

echo "--- BOISSONS ---\n";
$stmt = $db->query("SELECT name, image_url FROM plates WHERE category = 'Boissons' ORDER BY name");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $image_name = basename($row['image_url']);
    echo "{$row['name']} -> $image_name\n";
}

echo "\n--- DESSERTS ---\n";
$stmt = $db->query("SELECT name, image_url FROM plates WHERE category = 'Desserts' ORDER BY name");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $image_name = basename($row['image_url']);
    echo "{$row['name']} -> $image_name\n";
}

echo "\n=== Images disponibles dans assets ===\n";
$assets_dir = '../../frontend/src/assets';
if (is_dir($assets_dir)) {
    $files = scandir($assets_dir);
    $images = array_filter($files, function($file) {
        return preg_match('/\.(jpg|jpeg|png)$/i', $file);
    });
    sort($images);
    foreach ($images as $image) {
        echo "$image\n";
    }
}
?>
