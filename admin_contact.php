<?php
session_start();
require_once 'fonctions/config.php';

if (!isset($_SESSION['type']) || $_SESSION['type'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Filtre éventuel par statut
$filtre = $_GET['filtre'] ?? 'tous';
$sql = "SELECT m.*, c.nom, c.prenom, c.email 
        FROM messages_contact m 
        JOIN clients c ON m.id_client = c.id_client";

if ($filtre !== 'tous') {
    $sql .= " WHERE m.statut = :filtre";
}
$sql .= " ORDER BY m.date_envoi DESC";

$stmt = $pdo->prepare($sql);
if ($filtre !== 'tous') {
    $stmt->bindValue(':filtre', $filtre);
}
$stmt->execute();
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Support client - Administration</title>
<link href="style/bootstrap-4.6.2-dist/css/bootstrap.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Gestion du support client</h2>
    <div class="my-3">
        <a href="?filtre=tous" class="btn btn-outline-secondary btn-sm">Tous</a>
        <a href="?filtre=non_lu" class="btn btn-outline-danger btn-sm">Non lus</a>
        <a href="?filtre=en_cours" class="btn btn-outline-warning btn-sm">En cours</a>
        <a href="?filtre=résolu" class="btn btn-outline-success btn-sm">Résolus</a>
    </div>

    <table class="table table-striped table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>Client</th>
                <th>Sujet</th>
                <th>Message</th>
                <th>Date</th>
                <th>Statut</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($messages as $msg): ?>
            <tr>
                <td><?= htmlspecialchars($msg['prenom'] . ' ' . $msg['nom']) ?><br>
                    <small><?= htmlspecialchars($msg['email']) ?></small>
                </td>
                <td><?= htmlspecialchars($msg['sujet']) ?></td>
                <td><?= nl2br(htmlspecialchars(substr($msg['message'], 0, 80))) ?>...</td>
                <td><?= date('d/m/Y H:i', strtotime($msg['date_envoi'])) ?></td>
                <td>
                    <span class="badge badge-<?= $msg['statut'] === 'résolu' ? 'success' : ($msg['statut'] === 'en_cours' ? 'warning' : 'secondary') ?>">
                        <?= htmlspecialchars($msg['statut']) ?>
                    </span>
                </td>
                <td>
                    <a href="admin_contact_view.php?id=<?= $msg['id_message'] ?>" class="btn btn-user btn-sm">Ouvrir</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="index.php" class="btn btn-secondary">Retour au site</a>
</div>
</body>
</html>
