<?php
session_start();
require_once 'fonctions/config.php';

if (!isset($_SESSION['id_client'])) {
    header("Location: connexion.php");
    exit;
}

$id_client = (int)$_SESSION['id_client'];

// Récupérer les messages précédents de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM messages_contact WHERE id_client = ? ORDER BY date_envoi DESC");
$stmt->execute([$id_client]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Contact Support - Formatou</title>
    <link href="style/bootstrap-4.6.2-dist/css/bootstrap.css" rel="stylesheet">
</head>
<body>

<header class="bg-dark text-white text-center py-4">
    <h1>Contacter le support</h1>
    <p>Besoin d’aide ? Nous sommes là pour vous accompagner.</p>
</header>

<div class="container my-5">
    <div class="row">
        <div class="col-md-8 mx-auto bg-white p-4 rounded shadow-sm">
            <h3>Envoyer un message au support</h3>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-info"><?= htmlspecialchars($_SESSION['message']) ?></div>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>

            <form method="POST" action="fonctions/traitement_contact.php">
                <div class="form-group">
                    <label>Sujet</label>
                    <input type="text" name="sujet" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Message</label>
                    <textarea name="message" class="form-control" rows="5" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Envoyer</button>
                <a href="index.php" class="btn btn-secondary">Retour au site</a>
            </form>

            <hr>

            <h4>Vos précédents messages</h4>
            <?php if (count($messages) === 0): ?>
                <p>Aucun message envoyé pour le moment.</p>
            <?php else: ?>
                <ul class="list-group">
                    <?php foreach ($messages as $msg): ?>
                        <li class="list-group-item">
                            <strong><?= htmlspecialchars($msg['sujet']) ?></strong>  
                            <span class="badge badge-<?= $msg['statut'] === 'résolu' ? 'success' : ($msg['statut'] === 'en_cours' ? 'warning' : 'secondary') ?>">
                                <?= htmlspecialchars($msg['statut']) ?>
                            </span>
                            <a href="consulter_contact.php?id=<?= $msg['id_message'] ?>" class="btn btn-primary btn-sm float-right">Consulter</a><br>
                            <small class="text-muted"><?= date('d/m/Y H:i', strtotime($msg['date_envoi'])) ?></small>
                            <p class="mt-2 mb-0"><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
                            
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
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
