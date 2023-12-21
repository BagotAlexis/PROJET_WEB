<?php
// Assurez-vous que seuls les utilisateurs authentifiés peuvent exécuter ce script
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

// Ajout d'un livre
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Préparation de la requête SQL avec des placeholders
    $stmt = $db->prepare("INSERT INTO Livre (ISSN, Titre, Resume, Nbpages, Domaine) VALUES (:issn, :titre, :resume, :nbpages, :domaine)");

    // Liaison des valeurs avec les placeholders
    $stmt->bindParam(':issn', $_POST['ISSN']);
    $stmt->bindParam(':titre', $_POST['Titre']);
    $stmt->bindParam(':resume', $_POST['Resume']); // Utilisez 'Resume' au lieu de 'Résumé' si c'est le nom dans la base de données
    $stmt->bindParam(':nbpages', $_POST['Nbpages']);
    $stmt->bindParam(':domaine', $_POST['Domaine']);

    // Exécution de la requête
    $stmt->execute();

    // Redirection vers la page welcome.php avec un message de succès
    header('Location: welcome.php?success=1');
    exit();
}
?>
