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

$input = json_decode(file_get_contents('php://input'), true);

$id = trim($input['id'] ?? '');

if (empty($id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID du visiteur manquant.']);
    exit;
}

$database = Database::getInstance();
$pdo = $database->getPDO();

try {
    $stmt = $pdo->prepare("DELETE FROM employe WHERE EMP_ID = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $success = $stmt->execute();

    if ($success && $stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Visiteur supprimé avec succès.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Visiteur non trouvé.']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression : ' . $e->getMessage()]);
}