<?php
session_start();

// Déconnexion
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: administration.php');
    exit();
}

// Authentification
if (!isset($_SESSION['admin_connecte'])) {
$login = isset($_POST['login']) ? $_POST['login'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';



    if ($login === 'Administrateur' && $password === 'admih023BGRsfv5$n123') {
        $_SESSION['admin_connecte'] = true;
    } else {
        // Formulaire de connexion (copié de gestion.php)
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
                    <h2>🔐 Connexion Administrateur</h2>
                    <form method="post">
                        <label for="login">Login :</label>
                        <input type="text" name="login" required>
                        <label for="password">Mot de passe :</label>
                        <input type="password" name="password" required>
                        <button type="submit">Se connecter</button>
                    </form>
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

// Variables messages
$message_salle = '';
$message_capteur = '';

// Traitement des formulaires
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ajout d'une salle
    if (isset($_POST['ajouter_salle'])) {
        $salle = $conn->real_escape_string($_POST['salle']);
        if (!empty($salle)) {
            $result = $conn->query("INSERT INTO Salle (nom_salle) VALUES ('$salle')");
            $message_salle = $result ? "✅ Salle '$salle' ajoutée avec succès." : "❌ Erreur lors de l'ajout.";
        }
    }

    // Suppression d'une salle
    if (isset($_POST['supprimer_salle'])) {
        $salle = $conn->real_escape_string($_POST['salle']);
        if (!empty($salle)) {
            $conn->query("DELETE FROM Capteur WHERE nom_cap LIKE '$salle%'");
            $result = $conn->query("DELETE FROM Salle WHERE nom_salle = '$salle'");
            $message_salle = $result ? "✅ Salle '$salle' supprimée." : "❌ Erreur lors de la suppression.";
        }
    }

    // Ajout d'un capteur
    if (isset($_POST['ajouter_capteur'])) {
        $capteur = $conn->real_escape_string($_POST['capteur']);
        if (!empty($capteur)) {
            $result = $conn->query("INSERT INTO Capteur (nom_cap) VALUES ('$capteur')");
            $message_capteur = $result ? "✅ Capteur '$capteur' ajouté." : "❌ Erreur lors de l'ajout.";
        }
    }

    // Suppression d'un capteur
    if (isset($_POST['supprimer_capteur'])) {
        $capteur = $conn->real_escape_string($_POST['capteur']);
        if (!empty($capteur)) {
            $conn->query("DELETE FROM Mesure WHERE nom_cap = '$capteur'");
            $result = $conn->query("DELETE FROM Capteur WHERE nom_cap = '$capteur'");
            $message_capteur = $result ? "✅ Capteur '$capteur' supprimé." : "❌ Capteur non trouvé ou erreur.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EnergyWatch - Administration</title>
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
    <div class="consultation-entete">
        <h1>Administration <a href="administration.php?logout=1" class="logout">Déconnexion</a></h1>
    </div>

    <div class="card">
        <h2>🏠 Gestion des Salles</h2>
        <?php if ($message_salle) echo "<p>$message_salle</p>"; ?>
        <form method="post">
            <input type="text" name="salle" placeholder="Nom de la salle (ex: E001)" required>
            <button name="ajouter_salle" type="submit">➕ Ajouter</button>
            <button name="supprimer_salle" type="submit">➖ Supprimer</button>
        </form>
    </div>

    <div class="card">
        <h2>🔧 Gestion des Capteurs</h2>
        <?php if ($message_capteur) echo "<p>$message_capteur</p>"; ?>
        <form method="post">
            <input type="text" name="capteur" placeholder="Nom du capteur (ex: E001_temperature)" required>
            <button name="ajouter_capteur" type="submit">➕ Ajouter</button>
            <button name="supprimer_capteur" type="submit">➖ Supprimer</button>
        </form>
    </div>

    <div class="card">
        <h2>📋 Liste des salles existantes</h2>
        <ul>
            <?php
            $res = $conn->query("SELECT nom_salle FROM Salle ORDER BY nom_salle");
            while ($row = $res->fetch_assoc()) {
                echo "<li>" . htmlspecialchars($row['nom_salle']) . "</li>";
            }
            ?>
        </ul>
    </div>

    <div class="card">
        <h2>📟 Liste des capteurs existants</h2>
        <ul>
            <?php
            $res = $conn->query("SELECT nom_cap FROM Capteur ORDER BY nom_cap");
            while ($row = $res->fetch_assoc()) {
                echo "<li>" . htmlspecialchars($row['nom_cap']) . "</li>";
            }
            ?>
        </ul>
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
