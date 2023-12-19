<?php
session_start();
session_unset();
session_destroy();
header('Location: index.php'); // Redirection vers la page index.php qui est la page de login
exit();
?>
