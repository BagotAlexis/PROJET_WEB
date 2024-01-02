<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

try {
    $db = new PDO('mysql:host=localhost;dbname=projet_web', 'root', '');

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['NomAAdminSupprimer'])) {
        $nom = $_POST['NomAAdminSupprimer'];

        $stmt = $db->prepare("DELETE FROM admin WHERE Nom = :nom");
        $stmt->execute(['nom' => $nom]);

        header('Location: welcome.php?success=admin_deleted');
    } else {
        header('Location: welcome.php?error=invalid_input');
    }
} catch (PDOException $e) {
    header('Location: welcome.php?error=db_error');
}

exit();
?>
