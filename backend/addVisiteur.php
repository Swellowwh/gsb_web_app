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

$nom = trim($input['nom'] ?? '');
$prenom = trim($input['prenom'] ?? '');
$userId = trim($input['userId'] ?? '');
$adresse = trim($input['adresse'] ?? '');
$codePostal = trim($input['codePostal'] ?? '');
$ville = trim($input['ville'] ?? '');
$role = trim($input['role'] ?? '');
$dateEmbauche = trim($input['dateEmbauche'] ?? '');
$username = trim($input['username'] ?? '');
$password = $input['password'] ?? '';

$database = Database::getInstance();
$pdo = $database->getPDO();

$stmt = $pdo->prepare("INSERT INTO employe (EMP_NOM, EMP_prenom, EMP_adresse, EMP_cp, EMP_ville, EMP_role, EMP_date_Embauche, user_id) 
                      VALUES (:nom, :prenom, :adresse, :cp, :ville, :role, :dateEmbauche, :userId)");
$stmt->bindParam(':nom', $nom, PDO::PARAM_STR);
$stmt->bindParam(':prenom', $prenom, PDO::PARAM_STR);
$stmt->bindParam(':adresse', $adresse, PDO::PARAM_STR);
$stmt->bindParam(':cp', $codePostal, PDO::PARAM_STR);
$stmt->bindParam(':ville', $ville, PDO::PARAM_STR);
$stmt->bindParam(':role', $role, PDO::PARAM_STR);
$stmt->bindParam(':dateEmbauche', $dateEmbauche, PDO::PARAM_STR);
$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
$success = $stmt->execute();

if ($success) {
    $lastInsertId = $pdo->lastInsertId();
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
