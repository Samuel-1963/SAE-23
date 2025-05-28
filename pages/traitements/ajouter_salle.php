<?php
require_once 'db.php';

if (
    isset($_POST['nom_salle'], $_POST['type_salle'], $_POST['capacite'], $_POST['id_batiment']) &&
    !empty($_POST['nom_salle']) && !empty($_POST['type_salle']) &&
    is_numeric($_POST['capacite']) && is_numeric($_POST['id_batiment'])
) {
    $nom = htmlspecialchars($_POST['nom_salle']);
    $type = htmlspecialchars($_POST['type_salle']);
    $capacite = (int)$_POST['capacite'];
    $id_bat = (int)$_POST['id_batiment'];

    $stmt = $pdo->prepare("INSERT INTO salle (nom, type, capacite, id_batiment) VALUES (:nom, :type, :capacite, :id_bat)");
    $stmt->execute([
        'nom' => $nom,
        'type' => $type,
        'capacite' => $capacite,
        'id_bat' => $id_bat
    ]);

    header('Location: ../administration.php');
    exit();
} else {
    echo "Erreur : veuillez remplir tous les champs correctement.";
}
?>
