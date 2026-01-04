<?php
session_start();
require_once 'config.php';

if (!isset($_POST['email'], $_POST['motdepasse'])) {
    $_SESSION['message'] = "Veuillez saisir vos identifiants.";
    header("Location: ../connexion.php");
    exit;
}

$email = strtolower(trim($_POST['email']));
$motdepasse = $_POST['motdepasse'];

$stmt = $pdo->prepare("SELECT * FROM clients WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user && (
        password_verify($motdepasse, $user['motdepasse']) ||
        $motdepasse === $user['motdepasse'] // Permet connexion avec anciens comptes non hashés
    )) {
    $_SESSION['id_client'] = $user['id_client'];
    $_SESSION['nom'] = $user['nom'];
    $_SESSION['prenom'] = $user['prenom'];
    $_SESSION['type'] = $user['type_compte'];
    $_SESSION['utilisateur'] = $user['email'];

    header("Location: ../index.php");
    exit;
} else {
    $_SESSION['message'] = "Identifiants incorrects.";
    header("Location: ../connexion.php");
    exit;
}
?>
