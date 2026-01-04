<?php
session_start();
require_once 'fonctions/config.php';

if (!isset($_SESSION['id_client'])) {
    header("Location: connexion.php");
    exit;
}

$id_client = (int)$_SESSION['id_client'];
$id_inscription = (int)$_GET['id_inscription'] ?? 0;

// Récupérer les cartes existantes du client
$stmt = $pdo->prepare("SELECT * FROM cartes_bancaires WHERE id_client = ?");
$stmt->execute([$id_client]);
$cartes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Paiement - Formatou</title>
<link href="style/bootstrap-4.6.2-dist/css/bootstrap.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">

<h2>Paiement de votre inscription</h2>

<form method="POST" action="fonctions/traitement_paiement.php">
    <input type="hidden" name="id_inscription" value="<?= $id_inscription ?>">

    <?php if (count($cartes) > 0): ?>
        <div class="form-group">
            <label>Sélectionner un moyen de paiement existant</label>
            <select class="form-control" name="id_carte" id="id_carte" onchange="remplirCarte()">
                <option value="">-- Nouvelle carte --</option>
                <?php foreach ($cartes as $c): ?>
                    <option value="<?= $c['id_carte'] ?>"
                        data-num="<?= $c['numero_carte'] ?>"
                        data-date="<?= $c['date_expiration'] ?>"
                        data-cvv="<?= $c['cvv'] ?>"
                        data-nom="<?= htmlspecialchars($c['nom_titulaire']) ?>">
                        Carte se terminant par <?= substr($c['numero_carte'], -4) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    <?php endif; ?>

    <div class="form-group">
        <label>Numéro de carte</label>
        <input type="text" class="form-control" name="numero_carte" id="numero_carte" maxlength="16" required>
    </div>
    <div class="form-row">
        <div class="form-group col-md-4">
            <label>Date d'expiration (MM/AA)</label>
            <input type="text" class="form-control" name="date_expiration" id="date_expiration" maxlength="5" required>
        </div>
        <div class="form-group col-md-4">
            <label>CVV</label>
            <input type="text" class="form-control" name="cvv" id="cvv" maxlength="3" required>
        </div>
        <div class="form-group col-md-4">
            <label>Nom du titulaire</label>
            <input type="text" class="form-control" name="nom_titulaire" id="nom_titulaire" required>
        </div>
    </div>

    <div class="form-check mb-3">
        <input type="checkbox" class="form-check-input" name="enregistrer_carte" id="enregistrer_carte">
        <label for="enregistrer_carte">Enregistrer ce moyen de paiement pour la prochaine fois</label>
    </div>

    <div class="form-check mb-3">
        <input type="checkbox" class="form-check-input" id="consentement" required>
        <label for="consentement">Je reconnais avoir pris connaissance des <a href='cgv.php'>Conditions générales de vente</a> et les accepter.</label>
    </div>

    <button type="submit" class="btn btn-success">Valider le paiement</button>
    <a href="profil.php" class="btn btn-secondary">Annuler</a>
</form>

<script src="fonctions/paiement.js"></script>

</div>
</body>
</html>
