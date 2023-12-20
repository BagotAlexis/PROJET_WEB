<?php
// Assurez-vous que seuls les utilisateurs authentifiés peuvent exécuter ce script
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

// Connexion à la base de données
$db = new PDO('mysql:host=localhost;dbname=projet_web', 'root', '');

// Ajout d'un auteur
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Assurez-vous que ces noms de colonnes correspondent exactement à ceux de votre base de données.
    $stmt = $db->prepare("INSERT INTO Auteur (Nom, Prenom, DateNaissance, Nationalite) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $_POST['Nom'],
        $_POST['Prenom'],
        $_POST['DateNaissance'],
        $_POST['Nationalite']
    ]);

    // Redirection vers la page welcome.php avec un message de succès
    header('Location: welcome.php?success=1');
    exit();
}
?>
