<?php
require_once './global/detect_error.php';
require_once './global/cors.php';
require_once './global/pdo.php';
require_once './global/jwt.php';

try {
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

    $userData = JwtUtils::decodeToken($token);
    if (!$userData) {
        throw new Exception('Token invalide ou expiré lors de la vérification');
    }

    $userId = $userData['user_id'];

    // Lecture de l'input
    $input = json_decode(file_get_contents('php://input'), true);
    $ficheId = intval($input['ficheId']);

    if (!$ficheId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID de fiche manquant ou invalide']);
        exit;
    }

    $pdo = Database::getInstance()->getPDO();

    // Vérifie que la fiche appartient bien à l'utilisateur
    $checkStmt = $pdo->prepare("SELECT id FROM fiche_frais WHERE id = :id AND user_id = :userId");
    $checkStmt->bindParam(':id', $ficheId, PDO::PARAM_INT);
    $checkStmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $checkStmt->execute();

    $fiche = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$fiche) {
        echo json_encode(['success' => false, 'message' => 'Fiche non trouvée ou non autorisée']);
        exit;
    }

    // Récupération des détails liés à cette fiche
    $stmt = $pdo->prepare("
        SELECT d.*
        FROM fiche_frais_detail d
        WHERE d.fiche_id = :ficheId
    ");
    $stmt->bindParam(':ficheId', $ficheId, PDO::PARAM_INT);
    $stmt->execute();

    $details = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'details' => $details]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur', 'debug' => $e->getMessage()]);
}