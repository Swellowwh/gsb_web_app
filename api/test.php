<?php
require_once 'global/pdo.php';

try {
    // Obtenir l'instance de la base de données
    $db = Database::getInstance();
    $pdo = $db->getPDO();
    
    // Vérifier la connexion en exécutant une requête simple
    $stmt = $pdo->query("SELECT 'Connexion réussie!' AS message");
    $result = $stmt->fetch();
    
    // Afficher le message de succès
    echo "<div style='color: green; font-weight: bold;'>";
    echo "✅ " . $result['message'] . "<br>";
    echo "Connecté à la base de données: " . $_ENV['DB_NAME'] . "<br>";
    echo "Utilisateur: " . $_ENV['DB_USER'] . "<br>";
    echo "Hôte: " . $_ENV['DB_HOST'];
    echo "</div>";
    
    // Afficher des informations supplémentaires sur la connexion
    echo "<hr>";
    echo "<h3>Informations sur le serveur:</h3>";
    echo "<pre>";
    echo "Version PHP: " . phpversion() . "<br>";
    echo "Version du serveur MySQL: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "<br>";
    echo "Version du client MySQL: " . $pdo->getAttribute(PDO::ATTR_CLIENT_VERSION) . "<br>";
    echo "Pilote PDO utilisé: " . $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) . "<br>";
    echo "</pre>";
    
} catch (Exception $e) {
    // Afficher un message d'erreur
    echo "<div style='color: red; font-weight: bold;'>";
    echo "❌ Erreur de connexion à la base de données<br>";
    echo $e->getMessage();
    echo "</div>";
    
    // Afficher des informations supplémentaires pour le débogage
    echo "<hr>";
    echo "<h3>Vérifiez les points suivants :</h3>";
    echo "<ul>";
    echo "<li>Le fichier .env existe-t-il à la racine du projet ?</li>";
    echo "<li>Les informations de connexion dans le fichier .env sont-elles correctes ?</li>";
    echo "<li>Le service MySQL/MariaDB est-il démarré ?</li>";
    echo "<li>L'utilisateur a-t-il les droits d'accès à la base de données ?</li>";
    echo "</ul>";
}
?>