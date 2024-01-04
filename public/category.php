<?php
// Include error handling and database connection files
require 'errors-handling.php';
require 'db.php';

// Get page parameter from the URL or set default value
$page = isset($_GET['page']) ? $_GET['page'] : 1;

// Set default category name if category is not provided in the URL
if (isset($_GET['category'])) {
    $category = $_GET['category'];
} else {
    $category = "Default category name";
}

// Define the number of items per page and calculate the offset
$itemsPerPage = 5;
$offset = ($page - 1) * $itemsPerPage;

// SQL query to retrieve publications with associated media information
// Use aliases for table names to improve readability
$stmt = $pdo->prepare("SELECT
    p.title,
    p.subtitle,
    m.media_url,
    p.url
FROM
    publications AS p
INNER JOIN
    media AS m ON p.media_id = m.media_id
WHERE
    p.category_id = (
        SELECT
            category_id
        FROM
            categories
        WHERE
            name = :category
    )
    AND m.media_id = (
        SELECT
            MAX(media_id)
        FROM
            media
        WHERE
            media_id = p.media_id
    )
ORDER BY p.publication_date DESC
LIMIT :offset, :itemsPerPage;
");

// Bind parameters and execute the query
$stmt->bindParam(':category', $category, PDO::PARAM_STR);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':itemsPerPage', $itemsPerPage, PDO::PARAM_INT);
$stmt->execute();

// Fetch the results
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Query to get the total number of records for pagination
$query = "SELECT COUNT(*) as total_records
    FROM publications
    WHERE category_id = (
        SELECT category_id
        FROM categories
        WHERE name = :category
    )";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':category', $category, PDO::PARAM_STR);
$stmt->execute();

// Fetch the total number of records
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$totalRecords = $result['total_records'];
$pagesNumber = ceil($totalRecords / $itemsPerPage);

// Comments for clarification
// $page: Current page number from the URL
// $category: Category name from the URL or default if not provided
// $itemsPerPage: Number of items to display per page
// $offset: Offset for pagination to retrieve the correct set of records
// $results: Array containing the publications with media information for the current page
// $totalRecords: Total number of records in the category for pagination
// $pagesNumber: Total number of pages based on the items per page
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/mobile.css">
    <title>
        <?php echo htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?>
    </title>
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
                        echo "<a href='category.php?category=$category&page=$prev'><<</a>";
                    }

                    for ($i = 1; $i <= $pagesNumber; $i++) {
                        if ($page == $i) {
                            $class = ' class="active"';
                        } else {
                            $class = '';
                        }

                        echo " <a href='category.php?category=$category&page=$i'$class>$i</a> ";
                    }

                    if ($page != $pagesNumber) {
                        $next = $page + 1;
                        echo "<a href='category.php?category=$category&page=$next'>>></a>";
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