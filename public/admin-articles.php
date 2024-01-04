<?php
// Start the session
session_start();

// Include the Content Security Policy from the csp.php file
require_once '../config/csp.php';

// Include necessary files
require_once __DIR__ . '/../vendor/autoload.php'; // Autoload for Composer
require_once 'errors-handling.php'; // Custom error handling functions
require_once 'db.php'; // Database connection functions
/* require_once 'meilisearch-functions.php'; */ // Uncomment if you need to include MeiliSearch functions

// Function to send delete operation to MeiliSearch
function sendDeleteToMeiliSearch($deleteId) {
    global $client, $index;

    try {
        // Your code to send the corresponding update to MeiliSearch
        $index->deleteDocument($deleteId);
    } catch (\Exception $e) {
        echo "MeiliSearch error: " . $e->getMessage();
    }
}

// Use the MeiliSearch client
use MeiliSearch\Client;

// Redirect to login.php if the session username is not set
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Set PDO error mode to exceptions
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create a MeiliSearch client and connect to the 'publications' index
$client = new Client('http://localhost:7700', 'uiG6qPcWIAe8akP5UdLBjdiLCDYuW_3gbK2mXg-pzHM');
$index = $client->index('publications');

// Fetch articles from the database if the request method is GET
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $sql = "SELECT * FROM publications ORDER BY publications.publication_date DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Check if 'publication_id' is set in the GET parameters
if (isset($_GET['publication_id'])) {
    $deleteId = $_GET['publication_id'];

    // Prepare and execute the SQL query to delete related records from 'publications_subcategories'
    $sqlDeleteSubcategories = "DELETE FROM publications_subcategories WHERE publication_id = :publication_id";
    $stmtDeleteSubcategories = $pdo->prepare($sqlDeleteSubcategories);
    $stmtDeleteSubcategories->bindParam(':publication_id', $deleteId, PDO::PARAM_INT);

    if (!$stmtDeleteSubcategories->execute()) {
        echo "Error deleting related records.";
        exit();
    }

    // Prepare and execute the SQL query to delete the publication from 'publications'
    $sqlDeletePublication = "DELETE FROM publications WHERE publication_id = :publication_id";
    $stmtDeletePublication = $pdo->prepare($sqlDeletePublication);
    $stmtDeletePublication->bindParam(':publication_id', $deleteId, PDO::PARAM_INT);

    if ($stmtDeletePublication->execute()) {
        sendDeleteToMeiliSearch($deleteId);
        header("Location: admin-articles.php?success=deleted");
        echo "Delete sent to MeiliSearch for publicationId: $deleteId";
        exit();
    } else {
        echo "Error deleting article.";
        exit();
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


        <main class="main-admin">

            <!-- ASIDE ADMIN -->
            <?php include 'elems/aside-admin.php' ?>
            <!-- ASIDE ADMIN -->

            <div class="add-content">
                <div class="add-form">

                    <table cellspacing="20px">
                        <colgroup>
                            <col id="id">
                            <col id="title">
                            <col id="edit">
                            <col id="delete">
                        </colgroup>

                        <tbody>
                            <?php foreach ($articles as $row): ?>
                                <tr>
                                    <td>
                                        <?= $row['publication_id'] ?>
                                    </td>
                                    <td>
                                        <?= $row['title'] ?>
                                    </td>
                                    <td><a href="edit-article.php?publication_id=<?= $row['publication_id'] ?>"
                                            class="edit-link">Modifier</a></td>
                                    <td><a href="admin-articles.php?publication_id=<?= $row['publication_id'] ?>"
                                            class="delete-link">Supprimer</a></td>

                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>


                </div>
            </div>

        </main>
    </div>
    
    <script src="https://kit.fontawesome.com/78197913c0.js" crossorigin="anonymous"></script>
    <script src="/assets/js/script.js"></script>

    <script>


    </script>


</body>

</html>