<?php
session_start();

// Connexion simple de test (à remplacer par base de données)
$gestionnaires = [
    "gestionA" => ["mdp" => "azerty", "batiment_id" => 1],
    "gestionE" => ["mdp" => "azerty", "batiment_id" => 2]
];

// Traitement connexion
if (isset($_POST['login']) && isset($_POST['password'])) {
    $login = $_POST['login'];
    $password = $_POST['password'];

    if (isset($gestionnaires[$login]) && $gestionnaires[$login]['mdp'] === $password) {
        $_SESSION['gestionnaire'] = $login;
        $_SESSION['batiment_id'] = $gestionnaires[$login]['batiment_id'];
    } else {
        $erreur = "Identifiants incorrects.";
    }
}

// Déconnexion
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: gestion.php");
    exit();
}
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
        <button id="menu-toggle" aria-label="Ouvrir le menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <a href="../index.html" class="titre-accueil">
            <h1>EnergyWatch</h1>
        </a>
        <nav id="main-nav">
            <ul>
                <li><a href="administration.php">Administration</a></li>
                <li><a href="gestion.php">Gestion</a></li>
                <li><a href="consultation.html">Consultation</a></li>
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
            <h2>📋 Espace Gestionnaire</h2>

            <?php if (!isset($_SESSION['gestionnaire'])): ?>
                <div class="gestion-login">
                    <h3>Connexion Gestionnaire</h3>
                    <?php if (isset($erreur)) echo "<p style='color:red;'>$erreur</p>"; ?>
                    <form action="gestion.php" method="POST">
                        <label for="login">Identifiant :</label>
                        <input type="text" name="login" required>

                        <label for="password">Mot de passe :</label>
                        <input type="password" name="password" required>

                        <button type="submit">Se connecter</button>
                    </form>
                </div>
            <?php else: ?>
                <p>Connecté en tant que <strong><?= htmlspecialchars($_SESSION['gestionnaire']) ?></strong>.
                    <a href="gestion.php?logout=true">Se déconnecter</a>
                </p>

                <div class="donnees-gestion">
                    <h3>Mesures des capteurs – Bâtiment ID : <?= $_SESSION['batiment_id'] ?></h3>

                    <?php
                    try {
                        $pdo = new PDO("mysql:host=localhost;dbname=energywatch", "root", "");
                        $id_bat = $_SESSION['batiment_id'];

                        $sql = "
                            SELECT s.nom AS salle, c.nom AS capteur, m.valeur, m.date_heure
                            FROM mesures m
                            JOIN capteurs c ON m.id_capteur = c.id
                            JOIN salles s ON c.id_salle = s.id
                            WHERE s.id_batiment = :id_bat
                            ORDER BY m.date_heure DESC
                            LIMIT 20
                        ";

                        $stmt = $pdo->prepare($sql);
                        $stmt->execute(['id_bat' => $id_bat]);
                        $mesures = $stmt->fetchAll();

                        if ($mesures):
                    ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Salle</th>
                                <th>Capteur</th>
                                <th>Valeur</th>
                                <th>Date/Heure</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mesures as $m): ?>
                                <tr>
                                    <td><?= htmlspecialchars($m['salle']) ?></td>
                                    <td><?= htmlspecialchars($m['capteur']) ?></td>
                                    <td><?= htmlspecialchars($m['valeur']) ?></td>
                                    <td><?= htmlspecialchars($m['date_heure']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                        <p>Aucune mesure trouvée pour ce bâtiment.</p>
                    <?php endif; ?>

                    <h3>Statistiques par salle (min / max / moyenne)</h3>
                    <?php
                        $sqlStats = "
                            SELECT s.nom AS salle,
                                   c.nom AS capteur,
                                   MIN(m.valeur) AS min,
                                   MAX(m.valeur) AS max,
                                   ROUND(AVG(m.valeur), 2) AS moyenne
                            FROM mesures m
                            JOIN capteurs c ON m.id_capteur = c.id
                            JOIN salles s ON c.id_salle = s.id
                            WHERE s.id_batiment = :id_bat
                            GROUP BY s.nom, c.nom
                        ";

                        $stmtStats = $pdo->prepare($sqlStats);
                        $stmtStats->execute(['id_bat' => $id_bat]);
                        $stats = $stmtStats->fetchAll();

                        if ($stats):
                    ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Salle</th>
                                <th>Capteur</th>
                                <th>Min</th>
                                <th>Max</th>
                                <th>Moyenne</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stats as $s): ?>
                                <tr>
                                    <td><?= htmlspecialchars($s['salle']) ?></td>
                                    <td><?= htmlspecialchars($s['capteur']) ?></td>
                                    <td><?= htmlspecialchars($s['min']) ?></td>
                                    <td><?= htmlspecialchars($s['max']) ?></td>
                                    <td><?= htmlspecialchars($s['moyenne']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                        <p>Aucune statistique disponible pour ce bâtiment.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <p>&copy; 2025 EnergyWatch - Tous droits réservés</p>
    </footer>
</body>
</html>
