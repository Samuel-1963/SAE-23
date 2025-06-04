<?php
session_start();

// Authentification directe
if (!isset($_SESSION['gest_connecte'])) {
    if ($_POST['login'] ?? '' === 'Gerant' && $_POST['password'] ?? '' === 'hgvcJB564F*') {
        $_SESSION['gest_connecte'] = true;
    } else {
        // Formulaire de connexion intégré
        die('
        <h2>Connexion Requise</h2>
        <form method="post">
            <input type="text" name="login" placeholder="Login" required><br>
            <input type="password" name="password" placeholder="Mot de passe" required><br>
            <button type="submit">Se connecter</button>
        </form>
        ');
    }
}

// Connexion BDD
$conn = new mysqli('localhost', 'guerin', 'passroot', 'sae23');
if ($conn->connect_error) die("Connexion échouée : " . $conn->connect_error);

// Traitement du formulaire de recherche
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recherche'])) {
    $filtres = [
        'salle' => $conn->real_escape_string($_POST['salle'] ?? ''),
        'type' => $conn->real_escape_string($_POST['type'] ?? ''),
        'date_debut' => $conn->real_escape_string($_POST['date_debut'] ?? ''),
        'date_fin' => $conn->real_escape_string($_POST['date_fin'] ?? '')
    ];
    
    // Stockage pour réutilisation
    $_SESSION['filtres'] = $filtres;
    header("Location: resultats_recherche.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion Bâtiment E</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .card { background: #f9f9f9; padding: 15px; margin: 10px 0; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 10px; border: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        .sensor-temp { color: #e74c3c; }
        .sensor-hum { color: #3498db; }
        .sensor-lum { color: #f39c12; }
        .sensor-co2 { color: #2ecc71; }
        .logout { float: right; }
        .form-group { margin: 10px 0; }
    </style>
</head>
<body>

    <h1>Gestion Bâtiment E <a href="?logout=1" class="logout">Déconnexion</a></h1>

    <!-- Statistiques par salle -->
    <div class="card">
        <h2>📊 Statistiques Globales</h2>
        <?php
        $stats = $conn->query("
            SELECT 
                SUBSTRING(nom_cap, 1, 4) as salle,
                AVG(CASE WHEN nom_cap LIKE '%temperature%' THEN valeur_mesure END) as avg_temp,
                MIN(CASE WHEN nom_cap LIKE '%temperature%' THEN valeur_mesure END) as min_temp,
                MAX(CASE WHEN nom_cap LIKE '%temperature%' THEN valeur_mesure END) as max_temp,
                AVG(CASE WHEN nom_cap LIKE '%humidite%' THEN valeur_mesure END) as avg_hum,
                AVG(CASE WHEN nom_cap LIKE '%luminosite%' THEN valeur_mesure END) as avg_lum,
                AVG(CASE WHEN nom_cap LIKE '%co2%' THEN valeur_mesure END) as avg_co2
            FROM Mesure
            WHERE nom_cap LIKE 'E%'
            GROUP BY salle
        ");
        
        while ($salle = $stats->fetch_assoc()) {
            echo "<h3>Salle {$salle['salle']}</h3>";
            echo "<p>🌡️ Température: Moy={$salle['avg_temp']}°C | Min={$salle['min_temp']}°C | Max={$salle['max_temp']}°C</p>";
            echo "<p>💧 Humidité: Moy={$salle['avg_hum']}%</p>";
            echo "<p>💡 Lumière: Moy={$salle['avg_lum']}lux</p>";
            echo "<p>☁️ CO2: Moy={$salle['avg_co2']}ppm</p>";
        }
        ?>
    </div>

    <!-- Formulaire de recherche -->
    <div class="card">
        <h2>🔍 Recherche Avancée</h2>
        <form method="post">
            <input type="hidden" name="recherche" value="1">
            
            <div class="form-group">
                <label>Salle:
                    <select name="salle">
                        <option value="">Toutes</option>
                        <?php
                        $salles = $conn->query("SELECT DISTINCT SUBSTRING(nom_cap, 1, 4) as salle FROM Capteur WHERE nom_cap LIKE 'E%'");
                        while ($s = $salles->fetch_assoc()) {
                            echo "<option value='{$s['salle']}'>{$s['salle']}</option>";
                        }
                        ?>
                    </select>
                </label>
            </div>
            
            <div class="form-group">
                <label>Type:
                    <select name="type">
                        <option value="">Tous</option>
                        <option value="temperature">Température</option>
                        <option value="humidite">Humidité</option>
                        <option value="luminosite">Luminosité</option>
                        <option value="co2">CO2</option>
                    </select>
                </label>
            </div>
            
            <div class="form-group">
                <label>Du: <input type="date" name="date_debut" required></label>
                <label>Au: <input type="date" name="date_fin" required></label>
            </div>
            
            <button type="submit">Rechercher</button>
        </form>
    </div>

    <!-- Dernières mesures -->
    <h2>📝 Dernières Mesures</h2>
    <table>
        <tr>
            <th>Date/Heure</th>
            <th>Salle</th>
            <th>Type</th>
            <th>Valeur</th>
        </tr>
        <?php
        $mesures = $conn->query("
            SELECT 
                date_mesure, 
                horaire_mesure, 
                nom_cap,
                valeur_mesure
            FROM Mesure
            WHERE nom_cap LIKE 'E%'
            ORDER BY date_mesure DESC, horaire_mesure DESC
            LIMIT 20
        ");
        
        while ($m = $mesures->fetch_assoc()) {
            $salle = substr($m['nom_cap'], 0, 4);
            $type = substr($m['nom_cap'], 5);
            $classe = 'sensor-' . substr($type, 0, 3);
            $unite = match($type) {
                'temperature' => '°C',
                'humidite' => '%',
                'luminosite' => 'lux',
                'co2' => 'ppm',
                default => ''
            };
            
            echo "<tr>
                    <td>{$m['date_mesure']} {$m['horaire_mesure']}</td>
                    <td>{$salle}</td>
                    <td>{$type}</td>
                    <td class='{$classe}'>{$m['valeur_mesure']}{$unite}</td>
                  </tr>";
        }
        ?>
    </table>

</body>
</html>