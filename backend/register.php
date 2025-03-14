<?php
require_once './global/detect_error.php';
require_once './global/cors.php';
require_once './global/pdo.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée. Utilisez POST.']);
        exit;
    }

    // Récupération des données JSON envoyées
    $input = json_decode(file_get_contents('php://input'), true);
    $email = isset($input['email']) ? trim($input['email']) : '';
    $password = isset($input['password']) ? $input['password'] : '';

    // Vérification des entrées
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Veuillez saisir une adresse email valide et un mot de passe d\'au moins 6 caractères.']);
        exit;
    }

    $database = Database::getInstance();
    $pdo = $database->getPDO();

    // Vérifier si l'utilisateur existe déjà
    $stmt = $pdo->prepare("SELECT id FROM user WHERE email = :email");
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Nom d\'utilisateur déjà pris']);
        exit;
    }

    // Hasher le mot de passe avant insertion
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insérer le nouvel utilisateur
    $stmt = $pdo->prepare("INSERT INTO user (email, password) VALUES (:email, :password)");
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);

    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(['success' => true, 'message' => 'Utilisateur créé avec succès']);
    } else {
        throw new Exception("Erreur lors de l'insertion de l'utilisateur");
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur base de données: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
