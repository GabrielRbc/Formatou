<?php
session_start();
require_once 'fonctions/config.php';

// Vérification de l'ID formation passé en paramètre
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Erreur : formation invalide.");
}

$idFormation = (int)$_GET['id'];

// Récupération des infos de la formation
$stmt = $pdo->prepare("
    SELECT f.*, c.nom_categorie 
    FROM formations f
    JOIN categories_formations c ON f.id_categorie = c.id_categorie
    WHERE f.id_formation = ?
");
$stmt->execute([$idFormation]);
$formation = $stmt->fetch();

if (!$formation) {
    die("Formation introuvable.");
}

// Récupération des disponibilités associées
$stmtDisp = $pdo->prepare("
    SELECT * FROM disponibilites 
    WHERE id_formation = ? 
    ORDER BY date_debut ASC
");
$stmtDisp->execute([$idFormation]);
$disponibilites = $stmtDisp->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($formation['nom_formation']) ?> - Formatou</title>
    <link href="style/bootstrap-4.6.2-dist/css/bootstrap.css" rel="stylesheet">
    <link href="style/style.css" rel="stylesheet">
</head>
<body>

<header class="text-center bg-dark text-white py-4">
    <h1><?= htmlspecialchars($formation['nom_formation']) ?></h1>
    <p>Catégorie : <?= htmlspecialchars($formation['nom_categorie']) ?></p>
</header>

<main class="container mt-4 mb-5">

    <div class="row">
        <div class="col-md-4">
            <?php if (!empty($formation['image_url'])): ?>
                <img src="<?= htmlspecialchars($formation['image_url']) ?>" alt="Image formation" class="img-fluid rounded shadow-sm mb-3">
            <?php endif; ?>
        </div>

        <div class="col-md-8">
            <h3>Description</h3>
            <p><?= nl2br(htmlspecialchars($formation['description'])) ?></p>
            <p><strong>Durée :</strong> <?= htmlspecialchars($formation['duree']) ?> | <strong>Prix :</strong> <?= htmlspecialchars($formation['prix']) ?>€</p>
        </div>
    </div>

    <hr>

    <h3 class="mt-5 mb-3 text-center">Disponibilités</h3>

    <?php if (count($disponibilites) > 0): ?>
        <table class="table table-striped table-bordered text-center">
            <thead class="thead-dark">
                <tr>
                    <th>Lieu</th>
                    <th>Date début</th>
                    <th>Date fin</th>
                    <th>Places disponibles</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($disponibilites as $disp): ?>
                <tr>
                    <td><?= htmlspecialchars($disp['lieu']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($disp['date_debut'])) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($disp['date_fin'])) ?></td>
                    <td><?= htmlspecialchars($disp['nb_places_dispo']) ?> / <?= htmlspecialchars($disp['nb_places_total']) ?></td>
                    <td>
                        <?php if ($disp['nb_places_dispo'] > 0): ?>
                            <form action="fonctions/inscrire.php" method="POST" style="display:inline;">
                                <input type="hidden" name="id_disponibilite" value="<?= $disp['id_disponibilite'] ?>">
                                <button type="submit" class="btn btn-primary btn-sm">S'inscrire</button>
                            </form>
                        <?php else: ?>
                            <span class="text-danger">Complet</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-center">Aucune disponibilité pour le moment.</p>
    <?php endif; ?>

    <div class="text-center mt-4">
        <a href="index.php" class="btn btn-secondary">← Retour aux formations</a>
    </div>
</main>

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
