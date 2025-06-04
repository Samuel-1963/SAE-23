<?php
session_start();

// Authentification simple
if (!isset($_SESSION['gest_connecte'])) {
    if ($_POST['login'] ?? '' === 'Gerant' && $_POST['password'] ?? '' === 'hgvcJB564F*') {
        $_SESSION['gest_connecte'] = true;
    } else {
        header('Location: login_gestion.php');
        exit();
    }
}

// Déconnexion
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: gestion.php");
    exit();
}

// Connexion BDD
$conn = new mysqli('localhost', 'guerin', 'passroot', 'sae23');
if ($conn->connect_error) die("Connexion échouée : " . $conn->connect_error);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion Bâtiment E</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f2f2f2; }
        .sensor-temp { color: #e74c3c; }
        .sensor-humidity { color: #3498db; }
        .sensor-light { color: #f39c12; }
        .sensor-co2 { color: #2ecc71; }
        .logout { float: right; }
    </style>
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
    <h1>Gestion des capteurs <a href="?logout=1" class="logout">Déconnexion</a></h1>

    <!-- Filtres rapides -->
    <form method="get">
        <label>Date: <input type="date" name="date" value="<?= $_GET['date'] ?? date('Y-m-d') ?>"></label>
        <label>Salle: 
            <select name="salle">
                <option value="">Toutes</option>
                <?php
                $salles = $conn->query("SELECT DISTINCT SUBSTRING(nom_cap, 1, 4) as salle FROM Capteur ORDER BY salle");
                while ($s = $salles->fetch_assoc()) {
                    $sel = ($_GET['salle'] ?? '') === $s['salle'] ? 'selected' : '';
                    echo "<option value='{$s['salle']}' $sel>{$s['salle']}</option>";
                }
                ?>
            </select>
        </label>
        <button type="submit">Filtrer</button>
    </form>

    <!-- Affichage brut des données -->
    <table>
        <tr>
            <th>Date/Heure</th>
            <th>Capteur</th>
            <th>Valeur</th>
        </tr>
        <?php
        $sql = "SELECT date_mesure, horaire_mesure, nom_cap, valeur_mesure FROM Mesure WHERE 1";
        
        if (!empty($_GET['date'])) $sql .= " AND date_mesure = '{$_GET['date']}'";
        if (!empty($_GET['salle'])) $sql .= " AND nom_cap LIKE '{$_GET['salle']}%'";
        
        $sql .= " ORDER BY date_mesure DESC, horaire_mesure DESC LIMIT 100";
        
        $result = $conn->query($sql);
        
        while ($row = $result->fetch_assoc()) {
            $type = explode('_', $row['nom_cap'])[1];
            $class = 'sensor-' . str_replace(['temperature','humidite','luminosite'], ['temp','humidity','light'], $type);
            $unit = match($type) {
                'temperature' => '°C',
                'humidite' => '%',
                'luminosite' => 'lux',
                'co2' => 'ppm',
                default => ''
            };
            
            echo "<tr>
                    <td>{$row['date_mesure']} {$row['horaire_mesure']}</td>
                    <td>{$row['nom_cap']}</td>
                    <td class='$class'>{$row['valeur_mesure']} $unit</td>
                  </tr>";
        }
        ?>
    </table>

    <footer>
        <p>&copy; 2025 EnergyWatch - Tous droits réservés | <a href="mentions-legales.html">Mentions légales</a></p>
    </footer>

    <script>
        document.getElementById('menu-toggle').addEventListener('click', function () {
            const nav = document.getElementById('main-nav');
            nav.classList.toggle('active');
            this.querySelectorAll('span').forEach(span => span.classList.toggle('active'));
        });
    </script>
</body>
</html>