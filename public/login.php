<?php

// Set Content Security Policy for font sources
require_once '../config/csp.php';

// Include error handling and database connection scripts
require 'errors-handling.php';
require './db.php';

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get data from the form
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Execute an SQL query to retrieve user data with the specified username
    $query = "SELECT * FROM admins WHERE username = :username";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);

    // Display information for debugging purposes
    /* echo "SQL Query: $query";
    echo "Username: $username, Password: $password"; */

    $stmt->execute();
    $user = $stmt->fetch();

    // Check if the user exists and the password is correct
    if ($user && password_verify($password, $user['password'])) {
        // Authentication successful
        session_start();
        $_SESSION['username'] = $username;
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        // Redirect to a protected page or perform other actions
        header('Location: admin-panel.php');
        exit();
    } else {
        // Authentication error
        echo "Invalid username or password";
    }
}
?>

<!DOCTYPE html>

<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width" maximum-scale=1.0 user-scalable=0>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/mobile.css">
    <title>Accueil</title>
</head>

<body>

    <div class="container">
            <div class="login">
            <form action="login.php" method="POST">
                <input type="text" name="username" placeholder="Логин" class="add-article" required autocomplete="off"><br><br>
                <input type="password" name="password" placeholder="Пароль" class="add-article" required autocomplete="off"><br><br>
                <input type="submit" value="Войти" class="admin-button">
            </form>

        </div>

        <script src="https://kit.fontawesome.com/78197913c0.js" crossorigin="anonymous"></script>
        <script src="/assets/js/script.js"></script>

    </div>

</body>

</html>