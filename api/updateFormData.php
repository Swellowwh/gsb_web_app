<?php
require_once './global/detect_error.php';
require_once './global/cors.php';
require_once './global/pdo.php';
require_once './global/jwt.php';

if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée. Utilisez PUT ou POST.']);
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

    // Correction: utiliser idHistory et non id
    $idHistory = isset($input['idHistory']) ? intval($input['idHistory']) : 0;
    $date = isset($input['date']) ? trim($input['date']) : '';
    $montant = isset($input['montant']) ? floatval($input['montant']) : 0;
    $description = isset($input['description']) ? trim($input['description']) : '';
    
    // Traitement du commentaire optionnel
    $commentaire = isset($input['commentaire']) ? trim($input['commentaire']) : null;

    if (empty($idHistory) || $idHistory <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'L\'ID de la fiche de frais est requis']);
        exit;
    }

    if (empty($date)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Le champ date est obligatoire']);
        exit;
    }
    
    if (empty($montant) || $montant <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Le champ montant est obligatoire et doit être supérieur à 0']);
        exit;
    }
    
    if (empty($description)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Le champ description est obligatoire']);
        exit;
    }

    $database = Database::getInstance();
    $pdo = $database->getPDO();

    // Vérification que la fiche de frais appartient à l'utilisateur
    // Correction: Le nom de la colonne ID d'après le schéma fourni
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM fiche_frais WHERE ID = :idHistory AND user_id = :userId");
    $checkStmt->bindParam(':idHistory', $idHistory, PDO::PARAM_INT);
    $checkStmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $checkStmt->execute();
    
    if ($checkStmt->fetchColumn() == 0) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Vous n\'êtes pas autorisé à modifier cette fiche de frais']);
        exit;
    }
    
    // Vérification que le statut est "Pending"
    $statusStmt = $pdo->prepare("SELECT statut FROM fiche_frais WHERE ID = :idHistory");
    $statusStmt->bindParam(':idHistory', $idHistory, PDO::PARAM_INT);
    $statusStmt->execute();
    $statut = $statusStmt->fetchColumn();
    
    if ($statut !== 'Pending') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Seules les fiches de frais en attente peuvent être modifiées']);
        exit;
    }

    // Construction de la requête SQL en fonction de la présence ou non du commentaire
    $sql = "UPDATE fiche_frais SET DATE = :date, DESCRIPTION = :description, MONTANT = :montant";
    
    // Ajout du commentaire à la requête s'il est fourni
    if ($commentaire !== null) {
        $sql .= ", commentaire = :commentaire";
    }
    
    // Finalisation de la requête
    $sql .= " WHERE ID = :idHistory AND user_id = :userId";
    
    $stmt = $pdo->prepare($sql);
    
    $stmt->bindParam(':date', $date, PDO::PARAM_STR);
    $stmt->bindParam(':description', $description, PDO::PARAM_STR);
    $stmt->bindParam(':montant', $montant, PDO::PARAM_STR);
    
    // Liaison du commentaire si nécessaire
    if ($commentaire !== null) {
        $stmt->bindParam(':commentaire', $commentaire, PDO::PARAM_STR);
    }
    
    // Correction: utiliser idHistory et non id
    $stmt->bindParam(':idHistory', $idHistory, PDO::PARAM_INT);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);

    $success = $stmt->execute();

    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Fiche de frais mise à jour avec succès']);
    } else {
        $errorInfo = $stmt->errorInfo();
        throw new Exception('Erreur lors de la mise à jour de la fiche de frais: ' . $errorInfo[2]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>