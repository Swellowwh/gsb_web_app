<?php
require_once './global/detect_error.php';
require_once './global/cors.php';
require_once './global/pdo.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée. Utilisez POST.']);
    exit;
}

try {
    $database = Database::getInstance();
    $pdo = $database->getPDO();

    $stmt = $pdo->prepare("SELECT id, email FROM user");
    $stmt->execute();
    $emails = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'emails' => $emails]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Erreur lors de la récupération des emails',
    ]);
    exit;
}