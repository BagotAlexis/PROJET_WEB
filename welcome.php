<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

$username = $_SESSION['user'];
$db = new PDO('mysql:host=localhost;dbname=projet_web', 'root', '');

// Récupération des auteurs
$stmtAuteurs = $db->query("SELECT * FROM Auteur");
$auteurs = $stmtAuteurs->fetchAll();

// Récupération des livres
$stmtLivres = $db->query("SELECT * FROM Livre");
$livres = $stmtLivres->fetchAll();

// Récupération des associations auteurs-livres
$stmtEcrits = $db->query("SELECT e.Id, a.Nom AS AuteurNom, l.Titre AS LivreTitre FROM Ecrit e JOIN Auteur a ON e.Num = a.Num JOIN Livre l ON e.ISSN = l.ISSN");
$ecrits = $stmtEcrits->fetchAll();

// Préparation des données pour les graphiques
$domaineCounts = array_count_values(array_column($livres, 'Domaine'));
$barLabels = json_encode(array_keys($domaineCounts));
$barData = json_encode(array_values($domaineCounts));

// Les données pour le graphique en camembert sont les mêmes que pour le graphique en barres dans cet exemple
$pieLabels = $barLabels;
$pieData = $barData;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bienvenue</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="container">
    <h1>Bonjour, <?php echo htmlspecialchars($username); ?>!</h1>

    <!-- Ajout de livres -->
    <section>
        <h2>Ajout de livres</h2>
        <form action="ajouter_livre.php" method="post">
            <input type="text" name="ISSN" placeholder="ISSN" required>
            <input type="text" name="Titre" placeholder="Titre" required>
            <textarea name="Resume" placeholder="Résumé" required></textarea>
            <input type="number" name="Nbpages" placeholder="Nombre de pages" required>
            <input type="text" name="Domaine" placeholder="Domaine" required>
            <input type="submit" value="Ajouter livre">
        </form>
    </section>

    <!-- Ajout d'auteurs -->
    <section>
        <h2>Ajout d'auteurs</h2>
        <form action="ajouter_auteur.php" method="post">
            <input type="text" name="Nom" placeholder="Nom" required>
            <input type="text" name="Prenom" placeholder="Prénom" required>
            <input type="date" name="DateNaissance" placeholder="Date de Naissance" required>
            <input type="text" name="Nationalite" placeholder="Nationalité" required>
            <input type="submit" value="Ajouter auteur">
        </form>
    </section>

    <!-- Association de livres et d'auteurs -->
    <section>
        <h2>Associer un livre et un auteur</h2>
        <form action="associer_auteur_livre.php" method="post">
            <input type="text" name="NumAuteur" placeholder="Numéro de l'auteur" required>
            <input type="text" name="ISSNLivre" placeholder="ISSN du livre" required>
            <input type="submit" value="Associer">
        </form>
    </section>

     <!-- Section de visualisation des auteurs -->
<section>
    <h2>Liste des Auteurs</h2>
    <table border="1"> <!-- Ajout d'une bordure pour une meilleure visibilité -->
        <thead>
            <tr>
                <th>Numéro</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Date de Naissance</th>
                <th>Nationalité</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($auteurs as $auteur): ?>
                <tr>
                    <td><?php echo htmlspecialchars($auteur['Num']); ?></td>
                    <td><?php echo htmlspecialchars($auteur['Nom']); ?></td>
                    <td><?php echo htmlspecialchars($auteur['Prenom']); ?></td>
                    <td><?php echo htmlspecialchars($auteur['DateNaissance']); ?></td>
                    <td><?php echo htmlspecialchars($auteur['Nationalite']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

<!-- Section de visualisation des livres -->
<section>
    <h2>Liste des Livres</h2>
    <table border="1">
        <thead>
            <tr>
                <th>ISSN</th>
                <th>Titre</th>
                <th>Résumé</th>
                <th>Nombre de Pages</th>
                <th>Domaine</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($livres as $livre): ?>
                <tr>
                    <td><?php echo htmlspecialchars($livre['ISSN']); ?></td>
                    <td><?php echo htmlspecialchars($livre['Titre']); ?></td>
                    <td><?php echo htmlspecialchars($livre['Resume']); ?></td>
                    <td><?php echo htmlspecialchars($livre['Nbpages']); ?></td>
                    <td><?php echo htmlspecialchars($livre['Domaine']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

<!-- Section de visualisation des associations -->
<section>
    <h2>Associations Auteurs-Livres</h2>
    <table border="1">
        <thead>
            <tr>
                <th>ID Association</th>
                <th>Nom de l'Auteur</th>
                <th>Titre du Livre</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ecrits as $ecrit): ?>
                <tr>
                    <td><?php echo htmlspecialchars($ecrit['Id']); ?></td>
                    <td><?php echo htmlspecialchars($ecrit['AuteurNom']); ?></td>
                    <td><?php echo htmlspecialchars($ecrit['LivreTitre']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<!-- Formulaire de suppression de livres -->
<section>
    <h2>Supprimer un Livre</h2>
    <form action="supprimer_livre.php" method="post">
        <select name="ISSN" required>
            <option value="">Sélectionnez un livre</option>
            <?php foreach ($livres as $livre): ?>
                <option value="<?php echo htmlspecialchars($livre['ISSN']); ?>">
                    <?php echo htmlspecialchars($livre['Titre']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input type="submit" value="Supprimer Livre">
    </form>
</section>

<!-- Formulaire de suppression d'auteurs -->
<section>
    <h2>Supprimer un Auteur</h2>
    <form action="supprimer_auteur.php" method="post">
        <select name="Num" required>
            <option value="">Sélectionnez un auteur</option>
            <?php foreach ($auteurs as $auteur): ?>
                <option value="<?php echo htmlspecialchars($auteur['Num']); ?>">
                    <?php echo htmlspecialchars($auteur['Nom']) . " " . htmlspecialchars($auteur['Prenom']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input type="submit" value="Supprimer Auteur">
    </form>
</section>

<!-- Dashboard pour les graphiques à droite -->
<div class="dashboard" style="flex-grow:  1;width: 400px;">
            <h2>Tableau de Bord</h2>
            <canvas id="booksChart"></canvas>
            <canvas id="domainPieChart"></canvas>
        </div>
    </div>

    <script>
    const barCtx = document.getElementById('booksChart').getContext('2d');
    const barChart = new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: <?php echo $barLabels; ?>,
            datasets: [{
                label: 'Nombre de Livres par Domaine',
                data: <?php echo $barData; ?>,
                backgroundColor: 'rgba(0, 123, 255, 0.5)',
                borderColor: 'rgba(0, 123, 255, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    const pieCtx = document.getElementById('domainPieChart').getContext('2d');
    const pieChart = new Chart(pieCtx, {
        type: 'pie',
        data: {
            labels: <?php echo $pieLabels; ?>,
            datasets: [{
                data: <?php echo $pieData; ?>,
                backgroundColor: [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'
                ],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
</script>



    <a href="logout.php" class="logout-button">Déconnexion</a>
</body>
</html>