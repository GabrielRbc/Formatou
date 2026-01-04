<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['id_client'])) {
    $_SESSION['message'] = "Vous devez être connecté pour vous inscrire.";
    header("Location: ../connexion.php");
    exit;
}

if (!isset($_POST['id_disponibilite'])) {
    header("Location: ../index.php");
    exit;
}

$id_client = (int)$_SESSION['id_client'];
$id_dispo = (int)$_POST['id_disponibilite'];

try {
    $pdo->beginTransaction();

    // Vérifier disponibilité
    $stmt = $pdo->prepare("SELECT nb_places_dispo FROM disponibilites WHERE id_disponibilite = ? FOR UPDATE");
    $stmt->execute([$id_dispo]);
    $dispo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$dispo) {
        throw new Exception("Cette session n'existe pas.");
    }

    if ($dispo['nb_places_dispo'] <= 0) {
        throw new Exception("Plus de places disponibles pour cette session.");
    }

    // Vérifier si déjà inscrit
    $stmt = $pdo->prepare("SELECT id_inscription FROM inscriptions WHERE id_client = ? AND id_disponibilite = ?");
    $stmt->execute([$id_client, $id_dispo]);
    if ($stmt->fetch()) {
        throw new Exception("Vous êtes déjà inscrit à cette session !");
    }

    // Inscrire l'utilisateur
    $stmt = $pdo->prepare("INSERT INTO inscriptions (id_client, id_disponibilite, date_inscription, statut) VALUES (?, ?, NOW(), 'en_attente')");
    $stmt->execute([$id_client, $id_dispo]);

    // Décrémenter le nombre de places
    $stmt = $pdo->prepare("UPDATE disponibilites SET nb_places_dispo = nb_places_dispo - 1 WHERE id_disponibilite = ?");
    $stmt->execute([$id_dispo]);

    $pdo->commit();

    $_SESSION['message'] = "Inscription réussie !";
    header("Location: ../profil.php");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['message'] = "Erreur : " . $e->getMessage();
    // Redirection vers la page de la formation
    header("Location: ../disponibilites.php?id=" . $id_dispo);
    exit;
}
