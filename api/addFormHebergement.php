<?php
require_once './global/detect_error.php';
require_once './global/cors.php';
require_once './global/pdo.php';
require_once './global/jwt.php';

// Vérification de la méthode HTTP
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

    // Récupération et validation des données entrantes
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

    if ($dateArrivee < date('Y-m-d')) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'La date ne peut pas être antérieure à aujourd’hui']);
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

    // Connexion à la base de données
    $pdo = Database::getInstance()->getPDO();

    // Vérifier si une fiche d’hébergement existe déjà pour le même mois
    $mois = date('Y-m', strtotime($dateArrivee));
    $checkStmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM fiche_frais 
        WHERE user_id = :userId 
          AND TYPE = 'Hébergement' 
          AND DATE_FORMAT(DATE, '%Y-%m') = :mois
    ");
    $checkStmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $checkStmt->bindParam(':mois', $mois, PDO::PARAM_STR);
    $checkStmt->execute();

    if ($checkStmt->fetchColumn() > 0) {
        http_response_code(409); // Conflit
        echo json_encode([
            'success' => false,
            'message' => "Une fiche d'hébergement existe déjà pour le mois $mois"
        ]);
        exit;
    }

    // Insertion dans la base de données
    $stmt = $pdo->prepare("
        INSERT INTO fiche_frais (TYPE, DATE, DESCRIPTION, MONTANT, user_id) 
        VALUES (:type, :dateArrivee, :description, :montant, :userId)
    ");

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
        throw new Exception("Erreur lors de l'ajout des frais d'hébergement : " . $errorInfo[2]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données : ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
