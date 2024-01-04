<?php

require_once('../config/config.php');


$host = DB_HOST;
$db   = DB_NAME;
$user = DB_USER;
$pass = DB_PASSWORD;
$port = DB_PORT;

try
    {
        $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);
    }
catch (Exception $e)
    {
            die('Erreur : ' . $e->getMessage());
    }

?>