<?php
try {
    $db = new PDO('mysql:host=localhost;dbname=el_maestro', 'root', '');
    $stmt = $db->query('SELECT COUNT(*) FROM plates');
    $count = $stmt->fetchColumn();
    echo "Nombre de plats: $count\n";
    
    if ($count > 0) {
        $stmt = $db->query('SELECT id, name, price, category, image_url FROM plates LIMIT 5');
        $plats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Détails des premiers plats:\n";
        foreach ($plats as $plat) {
            echo "- {$plat['name']} ({$plat['price']} FCFA) - Image: {$plat['image_url']}\n";
        }
    }
} catch (Exception $e) {
    echo 'Erreur: ' . $e->getMessage();
}
?>
