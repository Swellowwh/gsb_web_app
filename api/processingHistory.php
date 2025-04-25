<?php
require_once './global/pdo.php';
require_once './global/jwt.php';
require_once './global/cors.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

// Récupération et vérification du token
$token = JwtUtils::getToken();
if (!$token) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit;
}

$user = JwtUtils::decodeToken($token);
if (!$user) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Token invalide']);
    exit;
}

$userId = $user['user_id'];
$role = $user['role'] ?? ''; // Récupérer le rôle de l'utilisateur

// Vérifier que l'utilisateur a le droit de valider/refuser des fiches
$allowedRoles = ['COMPTABLE', 'ADMINISTRATEUR'];
if (!in_array($role, $allowedRoles)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Vous n\'avez pas les droits nécessaires pour effectuer cette action']);
    exit;
}

// Récupération des données de la requête
$input = json_decode(file_get_contents('php://input'), true);
$id = intval($input['idHistory'] ?? 0);
$action = $input['processingHistory'] ?? '';

// Validation des données
if (!$id || !in_array($action, ['Accepted', 'Rejected', 'Trash'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Paramètres invalides']);
    exit;
}

try {
    $pdo = Database::getInstance()->getPDO();
    
    // On ne vérifie plus si la fiche appartient à l'utilisateur, on vérifie juste si elle existe
    $stmtCheck = $pdo->prepare("SELECT id FROM fiche_frais WHERE id = :id");
    $stmtCheck->execute([':id' => $id]);
    $fiche = $stmtCheck->fetch();

    if (!$fiche) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Fiche introuvable']);
        exit;
    }

    // Journal pour le débogage
    error_log("Traitement de la fiche ID: $id avec l'action: $action par l'utilisateur ID: $userId (rôle: $role)");

    if ($action === 'Trash') {
        // Supprimer la fiche et ses détails
        $pdo->beginTransaction();
        
        // Supprimer les détails puis la fiche principale
        $detailStmt = $pdo->prepare("DELETE FROM fiche_frais_detail WHERE fiche_id = :id");
        $detailStmt->execute([':id' => $id]);
        
        $mainStmt = $pdo->prepare("DELETE FROM fiche_frais WHERE id = :id");
        $mainStmt->execute([':id' => $id]);
        $rowsAffected = $mainStmt->rowCount();
        
        if ($rowsAffected === 0) {
            $pdo->rollBack();
            throw new Exception('Impossible de supprimer la fiche');
        }
        
        $pdo->commit();
    } else {
        // Met à jour le statut
        $stmt = $pdo->prepare("UPDATE fiche_frais SET statut = :statut WHERE id = :id");
        $stmt->execute([':statut' => $action, ':id' => $id]);
        $rowsAffected = $stmt->rowCount();
        
        if ($rowsAffected === 0) {
            throw new Exception('La mise à jour du statut a échoué');
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Action effectuée avec succès'
    ]);
    
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Erreur PDO: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données: ' . $e->getMessage()]);
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Exception: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}