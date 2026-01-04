<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['id_client'])) {
    $_SESSION['message'] = "Vous devez être connecté pour annuler une inscription.";
    header("Location: ../connexion.php");
    exit;
}

if (!isset($_POST['id_inscription'])) {
    $_SESSION['message'] = "Inscription invalide.";
    header("Location: ../profil.php");
    exit;
}

$id_client = (int)$_SESSION['id_client'];
$id_inscription = (int)$_POST['id_inscription'];

try {
    $pdo->beginTransaction();

    // Vérifier que l'inscription appartient bien à l'utilisateur et récupérer l'id_disponibilite
    $stmt = $pdo->prepare("SELECT id_disponibilite FROM inscriptions WHERE id_inscription = ? AND id_client = ? FOR UPDATE");
    $stmt->execute([$id_inscription, $id_client]);
    $inscription = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$inscription) {
        throw new Exception("Inscription introuvable.");
    }

    $id_dispo = (int)$inscription['id_disponibilite'];

    // Remettre la place disponible
    $stmt = $pdo->prepare("UPDATE disponibilites SET nb_places_dispo = nb_places_dispo + 1 WHERE id_disponibilite = ?");
    $stmt->execute([$id_dispo]);

    // Supprimer l'inscription
    $stmt = $pdo->prepare("DELETE FROM inscriptions WHERE id_inscription = ?");
    $stmt->execute([$id_inscription]);

    $pdo->commit();

    $_SESSION['message'] = "Votre inscription a été supprimée avec succès.";
    header("Location: ../profil.php");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['message'] = "Erreur : " . $e->getMessage();
    header("Location: ../profil.php");
    exit;
}
?>
