<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

try {
    $db = new PDO('mysql:host=localhost;dbname=projet_web', 'root', '');

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['Mail'], $_POST['Nom'], $_POST['Prenom'], $_POST['Password'], $_POST['Tel'])) {
        $mail = $_POST['Mail'];
        $nom = $_POST['Nom'];
        $prenom = $_POST['Prenom'];
        $password = password_hash($_POST['Password'], PASSWORD_DEFAULT); // Hashing du mot de passe
        $tel = $_POST['Tel'];

        // Vérifiez si un admin avec le même nom et prénom existe déjà
        $checkStmt = $db->prepare("SELECT * FROM admin WHERE Nom = :nom AND Prenom = :prenom");
        $checkStmt->execute(['nom' => $nom, 'prenom' => $prenom]);
        if ($checkStmt->fetch()) {
            // Un admin existe déjà avec ce nom et prénom
            header('Location: welcome.php?error=admin_exists');
            exit();
        }

        // Insérez le nouvel admin si le nom et le prénom ne sont pas déjà pris
        $stmt = $db->prepare("INSERT INTO admin (Mail, Nom, Prenom, Password, Tel) VALUES (:mail, :nom, :prenom, :password, :tel)");
        $stmt->execute(['mail' => $mail, 'nom' => $nom, 'prenom' => $prenom, 'password' => $password, 'tel' => $tel]);

        header('Location: welcome.php?success=admin_added');
    } else {
        header('Location: welcome.php?error=invalid_input');
    }
} catch (PDOException $e) {
    header('Location: welcome.php?error=db_error');
}

exit();
?>
