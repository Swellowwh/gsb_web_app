<?php
require_once './global/detect_error.php';
require_once './global/cors.php';
require_once './global/pdo.php';
require_once './global/jwt.php';

// Modification de la vérification pour accepter la méthode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée. Utilisez POST.']);
    exit;
}

// Récupération du token JWT
$token = JwtUtils::getToken();
if (!$token) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Aucun token trouvé']);
    exit;
}

try {
    $userData = JwtUtils::decodeToken($token);
    if (!$userData) {
        throw new Exception('Token invalide ou expiré lors de la vérification');
    }

    $userId = $userData['user_id'];
    $userRole = $userData['role'];

    // Connexion à la base de données
    $pdo = Database::getInstance()->getPDO();

    // Requête pour récupérer toutes les avances de l'utilisateur
    $stmt = $pdo->prepare("
        SELECT 
            id_avance AS id, 
            montant, 
            description, 
            status, 
            date_creation AS date 
        FROM 
            gsb_avance
        WHERE 
            user_id = :userId AND status = 'accepted'
        ORDER BY 
            date_creation DESC
    ");

    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();

    $avances = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'avances' => $avances
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données : ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
