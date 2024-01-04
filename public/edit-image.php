<?php

// Set Content Security Policy for font sources
require_once '../config/csp.php';

// Include the database connection file
require 'db.php';

// Check if the 'media_id' parameter was sent
if (isset($_GET['media_id'])) {
    $media_id = $_GET['media_id'];

    // Execute an SQL query to retrieve information about the image
    $sql = "SELECT * FROM media WHERE media_id = :media_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':media_id', $media_id, PDO::PARAM_INT);
    $stmt->execute();
    $image = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if an image with the specified 'media_id' exists
    if ($image) {
        // If the form was submitted
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Validate and sanitize user inputs
            $newCredentials = filter_input(INPUT_POST, 'newCredentials', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $newAlt = filter_input(INPUT_POST, 'newAlt', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            // Validate user inputs and handle potential validation errors
            if (empty($newCredentials) || empty($newAlt)) {
                echo "Credentials and Alt are required fields.";
                exit();
            }

            // Execute an SQL query to update information about the image
            $updateSql = "UPDATE media SET credentials = :newCredentials, alt = :newAlt WHERE media_id = :media_id";
            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->bindParam(':newCredentials', $newCredentials, PDO::PARAM_STR);
            $updateStmt->bindParam(':newAlt', $newAlt, PDO::PARAM_STR);
            $updateStmt->bindParam(':media_id', $media_id, PDO::PARAM_INT);

            // If the update is successful, return to the page with images
            if ($updateStmt->execute()) {
                header("Location: admin-images.php?success=updated");
                exit();
            } else {
                echo "Error updating image information.";
                exit();
            }
        }
    } else {
        echo "Image not found.";
        exit();
    }
} else {
    echo "The 'media_id' parameter is not specified.";
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
    </div>
    <div class="container-admin">
        <main class="main-admin">
            <!-- ASIDE ADMIN -->
            <?php include 'elems/aside-admin.php' ?>
            <!-- ASIDE ADMIN -->
        <div class="add-content">
        <div class="add-form" id="edit-image-form">

        <h1>Edit Image</h1>
        <img src="<?= $image['media_url'] ?>" class="admin-image"><br><br>
        <form method="post">
            <label for="newCredentials">New Credentials:</label>
            <input type="text" name="newCredentials" value="<?= $image['credentials'] ?>" class="add-article"><br><br>

            <label for="newAlt">New Alt Text:</label>
            <input type="text" name="newAlt" value="<?= $image['alt'] ?>" class="add-article"><br><br>

            <input type="submit" class="admin-button" value="Update">
        </form>
        </div>
        </div>
        
        </main>
    </div>

</body>

</html>