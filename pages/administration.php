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


    if ($login === 'Admin' && $password === 'admin123') {
        $_SESSION['admin_connecte'] = true;
    } else {
        // Formulaire de connexion
        die('
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <title>Connexion Administration</title>
            <link rel="stylesheet" href="../styles.css">
        </head>
        <body>
            <header>
                <a href="../index.html" class="titre-accueil"><h1>EnergyWatch</h1></a>
            </header>
            <main>
                <section id="admin">
                    <h2>🔐 Connexion Administrateur</h2>
                    <form method="post">
                        <label for="login">Identifiant :</label>
                        <input type="text" name="login" required>
                        <label for="password">Mot de passe :</label>
                        <input type="password" name="password" required>
                        <button type="submit">Connexion</button>
                    </form>
                </section>
            </main>
            <footer>
                <p>&copy; 2025 EnergyWatch - Tous droits réservés</p>
            </footer>
        </body>
        </html>');
    }
}

// Connexion BDD
$conn = new mysqli('localhost', 'guerin', 'passroot', 'sae23');
if ($conn->connect_error) die("Erreur connexion BDD: " . $conn->connect_error);

// Traitement formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_salle']) && !empty($_POST['salle'])) {
        $salle = $conn->real_escape_string($_POST['salle']);
        $conn->query("INSERT INTO Salle (nom_salle) VALUES ('$salle')");
    }

    if (isset($_POST['del_salle']) && !empty($_POST['salle'])) {
        $salle = $conn->real_escape_string($_POST['salle']);
        $conn->query("DELETE FROM Salle WHERE nom_salle = '$salle'");
        $conn->query("DELETE FROM Capteur WHERE nom_cap LIKE '$salle%'");
    }

    if (isset($_POST['add_capteur']) && !empty($_POST['capteur']) && !empty($_POST['type'])) {
        $capteur = $conn->real_escape_string($_POST['capteur']);
        $type = $conn->real_escape_string($_POST['type']);
        $nom_cap = $capteur . "_" . $type;
        $conn->query("INSERT INTO Capteur (nom_cap) VALUES ('$nom_cap')");
    }

    if (isset($_POST['del_capteur']) && !empty($_POST['capteur'])) {
        $capteur = $conn->real_escape_string($_POST['capteur']);
        $conn->query("DELETE FROM Capteur WHERE nom_cap = '$capteur'");
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>EnergyWatch - Administration</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <header>
        <a href="../index.html" class="titre-accueil"><h1>EnergyWatch</h1></a>
        <button id="menu-toggle" aria-label="Menu"><span></span><span></span><span></span></button>
        <nav id="main-nav">
            <ul>
                <li><a href="administration.php">Administration</a></li>
                <li><a href="gestion.php">Gestion</a></li>
                <li><a href="consultation.php">Consultation</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section id="admin-panel">
            <div class="consultation-entete">
                <h1>Espace Administration <a href="?logout=1" class="logout">Déconnexion</a></h1>
            </div>

            <div class="card">
                <h2>🏫 Gestion des Salles</h2>
                <form method="post">
                    <label>Nom salle :
                        <input type="text" name="salle" required>
                    </label>
                    <button type="submit" name="add_salle">Ajouter</button>
                    <button type="submit" name="del_salle">Supprimer</button>
                </form>
            </div>

            <div class="card">
                <h2>📡 Gestion des Capteurs</h2>
                <form method="post">
                    <label>Code Salle (ex: E003) :
                        <input type="text" name="capteur" required>
                    </label>
                    <label>Type :
                        <select name="type" required>
                            <option value="temperature">Température</option>
                            <option value="humidite">Humidité</option>
                            <option value="luminosite">Luminosité</option>
                            <option value="co2">CO2</option>
                        </select>
                    </label>
                    <button type="submit" name="add_capteur">Ajouter</button>
                    <button type="submit" name="del_capteur">Supprimer</button>
                </form>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2025 EnergyWatch - Tous droits réservés</p>
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
