<?php
session_start();

// Authentification directe
if (!isset($_SESSION['gest_connecte'])) {
    $login = isset($_POST['login']) ? $_POST['login'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    if ($login === 'Gerant' && $password === 'hgvcJB564F*') {
        $_SESSION['gest_connecte'] = true;
    } else {
        // Formulaire de connexion intégré
        die('
        <!DOCTYPE html>
        <html>
        <head>
            <title>Connexion</title>
            <style>
                body { font-family: Arial, sans-serif; background: #f0f0f0; }
                .login-box { max-width: 300px; margin: 100px auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
                input { width: 100%; padding: 8px; margin: 5px 0; }
                button { width: 100%; padding: 10px; background: #4CAF50; color: white; border: none; cursor: pointer; }
            </style>
        </head>
        <body>
            <div class="login-box">
                <h2>Connexion</h2>
                <form method="post">
                    <input type="text" name="login" placeholder="Login" required>
                    <input type="password" name="password" placeholder="Mot de passe" required>
                    <button type="submit">Se connecter</button>
                </form>
            </div>
        </body>
        </html>
        ');
    }
}

// Connexion BDD
$conn = new mysqli('localhost', 'guerin', 'passroot', 'sae23');
if ($conn->connect_error) die("Connexion échouée : " . $conn->connect_error);

// Traitement du formulaire de recherche
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['recherche'])) {
    $filtres = array(
        'salle' => $conn->real_escape_string(isset($_POST['salle']) ? $_POST['salle'] : ''),
        'type' => $conn->real_escape_string(isset($_POST['type']) ? $_POST['type'] : ''),
        'date_debut' => $conn->real_escape_string(isset($_POST['date_debut']) ? $_POST['date_debut'] : ''),
        'date_fin' => $conn->real_escape_string(isset($_POST['date_fin']) ? $_POST['date_fin'] : '')
    );
    
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
        
        if ($stats && $stats->num_rows > 0) {
            while ($salle = $stats->fetch_assoc()) {
                echo "<h3>Salle ".htmlspecialchars($salle['salle'])."</h3>";
                echo "<p>🌡️ Température: Moy=".round($salle['avg_temp'], 1)."°C | Min=".round($salle['min_temp'], 1)."°C | Max=".round($salle['max_temp'], 1)."°C</p>";
                echo "<p>💧 Humidité: Moy=".round($salle['avg_hum'], 1)."%</p>";
                echo "<p>💡 Lumière: Moy=".round($salle['avg_lum'], 1)." lux</p>";
                echo "<p>☁️ CO2: Moy=".round($salle['avg_co2'], 1)." ppm</p>";
            }
        } else {
            echo "<p>Aucune donnée statistique disponible.</p>";
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
                        if ($salles && $salles->num_rows > 0) {
                            while ($s = $salles->fetch_assoc()) {
                                echo "<option value='".htmlspecialchars($s['salle'])."'>".htmlspecialchars($s['salle'])."</option>";
                            }
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
            
            <button type="submit" class="btn">Rechercher</button>
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
        
        if ($mesures && $mesures->num_rows > 0) {
            while ($m = $mesures->fetch_assoc()) {
                $salle = substr($m['nom_cap'], 0, 4);
                $type = substr($m['nom_cap'], 5);
                $classe = 'sensor-' . substr($type, 0, 3);
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
                        <td>".htmlspecialchars($m['date_mesure'])." ".htmlspecialchars($m['horaire_mesure'])."</td>
                        <td>".htmlspecialchars($salle)."</td>
                        <td>".htmlspecialchars($type)."</td>
                        <td class='".htmlspecialchars($classe)."'>".htmlspecialchars($m['valeur_mesure'])."$unite</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='4'>Aucune mesure récente</td></tr>";
        }
        ?>
    </table>

</body>
</html>