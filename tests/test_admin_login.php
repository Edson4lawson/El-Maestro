<?php
// Test du login admin pour obtenir un token
try {
    $db = new PDO('mysql:host=localhost;dbname=el_maestro', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== TEST LOGIN ADMIN ===\n";
    
    // Test login endpoint
    $loginData = [
        'email' => 'admin@elmaestro.bj',
        'password' => 'admin123'
    ];
    
    if (function_exists('curl_version')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/backend/api/index.php?route=admin/login');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Origin: http://localhost:5173'
        ]);
        curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
        curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "Code HTTP: $httpCode\n";
        echo "Réponse: $response\n";
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if (isset($data['session_token'])) {
                echo "✅ Login réussi ! Token: {$data['session_token']}\n";
                
                // Maintenant tester l'API menu avec ce token
                echo "\n=== TEST API MENU AVEC TOKEN LOGIN ===\n";
                $ch2 = curl_init();
                curl_setopt($ch2, CURLOPT_URL, 'http://localhost:8000/backend/api/index.php?route=admin/menu');
                curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch2, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $data['session_token'],
                    'Origin: http://localhost:5173'
                ]);
                curl_setopt($ch2, CURLOPT_COOKIEFILE, 'cookie.txt');
                
                $response2 = curl_exec($ch2);
                $httpCode2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
                curl_close($ch2);
                
                echo "Code HTTP: $httpCode2\n";
                echo "Réponse: " . substr($response2, 0, 200) . "...\n";
                
                if ($httpCode2 === 200) {
                    echo "✅ API Menu fonctionne avec token login !\n";
                }
            }
        } else {
            echo "❌ Login échoué\n";
        }
    }
    
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}
?>
