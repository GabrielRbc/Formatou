<?php
session_start();
require_once 'config.php';

// Vérifier que l'utilisateur est bien un administrateur
if (!isset($_SESSION['id_client']) || $_SESSION['type'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$id_admin = (int)$_SESSION['id_client']; // L'admin est aussi dans la table clients
$id_message = (int)($_POST['id_message'] ?? 0);
$reponse = trim($_POST['reponse'] ?? '');
$statut = $_POST['statut'] ?? 'en_cours';

if ($id_message <= 0 || $reponse === '') {
    $_SESSION['message'] = "Le message ne peut pas être vide.";
    header("Location: ../admin_contact_view.php?id=$id_message");
    exit;
}

try {
    $pdo->beginTransaction();

    // Insérer la réponse
    $stmt = $pdo->prepare("
        INSERT INTO reponses_contact (id_message, id_admin, reponse)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$id_message, $id_admin, $reponse]);

    // Mettre à jour le statut du message
    $stmt = $pdo->prepare("
        UPDATE messages_contact
        SET statut = ?
        WHERE id_message = ?
    ");
    $stmt->execute([$statut, $id_message]);

    $pdo->commit();

    $_SESSION['message'] = "Réponse envoyée avec succès.";
    header("Location: ../admin_contact_view.php?id=$id_message");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['message'] = "Erreur : " . $e->getMessage();
    header("Location: ../admin_contact_view.php?id=$id_message");
    exit;
}
