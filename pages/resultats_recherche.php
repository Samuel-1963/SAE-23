<?php
session_start();
if (!isset($_SESSION['gest_connecte'])) header("Location: gestion.php");

$conn = new mysqli('localhost', 'guerin', 'passroot', 'sae23');
$filtres = $_SESSION['filtres'] ?? [];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Résultats</title>
    <style>/* Reprendre le même style que gestion.php */</style>
</head>
<body>

    <h1>Résultats de recherche <a href="gestion.php">Retour</a></h1>
    
    <?php
    $sql = "SELECT * FROM Mesure WHERE nom_cap LIKE 'E%'";
    
    if (!empty($filtres['salle'])) $sql .= " AND nom_cap LIKE '{$filtres['salle']}%'";
    if (!empty($filtres['type'])) $sql .= " AND nom_cap LIKE '%{$filtres['type']}'";
    if (!empty($filtres['date_debut'])) $sql .= " AND date_mesure >= '{$filtres['date_debut']}'";
    if (!empty($filtres['date_fin'])) $sql .= " AND date_mesure <= '{$filtres['date_fin']}'";
    
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        echo "<table><tr><th>Date</th><th>Capteur</th><th>Valeur</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr><td>{$row['date_mesure']}</td><td>{$row['nom_cap']}</td><td>{$row['valeur_mesure']}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Aucun résultat trouvé</p>";
    }
    ?>

</body>
</html>