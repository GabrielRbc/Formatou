<?php
require_once 'fonctions/config.php';
session_start();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Formatou</title>
    <link href="style/bootstrap-4.6.2-dist/css/bootstrap.css" rel="stylesheet">
    <link href="style/style.css" rel="stylesheet">
</head>
<body>

<header class="header-site text-center">
    <h1>Site d'inscription à des formations</h1>
    <p>Découvrez nos formations !</p>
</header>

<!-- Barre de navigation -->
<nav class="navbar">
    <div class="nav-links">
        <?php
        // Affichage dynamique des catégories dans la navbar
        $stmtCatNav = $pdo->query("SELECT * FROM categories_formations");
        while ($catNav = $stmtCatNav->fetch()) {
            echo '<a href="#categorie'.$catNav['id_categorie'].'">'.htmlspecialchars($catNav['nom_categorie']).'</a>';
        }
        ?>
    </div>

    <div class="user-actions">
        <?php if (isset($_SESSION['type']) && ($_SESSION['type'] === 'client' || $_SESSION['type'] === 'admin')): ?>
            <span>Bonjour <?= htmlspecialchars($_SESSION['prenom']) ?></span>
            <div class="user-buttons">
                <a href="profil.php" class="btn btn-user">Mon profil</a>
                <a href="fonctions/deconnexion.php" class="btn btn-user">Déconnexion</a>
            </div>
        <?php else: ?>
            <a href="connexion.php" class="btn btn-user">Connexion</a>
        <?php endif; ?>
    </div>
</nav>

<main class="container mt-4"> 
    <?php 
    // Boucle des catégories avec sections ancrées
    $stmtCat = $pdo->query("SELECT * FROM categories_formations");
    while ($cat = $stmtCat->fetch()) {
        echo '<section id="categorie'.$cat['id_categorie'].'">';
        echo '<h2>'.htmlspecialchars($cat['nom_categorie']).'</h2>'; 
    ?>
        <div class="scroll-container"> 
            <button class="scroll-btn scroll-left">&#10094;</button>
            <div class="formations-scroll">

        <?php
        $stmtForm = $pdo->prepare("SELECT * FROM formations WHERE id_categorie = ?");
        $stmtForm->execute([$cat['id_categorie']]);
        while ($form = $stmtForm->fetch()) {
            echo '<div class="formation">';
            echo "<h3 class='formation-title'>".htmlspecialchars($form['nom_formation']).'</h3>';
            if (!empty($form['image_url'])) { 
                echo '<img src="'.htmlspecialchars($form['image_url']).'" alt="Image '.htmlspecialchars($form['nom_formation']).'">';
            }
            echo 'Durée : '.htmlspecialchars($form['duree']).'<br>';
            echo 'Prix : '.htmlspecialchars($form['prix']).' €<br>';
            echo '<a href="disponibilites.php?id='.$form['id_formation'].'" class="btn btn-outline-info">Voir les disponibilités</a>';
            echo '</div>';
        } ?>

            </div>
            <button class="scroll-btn scroll-right">&#10095;</button>
        </div>

        </section>
    <?php
    }
    ?>
</main>

<footer class="bg-dark text-white text-center py-3">
    <p>
        <a href="mentions_legales.php" class="text-white">Mentions Légales</a> -
        <a href="confidentialite.php" class="text-white">Politique de confidentialité</a> -
        <a href="cgv.php" class="text-white">CGV</a> -
        <?php if (isset($_SESSION['type'])): ?>
            <?php if ($_SESSION['type'] !== 'admin'): ?>
                <a href="contact.php" class="text-white">Contacter le support</a>
            <?php else: ?>
                <a href="admin_contact.php" class="text-white">Gestion du support</a>
            <?php endif; ?>
        <?php endif; ?>
    </p>
    <p>© Formatou - 2025</p>
</footer>

<script> 
// Boutons de défilement horizontal
document.querySelectorAll('.scroll-container').forEach(container => {
    const scrollBox = container.querySelector('.formations-scroll');
    const leftBtn = container.querySelector('.scroll-left');
    const rightBtn = container.querySelector('.scroll-right');
    leftBtn.addEventListener('click', () => scrollBox.scrollBy({ left: -300, behavior: 'smooth' }));
    rightBtn.addEventListener('click', () => scrollBox.scrollBy({ left: 300, behavior: 'smooth' }));
});

// Défilement fluide vers les sections lors d'un clic sur la navbar
document.querySelectorAll('.nav-links a').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const targetId = this.getAttribute('href');
        const section = document.querySelector(targetId);
        if (section) {
            window.scrollTo({
                top: section.offsetTop - 80, // Décalage si tu as un header fixe
                behavior: 'smooth'
            });
        }
    });
});
</script>

</body>
</html>
