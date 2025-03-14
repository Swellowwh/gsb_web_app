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

    $dateArrivee = isset($input['dateArrivee']) ? trim($input['dateArrivee']) : '';
    $description = isset($input['description']) ? trim($input['description']) : '';
    $montantTotal = isset($input['montantTotal']) ? floatval($input['montantTotal']) : 0;

    if (empty($dateArrivee)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Le champ date d\'arrivée est obligatoire']);
        exit;
    }
    
    if (empty($description)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Le champ description est obligatoire']);
        exit;
    }
    
    if (empty($montantTotal) || $montantTotal <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Le montant total doit être supérieur à 0']);
        exit;
    }

    $database = Database::getInstance();
    $pdo = $database->getPDO();

    $stmt = $pdo->prepare("INSERT INTO fiche_frais (TYPE, DATE, DESCRIPTION, MONTANT, user_id) 
                          VALUES (:type, :dateArrivee, :description, :montant, :userId)");
                          
    $type = 'Hébergement';
    $stmt->bindParam(':type', $type, PDO::PARAM_STR);
    $stmt->bindParam(':dateArrivee', $dateArrivee, PDO::PARAM_STR);
    $stmt->bindParam(':description', $description, PDO::PARAM_STR);
    $stmt->bindParam(':montant', $montantTotal, PDO::PARAM_STR);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);

    $success = $stmt->execute();

    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Frais d\'hébergement ajoutés avec succès']);
    } else {
        $errorInfo = $stmt->errorInfo();
        throw new Exception('Erreur lors de l\'ajout des frais d\'hébergement: ' . $errorInfo[2]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>