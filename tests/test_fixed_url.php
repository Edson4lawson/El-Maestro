<?php
// Test avec la nouvelle URL corrigée
echo "=== TEST URL CORRIGÉE ===\n";

if (function_exists('curl_version')) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/backend/api/index.php?route=admin/menu');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer test_session_token_1778271000',
        'Origin: http://localhost:5173'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "URL: http://localhost:8000/backend/api/index.php?route=admin/menu\n";
    echo "Code HTTP: $httpCode\n";
    echo "Réponse: " . substr($response, 0, 200) . "...\n";
    
    if ($httpCode === 200) {
        echo "✅ URL corrigée fonctionne !\n";
    } else {
        echo "❌ Erreur avec URL corrigée\n";
        
        // Tester avec login token
        echo "\n=== TEST AVEC TOKEN LOGIN ===\n";
        $ch2 = curl_init();
        curl_setopt($ch2, CURLOPT_URL, 'http://localhost:8000/backend/api/index.php?route=admin/login');
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_POST, true);
        curl_setopt($ch2, CURLOPT_POSTFIELDS, json_encode([
            'email' => 'admin@elmaestro.bj',
            'password' => 'admin123'
        ]));
        curl_setopt($ch2, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Origin: http://localhost:5173'
        ]);
        
        $loginResponse = curl_exec($ch2);
        $loginCode = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
        curl_close($ch2);
        
        echo "Login Code: $loginCode\n";
        echo "Login Response: $loginResponse\n";
    }
} else {
    echo "cURL non disponible\n";
}
?>
