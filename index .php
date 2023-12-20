<?php
session_start();

$db = new PDO('mysql:host=localhost;dbname=projet_web', 'root', '');

// Gestion des cookies
if (!isset($_COOKIE['cookie_accepted'])) {
    echo '<script>
            function showCookiePopup() {
                document.getElementById("cookie-popup").style.display = "block";
            }

            function acceptCookies() {
                document.cookie = "cookie_accepted=true; expires=Thu, 31 Dec 2099 23:59:59 UTC; path=/";
                document.getElementById("cookie-popup").style.display = "none";
            }
        </script>';

    echo '<div id="cookie-popup" style="display:none; position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); padding: 10px; background-color: #f2f2f2; border: 1px solid #ddd; border-radius: 5px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); max-width: 400px; text-align: center;">
            <p>This website uses cookies to ensure you get the best experience on our website.</p>
            <button onclick="acceptCookies()">Accept</button>
        </div>';

    echo '<script>showCookiePopup();</script>';
}

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $db->prepare("SELECT * FROM admin WHERE Nom = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['Password'])) {
        $_SESSION['user'] = $user['Nom'];
        header('Location: welcome.php');
        exit();
    } else {
        $error = "Erreur dans les données de login.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
</head>
<body>
    <?php if (isset($error)): ?>
        <p><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="post">
        <label for="username">Nom d'utilisateur :</label>
        <input type="text" name="username" id="username" required>

        <label for="password">Mot de passe :</label>
        <input type="password" name="password" id="password" required>

        <button type="submit" name="login">Se connecter</button>
    </form>
</body>
</html>
