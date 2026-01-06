<?php
// Connexion à la base de données
if ($_SERVER["HTTP_HOST"] == "localhost") {
    $host = "localhost";
    $user = "root";
    $pass = "root";
} else {
    $host = "mysql-formatou.alwaysdata.net";
    $user = 'formatou';
    $pass = 'mdpFormatou2026';
}
$dbname = 'formatou_bdd';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>