<?php
require_once 'db.php'; // Fichier de connexion PDO

if (isset($_POST['nom_batiment']) && !empty($_POST['nom_batiment'])) {
    $nom = htmlspecialchars($_POST['nom_batiment']);

    $stmt = $pdo->prepare("INSERT INTO batiment (nom) VALUES (:nom)");
    $stmt->execute(['nom' => $nom]);

    header('Location: ../administration.php');
    exit();
} else {
    echo "Erreur : nom du bâtiment manquant.";
}
?>
