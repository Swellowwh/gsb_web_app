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

    $date = isset($input['date']) ? trim($input['date']) : '';
    $montant = isset($input['montant']) ? floatval($input['montant']) : 0;
    $motif = isset($input['motif']) ? trim($input['motif']) : '';

    if (empty($date)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Le champ date est obligatoire']);
        exit;
    }

    if ($montant <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Le montant doit être supérieur à 0']);
        exit;
    }

    if (empty($motif)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Le motif de la demande est obligatoire']);
        exit;
    }

    $pdo = Database::getInstance()->getPDO();

    $mois = date('Y-m', strtotime($date));

    $checkStmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM gsb_avance 
        WHERE user_id = :userId 
          AND DATE_FORMAT(date_creation, '%Y-%m') = :mois
    ");
    $checkStmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $checkStmt->bindParam(':mois', $mois, PDO::PARAM_STR);
    $checkStmt->execute();

    if ($checkStmt->fetchColumn() > 0) {
        http_response_code(400); // Code 400 Bad Request
        echo json_encode([
            'success' => false,
            'message' => "Vous ne pouvez créer qu'une demande d'avance par mois. Une demande existe déjà pour le mois $mois"
        ]);
        exit;
    }

    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare("
            INSERT INTO gsb_avance (montant, description, status, user_id, date_creation) 
            VALUES (:montant, :description, 'pending', :userId, :date)
        ");

        $stmt->bindParam(':montant', $montant, PDO::PARAM_STR);
        $stmt->bindParam(':description', $motif, PDO::PARAM_STR);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':date', $date, PDO::PARAM_STR);

        if (!$stmt->execute()) {
            throw new Exception("Erreur lors de la création de la demande d'avance");
        }

        $avanceId = $pdo->lastInsertId();

        $pdo->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Demande d\'avance enregistrée avec succès !',
            'details' => [
                'id' => $avanceId,
                'montant' => $montant,
                'status' => 'pending'
            ]
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données : ' . $e->getMessage()]);
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>