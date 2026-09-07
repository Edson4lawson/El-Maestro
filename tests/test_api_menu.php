<?php
// Test de l'API menu sans authentification pour débugger
session_start();

// Simuler une session admin valide
$_SESSION['admin_authenticated'] = true;
$_SESSION['admin_session_token'] = 'test_token';

// Test direct de la récupération des plats
try {
    $db = new PDO('mysql:host=localhost;dbname=el_maestro', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $query = "SELECT * FROM plates ORDER BY name ASC LIMIT 10";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $plates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "=== TEST DIRECT BDD ===\n";
    echo "Nombre de plats trouvés: " . count($plates) . "\n\n";
    
    foreach ($plates as $plate) {
        echo "ID: {$plate['id']}\n";
        echo "Nom: {$plate['name']}\n";
        echo "Prix: {$plate['price']}\n";
        echo "Catégorie: {$plate['category']}\n";
        echo "Image: {$plate['image_url']}\n";
        echo "Disponible: " . ($plate['is_available'] ?? 'non défini') . "\n";
        echo "---\n";
    }
    
    echo "\n=== TEST API ===\n";
    
    // Test de l'API avec curl si disponible
    if (function_exists('curl_version')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/backend/api/index.php?route=admin/menu');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "Code HTTP: $httpCode\n";
        echo "Réponse API: $response\n";
    } else {
        echo "cURL non disponible\n";
    }
    
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}
?>
