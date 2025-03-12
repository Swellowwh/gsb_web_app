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
    throw new Exception('Aucun token trouvé');
}

// Décodage manuel du token pour voir son contenu
$tokenParts = explode('.', $token);
if (count($tokenParts) != 3) {
    throw new Exception('Format de token invalide');
}

$payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[1])), true);

$userData = JwtUtils::decodeToken($token);

if (!$userData) {
    throw new Exception('Token invalide ou expiré lors de la vérification');
}

$userId = $userData['user_id'];

$input = json_decode(file_get_contents('php://input'), true);

$date = trim($input['date'] ?? '');
$distance = trim($input['distance'] ?? '');
$description = trim($input['description'] ?? '');

$database = Database::getInstance();
$pdo = $database->getPDO();

$stmt = $pdo->prepare("INSERT INTO fiche_frais (TYPE, DATE, DESCRIPTION, MONTANT, EMP_ID, TAUX_ID) 
                      VALUES (:type, :date, :description, 
                      :nombre_km * (SELECT T_MONTANT FROM taux_frais WHERE T_ID = :taux_id), 
                      :emp_id, :taux_id)");
$stmt->bindParam(':type', 'Km', PDO::PARAM_STR);
$stmt->bindParam(':date', $date, PDO::PARAM_STR);
$stmt->bindParam(':description', $description, PDO::PARAM_STR);
$stmt->bindParam(':distance', $distance, PDO::PARAM_INT);
$stmt->bindParam(':taux_id', 'Km', PDO::PARAM_STR);
$stmt->bindParam(':emp_id', $userId, PDO::PARAM_INT);
$success = $stmt->execute();
