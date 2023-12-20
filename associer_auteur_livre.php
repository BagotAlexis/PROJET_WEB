<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

$db = new PDO('mysql:host=localhost;dbname=projet_web', 'root', '');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $db->prepare("INSERT INTO Ecrit (Num, ISSN) VALUES (?, ?)");
    $stmt->execute([
        $_POST['NumAuteur'], // Assurez-vous que le champ 'NumAuteur' est bien nommé dans votre formulaire HTML
        $_POST['ISSNLivre']  // Assurez-vous que le champ 'ISSNLivre' est bien nommé dans votre formulaire HTML
    ]);

    header('Location: welcome.php?success=1');
    exit();
}
?>
