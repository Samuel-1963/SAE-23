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
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="60">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EnergyWatch - Résultats</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="icon" href="../images/icon.ico" type="image/x-icon">
</head>
<body>
    <header>
        <a href="../index.html" class="titre-accueil">
            <h1>EnergyWatch</h1>
        </a>
        <button id="menu-toggle" aria-label="Ouvrir le menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <nav id="main-nav">
            <ul>
                <li><a href="administration.php">Administration</a></li>
                <li><a href="gestion.php">Gestion</a></li>
                <li><a href="consultation.php">Consultation</a></li>
                <li><a href="#">Gestion de Projet</a>
                    <ul class="sous-menu">
                        <li><a href="gantt.html">GANTT</a></li>
                        <li><a href="syntheses.html">Synthèses personnelles</a></li>
                        <li><a href="problemes.html">Problèmes / Solutions</a></li>
                        <li><a href="conclusion.html">Conclusion</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
    </header>
<main>
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
</main>

    <footer>
        <p>&copy; 2025 EnergyWatch - Tous droits réservés | <a href="mentions-legales.html">Mentions légales</a></p>
    </footer>

    <script>
        document.getElementById('menu-toggle').addEventListener('click', function () {
            const nav = document.getElementById('main-nav');
            nav.classList.toggle('active');
            this.querySelectorAll('span').forEach(span =>
                span.classList.toggle('active'));
        });
    </script>
</body>
</html>