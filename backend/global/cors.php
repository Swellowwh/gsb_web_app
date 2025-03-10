<?php

$allowed_origins = [
    'http://51.83.76.210:5173',
    'http://51.83.76.210:8000'
];

// Récupérer l'origine de la requête
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

// Vérifier si l'origine est autorisée
if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    header("Access-Control-Allow-Origin: http://51.83.76.210:5173"); // Origine par défaut
}

header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Si la requête est une OPTIONS (Preflight request), on répond avec un 200 sans traitement
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Le reste de votre code PHP ici

?>