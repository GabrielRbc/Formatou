<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['id_client'])) {
    header("Location: ../connexion.php");
    exit;
}

if (!isset($_POST['id_inscription'])) {
    $_SESSION['message'] = "Inscription invalide.";
    header("Location: ../profil.php");
    exit;
}

$id_inscription = (int)$_POST['id_inscription'];
$id_client = (int)$_SESSION['id_client'];

// Récupération des données bancaires
$numero = preg_replace('/\D/', '', $_POST['numero_carte']); // garder que les chiffres
$date_exp = $_POST['date_expiration'];
$cvc = $_POST['cvv'];
$nom_carte = trim($_POST['nom_titulaire']);
$enregistrer = isset($_POST['enregistrer_carte']);
$mode = 'Carte Bancaire';

try {
    $pdo->beginTransaction();

    // Vérifier que l'inscription existe et est en_attente
    $stmt = $pdo->prepare("SELECT * FROM inscriptions WHERE id_inscription = ? AND id_client = ? FOR UPDATE");
    $stmt->execute([$id_inscription, $id_client]);
    $inscription = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$inscription || $inscription['statut'] !== 'en_attente') {
        throw new Exception("Inscription invalide pour paiement.");
    }

    // Récupérer le montant de la formation associée à cette inscription
    $stmt = $pdo->prepare("
        SELECT f.prix
        FROM inscriptions i
        JOIN disponibilites d ON i.id_disponibilite = d.id_disponibilite
        JOIN formations f ON d.id_formation = f.id_formation
        WHERE i.id_inscription = ? AND i.id_client = ?
    ");
    $stmt->execute([$id_inscription, $id_client]);
    $prix_info = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$prix_info) {
        throw new Exception("Impossible de récupérer le prix de la formation.");
    }

    $montant = (float)$prix_info['prix'];

    // Optionnel : enregistrer la carte bancaire pour réutilisation
    if ($enregistrer) {
    // Vérifier si cette carte existe déjà pour le client
    $stmt = $pdo->prepare("
        SELECT id_carte 
        FROM cartes_bancaires 
        WHERE id_client = ? AND numero_carte = ? AND date_expiration = ? AND nom_carte = ?
    ");
    $stmt->execute([$id_client, $numero, $date_exp, $nom_carte]);
    $carte_existante = $stmt->fetch();

    if (!$carte_existante) {
        // Si elle n'existe pas, on l'insère
        $stmt = $pdo->prepare("
            INSERT INTO cartes_bancaires (id_client, numero_carte, date_expiration, cvc, nom_carte)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$id_client, $numero, $date_exp, $cvc, $nom_carte]);
    }

    $stmtConsent = $pdo->prepare("
        INSERT INTO consentements_clients (id_client)
        VALUES (?)
    ");
    $stmtConsent->execute([$id_client]);
}

    // Enregistrer le paiement
    $stmt = $pdo->prepare("INSERT INTO paiements (id_inscription, montant, mode, statut) VALUES (?, ?, ?, 'payé')");
    $stmt->execute([$id_inscription, $montant, $mode]);

    // Mettre à jour l'inscription
    $stmt = $pdo->prepare("UPDATE inscriptions SET statut = 'confirmée' WHERE id_inscription = ?");
    $stmt->execute([$id_inscription]);

    $pdo->commit();

    $_SESSION['message'] = "Paiement effectué et inscription confirmée !";
    header("Location: ../profil.php");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['message'] = "Erreur : " . $e->getMessage();
    header("Location: ../payer.php?id_inscription=" . $id_inscription);
    exit;
}
?>
