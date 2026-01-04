<?php
session_start();
require_once 'fonctions/config.php';

// Si déjà connecté → retour à l’accueil
if (isset($_SESSION['id_client'])) {
    header("Location: index.php");
    exit;
}

// Message de retour (optionnel)
$message = $_SESSION['message'] ?? null;
unset($_SESSION['message']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Connexion / Inscription - Formatou</title>
    <link href="style/bootstrap-4.6.2-dist/css/bootstrap.css" rel="stylesheet">
    <link href="style/style.css" rel="stylesheet">
</head>
<body>

<header class="text-center bg-dark text-white py-4">
    <h1>Accès à votre espace</h1>
    <p>Connectez-vous, inscrivez-vous ou continuez en tant qu’invité</p>
</header>

<div class="container mt-5 mb-5">
    <?php if ($message): ?>
        <div class="alert alert-info text-center"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <!-- Connexion -->
        <div class="col-md-5 bg-white p-4 rounded shadow-sm">
            <h3 class="text-center mb-3">Connexion</h3>
            <form action="fonctions/traitement_connexion.php" method="POST">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" class="form-control" name="email" required>
                </div>
                <div class="form-group">
                    <label>Mot de passe</label>
                    <input type="password" class="form-control" name="motdepasse" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Se connecter</button>
            </form>
        </div>

        <!-- Inscription -->
        <div class="col-md-6 bg-white p-4 rounded shadow-sm ml-md-3 mt-4 mt-md-0">
            <h3 class="text-center mb-3">Inscription</h3>
            <form action="fonctions/traitement_inscription.php" method="POST" id="formInscription">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Nom</label>
                        <input type="text" class="form-control" name="nom" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Prénom</label>
                        <input type="text" class="form-control" name="prenom" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Date de naissance</label>
                    <input type="date" class="form-control" name="date_naissance" id="date_naissance" required>
                </div>

                <div id="bloc_responsable" style="display:none; border:1px solid #ccc; padding:15px; border-radius:8px; margin-bottom:15px;">
                    <h5>Responsable légal (obligatoire pour les mineurs)</h5>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Nom</label>
                            <input type="text" class="form-control" name="nom_responsable">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Prénom</label>
                            <input type="text" class="form-control" name="prenom_responsable">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Email</label>
                            <input type="email" class="form-control" name="email_responsable">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Téléphone</label>
                            <input type="tel" class="form-control" name="telephone_responsable">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Lien de parenté</label>
                        <input type="text" class="form-control" name="lien_parente" placeholder="ex : père, mère, tuteur...">
                    </div>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" class="form-control" name="email" required>
                </div>
                <div class="form-group">
                    <label>Téléphone</label>
                    <input type="tel" class="form-control" name="telephone" required>
                </div>
                <div class="form-group">
                    <label>Adresse</label>
                    <input type="text" class="form-control" name="adresse" required>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Code Postal</label>
                        <input type="text" class="form-control" name="codePostal" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Ville</label>
                        <input type="text" class="form-control" name="ville" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Mot de passe</label>
                    <input type="password" class="form-control" name="motdepasse" required>
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="consentement" required>
                    <label for="consentement">Je reconnais avoir pris connaissance des <a href='mentions_legales.php'>Mentions légales</a> et les accepter. Et j'atteste sur l'honneur l'exactitude des informations renseignées dans le formulaire</label>
                </div>
                <button type="submit" class="btn btn-success btn-block">S'inscrire</button>
            </form>

            <script>
            document.getElementById('date_naissance').addEventListener('change', function() {
                const birthDate = new Date(this.value);
                const today = new Date();
                const age = today.getFullYear() - birthDate.getFullYear();
                const m = today.getMonth() - birthDate.getMonth();
                const isMinor = (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) ? age - 1 : age;
                document.getElementById('bloc_responsable').style.display = (isMinor < 15) ? 'block' : 'none';
            });
            </script>

        </div>
    </div>

    <!-- Invité -->
    <div class="text-center mt-5">
        <form action="fonctions/inviter.php" method="POST">
            <button type="submit" class="btn btn-secondary">Continuer en tant qu’invité</button>
        </form>
    </div>
</div>

<div class="container mt-4 mb-5 text-muted small">
    <p>
        Pour gérer et suivre votre inscription sur la formation que vous avez sélectionnée, Formatou collecte des données personnelles vous concernant sur la base de son intérêt légitime (article 6 du RGPD). 
        Ces données pourront également être utilisées pour vous adresser de l'information sur Formatou, ses actions et ses formations, pour des enquêtes de satisfaction et des études afin de personnaliser votre parcours et votre expérience au sein de Formatou. 
        Si vous souhaitez vous y opposer, vous pouvez contacter Formatou à l’adresse suivante : 
        <a href="mailto:info@formatou.fr">info@formatou.fr</a>.
        <br>
        Pour en savoir plus sur le traitement de vos données personnelles, consultez notre <a href="confidentialite.php">politique de confidentialité</a>.
    </p>
</div>

<footer class="bg-dark text-white text-center py-3">
    <p>
        <a href="mentions_legales.php" class="text-white">Mentions Légales</a> -
        <a href="confidentialite.php" class="text-white">Politique de confidentialité</a> -
        <a href="cgv.php" class="text-white">CGV</a> -
        <a href="contact.php" class="text-white">Nous contacter</a>
    </p>
    <p>© Formatou - 2025</p>
</footer>

<script src="style/bootstrap-4.6.2-dist/js/bootstrap.bundle.js"></script>
</body>
</html>
