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
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Aucun token trouvé']);
    exit;
}

try {
    $userData = JwtUtils::decodeToken($token);
    if (!$userData) {
        throw new Exception('Token invalide ou expiré');
    }

    $userId = $userData['user_id'];
    $input = json_decode(file_get_contents('php://input'), true);

    if (!is_array($input)) {
        throw new Exception('Payload JSON invalide');
    }

    $pdo = Database::getInstance()->getPDO();

    $pdo->beginTransaction();

    foreach ($input as $entry) {
        $detailId = intval($entry['detailId'] ?? 0);
        $ficheId = intval($entry['ficheId'] ?? 0);
        $quantite = floatval($entry['quantite'] ?? 0);

        if ($detailId <= 0 || $ficheId <= 0 || $quantite < 0) {
            throw new Exception('Entrée invalide: ID ou quantité manquante');
        }

        // Vérification que la fiche appartient bien au user et qu'elle est modifiable
        $checkFiche = $pdo->prepare("SELECT statut FROM fiche_frais WHERE id = :id AND user_id = :userId");
        $checkFiche->execute([':id' => $ficheId, ':userId' => $userId]);
        $fiche = $checkFiche->fetch();

        if (!$fiche || $fiche['statut'] !== 'Pending') {
            throw new Exception('Fiche inaccessible ou non modifiable');
        }

        // Récupération du type du détail
        $detail = $pdo->prepare("SELECT type FROM fiche_frais_detail WHERE id = :id AND fiche_id = :ficheId");
        $detail->execute([':id' => $detailId, ':ficheId' => $ficheId]);
        $typeRow = $detail->fetch();

        if (!$typeRow) {
            throw new Exception("Détail introuvable pour la fiche donnée");
        }

        $type = $typeRow['type'];

        // Récupération du taux
        $rateStmt = $pdo->prepare("SELECT T_MONTANT FROM taux_frais WHERE T_ID = :type");
        $rateStmt->execute([':type' => $type]);
        $taux = $rateStmt->fetchColumn();

        if (!$taux) {
            throw new Exception("Taux introuvable pour le type $type");
        }

        $montant = $quantite * floatval($taux);

        // Mise à jour du détail
        $updateDetail = $pdo->prepare("UPDATE fiche_frais_detail SET quantite = :quantite, montant = :montant WHERE id = :id");
        $updateDetail->execute([
            ':quantite' => $quantite,
            ':montant' => $montant,
            ':id' => $detailId
        ]);
    }

    // Mise à jour du total de la fiche
    $updateTotal = $pdo->prepare("UPDATE fiche_frais SET montant_total = (
        SELECT SUM(montant) FROM fiche_frais_detail WHERE fiche_id = :ficheId
    ) WHERE id = :ficheId");
    $updateTotal->execute([':ficheId' => $ficheId]);

    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Modifications enregistrées avec succès']);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
