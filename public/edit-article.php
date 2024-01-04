<?php

// Set Content Security Policy for font sources
require_once '../config/csp.php';

// Include error handling and database connection files
require 'errors-handling.php';
require 'db.php';

// Check if the 'publication_id' parameter was sent

// Start a session
session_start();

// Redirect to login page if session variables are not set
if (!isset($_SESSION['username']) && !isset($_SESSION['csrf_token'])) {
    header('Location: login.php');
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    exit();
}

// Get CSRF token from the session
$csrfToken = $_SESSION['csrf_token'];

// Check if 'publication_id' parameter is set
if (isset($_GET['publication_id'])) {
    // Use 'publication_id' here
    $publication_id = $_GET['publication_id'];

    // Execute an SQL query to get information about the article
    $sql = "SELECT publication_id, title, subtitle, text FROM publications WHERE publication_id = :publication_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':publication_id', $publication_id, PDO::PARAM_INT);
    $stmt->execute();
    $article = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if an article with the given 'publication_id' exists
    if ($article) {
        // If the form was submitted
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Add CSRF token check here
            if (isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
                // Get new values from the form
                $newTitle = $_POST['newTitle'];
                $newSubtitle = $_POST['newSubtitle'];

                // Handle $newText here
                $newText = $_POST['newText'] ?? ''; // Use the null coalescing operator

                // Execute an SQL query to update information about the article
                $updateSql = "UPDATE publications SET title = :newTitle, subtitle = :newSubtitle, text = :newText WHERE publication_id = :publication_id";
                $updateStmt = $pdo->prepare($updateSql);
                $updateStmt->bindParam(':newTitle', $newTitle, PDO::PARAM_STR);
                $updateStmt->bindParam(':newSubtitle', $newSubtitle, PDO::PARAM_STR);
                $updateStmt->bindParam(':newText', $newText, PDO::PARAM_STR);
                $updateStmt->bindParam(':publication_id', $publication_id, PDO::PARAM_INT);

                // If the update is successful, redirect to the page with articles
                if ($updateStmt->execute()) {
                    header("Location: admin-articles.php?success=updated");
                    exit();
                }
            } else {
                echo "CSRF Error!";
                exit();
            }
        }
    } else {
        echo "Article not found.";
        exit();
    }
} else {
    echo "The 'publication_id' parameter is not specified.";
    exit();
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
        <!-- Добавьте функции админской панели здесь -->
    </div>


    <div class="container-admin">
        <div class="main-admin">
            <!-- ASIDE ADMIN -->
            <?php include 'elems/aside-admin.php' ?>
            <!-- ASIDE ADMIN -->
            <div class="add-content">
                <div class="add-form">

                    <h1>Edit article</h1>

                    <form method="post">
                        <label for="newTitle">Nouveau titre :</label><br><br>
                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                        <input type="text" name="newTitle" value="<?= $article['title'] ?>" class="add-article"><br><br>

                        <label for="newSubtitle">Nouveau sous-titre :</label><br><br>
                        <textarea type="text" name="newSubtitle" value="<?= $article['subtitle'] ?>" id="subtitle" class="add-article"></textarea><br><br>

                        <label for="newText">Nouveau texte :</label><br><br>
                        <textarea name="newText" value="newText" id="text" class="add-article"><?= $article['text'] ?></textarea><br><br>

                        <input type="submit" class="admin-button" value="Modifier">
                    </form>
                </div>
            </div>
            <main class="main-admin">

            </main>
        </div>

</body>

</html>