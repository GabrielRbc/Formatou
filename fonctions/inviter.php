<?php
session_start();
$_SESSION['type'] = 'invite';
$_SESSION['nom'] = null;
$_SESSION['prenom'] = null;
$_SESSION['id_client'] = null;
header("Location: ../index.php");
exit;
?>
