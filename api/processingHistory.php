<?php
require_once './global/pdo.php';
require_once './global/jwt.php';
require_once './global/cors.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$token = JwtUtils::getToken();
$user = JwtUtils::decodeToken($token);
$userId = $user['user_id'];

$input = json_decode(file_get_contents('php://input'), true);
$id = intval($input['idHistory'] ?? 0);
$action = $input['processingHistory'] ?? '';

if (!$id || !in_array($action, ['Accepted', 'Rejected', 'Trash'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Paramètres invalides']);
    exit;
}

try {
    $pdo = Database::getInstance()->getPDO();

    // Vérifie que la fiche appartient bien à l'utilisateur connecté
    $stmtCheck = $pdo->prepare("
        SELECT * FROM fiche_frais WHERE id = :id AND user_id = :userId
    ");
    $stmtCheck->execute([':id' => $id, ':userId' => $userId]);
    $fiche = $stmtCheck->fetch();

    if (!$fiche) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Accès refusé']);
        exit;
    }

    if ($action === 'Trash') {
        // Supprimer la fiche et ses détails
        $pdo->beginTransaction();
        $pdo->prepare("DELETE FROM fiche_frais_detail WHERE fiche_id = :id")->execute([':id' => $id]);
        $pdo->prepare("DELETE FROM fiche_frais WHERE id = :id")->execute([':id' => $id]);
        $pdo->commit();
    } else {
        // Met à jour le statut
        $stmt = $pdo->prepare("UPDATE fiche_frais SET statut = :statut WHERE id = :id");
        $stmt->execute([':statut' => $action, ':id' => $id]);
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}
