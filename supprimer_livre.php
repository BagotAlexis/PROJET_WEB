<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

// Connexion à la base de données avec gestion d'erreurs
try {
    $db = new PDO('mysql:host=localhost;dbname=projet_web', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}

// Vérification de la méthode de la requête et de l'existence de l'input
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ISSN'])) {
    $issn = $_POST['ISSN'];

    // Début de la transaction
    $db->beginTransaction();

    try {
        // Suppression de toutes les associations liées au livre
        $stmt = $db->prepare("DELETE FROM Ecrit WHERE ISSN = :issn");
        $stmt->execute(['issn' => $issn]);

        // Suppression du livre
        $stmt = $db->prepare("DELETE FROM Livre WHERE ISSN = :issn");
        $stmt->execute(['issn' => $issn]);

        // Validation de la transaction
        $db->commit();

        header('Location: welcome.php?success=1');
    } catch (PDOException $e) {
        // Annulation de la transaction en cas d'erreur
        $db->rollBack();
        header('Location: welcome.php?error=delete_failed');
    }

    exit();
} else {
    header('Location: welcome.php?error=invalid_request');
    exit();
}
?>
