<?php
// Include Content Security Policy from csp.php
require_once '../config/csp.php';

// Include custom error handling functions
require 'errors-handling.php';

// Include database connection functions
require 'db.php';

// Start session
session_start();

// Redirect to login page if the user is not authenticated
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Set PDO error mode to exceptions for better error handling
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Define the base directory for media files
define('BASE_DIRECTORY', '/var/www/art/public/');

// Fetch media files from the database if the request method is GET
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $sql = "SELECT * FROM media ORDER BY media.upload_timestamp DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $mediaFiles = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Check if 'media_id' and 'media_url' are set in the GET parameters
if (isset($_GET['media_id']) && isset($_GET['media_url'])) {
    // Get 'media_id' and 'media_url' from the query parameters
    $mediaId = $_GET['media_id'];
    $mediaUrlRelative = $_GET['media_url'];

    // Create the full path to the file
    $mediaUrl = BASE_DIRECTORY . $mediaUrlRelative;

    // Check if the file exists and delete it
    if (file_exists($mediaUrl) && unlink($mediaUrl)) {
        // Delete the corresponding record from the database using a prepared statement
        try {
            $sql = "DELETE FROM media WHERE media_id = :media_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':media_id', $mediaId, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {
            // Log database errors or handle the exception as needed
            echo "Database error: " . $e->getMessage();
        }
    } else {
        // Display an error message if the file cannot be deleted
        echo "Error deleting file: " . $mediaUrl;
    }

    // Redirect to the admin-images.php page with a success message
    header("Location: admin-images.php?success=deleted");
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
                <div class="photo-items">
                    <?php if (isset($mediaFiles)): ?>
                        <?php foreach ($mediaFiles as $row): ?>
                            <div class="admin-image-item">
                                <img src="<?= $row['media_url'] ?>" class="admin-image"><br><br>
                                ID de fochier :
                                <?= $row['media_id'] ?><br><br>
                                <a href="edit-image.php?media_id=<?= $row['media_id'] ?>" class="edit-link">L'information sur
                                    l'image :</a>
                                <?= $row['credentials'] ?><br><br>
                                <a href="edit-image.php?media_id=<?= $row['media_id'] ?>" class="edit-link">Text alternative de
                                    fichier :</a>
                                <?= $row['alt'] ?><br><br>
                                <?= $row['upload_timestamp'] ?><br><br>

                                <a href="admin-images.php?media_id=<?= $row['media_id'] ?>&media_url=<?= $row['media_url'] ?>"
                                    class="delete-link">Supprimer</a>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            </div>

        </main>
    </div>
    
    <script src="https://kit.fontawesome.com/78197913c0.js" crossorigin="anonymous"></script>
    <script src="/assets/js/script.js"></script>



</body>

</html>