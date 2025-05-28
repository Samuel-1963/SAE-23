<?php
require_once 'db.php';

if (
    isset($_POST['nom_capteur'], $_POST['type_capteur'], $_POST['unite'], $_POST['id_salle']) &&
    !empty($_POST['nom_capteur']) && !empty($_POST['type_capteur']) &&
    !empty($_POST['unite']) && is_numeric($_POST['id_salle'])
) {
    $nom = htmlspecialchars($_POST['nom_capteur']);
    $type = htmlspecialchars($_POST['type_capteur']);
    $unite = htmlspecialchars($_POST['unite']);
    $id_salle = (int)$_POST['id_salle'];

    $stmt = $pdo->prepare("INSERT INTO capteur (nom, type, unite, id_salle) VALUES (:nom, :type, :unite, :id_salle)");
    $stmt->execute([
        'nom' => $nom,
        'type' => $type,
        'unite' => $unite,
        'id_salle' => $id_salle
    ]);

    header('Location: ../administration.php');
    exit();
} else {
    echo "Erreur : tous les champs doivent être remplis correctement.";
}
?>
