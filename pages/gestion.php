<?php
session_start();

// Connexion simple (à sécuriser plus tard)
$login_correct = "Gerant";
$password_correct = "hgvcJB564F*";

// Traitement de la connexion
if (isset($_POST['login']) && isset($_POST['password'])) {
    if ($_POST['login'] === $login_correct && $_POST['password'] === $password_correct) {
        $_SESSION['admin'] = true;
    } else {
        $error = "Identifiants incorrects.";
    }
}

// Déconnexion
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: administration.php");
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
    <style>
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .form-container {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 15px;
        }
        .form-group {
            flex: 1;
            min-width: 200px;
        }
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
                <li><a href="gestion.php" class="active">Gestion</a></li>
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
        <section id="gestion">
            <div class="section-entete">
                <h2>📊 Gestion du bâtiment <?php echo htmlspecialchars($batiment_gestionnaire); ?></h2>
                <p>Interface réservée aux gestionnaires pour consulter les données de votre bâtiment.</p>
            </div>

            <!-- Statistiques globales -->
            <h3>Statistiques récentes</h3>
            <div class="stats-container">
                <?php
                // Connexion à la base de données
                $servername = "localhost";
                $username = "guerin";
                $password = "passroot";
                $dbname = "sae23";
                
                $conn = new mysqli($servername, $username, $password, $dbname);
                if ($conn->connect_error) die("Échec de la connexion : " . $conn->connect_error);
                
                // Requête pour les statistiques par type de capteur
                $types_capteurs = ['temp', 'hum', 'press'];
                
                foreach ($types_capteurs as $type) {
                    $sql = "SELECT 
                            AVG(valeur_mesure) as moyenne,
                            MIN(valeur_mesure) as minimum,
                            MAX(valeur_mesure) as maximum
                            FROM Mesure m
                            JOIN Capteur c ON m.id_cap = c.id_cap
                            WHERE c.batiment = ? AND c.nom_cap LIKE '%$type%'
                            AND date_mesure >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
                    
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("s", $batiment_gestionnaire);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $stats = $result->fetch_assoc();
                    
                    $unite = ($type === 'temp') ? '°C' : (($type === 'hum') ? '%' : 'hPa');
                    $titre = ($type === 'temp') ? 'Température' : (($type === 'hum') ? 'Humidité' : 'Pression');
                    $icone = ($type === 'temp') ? '🌡️' : (($type === 'hum') ? '💧' : '📊');
                    
                    echo "<div class='stat-card'>";
                    echo "<h4>$icone $titre (7 derniers jours)</h4>";
                    echo "<p>Moyenne: " . round($stats['moyenne'], 2) . " $unite</p>";
                    echo "<p>Minimum: " . round($stats['minimum'], 2) . " $unite</p>";
                    echo "<p>Maximum: " . round($stats['maximum'], 2) . " $unite</p>";
                    echo "</div>";
                }
                ?>
            </div>

            <!-- Formulaire de recherche -->
            <div class="form-container">
                <h3>Recherche avancée</h3>
                <form action="resultats_recherche.php" method="get">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="capteur">Capteur:</label>
                            <select id="capteur" name="capteur" required>
                                <option value="">-- Sélectionnez un capteur --</option>
                                <?php
                                // Récupération des capteurs du bâtiment
                                $sql = "SELECT id_cap, nom_cap FROM Capteur WHERE batiment = ?";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("s", $batiment_gestionnaire);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                
                                while ($row = $result->fetch_assoc()) {
                                    echo "<option value='" . htmlspecialchars($row['id_cap']) . "'>" . 
                                         htmlspecialchars($row['nom_cap']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="date_debut">Date début:</label>
                            <input type="date" id="date_debut" name="date_debut" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="date_fin">Date fin:</label>
                            <input type="date" id="date_fin" name="date_fin" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn">Rechercher</button>
                </form>
            </div>

            <!-- Dernières mesures -->
            <h3>Dernières mesures</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Heure</th>
                            <th>Capteur</th>
                            <th>Valeur</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Requête pour les dernières mesures du bâtiment
                        $sql = "SELECT m.date_mesure, m.horaire_mesure, c.nom_cap, m.valeur_mesure 
                                FROM Mesure m
                                JOIN Capteur c ON m.id_cap = c.id_cap
                                WHERE c.batiment = ?
                                ORDER BY m.date_mesure DESC, m.horaire_mesure DESC
                                LIMIT 20";
                        
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("s", $batiment_gestionnaire);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                $sensorClass = '';
                                if (strpos($row["nom_cap"], 'temp') !== false) {
                                    $sensorClass = 'sensor-temp';
                                } elseif (strpos($row["nom_cap"], 'hum') !== false) {
                                    $sensorClass = 'sensor-humidity';
                                } elseif (strpos($row["nom_cap"], 'press') !== false) {
                                    $sensorClass = 'sensor-pressure';
                                }
                                
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row["date_mesure"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["horaire_mesure"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["nom_cap"]) . "</td>";
                                echo "<td class='" . $sensorClass . "'>" . htmlspecialchars($row["valeur_mesure"]) . 
                                    (strpos($row["nom_cap"], 'temp') !== false ? " °C" : 
                                    (strpos($row["nom_cap"], 'hum') !== false ? " %" : 
                                    (strpos($row["nom_cap"], 'press') !== false ? " hPa" : ""))) . 
                                    "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4'>Aucune donnée disponible pour votre bâtiment</td></tr>";
                        }
                        
                        $conn->close();
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
        // Gestion du menu responsive
        document.getElementById('menu-toggle').addEventListener('click', function () {
            const nav = document.getElementById('main-nav');
            nav.classList.toggle('active');
            this.querySelectorAll('span').forEach(span => span.classList.toggle('active'));
        });

        // Limiter la date de fin à aujourd'hui et gérer la cohérence des dates
        document.getElementById('date_debut').addEventListener('change', function() {
            const dateDebut = new Date(this.value);
            const dateFin = document.getElementById('date_fin');
            
            // Si date début > date fin, mettre date fin = date début
            if (dateFin.value && new Date(dateFin.value) < dateDebut) {
                dateFin.value = this.value;
            }
            
            // Date min pour date fin = date début
            dateFin.min = this.value;
        });

        // Définir la date max pour les deux champs à aujourd'hui
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('date_debut').max = today;
        document.getElementById('date_fin').max = today;
    </script>
</body>
</html>