<?php
require_once './global/detect_error.php';
require_once './global/cors.php';
require_once './global/pdo.php';
require_once './global/jwt.php';
require_once 'vendor/autoload.php';

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

$database = Database::getInstance();
$pdo = $database->getPDO();

// Vérifier si l'utilisateur est un administrateur
$stmt = $pdo->prepare("SELECT role FROM user WHERE id = :userId");
$stmt->bindParam(':userId', $userId, PDO::PARAM_INT); // Signifie que la valeur vérifier est un entier
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC); // Retourne les données sous forme de tableau

if (!$user || $user['role'] !== 'ADMINISTRATEUR') {
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé. Rôle administrateur requis.']);
    exit;
}

// Si l'utilisateur est administrateur, récupérer la liste des visiteurs
$stmt = $pdo->prepare("SELECT EMP_ID, EMP_NOM, EMP_PRENOM, EMP_ADRESSE, EMP_CP, EMP_VILLE, EMP_ROLE, EMP_DATE_EMBAUCHE FROM employe");
$stmt->execute();
$visiteurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'visiteurs' => $visiteurs
]);