<?php
// Créer une session admin de test pour l'API
try {
    $db = new PDO('mysql:host=localhost;dbname=el_maestro', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Récupérer l'admin admin@elmaestro.bj
    $stmt = $db->prepare("SELECT id FROM admins WHERE email = 'admin@elmaestro.bj' LIMIT 1");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        echo "Admin non trouvé\n";
        exit;
    }
    
    // Créer une session valide
    $sessionToken = 'test_session_token_' . time();
    $otpCode = '123456';
    $otpExpires = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // Supprimer anciennes sessions de test
    $db->exec("DELETE FROM admin_sessions WHERE session_token LIKE 'test_session_token_%'");
    
    // Insérer nouvelle session vérifiée
    $query = "INSERT INTO admin_sessions (admin_id, session_token, otp_code, otp_expires_at, is_verified, expires_at) 
              VALUES (:admin_id, :session_token, :otp_code, :otp_expires_at, 1, DATE_ADD(NOW(), INTERVAL 24 HOUR))";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':admin_id', $admin['id']);
    $stmt->bindParam(':session_token', $sessionToken);
    $stmt->bindParam(':otp_code', $otpCode);
    $stmt->bindParam(':otp_expires_at', $otpExpires);
    
    if ($stmt->execute()) {
        echo "Session de test créée avec succès\n";
        echo "Token: $sessionToken\n";
        echo "Admin ID: {$admin['id']}\n";
        
        // Tester l'API avec ce token
        echo "\n=== TEST API AVEC TOKEN ===\n";
        
        if (function_exists('curl_version')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/backend/api/index.php?route=admin/menu');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $sessionToken,
                'Origin: http://localhost:5173'
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            echo "Code HTTP: $httpCode\n";
            echo "Réponse: " . substr($response, 0, 300) . "...\n";
            
            if ($httpCode === 200) {
                echo "✅ API fonctionne avec token !\n";
            } else {
                echo "❌ Erreur API même avec token\n";
            }
        }
    } else {
        echo "Erreur création session\n";
    }
    
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}
?>
