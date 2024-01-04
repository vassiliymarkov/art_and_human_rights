<?php
// Set Content Security Policy for font sources
require_once '../config/csp.php';

// Include error handling file
require 'errors-handling.php';

// Include database connection file
require './db.php';

// Enable error display for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Get the requested page from the URL or default to page 1
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$path = "pages/$page.php"; // Unused variable, consider removing

// Define the number of items per page and calculate the offset
$itemsPerPage = 5;
$offset = ($page - 1) * $itemsPerPage;

// Database query to retrieve publications with associated media information
$results = getPublications($pdo, $offset, $itemsPerPage);

// Get total records for pagination
$totalRecords = getTotalRecords($pdo);
$pagesNumber = ceil($totalRecords / $itemsPerPage);

// Separate function to retrieve publication data
function getPublications($pdo, $offset, $itemsPerPage) {
    try {
        $stmt = $pdo->prepare("
            SELECT DISTINCT
                publications.title,
                publications.subtitle,
                media.media_url,
                publications.url,
                publications.publication_date
            FROM
                publications
            INNER JOIN
                publication_media_relations ON publications.media_id = publication_media_relations.media_id
            INNER JOIN
                media ON publication_media_relations.media_id = media.media_id
            ORDER BY
                publications.publication_date DESC
            LIMIT :offset, :itemsPerPage;
        ");

        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindParam(':itemsPerPage', $itemsPerPage, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Handle database errors
        echo "Database Error: " . $e->getMessage();
        return [];
    }
}

// Separate function to retrieve the total number of records for pagination
function getTotalRecords($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total_records FROM publications
        ");

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total_records'];
    } catch (PDOException $e) {
        // Handle database errors
        echo "Database Error: " . $e->getMessage();
        return 0;
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

        <!-- HEADER -->
        <?php include 'elems/header.php' ?>
        <!-- HEADER -->

        <!-- LOGO -->
        <?php include 'elems/logo.php' ?>
        <!-- LOGO -->

        <!-- CONTENT -->
        <div class="content">
            <!-- MAIN -->
            <main>
                <?php if (isset($results) && is_array($results)): ?>
                    <?php foreach ($results as $row): ?>
                        <article>
                            <h2>
                                <?= $row['title'] ?>
                            </h2>
                            <img src="<?= $row['media_url'] ?>" class="content-image" alt="">
                            <div class="text">
                                <p>
                                    <?= $row['subtitle'] ?>
                                </p>
                                <div class="more"><a href="<?= $row['url'] ?>">Lire de suite</a></div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No results found.</p>
                <?php endif; ?>

                <div class="pages-links">

                    <?php
                    if ($page != 1) {
                        $prev = $page - 1;
                        echo "<a href='?page=$prev'><<</a>";
                    }

                    for ($i = 1; $i <= $pagesNumber; $i++) {
                        if ($page == $i) {
                            $class = ' class="active"';
                        } else {
                            $class = '';
                        }

                        echo "  <a href='?page=$i'$class>$i</a> ";
                    }
                    if ($page != $pagesNumber) {
                        $next = $page + 1;
                        echo "<a href='?page=$next'>>></a>";
                    }

                    ?>
                </div>

            </main>
            <!-- MAIN -->

            <!-- ASIDE -->
            <?php include 'elems/aside.php' ?>
            <!-- ASIDE -->
        </div>
        <!-- CONTENT -->

        <!-- FOOTER -->
        <?php include 'elems/footer.php' ?>
        <!-- FOOTER -->

        <script src="https://kit.fontawesome.com/78197913c0.js" crossorigin="anonymous"></script>
        <script src="/assets/js/script.js"></script>



</body>

</html>