<?php
require_once './global/detect_error.php';
require_once './global/cors.php';
require_once './global/pdo.php';
require_once './global/jwt.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée. Utilisez POST.']);
    exit;
}

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

    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        throw new Exception('Données JSON invalides');
    }

    $idHistory = isset($input['idHistory']) ? trim($input['idHistory']) : '';
    $processingHistory = isset($input['processingHistory']) ? trim($input['processingHistory']) : '';

    $database = Database::getInstance();
    $pdo = $database->getPDO();

    if ($processingHistory === 'Accepted') {

        $stmt = $pdo->prepare("UPDATE fiche_frais SET statut = 'Accepted' WHERE id = :historyId");
        $stmt->bindParam(':historyId', $idHistory, PDO::PARAM_INT);
        $stmt->execute();

        echo json_encode(['success' => true]);
        exit;
    } elseif ($processingHistory === 'Rejected') {

        $stmt = $pdo->prepare("UPDATE fiche_frais SET statut = 'Rejected' WHERE id = :historyId");
        $stmt->bindParam(':historyId', $idHistory, PDO::PARAM_INT);
        $stmt->execute();


        echo json_encode(['success' => true]);
        exit;
    } elseif ($processingHistory === 'Trash') {

        $stmt = $pdo->prepare("DELETE FROM fiche_frais WHERE id = :historyId");
        $stmt->bindParam(':historyId', $idHistory, PDO::PARAM_INT);
        $stmt->execute();

        echo json_encode(['success' => true]);
        exit;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Token invalide']);
    exit;
}
