<?php

// En-têtes CORS pour autoriser les requêtes entre domaines différents
header("Access-Control-Allow-Origin: http://51.83.76.210:5173"); // ⚠️ Mets ton vrai domaine frontend
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Si la requête est une OPTIONS (Preflight request), on répond avec un 200 sans traitement
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}


?>