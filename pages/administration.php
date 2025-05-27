<?php
session_start();

// Connexion simple (à sécuriser plus tard)
$login_correct = "admin";
$password_correct = "admin123";

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
    <title>Administration - EnergyWatch</title>
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
        <section id="admin">
            <h2>🔐 Espace Administration</h2>

            <?php if (!isset($_SESSION['admin'])): ?>
                <div class="admin-login">
                    <h3>Connexion administrateur</h3>
                    <?php if (isset($error)) echo "<p style='color: red;'>$error</p>"; ?>
                    <form action="administration.php" method="POST">
                        <label for="login">Identifiant :</label>
                        <input type="text" name="login" required>

                        <label for="password">Mot de passe :</label>
                        <input type="password" name="password" required>

                        <button type="submit">Se connecter</button>
                    </form>
                </div>
            <?php else: ?>
                <p>Connecté en tant qu'administrateur. <a href="administration.php?logout=true">Se déconnecter</a></p>

                <div class="admin-panel">
                    <!-- Gestion des bâtiments -->
                    <h3>🏢 Ajouter un bâtiment</h3>
                    <form action="traitements/ajouter_batiment.php" method="POST">
                        <label for="nom_batiment">Nom :</label>
                        <input type="text" name="nom_batiment" required>
                        <button type="submit">Ajouter</button>
                    </form>

                    <!-- Gestion des salles -->
                    <h3>🏫 Ajouter une salle</h3>
                    <form action="traitements/ajouter_salle.php" method="POST">
                        <label for="nom_salle">Nom :</label>
                        <input type="text" name="nom_salle" required>
                        <label for="type_salle">Type :</label>
                        <input type="text" name="type_salle" required>
                        <label for="capacite">Capacité :</label>
                        <input type="number" name="capacite" required>
                        <label for="id_batiment">ID Bâtiment :</label>
                        <input type="number" name="id_batiment" required>
                        <button type="submit">Ajouter</button>
                    </form>

                    <!-- Gestion des capteurs -->
                    <h3>📟 Ajouter un capteur</h3>
                    <form action="traitements/ajouter_capteur.php" method="POST">
                        <label for="nom_capteur">Nom :</label>
                        <input type="text" name="nom_capteur" required>
                        <label for="type_capteur">Type :</label>
                        <input type="text" name="type_capteur" required>
                        <label for="unite">Unité :</label>
                        <input type="text" name="unite" required>
                        <label for="id_salle">ID Salle :</label>
                        <input type="number" name="id_salle" required>
                        <button type="submit">Ajouter</button>
                    </form>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <p>&copy; 2025 EnergyWatch - Tous droits réservés</p>
    </footer>
</body>
</html>
