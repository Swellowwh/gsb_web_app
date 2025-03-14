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

    $date = isset($input['date']) ? trim($input['date']) : '';
    $nombreRepas = isset($input['nombreRepas']) ? intval($input['nombreRepas']) : 0;
    $description = isset($input['description']) ? trim($input['description']) : '';
    $montantTotal = isset($input['montantTotal']) ? floatval($input['montantTotal']) : 0;

    if (empty($date)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Le champ date est obligatoire']);
        exit;
    }
    
    if (empty($nombreRepas) || $nombreRepas <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Le champ nombre de repas est obligatoire et doit être supérieur à 0']);
        exit;
    }
    
    if (empty($description)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Le champ description est obligatoire']);
        exit;
    }

    $database = Database::getInstance();
    $pdo = $database->getPDO();

    $tauxStmt = $pdo->prepare("SELECT T_MONTANT FROM taux_frais WHERE T_ID = 'Repas'");
    $tauxStmt->execute();
    $tauxRepas = $tauxStmt->fetchColumn();
    
    if (empty($montantTotal)) {
        $montantTotal = $nombreRepas * $tauxRepas;
    }

    $stmt = $pdo->prepare("INSERT INTO fiche_frais (TYPE, DATE, DESCRIPTION, MONTANT, user_id) 
                          VALUES (:type, :date, :description, :montant, :userId)");
                          
    $type = 'Repas';
    $stmt->bindParam(':type', $type, PDO::PARAM_STR);
    $stmt->bindParam(':date', $date, PDO::PARAM_STR);
    $stmt->bindParam(':description', $description, PDO::PARAM_STR);
    $stmt->bindParam(':montant', $montantTotal, PDO::PARAM_STR);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);

    $success = $stmt->execute();

    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Frais de repas ajoutés avec succès']);
    } else {
        $errorInfo = $stmt->errorInfo();
        throw new Exception('Erreur lors de l\'ajout des frais de repas: ' . $errorInfo[2]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>