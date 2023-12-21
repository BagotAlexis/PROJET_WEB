<?php
$searchQuery = $_GET['search_query'];

// Connexion à la base de données
$db = new PDO('mysql:host=localhost;dbname=bibliotheque_numerique', 'root', '');

// Requête SQL - exemple pour rechercher dans les livres
$stmt = $db->prepare("SELECT * FROM livres WHERE titre LIKE ?");
$stmt->execute(["%$searchQuery%"]);
$results = $stmt->fetchAll();

// Afficher les résultats
foreach ($results as $row) {
    echo $row['titre'] . "<br>";
}
?>
