<?php
$servername = "localhost";
$username = "guerin";
$password = "passroot";
$dbname = "sae23";

// BDD connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieves POST data
$nom_cap = isset($_POST['nom_cap']) ? $_POST['nom_cap'] : null;
$valeur_mesure = isset($_POST['valeur_mesure']) ? $_POST['valeur_mesure'] : null;

if ($nom_cap === null || $valeur_mesure === null) {
    http_response_code(400);
    echo json_encode(array("error" => "Missing parameters"));
    exit;
}

// Request
$stmt = $conn->prepare("INSERT INTO Mesure (date_mesure, horaire_mesure, valeur_mesure, nom_cap) VALUES (CURDATE(), CURTIME(), ?, ?)");
$stmt->bind_param("is", $valeur_mesure, $nom_cap);

if ($stmt->execute()) {
    echo json_encode(array("success" => true));
} else {
    http_response_code(500);
    echo json_encode(array("error" => "Insert failed"));
}

$stmt->close();
$conn->close();
?>
