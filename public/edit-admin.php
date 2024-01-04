<?php

// Set Content Security Policy for font sources
require_once '../config/csp.php';

// Include error handling and database connection files
require 'errors-handling.php';
require 'db.php';

// Check if the 'name' parameter was sent
session_start();
if (!isset($_SESSION['username']) && !isset($_SESSION['csrf_token'])) {
    // Redirect to the login page if the session variables are not set
    header('Location: login.php');
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    exit();
}

// Get the CSRF token from the session
$csrfToken = $_SESSION['csrf_token'];

if (isset($_GET['name'])) {
    $name = $_GET['name']; // Changed to 'name'

    // Execute an SQL query to get information about the admin
    $sql = "SELECT name, email, username FROM admins WHERE name = :name";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if an admin with the given name exists
    if (!$admin) {
        echo "Admin not found.";
        exit();
    }

    // Check if an admin with the given name exists
    if ($admin) {
        // If the form was submitted
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Add CSRF token check here
            if (isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
                // Get new values from the form
                $newPass = $_POST['newpass'];
                $confirmNewPass = $_POST['confirmnewpass'];
                $newEmail = $_POST['newemail'];
                $newUsername = $_POST['newusername'];

                // Check if both passwords match
                if ($newPass === $confirmNewPass) {
                    // Hash the password before updating
                    $hashedPassword = password_hash($newPass, PASSWORD_DEFAULT);

                    // Execute an SQL query to update admin information
                    $updateSql = "UPDATE admins SET password = :newPass, email = :newEmail, username = :newUsername WHERE name = :name";
                    $updateStmt = $pdo->prepare($updateSql);
                    $updateStmt->bindParam(':newPass', $hashedPassword, PDO::PARAM_STR);
                    $updateStmt->bindParam(':newEmail', $newEmail, PDO::PARAM_STR);
                    $updateStmt->bindParam(':newUsername', $newUsername, PDO::PARAM_STR);
                    $updateStmt->bindParam(':name', $name, PDO::PARAM_STR);

                    // If the update is successful, redirect to the admin management page
                    if ($updateStmt->execute()) {
                        header("Location: admins-manage.php?success=updated");
                        exit();
                    }
                } else {
                    echo "Les mots de passe ne correspondent pas.";
                }
            } else {
                echo "CSRF Error!";
                exit();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/mobile.css">

    <title>ART and human rights admin panel</title>
</head>

<body>

    <div class="admin-header">
        <h2>ART and human rights admin panel</h2>
    </div>


    <div class="container-admin">
        <div class="main-admin">
            <!-- ASIDE ADMIN -->
            <?php include 'elems/aside-admin.php' ?>
            <!-- ASIDE ADMIN -->
            <div class="add-content">
                <div class="add-form">

                    <h1>Edit admin</h1>

                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

                        <label for="newusername">Nouveau nom d'utilisateur</label><br><br>
                        <input type="text" name="newusername" value="<?= $admin['username'] ?>"
                            class="add-article"><br><br>

                        <label for="newemail">Nouveau mail :</label><br><br>
                        <input type="text" name="newemail" value="<?= $admin['email'] ?>" class="add-article"><br><br>

                        <label for="newpass">Nouveau mot de passe :</label><br><br>
                        <input type="password" name="newpass" value="" class="add-article" required><br><br>

                        <label for="confirmnewpass">Confirmez le nouveau mot de passe :</label><br><br>
                        <input type="password" name="confirmnewpass" value="" class="add-article" required><br><br>

                        <input type="submit" value="Modifier" class="admin-button">
                    </form>
                </div>
            </div>
            <main class="main-admin">

            </main>
        </div>

</body>