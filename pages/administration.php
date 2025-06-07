<?php
session_start();

// Disconnect
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: administration.php');
    exit();
}

// Authentication
if (!isset($_SESSION['admin_connecte'])) {
$login = isset($_POST['login']) ? $_POST['login'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';



    if ($login === 'Administrateur' && $password === 'h023BGRsfv5$') {
        $_SESSION['admin_connecte'] = true;
    } else {
        // Login form
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
                    <h3>Connexion Administrateur.</h3>
                    
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

// BDD connection
$conn = new mysqli('localhost', 'guerin', 'passroot', 'sae23');
if ($conn->connect_error) die("Connexion échouée : " . $conn->connect_error);

// Message variables
$message_salle = '';
$message_capteur = '';

// Form processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ajouter_capteur'])) {
        // Escape user input
        $nom_cap = trim($conn->real_escape_string($_POST['nom_cap']));
        $type_unite = $_POST['type_unite'];
        $nom_salle = $_POST['nom_salle'];

        // Separation type and unit
        list($type_cap, $unite_cap) = explode('|', $type_unite);

        // Remove leading "E" from room name if present (uppercase E only)
        if (stripos($nom_salle, 'E') === 0) {
            $nom_salle_trimmed = substr($nom_salle, 1);
        } else {
            $nom_salle_trimmed = $nom_salle;
        }

        // Valid values for dropdowns
        $valid_types = ["Humidité", "Luminosité", "CO2", "Température"];
        $valid_units = ["%", "°C", "ppm", "lux"];
        $valid_rooms = ["101", "102", "207", "208"];

        // Debug info for validation
        $debug_msgs = [];

        if (empty($nom_cap)) {
            $debug_msgs[] = "Sensor name is empty.";
        }
        if (!in_array($type_cap, $valid_types)) {
            $debug_msgs[] = "Invalid sensor type: '$type_cap'.";
        }
        if (!in_array($unite_cap, $valid_units)) {
            $debug_msgs[] = "Invalid unit: '$unite_cap'.";
        }
        if (!in_array($nom_salle_trimmed, $valid_rooms)) {
            $debug_msgs[] = "Invalid room: '$nom_salle' (trimmed to '$nom_salle_trimmed').";
        }

        // Validate inputs
        if (empty($debug_msgs)) {
            // Prepare statement
            $stmt = $conn->prepare("INSERT INTO sae23.Capteur (nom_cap, type_cap, unite_cap, nom_salle) VALUES (?, ?, ?, ?)");
            if ($stmt === false) {
                $message_capteur = "❌ Prepare failed: (" . $conn->errno . ") " . $conn->error;
            } else {
                // Use trimmed room name for insertion (without the leading E)
                $stmt->bind_param("ssss", $nom_cap, $type_cap, $unite_cap, $nom_salle_trimmed);
                if ($stmt->execute()) {
                    $message_capteur = "✅ Sensor '$nom_cap' successfully added.";
                } else {
                    $message_capteur = "❌ Execute failed: (" . $stmt->errno . ") " . $stmt->error;
                }
                $stmt->close();
            }
        } else {
            // Display detailed invalid data messages
            $message_capteur = "❌ Invalid data: " . implode(" ", $debug_msgs);
        }
    }


// Deleting a sensor
if (isset($_POST['supprimer_capteur'])) {
    // Trim and escape user input
    $nom_cap = trim($conn->real_escape_string($_POST['nom_cap']));

    if (!empty($nom_cap)) {
        // Delete measurements first due to foreign key constraints
        $deleteMesures = $conn->query("DELETE FROM sae23.Mesure WHERE nom_cap = '$nom_cap'");

        // Then delete the sensor
        $deleteCapteur = $conn->query("DELETE FROM sae23.Capteur WHERE nom_cap = '$nom_cap'");

        // Feedback message
        if ($deleteCapteur) {
            $message_capteur = "✅ Capteur '$nom_cap' supprimé.";
        } else {
            $message_capteur = "❌ Échec de la suppression du capteur. Vérifie s’il existe.";
        }
    } else {
        $message_capteur = "❌ Le nom du capteur ne peut pas être vide.";
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
        <h2>➕ Ajouter un Capteur</h2>
        <?php if (!empty($message_capteur) && isset($_POST['ajouter_capteur'])) echo "<p>$message_capteur</p>"; ?>
        <form method="post">
            <label>Nom du capteur :
                <input type="text" name="nom_cap" placeholder="Ex: E101_temperature" required>
            </label><br>

            <label>Type de capteur :
                <select name="type_unite" required>
                    <option value="">-- Sélectionner --</option>
                    <option value="Humidité|%">Humidité (%)</option>
                    <option value="Luminosité|lux">Luminosité (lux)</option>
                    <option value="CO2|ppm">CO2 (ppm)</option>
                    <option value="Température|°C">Température (°C)</option>
                </select>
            </label><br>

            <label>Salle :
                <select name="nom_salle" required>
                    <option value="">-- Sélectionner --</option>
                    <option value="101">E101</option>
                    <option value="102">E102</option>
                    <option value="207">E207</option>
                    <option value="208">E208</option>
                </select>
            </label><br>

            <button type="submit" name="ajouter_capteur">Ajouter le capteur</button>
        </form>
    </div>

    <div class="card">
        <h2>➖ Supprimer un Capteur</h2>
        <?php if (!empty($message_capteur) && isset($_POST['supprimer_capteur'])) echo "<p>$message_capteur</p>"; ?>
        <form method="post">
            <label>Nom du capteur :
                <input type="text" name="nom_cap" placeholder="Ex: E101_temperature" required>
            </label><br>
            <button type="submit" name="supprimer_capteur">Supprimer</button>
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
