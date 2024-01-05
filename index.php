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

// Initialisation des variables pour les filtres
$params = [];
$whereClauses = [];

// Obtention de la valeur maximale de pages dans la base de données
$stmtMaxPages = $db->query("SELECT MAX(Nbpages) AS MaxPages FROM Livre");
$maxPagesFromDb = $stmtMaxPages->fetch(PDO::FETCH_ASSOC);
$maxPagesValue = $maxPagesFromDb['MaxPages'];

// Récupération et application du filtre sur le nombre de pages
$minPages = isset($_POST['minPages']) ? (int)$_POST['minPages'] : 0;
$maxPages = isset($_POST['maxPages']) ? (int)$_POST['maxPages'] : $maxPagesValue; 


if ($minPages >= 0 && $maxPages > 0) {
    $whereClauses[] = "Livre.Nbpages BETWEEN :minPages AND :maxPages";
    $params[':minPages'] = $minPages;
    $params[':maxPages'] = $maxPages;
}

// Récupération des années de naissance des auteurs existants pour le filtre
$stmtAnneesNaissance = $db->query("SELECT DISTINCT YEAR(DateNaissance) AS AnneeNaissance FROM Auteur ORDER BY AnneeNaissance");
$anneesNaissance = $stmtAnneesNaissance->fetchAll(PDO::FETCH_COLUMN);

// Récupération et application du filtre sur l'année de naissance de l'auteur
$selectedAnneeNaissance = isset($_POST['anneeNaissance']) ? (int)$_POST['anneeNaissance'] : 0;
if ($selectedAnneeNaissance > 0) {
    $whereClauses[] = "YEAR(Auteur.DateNaissance) = :anneeNaissance";
    $params[':anneeNaissance'] = $selectedAnneeNaissance;
}

// Récupération des domaines existants pour le filtre
$stmtDomaines = $db->query("SELECT DISTINCT Domaine FROM Livre ORDER BY Domaine");
$domaines = $stmtDomaines->fetchAll(PDO::FETCH_COLUMN);

// Application du filtre sur le domaine
$searchTerm = isset($_POST['search']) ? $_POST['search'] : '';
$selectedDomaine = isset($_POST['domaine']) ? $_POST['domaine'] : '';
if (!empty($selectedDomaine) && $selectedDomaine !== "Tous les domaines") {
    $whereClauses[] = "Livre.Domaine = :domaine";
    $params[':domaine'] = $selectedDomaine;
}

// Construction de la requête SQL principale avec les filtres
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

if (!empty($whereClauses)) {
    $query .= " WHERE " . implode(' AND ', $whereClauses);
}

$query .= " GROUP BY Livre.ISSN ORDER BY Livre.Titre ASC";

// Exécution de la requête
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
        $stmt->execute([':username' => $username]);
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body>
<header>
        <form class="search-form" method="post">
            <input type="text" name="search" placeholder="Rechercher un livre" value="<?php echo htmlspecialchars($searchTerm); ?>">
            <button type="submit" name="submit_search"><i class="bi bi-search"></i> Rechercher</button>
            <button type="submit" name="cancel_search"> <i class="bi bi-x-lg"></i> Annuler la recherche</button>
        </form>
        <div class="login-form">
            <form method="post">
                <input type="text" name="username" placeholder="Nom d'utilisateur" required>
                <input type="password" name="password" placeholder="Mot de passe" required>
                <button type="submit" name="login"><i class="bi bi-gear-fill"> Connexion</i></button>
            </form>
        </div>
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
                <input type="range" id="maxPages" name="maxPages" min="0" max="<?php echo $maxPagesValue; ?>" value="<?php echo $maxPages; ?>" oninput="this.nextElementSibling.value = this.value">
                <output><?php echo $maxPages; ?></output>
            </div>
            
            <div>
                <label for="anneeNaissance">Année de naissance de l'auteur:</label>
                <select name="anneeNaissance">
                    <option value="">Toutes les années</option>
                    <?php foreach ($anneesNaissance as $annee): ?>
                        <option value="<?php echo htmlspecialchars($annee); ?>" <?php echo ($selectedAnneeNaissance === $annee) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($annee); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <input type="hidden" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>">

            <button type="submit" name="apply_filters"><i class="bi bi-search"> Rechercher avec filtres</i> </button>
        </form>
    </aside>

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
</body>
</html>
