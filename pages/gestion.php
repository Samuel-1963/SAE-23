<?php
session_start();

// Authentification simple
if (!isset($_SESSION['loggedin']) && (!isset($_POST['username']) || !isset($_POST['password']))) {
    loginForm();
    exit();
}

if (isset($_POST['username']) && isset($_POST['password'])) {
    if ($_POST['username'] === 'Gerant' && $_POST['password'] === 'hgvcJB564F*') {
        $_SESSION['loggedin'] = true;
    } else {
        echo "<p style='color:red;'>Identifiants incorrects.</p>";
        loginForm();
        exit();
    }
}

if (!isset($_SESSION['loggedin'])) {
    loginForm();
    exit();
}

function loginForm() {
    echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>Connexion Gestionnaire</title></head><body>';
    echo '<h2>Connexion Gestionnaire</h2>';
    echo '<form method="post"><label>Utilisateur : <input type="text" name="username" required></label><br><label>Mot de passe : <input type="password" name="password" required></label><br><input type="submit" value="Se connecter"></form>';
    echo '</body></html>';
}

// Connexion DB
$conn = new mysqli("localhost", "guerin", "passroot", "sae23");
if ($conn->connect_error) die("Connexion échouée : " . $conn->connect_error);

// Supposons que Gerant gère le bâtiment "E"
$batiment = "E";

// Récupération des capteurs du bâtiment
$capteurs = $conn->query("SELECT DISTINCT nom_cap FROM Capteur WHERE id_salle IN (SELECT id_salle FROM Salle WHERE id_bat = '$batiment')");

?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Gestion - EnergyWatch</title>
        <link rel="stylesheet" href="../styles.css">
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
        <h2>Mesures du bâtiment <?php echo $batiment; ?></h2>

        <form action="resultats_gestion.php" method="get">
            <label>Capteur :
                <select name="capteur" required>
                    <option value="">--Choisir--</option>
                    <?php while ($row = $capteurs->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($row['nom_cap']); ?>"><?php echo htmlspecialchars($row['nom_cap']); ?></option>
                    <?php endwhile; ?>
                </select>
            </label>
            <br>
            <label>Date de début : <input type="date" name="debut" required></label>
            <label>Date de fin : <input type="date" name="fin" required></label>
            <br>
            <input type="submit" value="Afficher les mesures">
        </form>

        <h3>Statistiques par salle</h3>
        <table>
            <thead>
                <tr><th>Salle</th><th>Moyenne</th><th>Min</th><th>Max</th></tr>
            </thead>
            <tbody>
            <?php
            $stats = $conn->query("SELECT s.nom_salle, ROUND(AVG(m.valeur_mesure),2) AS moyenne, MIN(m.valeur_mesure) AS min, MAX(m.valeur_mesure) AS max FROM Mesure m JOIN Capteur c ON m.nom_cap = c.nom_cap JOIN Salle s ON c.id_salle = s.id_salle WHERE s.id_bat = '$batiment' GROUP BY s.nom_salle");
            while ($row = $stats->fetch_assoc()) {
                echo "<tr><td>" . htmlspecialchars($row['nom_salle']) . "</td><td>" . htmlspecialchars($row['moyenne']) . "</td><td>" . htmlspecialchars($row['min']) . "</td><td>" . htmlspecialchars($row['max']) . "</td></tr>";
            }
            ?>
            </tbody>
        </table>
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
<?php $conn->close(); ?>