<?php
require_once 'vendor/autoload.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

/**
 * Classe utilitaire pour la gestion des tokens JWT
 * Simplifiée pour une meilleure lisibilité et utilisation
 */
class JwtUtils
{
    // Configuration
    private static $secret_key = 'test298449824t89re4t892s4t';
    private static $algorithm = 'HS256';
    private static $expiration = 3600; // 1 heure en secondes
    private static $cookie_name = 'gsb_session';

    /**
     * Génère un token JWT pour un utilisateur
     * 
     * @param array $userData Données de l'utilisateur à inclure dans le token
     * @return string Token JWT
     */
    public static function generateToken($userData)
    {
        $issuedAt = time();
        $expire = $issuedAt + self::$expiration;

        // Construction du payload
        $payload = array_merge([
            'iat' => $issuedAt, // Issued At
            'exp' => $expire,   // Expiration
        ], $userData);

        // Encodage du token
        return JWT::encode($payload, self::$secret_key, self::$algorithm);
    }

    /**
     * Récupère le token JWT depuis toutes les sources possibles
     * 
     * @return string|null Token JWT ou null si non trouvé
     */
    public static function getToken()
    {
        // 1. Vérifier le cookie
        if (isset($_COOKIE[self::$cookie_name])) {
            return $_COOKIE[self::$cookie_name];
        }
        
        // 2. Vérifier l'en-tête Authorization
        $headers = null;
        
        // Récupération de l'en-tête selon différentes configurations de serveur
        if (isset($_SERVER['Authorization'])) {
            $headers = $_SERVER['Authorization'];
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = $_SERVER['HTTP_AUTHORIZATION'];
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            if (isset($requestHeaders['Authorization'])) {
                $headers = $requestHeaders['Authorization'];
            }
        }
        
        // Extraction du token du format "Bearer [token]"
        if (!empty($headers) && preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }
        
        return null;
    }

    /**
     * Décode et vérifie un token JWT
     * 
     * @param string $token Token JWT à vérifier
     * @return array|false Données du token décodées ou false si invalide
     */
    public static function decodeToken($token)
    {
        try {
            // Décodage du token
            $decoded = JWT::decode($token, new Key(self::$secret_key, self::$algorithm));
            
            // Conversion en tableau
            $userData = (array) $decoded;
            
            // Retrait des champs techniques
            unset($userData['iat']);
            unset($userData['exp']);
            
            // Gestion de l'ancien format (rétrocompatibilité)
            if (isset($userData['data'])) {
                return (array) $userData['data'];
            }
            
            return $userData;
            
        } catch (\Exception $e) {
            // Journalisation de l'erreur
            error_log("Erreur JWT: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifie si l'utilisateur a une session valide
     * 
     * @return array|false Données utilisateur ou false si session invalide
     */
    public static function checkSession()
    {
        $token = self::getToken();
        
        if (!$token) {
            return false;
        }
        
        return self::decodeToken($token);
    }
}