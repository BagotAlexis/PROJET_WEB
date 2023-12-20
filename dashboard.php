<?php
// Connexion à la base de données
$host = 'localhost';
$db   = 'bibliotheque_numerique';
$user = 'root'; 
$pass = ''; 
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Compter les auteurs
$stmt = $pdo->query("SELECT COUNT(*) FROM auteur");
$nombreAuteurs = $stmt->fetchColumn();

// Compter les livres
$stmt = $pdo->query("SELECT COUNT(*) FROM livre");
$nombreLivres = $stmt->fetchColumn();

// Compter les relations 'ecrit'
$stmt = $pdo->query("SELECT COUNT(*) FROM ecrit");
$nombreEcrits = $stmt->fetchColumn();

echo "<div>Nombre d'auteurs : $nombreAuteurs</div>";
echo "<div>Nombre de livres : $nombreLivres</div>";
echo "<div>Nombre de relations (écrits) : $nombreEcrits</div>";
?>
