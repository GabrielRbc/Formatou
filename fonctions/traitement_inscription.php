<?php
session_start();
require_once 'config.php';

if (!isset($_POST['nom'], $_POST['prenom'], $_POST['email'], $_POST['motdepasse'], $_POST['date_naissance'])) {
    $_SESSION['message'] = "Veuillez remplir tous les champs.";
    header("Location: ../connexion.php");
    exit;
}

$nom = trim($_POST['nom']);
$prenom = trim($_POST['prenom']);
$email = strtolower(trim($_POST['email']));
$telephone = trim($_POST['telephone']);
$adresse = trim($_POST['adresse']);
$codePostal = trim($_POST['codePostal']);
$ville = trim($_POST['ville']);
$date_naissance = $_POST['date_naissance'];

$mdp = $_POST['motdepasse'];

if (
    strlen($mdp) < 8 ||
    !preg_match('/[A-Z]/', $mdp) ||
    !preg_match('/[a-z]/', $mdp) ||
    !preg_match('/[0-9]/', $mdp) ||
    !preg_match('/[^A-Za-z0-9]/', $mdp)
) {
    $_SESSION['message'] = "Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.";
    header("Location: ../connexion.php");
    exit;
}

$motdepasse = password_hash($mdp, PASSWORD_DEFAULT);

// Calcul âge
$age = (int)date_diff(date_create($date_naissance), date_create('today'))->y;

try {
    $check = $pdo->prepare("SELECT id_client FROM clients WHERE email = ?");
    $check->execute([$email]);

    if ($check->fetch()) {
        $_SESSION['message'] = "Un compte avec cet email existe déjà.";
        header("Location: ../connexion.php");
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO clients (nom, prenom, date_naissance, email, motdepasse, telephone, adresse, code_postal, ville, type_compte)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'client')
    ");
    $stmt->execute([$nom, $prenom, $date_naissance, $email, $motdepasse, $telephone, $adresse, $codePostal, $ville]);
    $id_client = $pdo->lastInsertId();

    $stmtConsent = $pdo->prepare("
        INSERT INTO consentements_clients (id_client)
        VALUES (?)
    ");
    $stmtConsent->execute([$id_client]);

    if ($age < 15) {
        $stmt = $pdo->prepare("
            INSERT INTO responsables_legaux (id_client, nom, prenom, telephone, email, lien_parente)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $id_client,
            trim($_POST['nom_responsable']),
            trim($_POST['prenom_responsable']),
            trim($_POST['telephone_responsable']),
            trim($_POST['email_responsable']),
            trim($_POST['lien_parente'])
        ]);
    }

    $_SESSION['id_client'] = $id_client;
    $_SESSION['nom'] = $nom;
    $_SESSION['prenom'] = $prenom;
    $_SESSION['type'] = 'client';

    $_SESSION['message'] = ($age < 15)
        ? "Inscription enregistrée avec le responsable légal."
        : "Inscription réussie !";

    header("Location: ../index.php");
    exit;

} catch (PDOException $e) {
    $_SESSION['message'] = "Erreur : " . $e->getMessage();
    header("Location: ../connexion.php");
    exit;
}
