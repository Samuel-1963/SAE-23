<?php
session_start();

// 1. GESTION DE L'AUTHENTIFICATION
$identifiants_valides = [
    'login' => 'Gerant',
    'password' => 'hgvcJB564F*'
];

// Déconnexion si demandée
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: gestion.php");
    exit();
}

// Vérification des identifiants si formulaire soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    if ($_POST['login'] === $identifiants_valides['login'] && 
        $_POST['password'] === $identifiants_valides['password']) {
        $_SESSION['authentifie'] = true;
    } else {
        $erreur = "Identifiants incorrects";
    }
}

// Redirection si non authentifié
if (!isset($_SESSION['authentifie'])) {
    // Affiche le formulaire de connexion
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Connexion Gestionnaire</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 300px; margin: 50px auto; }
            form { display: grid; gap: 10px; }
            input { padding: 8px; }
            button { padding: 10px; background: #4CAF50; color: white; border: none; }
            .erreur { color: red; }
        </style>
    </head>
    <body>
        <h2>Connexion Bâtiment E</h2>
        <?php if (isset($erreur)) echo "<p class='erreur'>$erreur</p>"; ?>
        <form method="post">
            <input type="text" name="login" placeholder="Login" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <button type="submit">Se connecter</button>
        </form>
    </body>
    </html>
    <?php
    exit();
}

// 2. CONNEXION À LA BASE DE DONNÉES (si authentifié)
$conn = new mysqli('localhost', 'guerin', 'passroot', 'sae23');
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// 3. RÉCUPÉRATION DES DONNÉES (simplifiée)
$sql = "SELECT 
        date_mesure, 
        horaire_mesure, 
        nom_cap, 
        valeur_mesure 
        FROM Mesure 
        WHERE nom_cap LIKE 'E%'
        ORDER BY date_mesure DESC, horaire_mesure DESC 
        LIMIT 100";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gestion Bâtiment E</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 0 auto; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f2f2f2; }
        .logout { float: right; }
        .sensor-temp { color: #e74c3c; }
        .sensor-humidity { color: #3498db; }
        .sensor-light { color: #f39c12; }
        .sensor-co2 { color: #2ecc71; }
    </style>
</head>
<body>

    <h1>Données des capteurs <a href="?logout=1" class="logout">Déconnexion</a></h1>

    <table>
        <tr>
            <th>Date</th>
            <th>Heure</th>
            <th>Capteur</th>
            <th>Valeur</th>
        </tr>
        <?php
        while ($row = $result->fetch_assoc()) {
            $type = explode('_', $row['nom_cap'])[1];
            $class = 'sensor-' . substr($type, 0, 4); // "temp", "humi", etc.
            $unite = match($type) {
                'temperature' => '°C',
                'humidite' => '%',
                'luminosite' => 'lux',
                'co2' => 'ppm',
                default => ''
            };
            
            echo "<tr>
                    <td>{$row['date_mesure']}</td>
                    <td>{$row['horaire_mesure']}</td>
                    <td>{$row['nom_cap']}</td>
                    <td class='$class'>{$row['valeur_mesure']} $unite</td>
                  </tr>";
        }
        ?>
    </table>

</body>
</html>