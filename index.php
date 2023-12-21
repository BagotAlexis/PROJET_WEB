<?php
session_start();

try {
    $db = new PDO('mysql:host=localhost;dbname=projet_web', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}

$livres = [];
if (isset($_POST['search'])) {
    $search = "%{$_POST['search']}%";
    $stmt = $db->prepare("SELECT Livre.*, GROUP_CONCAT(DISTINCT Auteur.Nom ORDER BY Auteur.Nom ASC SEPARATOR ', ') AS Auteurs 
                      FROM Livre 
                      LEFT JOIN Ecrit ON Livre.ISSN = Ecrit.ISSN 
                      LEFT JOIN Auteur ON Ecrit.Num = Auteur.Num 
                      WHERE Livre.Titre LIKE :search OR Auteur.Nom LIKE :searchAuteur 
                      GROUP BY Livre.ISSN");
    $stmt->execute(['search' => $search, 'searchAuteur' => $search]);
    $livres = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $db->query("SELECT Livre.*, GROUP_CONCAT(DISTINCT Auteur.Nom ORDER BY Auteur.Nom ASC SEPARATOR ', ') AS Auteurs 
                        FROM Livre 
                        LEFT JOIN Ecrit ON Livre.ISSN = Ecrit.ISSN 
                        LEFT JOIN Auteur ON Ecrit.Num = Auteur.Num 
                        GROUP BY Livre.ISSN");
    $livres = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if (isset($_POST['login'])) {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

    if ($username && $password) {
        $stmt = $db->prepare("SELECT * FROM admin WHERE Nom = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['Password'])) {
            $_SESSION['user'] = $user['Nom'];
            header('Location: welcome.php');
            exit();
        } else {
            $error = "Invalid credentials";
        }
    } else {
        $error = "Invalid input";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Recherche de Livres</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <form method="post">
            <input type="text" name="search" placeholder="Rechercher un livre">
            <button type="submit">Recherche</button>
        </form>
        <form method="post">
            <input type="text" name="username" placeholder="Nom d'utilisateur" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <button type="submit" name="login">Connexion</button>
        </form>
    </header>

    <aside>
        <!-- Filtres à implémenter -->
    </aside>

    <main>
        <?php if ($livres): ?>
            <ul>
                <?php foreach ($livres as $livre): ?>
                    <li>
                        <h3><?php echo htmlspecialchars($livre['Titre']); ?></h3>
                        <p>Auteur(s): <?php echo htmlspecialchars($livre['Auteurs']); ?></p>
                        <p>Nombre de pages: <?php echo htmlspecialchars($livre['Nbpages']); ?></p>
                        <p>Domaine: <?php echo htmlspecialchars($livre['Domaine']); ?></p>
                        <!-- Plus d'infos si nécessaire -->
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Aucun livre trouvé.</p>
        <?php endif; ?>
    </main>

    <?php if (isset($error)): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>
</body>
</html>
