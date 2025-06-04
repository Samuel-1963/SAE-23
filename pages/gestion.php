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

// Récupération des données du bâtiment E
$sql = "
SELECT M.date_mesure, M.horaire_mesure, M.nom_cap, M.valeur_mesure
FROM Mesure M
JOIN Capteur C ON M.nom_cap = C.nom_cap
JOIN Salle S ON C.id_salle = S.id_salle
JOIN Batiment B ON S.id_bat = B.id_bat
WHERE B.nom_bat = 'Bâtiment E'
ORDER BY M.date_mesure DESC, M.horaire_mesure DESC
LIMIT 50
";

$result = $conn->query($sql);

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
    <title>EnergyWatch - Consultation</title>
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
        <section>
            <h2>Dernières mesures - Bâtiment E</h2>
            <?php if ($result && $result->num_rows > 0): ?>
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
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['date_mesure']) ?></td>
                                <td><?= htmlspecialchars($row['horaire_mesure']) ?></td>
                                <td><?= htmlspecialchars($row['nom_cap']) ?></td>
                                <td><?= htmlspecialchars($row['valeur_mesure']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Aucune donnée à afficher.</p>
            <?php endif; ?>
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
