<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

// Connexion à la base de données avec les options de sécurité
$dbOptions = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES => false,
];
$db = new PDO('mysql:host=localhost;dbname=projet_web', 'root', '', $dbOptions);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Préparation de la requête SQL avec des placeholders
    $stmt = $db->prepare("INSERT INTO Ecrit (Num, ISSN) VALUES (:num, :issn)");

    // Liaison des valeurs avec les placeholders
    $stmt->bindParam(':num', $_POST['NumAuteur']); // Assurez-vous que le champ 'NumAuteur' est bien nommé dans votre formulaire HTML
    $stmt->bindParam(':issn', $_POST['ISSNLivre']); // Assurez-vous que le champ 'ISSNLivre' est bien nommé dans votre formulaire HTML

    // Exécution de la requête
    $stmt->execute();

    header('Location: welcome.php?success=1');
    exit();
}
?>
