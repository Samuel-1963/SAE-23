<?php
session_start();

// Gestion de la déconnexion
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: gestion.php');
    exit();
}

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
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>EnergyWatch - Connexion</title>
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
            <section id="admin">
                <h2>🔐 Connexion à l\'espace administration</h2>
                
                <div class="admin-login">
                    <h3>Connexion gestionnaire du bâtiment E.</h3
                    
                    '. (isset($error) ? '<p style="color: red;">'.$error.'</p>' : '') .'
                    
                    <form method="post">
                        <label for="login">Identifiant :</label>
                        <input type="text" name="login" id="login" required>
                        
                        <label for="password">Mot de passe :</label>
                        <input type="password" name="password" id="password" required>
                        
                        <button type="submit">Se connecter</button>
                    </form>
                    
                    <p>
                        <a href="../index.html">← Retour à l\'accueil</a>
                    </p>
                </div>
            </section>
        </main>

        <footer>
            <p>&copy; 2025 EnergyWatch - Tous droits réservés | <a href="mentions-legales.html">Mentions légales</a></p>
        </footer>

        <script>
            document.getElementById(\'menu-toggle\').addEventListener(\'click\', function () {
                const nav = document.getElementById(\'main-nav\');
                nav.classList.toggle(\'active\');
                this.querySelectorAll(\'span\').forEach(span =>
                    span.classList.toggle(\'active\'));
            });
        </script>
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EnergyWatch - Gestion</title>
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
        <section id="consultation">
        <div class="consultation-entete">
            <h1>Gestion Bâtiment E <a href="gestion.php?logout=1" class="logout">Déconnexion</a>
        </div>

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
                    // Display the room name
                    echo "<h3>Salle " . htmlspecialchars($salle['salle']) . "</h3>";

                    // Display average temperature (and min/max if available)
                    if (is_numeric($salle['avg_temp'])) {
                        echo "<p>🌡️ Température: Moy=" . round($salle['avg_temp'], 1) . "°C";
                        if (is_numeric($salle['min_temp']) && is_numeric($salle['max_temp'])) {
                            echo " | Min=" . round($salle['min_temp'], 1) . "°C | Max=" . round($salle['max_temp'], 1) . "°C";
                        }
                        echo "</p>";
                    }

                    // Display average humidity if available
                    if (is_numeric($salle['avg_hum'])) {
                        echo "<p>💧 Humidité: Moy=" . round($salle['avg_hum'], 1) . "%</p>";
                    }

                    // Display average light level if available
                    if (is_numeric($salle['avg_lum'])) {
                        echo "<p>💡 Lumière: Moy=" . round($salle['avg_lum'], 1) . " lux</p>";
                    }

                    // Display average CO2 level if available
                    if (is_numeric($salle['avg_co2'])) {
                        echo "<p>☁️ CO2: Moy=" . round($salle['avg_co2'], 1) . " ppm</p>";
                    }

                    // Add a line break between rooms
                    echo "<br>";
                }
            } else {
                // Display a message if there is no statistical data
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
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Date/Heure</th>
                        <th>Salle</th>
                        <th>Type</th>
                        <th>Valeur</th>
                    </tr>
                </thead>
                <tbody>
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
                            
                            // Déterminer la classe CSS et l'unité comme dans le premier exemple
                            if (strpos($m['nom_cap'], 'temperature') !== false) {
                                $classe = 'sensor-temp';
                                $unite = '°C';
                            } elseif (strpos($m['nom_cap'], 'humidite') !== false) {
                                $classe = 'sensor-humidity';
                                $unite = '%';
                            } elseif (strpos($m['nom_cap'], 'luminosite') !== false) {
                                $classe = 'sensor-light';
                                $unite = 'lux';
                            } elseif (strpos($m['nom_cap'], 'co2') !== false) {
                                $classe = 'sensor-co2';
                                $unite = 'ppm';
                            } elseif (strpos($m['nom_cap'], 'press') !== false) {
                                $classe = 'sensor-pressure';
                                $unite = 'hPa';
                            }
                            
                            echo "<tr>
                                    <td>".htmlspecialchars($m['date_mesure'])." ".htmlspecialchars($m['horaire_mesure'])."</td>
                                    <td>".htmlspecialchars($salle)."</td>
                                    <td>".htmlspecialchars($type)."</td>
                                    <td class='".$classe."'>".htmlspecialchars($m['valeur_mesure'])." ".$unite."</td>
                                </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4'>Aucune mesure récente</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </section>
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