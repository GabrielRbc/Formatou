<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['id_client'])) {
    header("Location: ../connexion.php");
    exit;
}

$id_client = (int)$_SESSION['id_client'];
$sujet = trim($_POST['sujet'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($sujet === '' || $message === '') {
    $_SESSION['message'] = "Veuillez remplir tous les champs.";
    header("Location: ../contact.php");
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO messages_contact (id_client, sujet, message) VALUES (?, ?, ?)");
    $stmt->execute([$id_client, $sujet, $message]);

    $_SESSION['message'] = "Votre message a bien été envoyé. Nous reviendrons vers vous rapidement.";
    header("Location: ../contact.php");
    exit;
} catch (PDOException $e) {
    $_SESSION['message'] = "Erreur lors de l’envoi : " . $e->getMessage();
    header("Location: ../contact.php");
    exit;
}
