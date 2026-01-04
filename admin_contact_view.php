<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'fonctions/config.php';

if (!isset($_SESSION['type']) || $_SESSION['type'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$id_message = (int)($_GET['id'] ?? 0);

// Récupération du message
$stmt = $pdo->prepare("
    SELECT m.*, c.nom, c.prenom, c.email 
    FROM messages_contact m
    JOIN clients c ON m.id_client = c.id_client
    WHERE m.id_message = ?
");
$stmt->execute([$id_message]);
$message = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$message) {
    die("Message introuvable.");
}

// Récupération des réponses précédentes
$stmt = $pdo->prepare("
    SELECT r.*, c.nom AS nom_admin, c.prenom AS prenom_admin
    FROM reponses_contact r
    JOIN clients c ON r.id_admin = c.id_client
    WHERE r.id_message = ?
    ORDER BY r.date_reponse ASC
");

$stmt->execute([$id_message]);
$reponses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Répondre au message - Support</title>
<link href="style/bootstrap-4.6.2-dist/css/bootstrap.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <a href="admin_contact.php" class="btn btn-secondary mb-3">⬅ Retour</a>
    <h3><?= htmlspecialchars($message['sujet']) ?></h3>
    <p><strong>De :</strong> <?= htmlspecialchars($message['prenom'] . ' ' . $message['nom']) ?> (<?= htmlspecialchars($message['email']) ?>)</p>
    <p><strong>Message :</strong></p>
    <div class="border p-3 mb-4 bg-light"><?= nl2br(htmlspecialchars($message['message'])) ?></div>

    <h5>Réponses du support</h5>
    <?php if (count($reponses) === 0): ?>
        <p>Aucune réponse pour le moment.</p>
    <?php else: ?>
        <ul class="list-group mb-4">
            <?php foreach ($reponses as $r): ?>
                <li class="list-group-item">
                    <strong><?= htmlspecialchars($r['nom_admin']) ?></strong> — 
                    <small><?= date('d/m/Y H:i', strtotime($r['date_reponse'])) ?></small><br>
                    <?= nl2br(htmlspecialchars($r['reponse'])) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="POST" action="fonctions/traitement_reponse_contact.php">
        <input type="hidden" name="id_message" value="<?= $id_message ?>">
        <div class="form-group">
            <label>Réponse du support</label>
            <textarea name="reponse" class="form-control" rows="4" required></textarea>
        </div>
        <div class="form-group">
            <label>Statut du message</label>
            <select name="statut" class="form-control">
                <option value="en_cours" <?= $message['statut'] === 'en_cours' ? 'selected' : '' ?>>En cours</option>
                <option value="résolu" <?= $message['statut'] === 'résolu' ? 'selected' : '' ?>>Résolu</option>
            </select>
        </div>
        <button type="submit" class="btn btn-success">Envoyer la réponse</button>
    </form>
</div>
</body>
</html>
