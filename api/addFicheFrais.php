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

// Récupération du token JWT
$token = JwtUtils::getToken();
if (!$token) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Aucun token trouvé']);
    exit;
}

try {
    $userData = JwtUtils::decodeToken($token);
    if (!$userData) {
        throw new Exception('Token invalide ou expiré lors de la vérification');
    }

    $userId = $userData['user_id'];

    // Récupération et validation des données entrantes
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        throw new Exception('Données JSON invalides');
    }

    $date = isset($input['date']) ? trim($input['date']) : '';
    $distance = isset($input['distance']) ? floatval($input['distance']) : 0;
    $nbRepas = isset($input['nbRepas']) ? intval($input['nbRepas']) : 0;
    $nbHebergements = isset($input['nbHebergements']) ? intval($input['nbHebergements']) : 0;
    $description = isset($input['description']) ? trim($input['description']) : '';
    $avance_id = isset($input['avance_id']) && !empty($input['avance_id']) ? intval($input['avance_id']) : null;

    if (empty($date)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Le champ date est obligatoire']);
        exit;
    }

    if ($date < date('Y-m-d')) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'La date ne peut pas être antérieure à aujourd\'hui']);
        exit;
    }

    if ($distance <= 0 && $nbRepas <= 0 && $nbHebergements <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Au moins un type de frais (kilométrage, repas ou hébergement) doit être renseigné']);
        exit;
    }

    if (empty($description)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Le champ description est obligatoire']);
        exit;
    }

    // Connexion à la base de données
    $pdo = Database::getInstance()->getPDO();

    $mois = date('Y-m', strtotime($date));

    $checkStmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM fiche_frais 
        WHERE user_id = :userId 
          AND DATE_FORMAT(date, '%Y-%m') = :mois
    ");
    $checkStmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $checkStmt->bindParam(':mois', $mois, PDO::PARAM_STR);
    $checkStmt->execute();

    if ($checkStmt->fetchColumn() > 0) {
        http_response_code(400); // Code 400 Bad Request
        echo json_encode([
            'success' => false,
            'message' => "Vous ne pouvez créer qu'une fiche de frais par mois. Une fiche existe déjà pour le mois $mois"
        ]);
        exit;
    }

    // Vérification de l'avance si fournie
    if ($avance_id !== null) {
        $avanceStmt = $pdo->prepare("
            SELECT id_avance, montant, status 
            FROM gsb_avance 
            WHERE id_avance = :avance_id AND user_id = :userId AND status = 'accepted'
        ");
        $avanceStmt->bindParam(':avance_id', $avance_id, PDO::PARAM_INT);
        $avanceStmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $avanceStmt->execute();
        
        $avance = $avanceStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$avance) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'L\'avance sélectionnée n\'existe pas ou n\'est pas acceptée']);
            exit;
        }
        
        // Vérifier si l'avance est déjà utilisée dans une autre fiche de frais
        $avanceUsedStmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM fiche_frais 
            WHERE avance_id = :avance_id
        ");
        $avanceUsedStmt->bindParam(':avance_id', $avance_id, PDO::PARAM_INT);
        $avanceUsedStmt->execute();
        
        if ($avanceUsedStmt->fetchColumn() > 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Cette avance est déjà utilisée dans une autre fiche de frais']);
            exit;
        }
    }

    // Récupération des taux
    $tauxStmt = $pdo->prepare("SELECT T_ID, T_MONTANT FROM taux_frais WHERE T_ID IN ('KM', 'REP', 'NUI')");
    $tauxStmt->execute();
    $tauxResults = $tauxStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $taux = [];
    foreach ($tauxResults as $row) {
        $taux[$row['T_ID']] = floatval($row['T_MONTANT']);
    }

    // Calcul des montants
    $montantKm = $distance > 0 ? $distance * $taux['KM'] : 0;
    $montantRepas = $nbRepas > 0 ? $nbRepas * $taux['REP'] : 0;
    $montantHebergement = $nbHebergements > 0 ? $nbHebergements * $taux['NUI'] : 0;
    $montantTotal = $montantKm + $montantRepas + $montantHebergement;

    // Génération d'une référence unique
    $reference = 'FR-' . date('Ym') . '-' . sprintf('%04d', mt_rand(1, 9999));

    $pdo->beginTransaction();

    try {
        $stmtFiche = $pdo->prepare("
            INSERT INTO fiche_frais (reference, date, description, montant_total, user_id, avance_id) 
            VALUES (:reference, :date, :description, :montant_total, :userId, :avance_id)");

        $stmtFiche->bindParam(':reference', $reference, PDO::PARAM_STR);
        $stmtFiche->bindParam(':date', $date, PDO::PARAM_STR);
        $stmtFiche->bindParam(':description', $description, PDO::PARAM_STR);
        $stmtFiche->bindParam(':montant_total', $montantTotal, PDO::PARAM_STR);
        $stmtFiche->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmtFiche->bindParam(':avance_id', $avance_id, PDO::PARAM_INT);

        if (!$stmtFiche->execute()) {
            throw new Exception("Erreur lors de la création de la fiche de frais");
        }

        $ficheId = $pdo->lastInsertId();

        // Préparation de la requête d'insertion des détails
        $stmtDetail = $pdo->prepare("
            INSERT INTO fiche_frais_detail (fiche_id, type, quantite, montant) 
            VALUES (:fiche_id, :type, :quantite, :montant)
        ");

        // Insertion des frais kilométriques si présents
        if ($distance > 0) {
            $stmtDetail->bindParam(':fiche_id', $ficheId, PDO::PARAM_INT);
            $stmtDetail->bindValue(':type', 'KM', PDO::PARAM_STR);
            $stmtDetail->bindParam(':quantite', $distance, PDO::PARAM_STR);
            $stmtDetail->bindParam(':montant', $montantKm, PDO::PARAM_STR);
            
            if (!$stmtDetail->execute()) {
                throw new Exception("Erreur lors de l'ajout des frais kilométriques");
            }
        }

        // Insertion des frais de repas si présents
        if ($nbRepas > 0) {
            $stmtDetail->bindParam(':fiche_id', $ficheId, PDO::PARAM_INT);
            $stmtDetail->bindValue(':type', 'REP', PDO::PARAM_STR);
            $stmtDetail->bindParam(':quantite', $nbRepas, PDO::PARAM_INT);
            $stmtDetail->bindParam(':montant', $montantRepas, PDO::PARAM_STR);
            
            if (!$stmtDetail->execute()) {
                throw new Exception("Erreur lors de l'ajout des frais de repas");
            }
        }

        // Insertion des frais d'hébergement si présents
        if ($nbHebergements > 0) {
            $stmtDetail->bindParam(':fiche_id', $ficheId, PDO::PARAM_INT);
            $stmtDetail->bindValue(':type', 'NUI', PDO::PARAM_STR);
            $stmtDetail->bindParam(':quantite', $nbHebergements, PDO::PARAM_INT);
            $stmtDetail->bindParam(':montant', $montantHebergement, PDO::PARAM_STR);
            
            if (!$stmtDetail->execute()) {
                throw new Exception("Erreur lors de l'ajout des frais d'hébergement");
            }
        }

        // Mettre à jour le statut de l'avance à "used" si une avance est fournie
        if ($avance_id !== null) {
            // Mettre à jour l'avance en utilisant le nom correct de la colonne
            $updateAvanceStmt = $pdo->prepare("
                UPDATE gsb_avance 
                SET status = 'used' 
                WHERE id_avance = :avance_id AND user_id = :userId
            ");
            $updateAvanceStmt->bindParam(':avance_id', $avance_id, PDO::PARAM_INT);
            $updateAvanceStmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            
            if (!$updateAvanceStmt->execute()) {
                throw new Exception("Erreur lors de la mise à jour du statut de l'avance");
            }
        }

        $pdo->commit();
        
        // Préparer les détails de la réponse
        $responseDetails = [
            'id' => $ficheId,
            'reference' => $reference,
            'total' => $montantTotal,
            'types' => [
                'km' => $montantKm,
                'repas' => $montantRepas,
                'hebergement' => $montantHebergement
            ]
        ];
        
        // Ajouter les informations sur l'avance si utilisée
        if ($avance_id !== null) {
            $responseDetails['avance'] = [
                'id' => $avance_id,
                'montant' => isset($avance['montant']) ? floatval($avance['montant']) : 0
            ];
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Fiche de frais ajoutée avec succès !',
            'details' => $responseDetails
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }

} catch (PDOException $e) {
    // En cas d'erreur PDO, on rollback toute transaction en cours
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données : ' . $e->getMessage()]);
} catch (Exception $e) {
    // En cas d'erreur générale, on rollback toute transaction en cours
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>