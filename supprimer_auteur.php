<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['Num'])) {
    $db = new PDO('mysql:host=localhost;dbname=projet_web', 'root', '');

    // Suppression de toutes les associations liées à l'auteur
    $stmt = $db->prepare("DELETE FROM Ecrit WHERE Num = ?");
    $stmt->execute([$_POST['Num']]);

    // Suppression de l'auteur
    $stmt = $db->prepare("DELETE FROM Auteur WHERE Num = ?");
    $stmt->execute([$_POST['Num']]);

    header('Location: welcome.php?success=1');
    exit();
}
?>
