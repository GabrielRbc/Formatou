<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['id_client'])) {
    header("Location: ../connexion.php");
    exit;
}

$id_client = (int)$_SESSION['id_client'];

// Récupérer et nettoyer les données du formulaire
$nom = trim($_POST['nom'] ?? '');
$prenom = trim($_POST['prenom'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$telephone = trim($_POST['telephone'] ?? '');
$adresse = trim($_POST['adresse'] ?? '');
$code_postal = trim($_POST['code_postal'] ?? '');
$ville = trim($_POST['ville'] ?? '');
$motdepasse_actuel = trim($_POST['motdepasse_actuel'] ?? '');
$motdepasse = trim($_POST['motdepasse'] ?? ''); // Nouveau mot de passe (optionnel)
$action = $_POST['action'] ?? 'update'; // update ou delete

try {
    // 🔹 Récupérer le hash du mot de passe actuel depuis la base
    $stmt = $pdo->prepare("SELECT motdepasse FROM clients WHERE id_client = ?");
    $stmt->execute([$id_client]);
    $current_hash = $stmt->fetchColumn();

    // 🔹 Vérifier que le mot de passe actuel est correct
    if (!password_verify($motdepasse_actuel, $current_hash)) {
        $_SESSION['message'] = "Le mot de passe actuel est incorrect.";
        header("Location: ../profil.php");
        exit;
    }

    if ($action === 'update') {
        // 🔹 Si un nouveau mot de passe est renseigné, le valider
        if (!empty($motdepasse)) {
            if (
                strlen($motdepasse) < 8 ||
                !preg_match('/[A-Z]/', $motdepasse) ||
                !preg_match('/[a-z]/', $motdepasse) ||
                !preg_match('/[0-9]/', $motdepasse) ||
                !preg_match('/[^A-Za-z0-9]/', $motdepasse)
            ) {
                $_SESSION['message'] = "Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.";
                header("Location: ../profil.php");
                exit;
            }

            $hash = password_hash($motdepasse, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE clients SET nom=?, prenom=?, email=?, telephone=?, adresse=?, code_postal=?, ville=?, motdepasse=? WHERE id_client=?");
            $stmt->execute([$nom, $prenom, $email, $telephone, $adresse, $code_postal, $ville, $hash, $id_client]);
        } else {
            // Pas de nouveau mot de passe → juste mettre à jour les infos
            $stmt = $pdo->prepare("UPDATE clients SET nom=?, prenom=?, email=?, telephone=?, adresse=?, code_postal=?, ville=? WHERE id_client=?");
            $stmt->execute([$nom, $prenom, $email, $telephone, $adresse, $code_postal, $ville, $id_client]);
        }

        // 🔹 Mettre à jour les sessions
        $_SESSION['nom'] = $nom;
        $_SESSION['prenom'] = $prenom;
        $_SESSION['utilisateur'] = $email;

        $_SESSION['message'] = "Profil mis à jour avec succès.";
        header("Location: ../profil.php");
        exit;

    } elseif ($action === 'delete') {
        // 🔹 Supprimer le compte après vérification du mot de passe actuel
        $stmt = $pdo->prepare("DELETE FROM clients WHERE id_client=?");
        $stmt->execute([$id_client]);

        // 🔹 Détruire la session et rediriger
        session_destroy();
        header("Location: ../index.php");
        exit;
    }

} catch (PDOException $e) {
    die("Erreur lors de l'opération : " . $e->getMessage());
}
