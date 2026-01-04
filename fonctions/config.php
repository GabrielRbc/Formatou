<?php
// Connexion à la base de données
$host = 'localhost';
$dbname = 'formatou';
$username = 'root';
$password = 'root'; // à adapter selon ton environnement

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
