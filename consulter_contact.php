<?php
session_start();
require_once 'fonctions/config.php';

// Vérifier la connexion client
if (!isset($_SESSION['id_client'])) {
    header("Location: connexion.php");
    exit;
}

$id_client = (int)$_SESSION['id_client'];
$id_message = (int)($_GET['id'] ?? 0);

// Vérifier que le message appartient bien à ce client
$stmt = $pdo->prepare("
    SELECT * FROM messages_contact 
    WHERE id_message = ? AND id_client = ?
");
$stmt->execute([$id_message, $id_client]);
$message = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$message) {
    die("Message introuvable ou accès non autorisé.");
}

// Récupérer les réponses du support
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
    <title>Votre demande au support - Formatou</title>
    <link href="style/bootstrap-4.6.2-dist/css/bootstrap.css" rel="stylesheet">
</head>
<body>

<header class="bg-dark text-white text-center py-4">
    <h1>Support Formatou</h1>
    <p>Suivi de votre demande</p>
</header>

<div class="container my-5">
    <a href="contact.php" class="btn btn-secondary mb-3">⬅ Retour à mes messages</a>

    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><?= htmlspecialchars($message['sujet']) ?></h4>
        </div>
        <div class="card-body">
            <p><strong>Date d’envoi :</strong> <?= date('d/m/Y H:i', strtotime($message['date_envoi'])) ?></p>
            <p><strong>Statut :</strong> 
                <span class="badge badge-<?= $message['statut'] === 'résolu' ? 'success' : ($message['statut'] === 'en_cours' ? 'warning' : 'secondary') ?>">
                    <?= htmlspecialchars($message['statut']) ?>
                </span>
            </p>
            <hr>
            <p><?= nl2br(htmlspecialchars($message['message'])) ?></p>
        </div>
    </div>

    <h5>Réponses du support</h5>
    <?php if (count($reponses) === 0): ?>
        <p>Aucune réponse pour le moment. Le support reviendra vers vous dès que possible.</p>
    <?php else: ?>
        <ul class="list-group mb-4">
            <?php foreach ($reponses as $rep): ?>
                <li class="list-group-item">
                    <strong><?= htmlspecialchars($rep['prenom_admin'] . ' ' . $rep['nom_admin']) ?></strong>
                    <small class="text-muted float-right">
                        <?= date('d/m/Y H:i', strtotime($rep['date_reponse'])) ?>
                    </small>
                    <p class="mt-2 mb-0"><?= nl2br(htmlspecialchars($rep['reponse'])) ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <a href="profil.php" class="btn btn-outline-primary">Retour à mon profil</a>
</div>

<footer class="bg-dark text-white text-center py-3">
    <p>
        <a href="mentions_legales.php" class="text-white">Mentions Légales</a> -
        <a href="confidentialite.php" class="text-white">Politique de confidentialité</a> -
        <a href="cgv.php" class="text-white">CGV</a> -
        <a href="contact.php" class="text-white">Contacter le support</a>
    </p>
    <p>© Formatou - 2025</p>
</footer>

</body>
</html>
