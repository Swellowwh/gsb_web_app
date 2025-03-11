<?php
require_once './global/detect_error.php';
require_once './global/cors.php';
require_once './global/pdo.php';
require_once './global/jwt.php';
require_once 'vendor/autoload.php';

use \Firebase\JWT\JWT;
use Dotenv\Dotenv;

try {
    // Chargement des variables d'environnement
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée. Utilisez POST.']);
        exit;
    }

    // Récupération des données frontend
    $input = json_decode(file_get_contents('php://input'), true);
    $username = trim($input['username'] ?? '');
    $password = $input['password'] ?? '';

    // Vérification des entrées
    if (empty($username) || empty($password)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Nom d\'utilisateur et mot de passe requis']);
        exit;
    }

    // Connexion à la base de données
    $database = Database::getInstance();
    $pdo = $database->getPDO();

    // Vérifier si l'utilisateur existe
    $stmt = $pdo->prepare("SELECT id, username, password, role FROM user WHERE username = :username");
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Nom d\'utilisateur ou mot de passe incorrect']);
        exit;
    }

    // Création du token JWT
    $secret_key = $_ENV['JWT_CONTROL'];
    
    // Vérification que la clé JWT est bien chargée
    if (!$secret_key) {
        throw new Exception('La clé JWT_CONTROL n\'est pas définie dans les variables d\'environnement');
    }
    
    $expiration = time() + 86400; // 24 heures
    
    $payload = [
        "iat" => time(),
        "exp" => $expiration,
        "user_id" => $user['id'],
        "username" => $user['username'],
        "role" => $user['role']
    ];
    
    $jwt = JWT::encode($payload, $secret_key, 'HS256');
    
    // Création du cookie gsb_session
    setcookie('gsb_session', $jwt, [
        'expires' => $expiration,
        'path' => '/',
        'secure' => false,     // Pour HTTP (mettre true pour HTTPS)
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    // Réponse JSON
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Connexion réussie',
        'token' => $jwt,
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role']
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
?>