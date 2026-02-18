<?php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'php_exam_db');

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($mysqli->connect_error) {
    die("Erreur de connexion à la base de données: " . $mysqli->connect_error);
}

// Définir l'encodage UTF-8
$mysqli->set_charset("utf8mb4");

/**
 * Fonction pour sécuriser les données avant insertion en BDD
 */
function secure_input($data) {
    global $mysqli;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $mysqli->real_escape_string($data);
}

function redirect($url) {
    header("Location: $url");
    exit();
}
?>