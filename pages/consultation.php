<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="60">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EnergyWatch - Consultation</title>
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
                <li><a href="consultation.php" class="active">Consultation</a></li>
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
                <h2>📈 Consultation des mesures</h2>
                <p>Cette page affiche la dernière mesure enregistrée par chaque capteur présent dans les bâtiments. Les données sont automatiquement mises à jour.</p>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Heure</th>
                            <th>Capteur</th>
                            <th>Salle</th>
                            <th>Valeur</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $servername = "localhost";
                        $username = "guerin";
                        $password = "passroot";
                        $dbname = "sae23";

                        $conn = new mysqli($servername, $username, $password, $dbname);

                        if ($conn->connect_error) {
                            die("Échec de la connexion : " . $conn->connect_error);
                        }

                        // Requête avec jointure pour obtenir la salle
                        $sql = "SELECT m.date_mesure, m.horaire_mesure, c.nom_cap, c.nom_salle, m.valeur_mesure
                                FROM Mesure m
                                JOIN Capteur c ON m.nom_cap = c.nom_cap
                                ORDER BY m.date_mesure DESC, m.horaire_mesure DESC
                                LIMIT 50";

                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                // Classe CSS selon le type de capteur
                                $sensorClass = '';
                                if (stripos($row["nom_cap"], 'temp') !== false) {
                                    $sensorClass = 'sensor-tem';
                                    $unit = "°C";
                                } elseif (stripos($row["nom_cap"], 'hum') !== false) {
                                    $sensorClass = 'sensor-hum';
                                    $unit = "%";
                                } elseif (stripos($row["nom_cap"], 'lum') !== false) {
                                    $sensorClass = 'sensor-lum';
                                    $unit = "lux";
                                } elseif (stripos($row["nom_cap"], 'co2') !== false) {
                                    $sensorClass = 'sensor-co2';
                                    $unit = "ppm";
                                } else {
                                    $unit = "";
                                }

                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row["date_mesure"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["horaire_mesure"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["nom_cap"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["nom_salle"]) . "</td>";
                                echo "<td class='" . $sensorClass . "'>" . htmlspecialchars($row["valeur_mesure"]) . " " . $unit . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5'>Aucune donnée disponible</td></tr>";
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
        document.getElementById('menu-toggle').addEventListener('click', function () {
            const nav = document.getElementById('main-nav');
            nav.classList.toggle('active');
            this.querySelectorAll('span').forEach(span => span.classList.toggle('active'));
        });
    </script>
</body>
</html>
