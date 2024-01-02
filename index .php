<?php
session_start();

// Database Connection
try {
    $db = new PDO('mysql:host=localhost;dbname=projet_web', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}

// Récupération des domaines existants
$stmtDomaines = $db->query("SELECT DISTINCT Domaine FROM Livre ORDER BY Domaine");
$domaines = $stmtDomaines->fetchAll(PDO::FETCH_COLUMN);

// Récupération et filtrage des livres
$searchTerm = isset($_POST['search']) ? $_POST['search'] : '';
$selectedDomaine = isset($_POST['domaine']) ? $_POST['domaine'] : '';
$whereClauses = [];
$params = [];

if ($searchTerm) {
    $whereClauses[] = "(Livre.Titre LIKE :searchTerm)";
    $params['searchTerm'] = "%$searchTerm%";
}

if ($selectedDomaine && $selectedDomaine !== "Tous les domaines") {
    $whereClauses[] = "Livre.Domaine = :domaine";
    $params['domaine'] = $selectedDomaine;
}

$query = "SELECT Livre.*, GROUP_CONCAT(DISTINCT Auteur.Nom ORDER BY Auteur.Nom ASC SEPARATOR ', ') AS Auteurs 
          FROM Livre 
          LEFT JOIN Ecrit ON Livre.ISSN = Ecrit.ISSN 
          LEFT JOIN Auteur ON Ecrit.Num = Auteur.Num";

if ($whereClauses) {
    $query .= " WHERE " . implode(' AND ', $whereClauses);
}

$query .= " GROUP BY Livre.ISSN";

$stmtLivres = $db->prepare($query);
$stmtLivres->execute($params);
$livres = $stmtLivres->fetchAll(PDO::FETCH_ASSOC);

// Check if the user has already accepted cookies
$cookieConsent = isset($_COOKIE['cookieConsent']) ? $_COOKIE['cookieConsent'] : '';

// Gestion de la connexion utilisateur
$error = '';
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
            $error = "Identifiants invalides";
        }
    } else {
        $error = "Entrée invalide";
    }
}

// Annulation de la recherche et affichage de tous les livres
if (isset($_POST['cancel_search'])) {
    header('Location: index.php');
    exit();
}

// Set cookie consent
if (isset($_POST['cookie_consent'])) {
    setcookie('cookieConsent', 'accepted', time() + (365 * 24 * 60 * 60), '/'); // Expires in 1 year
    $cookieConsent = 'accepted';
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Recherche de Livres</title>
    <link rel="stylesheet" href="style.css">
    <style>
        #cookie-consent {
            display: <?php echo ($cookieConsent !== 'accepted') ? 'block' : 'none'; ?>;
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background-color: #333;
            color: #fff;
            padding: 10px;
            text-align: center;
        }
    </style>
</head>
<body>

<!-- Cookie Consent Pop-up -->
<div id="cookie-consent">
    <p>This website uses delicious cookies. By using this site, you agree to our <a href="#">Cookie Policy</a>.</p>
    <form method="post">
        <button type="submit" name="cookie_consent">I accept</button>
    </form>
</div>

<!-- Your existing HTML content -->
<header>
    <form method="post">
        <input type="text" name="search" placeholder="Rechercher un livre" value="<?php echo htmlspecialchars($searchTerm); ?>">
        <select name="domaine">
            <option value="">Tous les domaines</option>
            <?php foreach ($domaines as $domaine): ?>
                <option value="<?php echo htmlspecialchars($domaine); ?>" <?php echo ($selectedDomaine === $domaine) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($domaine); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="submit_search">Rechercher</button>
        <button type="submit" name="cancel_search">Annuler</button>
    </form>
    <form method="post">
        <input type="text" name="username" placeholder="Nom d'utilisateur" required>
        <input type="password" name="password" placeholder="Mot de passe" required>
        <button type="submit" name="login">Connexion</button>
    </form>
</header>

<main id="book-info">
    <?php if ($livres): ?>
        <ul>
            <?php foreach ($livres as $livre): ?>
                <li>
                    <h3><?php echo htmlspecialchars($livre['Titre']); ?></h3>
                    <p>Auteur(s): <?php echo htmlspecialchars($livre['Auteurs']); ?></p>
                    <p>Nombre de pages: <?php echo htmlspecialchars($livre['Nbpages']); ?></p>
                    <p>Domaine: <?php echo htmlspecialchars($livre['Domaine']); ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Aucun livre trouvé.</p>
    <?php endif; ?>
</main>

<?php if ($error): ?>
    <p class="error"><?php echo $error; ?></p>
<?php endif; ?>

<script>
    // Check if the user has already accepted cookies
    if (localStorage.getItem('cookieConsent') !== 'accepted') {
        document.getElementById('cookie-consent').style.display = 'block';
    }

    function acceptCookies() {
        document.getElementById('cookie-consent').style.display = 'none';
    }
</script>

</body>
</html>
