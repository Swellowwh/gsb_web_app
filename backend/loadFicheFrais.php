<?php
require_once './global/detect_error.php';
require_once './global/cors.php';
require_once './global/pdo.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée. Utilisez POST.']);
    exit;
}

