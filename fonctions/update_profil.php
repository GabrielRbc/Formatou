<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['id_client'])) {
    header("Location: ../connexion.php");
    exit;
}

$id_client = (int)$_SESSION['id_client'];

// Récupérer et nettoyer les données
$nom = trim($_POST['nom']);
$prenom = trim($_POST['prenom']);
$email = strtolower(trim($_POST['email']));
$telephone = trim($_POST['telephone']);
$adresse = trim($_POST['adresse']);
$code_postal = trim($_POST['code_postal']);
$ville = trim($_POST['ville']);
$motdepasse = trim($_POST['motdepasse']);

try {
    if (!empty($motdepasse)) {
        $hash = password_hash($motdepasse, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE clients SET nom=?, prenom=?, email=?, telephone=?, adresse=?, code_postal=?, ville=?, motdepasse=? WHERE id_client=?");
        $stmt->execute([$nom, $prenom, $email, $telephone, $adresse, $code_postal, $ville, $hash, $id_client]);
    } else {
        $stmt = $pdo->prepare("UPDATE clients SET nom=?, prenom=?, email=?, telephone=?, adresse=?, code_postal=?, ville=? WHERE id_client=?");
        $stmt->execute([$nom, $prenom, $email, $telephone, $adresse, $code_postal, $ville, $id_client]);
    }

    // Mettre à jour les sessions
    $_SESSION['nom'] = $nom;
    $_SESSION['prenom'] = $prenom;
    $_SESSION['utilisateur'] = $email;

    $_SESSION['message'] = "Profil mis à jour avec succès.";
    header("Location: ../profil.php");
    exit;
} catch (PDOException $e) {
    die("Erreur lors de la mise à jour : " . $e->getMessage());
}
