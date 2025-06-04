<?php
session_start();
if (!isset($_SESSION['gest_connecte'])) {
    header("Location: gestion.php");
    exit();
}

$conn = new mysqli('localhost', 'guerin', 'passroot', 'sae23');
if ($conn->connect_error) die("Connexion échouée : " . $conn->connect_error);

$filtres = isset($_SESSION['filtres']) ? $_SESSION['filtres'] : array();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Résultats</title>
</head>
<body>

    <a href="gestion.php" class="back-btn">← Retour</a>
    <h1>Résultats de recherche</h1>
    
    <?php
    $sql = "SELECT * FROM Mesure WHERE nom_cap LIKE 'E%'";
    
    if (!empty($filtres['salle'])) {
        $sql .= " AND nom_cap LIKE '".$filtres['salle']."%'";
    }
    
    if (!empty($filtres['type'])) {
        $sql .= " AND nom_cap LIKE '%".$filtres['type']."'";
    }
    
    if (!empty($filtres['date_debut'])) {
        $sql .= " AND date_mesure >= '".$filtres['date_debut']."'";
    }
    
    if (!empty($filtres['date_fin'])) {
        $sql .= " AND date_mesure <= '".$filtres['date_fin']."'";
    }
    
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        echo "<table>
                <tr>
                    <th>Date</th>
                    <th>Heure</th>
                    <th>Capteur</th>
                    <th>Valeur</th>
                </tr>";
        
        while ($row = $result->fetch_assoc()) {
            $type = substr($row['nom_cap'], 5);
            $unite = '';
            
            if ($type === 'temperature') {
                $unite = '°C';
            } elseif ($type === 'humidite') {
                $unite = '%';
            } elseif ($type === 'luminosite') {
                $unite = 'lux';
            } elseif ($type === 'co2') {
                $unite = 'ppm';
            }
            
            echo "<tr>
                    <td>".htmlspecialchars($row['date_mesure'])."</td>
                    <td>".htmlspecialchars($row['horaire_mesure'])."</td>
                    <td>".htmlspecialchars($row['nom_cap'])."</td>
                    <td>".htmlspecialchars($row['valeur_mesure'])."$unite</td>
                  </tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>Aucun résultat trouvé avec ces filtres</p>";
    }
    ?>

</body>
</html>