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

    $date = trim($input['date'] ?? '');
    $distance = floatval($input['distance'] ?? 0);
    $description = trim($input['description'] ?? '');

    if (empty($date) || empty($distance) || empty($description)) {
        throw new Exception('Tous les champs sont obligatoires');
    }

    $database = Database::getInstance();
    $pdo = $database->getPDO();

    $tauxStmt = $pdo->prepare("SELECT T_MONTANT FROM taux_frais WHERE T_ID = 'Km'");
    $tauxStmt->execute();
    $tauxKm = $tauxStmt->fetchColumn();
    
    $montant = $distance * $tauxKm;

    $stmt = $pdo->prepare("INSERT INTO fiche_frais (TYPE, DATE, DESCRIPTION, MONTANT, user_id) 
                          VALUES (:type, :date, :description, :montant, :userId)");
                          
    $type = 'Km';
    $stmt->bindParam(':type', $type, PDO::PARAM_STR);
    $stmt->bindParam(':date', $date, PDO::PARAM_STR);
    $stmt->bindParam(':description', $description, PDO::PARAM_STR);
    $stmt->bindParam(':montant', $montant, PDO::PARAM_STR);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);

    $success = $stmt->execute();

    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Frais kilométriques ajoutés avec succès']);
    } else {
        $errorInfo = $stmt->errorInfo();
        throw new Exception('Erreur lors de l\'ajout des frais kilométriques: ' . $errorInfo[2]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>