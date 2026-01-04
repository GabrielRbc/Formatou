<?php
// profil.php
session_start();
require_once 'fonctions/config.php';

// Vérifier connexion
if (!isset($_SESSION['id_client'])) {
    // soit rediriger vers la connexion, soit afficher invité
    header("Location: connexion.php");
    exit;
}

$id_client = (int)$_SESSION['id_client'];

// Récupérer les inscriptions de l'utilisateur avec infos formation + disponibilité
$stmt = $pdo->prepare("
    SELECT i.id_inscription, i.date_inscription, i.statut,
           d.id_disponibilite, d.lieu, d.date_debut, d.date_fin,
           d.nb_places_total, d.nb_places_dispo,
           f.id_formation, f.nom_formation, f.image_url
    FROM inscriptions i
    JOIN disponibilites d ON i.id_disponibilite = d.id_disponibilite
    JOIN formations f ON d.id_formation = f.id_formation
    WHERE i.id_client = ?
    ORDER BY d.date_debut ASC
");
$stmt->execute([$id_client]);
$inscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Séparer en futurs et passés
$aujourdhui = new DateTimeImmutable('now');
$inscriptions_futures = [];
$inscriptions_passees = [];

foreach ($inscriptions as $ins) {
    $debut = new DateTimeImmutable($ins['date_debut']);
    if ($debut >= $aujourdhui) {
        $inscriptions_futures[] = $ins;
    } else {
        $inscriptions_passees[] = $ins;
    }
}

// Récupérer les infos utilisateur
$stmt_user = $pdo->prepare("SELECT nom, prenom, date_naissance, email, telephone, adresse, code_postal, ville FROM clients WHERE id_client = ?");
$stmt_user->execute([$id_client]);
$user_info = $stmt_user->fetch(PDO::FETCH_ASSOC);

// Récupérer les cartes bancaires enregistrées
$stmt_cartes = $pdo->prepare("SELECT id_carte, numero_carte, date_expiration, nom_carte FROM cartes_bancaires WHERE id_client = ?");
$stmt_cartes->execute([$id_client]);
$cartes = $stmt_cartes->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Mon profil - Formatou</title>
    <link href="style/bootstrap-4.6.2-dist/css/bootstrap.css" rel="stylesheet">
    <link href="style/style.css" rel="stylesheet">
</head>
<body>

<header class="d-flex justify-content-between align-items-center bg-dark text-white p-3">
    <div>
        <h1>Formatou</h1>
    </div>
    <div>
        <span>Bonjour <?= htmlspecialchars($_SESSION['prenom']) ?></span>
        <a href="fonctions/deconnexion.php" class="btn btn-outline-light btn-sm ml-2">Déconnexion</a>
    </div>
</header>

<main class="container my-5">
    <h2>Mon espace</h2>
    <p>Bonjour <strong><?= htmlspecialchars($_SESSION['prenom']) ?></strong> — voici vos inscriptions :</p>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-info"><?= htmlspecialchars($_SESSION['message']) ?></div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <section class="mb-5">
    <h3>Mes informations personnelles</h3>
    <form action="fonctions/update_profil.php" method="POST" class="mb-4">
        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Nom</label>
                <input type="text" class="form-control" name="nom" value="<?= htmlspecialchars($user_info['nom']) ?>" required>
            </div>
            <div class="form-group col-md-6">
                <label>Prénom</label>
                <input type="text" class="form-control" name="prenom" value="<?= htmlspecialchars($user_info['prenom']) ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user_info['email']) ?>" required>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Téléphone</label>
                <input type="tel" class="form-control" name="telephone" value="<?= htmlspecialchars($user_info['telephone']) ?>">
            </div>
            <div class="form-group col-md-6">
                <label>Date de naissance</label>
                <input type="date" class="form-control" name="date_naissance"
                    value="<?= htmlspecialchars($user_info['date_naissance'])?>" readonly>
            </div>
        </div>

        <div class="form-group">
            <label>Adresse</label>
            <input type="text" class="form-control" name="adresse" value="<?= htmlspecialchars($user_info['adresse']) ?>">
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Code postal</label>
                <input type="text" class="form-control" name="code_postal" value="<?= htmlspecialchars($user_info['code_postal']) ?>">
            </div>
            <div class="form-group col-md-6">
                <label>Ville</label>
                <input type="text" class="form-control" name="ville" value="<?= htmlspecialchars($user_info['ville']) ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Nouveau mot de passe (laisser vide pour garder l'actuel)</label>
            <input type="password" class="form-control" name="motdepasse">
        </div>

        <?php
        $age = date_diff(date_create($user_info['date_naissance']), date_create('today'))->y;
        if ($age < 15):
            $stmt_resp = $pdo->prepare("SELECT * FROM responsables_legaux WHERE id_client = ?");
            $stmt_resp->execute([$id_client]);
            $resp = $stmt_resp->fetch(PDO::FETCH_ASSOC);
            if ($resp):
        ?>
            <h4>Responsable légal</h4>
            <ul>
                <li><strong>Nom :</strong> <?= htmlspecialchars($resp['nom']) ?> <?= htmlspecialchars($resp['prenom']) ?></li>
                <li><strong>Email :</strong> <?= htmlspecialchars($resp['email']) ?></li>
                <li><strong>Téléphone :</strong> <?= htmlspecialchars($resp['telephone']) ?></li>
                <li><strong>Lien de parenté :</strong> <?= htmlspecialchars($resp['lien_parente']) ?></li>
            </ul>
        <?php endif; endif; ?>


        <button type="submit" class="btn btn-primary">Mettre à jour</button>
        <button type="submit" class="btn btn-primary"> Supprimer le compte</button>
    </form>
    </section>

    <section class="mb-5">
    <h3>Moyens de paiement enregistrés</h3>

    <?php if (count($cartes) === 0): ?>
        <p>Aucun moyen de paiement enregistré.</p>
    <?php else: ?>
        <ul class="list-group">
            <?php foreach ($cartes as $carte): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong><?= htmlspecialchars($carte['nom_carte']) ?></strong> — 
                        **** **** **** <?= substr($carte['numero_carte'], -4) ?> 
                        (exp: <?= htmlspecialchars($carte['date_expiration']) ?>)
                    </div>
                    <form method="POST" action="fonctions/supprimer_carte.php" onsubmit="return confirm('Supprimer ce moyen de paiement ?');">
                        <input type="hidden" name="id_carte" value="<?= (int)$carte['id_carte'] ?>">
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            🗑️
                        </button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    </section>

    <section class="mb-5">
        <h3>Inscriptions à venir</h3>
        <?php if (count($inscriptions_futures) === 0): ?>
            <p>Aucune inscription à venir.</p>
        <?php else: ?>
            <div class="list-group">
                <?php foreach ($inscriptions_futures as $ins): ?>
                    <div class="list-group-item">
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <?php if (!empty($ins['image_url'])): ?>
                                    <img src="<?= htmlspecialchars($ins['image_url']) ?>" alt="img" class="img-fluid rounded">
                                <?php endif; ?>
                            </div>
                            <div class="col-md-7">
                                <h5><?= htmlspecialchars($ins['nom_formation']) ?></h5>
                                <p class="mb-1"><strong>Lieu :</strong> <?= htmlspecialchars($ins['lieu']) ?></p>
                                <p class="mb-1">
                                    <strong>Dates :</strong>
                                    <?= date('d/m/Y H:i', strtotime($ins['date_debut'])) ?>
                                    — <?= date('d/m/Y H:i', strtotime($ins['date_fin'])) ?>
                                </p>
                                <p class="mb-1"><strong>Statut :</strong> <?= htmlspecialchars($ins['statut']) ?></p>
                                <small class="text-muted">Inscrit le <?= date('d/m/Y H:i', strtotime($ins['date_inscription'])) ?></small>
                            </div>
                            <div class="col-md-3 text-right">
                                <?php if ($ins['statut'] !== 'confirmée'): ?>

                                    <form method="GET" action="payer.php" style="display:inline;">
                                        <input type="hidden" name="id_inscription" value="<?= (int)$ins['id_inscription'] ?>">
                                        <button type="submit" class="btn btn-success btn-sm">Payer maintenant</button>
                                    </form>

                                    <form method="POST" action="fonctions/annuler_inscription.php" onsubmit="return confirm('Confirmer l\\'annulation de cette inscription ?');">
                                        <input type="hidden" name="id_inscription" value="<?= (int)$ins['id_inscription'] ?>">
                                        <button type="submit" class="btn btn-danger">Annuler</button>
                                    </form>

                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section>
        <h3>Inscriptions passées</h3>
        <?php if (count($inscriptions_passees) === 0): ?>
            <p>Aucune inscription passée.</p>
        <?php else: ?>
            <div class="list-group">
                <?php foreach ($inscriptions_passees as $ins): ?>
                    <div class="list-group-item">
                        <div class="row">
                            <div class="col-md-2">
                                <?php if (!empty($ins['image_url'])): ?>
                                    <img src="<?= htmlspecialchars($ins['image_url']) ?>" alt="img" class="img-fluid rounded">
                                <?php endif; ?>
                            </div>
                            <div class="col-md-10">
                                <h5><?= htmlspecialchars($ins['nom_formation']) ?></h5>
                                <p class="mb-1"><strong>Lieu :</strong> <?= htmlspecialchars($ins['lieu']) ?></p>
                                <p class="mb-1">
                                    <strong>Dates :</strong>
                                    <?= date('d/m/Y H:i', strtotime($ins['date_debut'])) ?>
                                    — <?= date('d/m/Y H:i', strtotime($ins['date_fin'])) ?>
                                </p>
                                <p class="mb-1"><strong>Statut :</strong> <?= htmlspecialchars($ins['statut']) ?></p>
                                <small class="text-muted">Inscrit le <?= date('d/m/Y H:i', strtotime($ins['date_inscription'])) ?></small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <div class="mt-4">
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
