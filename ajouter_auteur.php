<?php
// Assurez-vous que seuls les utilisateurs authentifiés peuvent exécuter ce script
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

// Connexion à la base de données
$dbOptions = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES => false,
];
$db = new PDO('mysql:host=localhost;dbname=projet_web', 'root', '', $dbOptions);

// Ajout d'un auteur
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Préparation de la requête SQL avec des placeholders
    $stmt = $db->prepare("INSERT INTO Auteur (Nom, Prenom, DateNaissance, Nationalite) VALUES (:nom, :prenom, :dateNaissance, :nationalite)");

    // Liaison des valeurs
    $stmt->bindParam(':nom', $_POST['Nom']);
    $stmt->bindParam(':prenom', $_POST['Prenom']);
    $stmt->bindParam(':dateNaissance', $_POST['DateNaissance']);
    $stmt->bindParam(':nationalite', $_POST['Nationalite']);

    // Exécution de la requête
    $stmt->execute();

    // Redirection vers la page welcome.php avec un message de succès
    header('Location: welcome.php?success=1');
    exit();
}
?>
