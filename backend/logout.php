<?php
require_once './global/cors.php';

$success = false;

if (isset($_COOKIE['gsb_session'])) {
    // Définir la date d'expiration dans le passé pour supprimer le cookie
    setcookie('gsb_session', '', time() - 3600, '/');
    unset($_COOKIE['gsb_session']);
    $success = true;
} else {
    $success = true;
}

echo json_encode([
    'success' => $success,
]);

exit;
?>