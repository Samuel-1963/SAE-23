<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="60">
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
        <?php
        include("config.php");

        $sql = "SELECT M1.*
                FROM Mesure M1
                INNER JOIN (
                    SELECT nom_cap, MAX(CONCAT(date_mesure, ' ', horaire_mesure)) as latest
                    FROM Mesure
                    GROUP BY nom_cap
                ) M2
                ON M1.nom_cap = M2.nom_cap
                AND CONCAT(M1.date_mesure, ' ', M1.horaire_mesure) = M2.latest
                ORDER BY M1.nom_cap ASC";

        $result = $conn->query($sql);

        echo "<h2>Dernières mesures des capteurs</h2>";
        echo "<table>";
        echo "<tr><th>Capteur</th><th>Date</th><th>Heure</th><th>Valeur</th></tr>";

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                    <td>" . htmlspecialchars($row["nom_cap"]) . "</td>
                    <td>" . htmlspecialchars($row["date_mesure"]) . "</td>
                    <td>" . htmlspecialchars($row["horaire_mesure"]) . "</td>
                    <td>" . htmlspecialchars($row["valeur_mesure"]) . "</td>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='4'>Aucune donnée disponible</td></tr>";
        }

        echo "</table>";
        $conn->close();
        ?>
    </main>

    <footer>
        <p>&copy; 2025 EnergyWatch - Tous droits réservés | <a href="mentions-legales.html">Mentions légales</a></p>
    </footer>

    <script>
        document.getElementById('menu-toggle').addEventListener('click', function() {
            const nav = document.getElementById('main-nav');
            nav.classList.toggle('active');
            this.querySelectorAll('span').forEach(span =>
                span.classList.toggle('active'));
        });
    </script>
</body>
</html>
