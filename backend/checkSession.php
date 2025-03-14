<?php
require_once './global/cors.php';
require_once './global/detect_error.php';
require_once './global/jwt.php';
require_once './global/pdo.php';
require_once 'vendor/autoload.php';

try {
    $token = JwtUtils::getToken();

    if (!$token) {
        throw new Exception('Aucun token trouvé');
    }

    $tokenParts = explode('.', $token);
    if (count($tokenParts) != 3) {
        throw new Exception('Format de token invalide');
    }

    $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[1])), true);

    $userData = JwtUtils::decodeToken($token);

    if (!$userData) {
        throw new Exception('Token invalide ou expiré lors de la vérification');
    }
    
    $database = Database::getInstance();
    $pdo = $database->getPDO();
    
    $tauxStmt = $pdo->prepare("SELECT T_ID as id, T_MONTANT as taux FROM taux_frais");
    $tauxStmt->execute();
    $tauxData = $tauxStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $tauxAssociatif = [];
    foreach ($tauxData as $taux) {
        $tauxAssociatif[$taux['id']] = [
            'taux' => (float)$taux['taux'],
        ];
    }

    echo json_encode([
        'success' => true,
        'message' => 'Token décodé avec succès',
        'token_data' => [
            'verified_data' => $userData
        ],
        'taux_frais' => $tauxAssociatif
    ]);
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de décodage du token: ' . $e->getMessage()
    ]);
}
?>