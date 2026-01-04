<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['id_client'])) {
    $_SESSION['message'] = "Vous devez être connecté.";
    header("Location: ../connexion.php");
    exit;
}

if (!isset($_POST['id_carte'])) {
    $_SESSION['message'] = "Carte invalide.";
    header("Location: ../profil.php");
    exit;
}

$id_client = (int)$_SESSION['id_client'];
$id_carte = (int)$_POST['id_carte'];

$stmt = $pdo->prepare("DELETE FROM cartes_bancaires WHERE id_carte = ? AND id_client = ?");
$stmt->execute([$id_carte, $id_client]);

$_SESSION['message'] = "Le moyen de paiement a été supprimé.";
header("Location: ../profil.php");
exit;