<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['ISSN'])) {
    $db = new PDO('mysql:host=localhost;dbname=projet_web', 'root', '');

    // Assurez-vous que le nom de la colonne est correct
    // Remplacez 'ISSNLivre' par le nom correct de la colonne, comme 'ISSN', si c'est le cas
    $stmt = $db->prepare("DELETE FROM Ecrit WHERE ISSN = ?");
    $stmt->execute([$_POST['ISSN']]);

    // Suppression du livre
    $stmt = $db->prepare("DELETE FROM Livre WHERE ISSN = ?");
    $stmt->execute([$_POST['ISSN']]);

    header('Location: welcome.php?success=1');
    exit();
}
?>
