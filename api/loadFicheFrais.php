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
    echo json_encode(['success' => false, 'message' => 'Aucun token trouvé']);
    exit;
}

// Décodage du token
try {
    $userData = JwtUtils::decodeToken($token);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Token invalide']);
    exit;
}

$userId = $userData['user_id'];

try {
    $database = Database::getInstance();
    $pdo = $database->getPDO();

    // 🔄 Clôturer automatiquement les fiches de frais trop anciennes
    $updateStmt = $pdo->prepare("
        UPDATE fiche_frais
        SET statut = 'Clotured'
        WHERE statut = 'Pending'
          AND DATE < DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
    ");
    $updateStmt->execute();

    // 🔎 Récupération du rôle
    $stmt = $pdo->prepare("SELECT role FROM user WHERE id = :userId");
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // 🔍 Récupération des fiches selon le rôle
    if ($user && ($user['role'] === 'ADMINISTRATEUR' || $user['role'] === 'COMPTABLE')) {
        $stmt = $pdo->prepare("
            SELECT id, type, date, description, montant, statut 
            FROM fiche_frais 
            ORDER BY 
              CASE WHEN statut = 'Pending' THEN 0 ELSE 1 END, 
              date DESC
        ");
        $stmt->execute();
    } elseif ($user && $user['role'] === 'VISITEUR_MEDICAL') {
        $stmt = $pdo->prepare("
            SELECT id, type, date, description, montant, statut 
            FROM fiche_frais 
            WHERE user_id = :userId 
            ORDER BY 
              CASE WHEN statut = 'Pending' THEN 0 ELSE 1 END, 
              date DESC
        ");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
    } else {
        // Rôle non autorisé
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Accès non autorisé.']);
        exit;
    }

    $table = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'tableau' => $table]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération des fiches de frais',
        'error' => $e->getMessage()
    ]);
    exit;
}
