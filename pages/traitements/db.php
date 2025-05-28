<?php
$host = '192.168.108.140';        // Adresse du serveur MySQL
$dbname = 'energywatch';    // Nom de ta base de données
$user = 'jguerin';             // Nom d'utilisateur MySQL
$pass = 'passroot';                 // Mot de passe MySQL (souvent vide en local)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    // Activer les erreurs PDO pour le débogage
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>
