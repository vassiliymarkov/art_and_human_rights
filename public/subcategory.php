<?php
require 'errors-handling.php';
require 'db.php';

// Get the page number from the URL, default to 1 if not set
$page = isset($_GET['page']) ? $_GET['page'] : 1;

// Get the 'category' parameter from the URL, default to "Default category name" if not set
$category = isset($_GET['category']) ? $_GET['category'] : "Default category name";

// Get the 'subcategory' parameter from the URL, default to "Default subcategory name" if not set
$subcategory = isset($_GET['subcategory']) ? $_GET['subcategory'] : "Default subcategory name";

// Define the number of items to display per page
$itemsPerPage = 5;

// Calculate the offset based on the current page
$offset = ($page - 1) * $itemsPerPage;

// Prepare the SQL statement to fetch publications
$stmt = $pdo->prepare("SELECT
    title,
    subtitle,
    media_url,
    url
FROM
    publications p
INNER JOIN
    publications_subcategories ps ON p.publication_id = ps.publication_id
INNER JOIN
    categories c ON p.category_id = c.category_id
INNER JOIN
    media m ON p.media_id = m.media_id
WHERE
    m.media_id = (
        SELECT
            media_id
        FROM
            media
        WHERE
            media_id = p.media_id
        ORDER BY
            media_id DESC
        LIMIT 1
    )
    AND c.name = :category
    AND ps.subcategory_id = (
        SELECT
            subcategory_id
        FROM
            subcategories
        WHERE
            name = :subcategory
            AND category_id = (
                SELECT
                    category_id
                FROM
                    categories
                WHERE
                    name = :category
            )
    )
ORDER BY
    p.publication_date DESC
LIMIT :offset, :itemsPerPage;
");

// Bind parameters
$stmt->bindParam(':category', $category, PDO::PARAM_STR);
$stmt->bindParam(':subcategory', $subcategory, PDO::PARAM_STR);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':itemsPerPage', $itemsPerPage, PDO::PARAM_INT);

// Execute the query
$stmt->execute();

// Fetch the results
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Query to count total records
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

// Fetch the result
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$totalRecords = $result['total_records'];

// Calculate the total number of pages
$pagesNumber = ceil($totalRecords / $itemsPerPage);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/mobile.css">
    <title>
        <?php echo htmlspecialchars($subcategory, ENT_QUOTES, 'UTF-8'); ?>
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