<?php
class OTPService {
    private $phoneNumber;
    
    public function __construct() {
        $this->phoneNumber = getenv('ADMIN_PHONE') ?: ($_ENV['ADMIN_PHONE'] ?? '+2290154047392');
    }
    
    // Générer code OTP à 6 chiffres
    public function generateCode() {
        return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }
    
    // Envoyer OTP par SMS
    public function sendOTP($phone, $otpCode) {
        $targetPhone = !empty($phone) ? $phone : $this->phoneNumber;
        
        // Log pour consultation dans les logs serveur (Render / Laragon)
        error_log("=== EL MAESTRO OTP ===");
        error_log("Destinataire : " . $targetPhone);
        error_log("Code OTP : " . $otpCode);
        error_log("Date d'expiration : " . date('Y-m-d H:i:s', strtotime('+5 minutes')));
        error_log("======================");
        
        // Si des identifiants Twilio sont configurés dans les variables d'environnement
        $twilioSid = getenv('TWILIO_SID') ?: ($_ENV['TWILIO_SID'] ?? null);
        $twilioToken = getenv('TWILIO_AUTH_TOKEN') ?: ($_ENV['TWILIO_AUTH_TOKEN'] ?? null);
        $twilioFrom = getenv('TWILIO_PHONE_NUMBER') ?: ($_ENV['TWILIO_PHONE_NUMBER'] ?? null);
        
        if ($twilioSid && $twilioToken && $twilioFrom) {
            try {
                $url = "https://api.twilio.com/2010-04-01/Accounts/{$twilioSid}/Messages.json";
                $data = [
                    'From' => $twilioFrom,
                    'To' => $targetPhone,
                    'Body' => "EL MAESTRO - Votre code de sécurité administrateur est : {$otpCode}. Valide 5 minutes."
                ];
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_USERPWD, "{$twilioSid}:{$twilioToken}");
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($httpCode >= 200 && $httpCode < 300) {
                    error_log("SMS Twilio envoyé avec succès à {$targetPhone}");
                    return true;
                } else {
                    error_log("Erreur Twilio HTTP {$httpCode}: {$response}");
                }
            } catch (Exception $e) {
                error_log("Exception lors de l'envoi SMS Twilio : " . $e->getMessage());
            }
        }
        
        return true;
    }
    
    // Vérifier format OTP
    public function validateOTP($otp) {
        return preg_match('/^\d{6}$/', $otp);
    }
    
    // Vérifier expiration
    public function isExpired($expiresAt) {
        return strtotime($expiresAt) < time();
    }
}
?>
