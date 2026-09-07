<?php
// Test de l'API avec les bonnes URLs
session_start();
$_SESSION['admin_authenticated'] = true;
$_SESSION['admin_session_token'] = 'test_token_123';
$_SESSION['admin_id'] = 1;

echo "=== TEST API CORRIGÉE ===\n";

// Test avec curl en simulant une requête CORS
if (function_exists('curl_version')) {
    $ch = curl_init();
    
    // Simuler les headers du navigateur
    curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/backend/api/index.php?route=admin/menu');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Origin: http://localhost:5173'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "Code HTTP: $httpCode\n";
    echo "Réponse: " . substr($response, 0, 200) . "...\n";
    
    if ($httpCode === 200) {
        echo "✅ API fonctionne !\n";
    } else {
        echo "❌ Erreur API\n";
    }
} else {
    echo "cURL non disponible\n";
}
?>
