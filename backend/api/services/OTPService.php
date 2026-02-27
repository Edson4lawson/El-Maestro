<?php
class OTPService {
    private $phoneNumber;
    
    public function __construct() {
        // En développement, vous pouvez configurer un numéro par défaut
        $this->phoneNumber = '+22912345678'; // À remplacer en production
    }
    
    // Générer code OTP à 6 chiffres
    public function generateCode() {
        return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }
    
    // Envoyer OTP par SMS
    public function sendOTP($phone, $otpCode) {
        // Log pour développement
        error_log("OTP généré pour $phone: $otpCode");
        
        // En production, intégrer avec un service SMS réel:
        // Exemple avec Twilio:
        /*
        $sid = 'your_twilio_sid';
        $token = 'your_twilio_token';
        $from = '+1234567890';
        
        $client = new Twilio\Rest\Client($sid, $token);
        
        try {
            $message = $client->messages->create(
                $phone,
                array(
                    'from' => $from,
                    'body' => "EL MAESTRO - Votre code de vérification: $otpCode. Valide 1 minute."
                )
            );
            
            error_log("SMS envoyé à $phone: SID " . $message->sid);
            return true;
        } catch (Exception $e) {
            error_log("Erreur envoi SMS: " . $e->getMessage());
            return false;
        }
        */
        
        // Simulation envoi réussi
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
    
    // Envoyer notification admin pour développement
    public function sendDevNotification($phone, $otpCode) {
        // Pour développement: afficher le code dans les logs
        error_log("=== DÉVELOPPEMENT OTP ===");
        error_log("Téléphone: $phone");
        error_log("Code: $otpCode");
        error_log("Valide jusqu'à: " . date('Y-m-d H:i:s', strtotime('+1 minute')));
        error_log("========================");
    }
}
?>
