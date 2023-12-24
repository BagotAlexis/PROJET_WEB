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

// Récupération et application du filtre sur le nombre de pages
$minPages = isset($_POST['minPages']) ? (int)$_POST['minPages'] : 0;
$maxPages = isset($_POST['maxPages']) ? (int)$_POST['maxPages'] : 5000; // Mettez une limite supérieure réaliste

$params = [];
$whereClauses = [];

if ($minPages > 0 || $maxPages < 10000) {
    $whereClauses[] = "Livre.Nbpages BETWEEN :minPages AND :maxPages";
    $params[':minPages'] = $minPages;
    $params[':maxPages'] = $maxPages;
}

// Récupération des domaines existants
$stmtDomaines = $db->query("SELECT DISTINCT Domaine FROM Livre ORDER BY Domaine");
$domaines = $stmtDomaines->fetchAll(PDO::FETCH_COLUMN);

$searchTerm = isset($_POST['search']) ? $_POST['search'] : '';
$selectedDomaine = isset($_POST['domaine']) ? $_POST['domaine'] : '';

$query = "SELECT Livre.*, GROUP_CONCAT(DISTINCT Auteur.Nom ORDER BY Auteur.Nom ASC SEPARATOR ', ') AS Auteurs 
          FROM Livre 
          LEFT JOIN Ecrit ON Livre.ISSN = Ecrit.ISSN 
          LEFT JOIN Auteur ON Ecrit.Num = Auteur.Num";

if (!empty($searchTerm)) {
    $search = "%{$searchTerm}%";
    $whereClauses[] = "(Livre.Titre LIKE :searchTermTitle OR Auteur.Nom LIKE :searchTermAuthor)";
    $params[':searchTermTitle'] = $search;
    $params[':searchTermAuthor'] = $search;
}

if (!empty($selectedDomaine) && $selectedDomaine !== "Tous les domaines") {
    $whereClauses[] = "Livre.Domaine = :domaine";
    $params[':domaine'] = $selectedDomaine;
}

if (!empty($whereClauses)) {
    $query .= " WHERE " . implode(' AND ', $whereClauses);
}

$query .= " GROUP BY Livre.ISSN";

$stmtLivres = $db->prepare($query);

try {
    $stmtLivres->execute($params);
    $livres = $stmtLivres->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur lors de l'exécution de la requête: " . $e->getMessage());
}

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

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Recherche de Livres</title>
    <link rel="stylesheet" href="main_page.css">
</head>
<body>
    <header>
        <form method="post">
            <input type="text" name="search" placeholder="Rechercher un livre" value="<?php echo htmlspecialchars($searchTerm); ?>">
            <button type="submit" name="submit_search">Rechercher</button>
            <button type="submit" name="cancel_search">Annuler la recherche</button>
        </form>
        <form method="post">
            <input type="text" name="username" placeholder="Nom d'utilisateur" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <button type="submit" name="login">Connexion</button>
        </form>
    </header>

    <aside id="filters">
        <form method="post" id="filters-form">
            <div>
                <label for="domaine">Domaine:</label>
                <select name="domaine">
                    <option value="">Tous les domaines</option>
                    <?php foreach ($domaines as $domaine): ?>
                        <option value="<?php echo htmlspecialchars($domaine); ?>" <?php echo ($selectedDomaine === $domaine) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($domaine); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="minPages">Nombre de pages minimum :</label>
                <input type="range" id="minPages" name="minPages" min="0" max="1000" value="<?php echo $minPages; ?>" oninput="this.nextElementSibling.value = this.value">
                <output><?php echo $minPages; ?></output>
            </div>

            <div>
                <label for="maxPages">Nombre de pages maximum :</label>
                <input type="range" id="maxPages" name="maxPages" min="0" max="1000" value="<?php echo $maxPages; ?>" oninput="this.nextElementSibling.value = this.value">
                <output><?php echo $maxPages; ?></output>
            </div>

            <!-- Conserver la valeur de recherche actuelle -->
            <input type="hidden" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>">

            <button type="submit" name="apply_filters">Rechercher avec filtres</button>
        </form>
    </aside>

    <main id="book-info">
        <!-- Affichage des livres -->
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
</body>
</html>