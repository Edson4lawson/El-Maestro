<?php
// Test avec une session admin valide
session_start();
$_SESSION['admin_authenticated'] = true;
$_SESSION['admin_session_token'] = 'test_token_123';
$_SESSION['admin_id'] = 1;

// Test direct du contrôleur sans middleware
try {
    $db = new PDO('mysql:host=localhost;dbname=el_maestro', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Simuler la requête du contrôleur
    $query = "SELECT * FROM plates WHERE 1=1 ORDER BY name ASC LIMIT 50 OFFSET 0";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $plates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formater comme le fait le contrôleur
    foreach ($plates as &$plate) {
        $plate['status'] = $plate['is_available'] ? 'available' : 'unavailable';
        $plate['rating'] = (float)$plate['base_rating'];
        $plate['prep_time'] = $plate['prep_time'] ?? 15;
        unset($plate['is_available'], $plate['base_rating']);
    }
    
    echo "=== TEST CONTROLEUR ===\n";
    echo json_encode([
        'success' => true,
        'data' => $plates,
        'total' => count($plates)
    ], JSON_PRETTY_PRINT) . "\n";
    
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}
?>
