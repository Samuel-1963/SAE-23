<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['login'] === 'Gerant' && $_POST['password'] === 'hgvcJB564F*') {
        session_start();
        $_SESSION['gest_connecte'] = true;
        header('Location: gestion.php');
        exit();
    }
    $error = "Accès refusé";
}
?>

<h2>Connexion</h2>
<?= $error ?? '' ?>
<form method="post">
    <input type="text" name="login" placeholder="Login" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <button type="submit">Entrer</button>
</form>