<?php

// Set Content Security Policy for font sources
require_once '../config/csp.php';

// Include error handling and database connection files
require 'errors-handling.php';
require 'db.php';

// Get page, category, and title parameters from the URL or set default values
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$category = isset($_GET['category']) ? $_GET['category'] : "Default category name";
$title = isset($_GET['title']) ? $_GET['title'] : '';

file_put_contents('/var/www/art/category.txt', 'Category: ' . var_export($_GET['category'], true) . "\n", FILE_APPEND);
file_put_contents('/var/www/art/title.txt', 'Title: ' . var_export($_GET['title'], true) . "\n", FILE_APPEND);

error_log("Category: " . $_GET['category']);
error_log("Title: " . $_GET['title']);

try {
    // SQL query to retrieve publication information including title, subtitle, media URL, credentials, URL, and text
    $sql = "SELECT p.title, p.subtitle, m.media_url, m.credentials, p.url, p.text
            FROM publications AS p
            INNER JOIN publication_media_relations AS pmr ON p.publication_id = pmr.publication_id
            INNER JOIN media AS m ON pmr.media_id = m.media_id
            WHERE p.title = :title";

    // Prepare the SQL statement
    $stmt = $pdo->prepare($sql);
    // Bind parameters
    $stmt->bindParam(':title', $title, PDO::PARAM_STR);
    // Execute the query
    $stmt->execute();

    // Get the result
    $textResult = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if any results were found
    if (empty($textResult)) {
        // Handle the case when no matching article is found
        header("Location: http://art/404.php");
        exit();
    }

    // SQL query to retrieve media URLs and credentials related to the publication
    $sqlImages = "SELECT media.media_url, media.credentials
                  FROM publications
                  LEFT JOIN publication_media_relations ON publications.publication_id = publication_media_relations.publication_id
                  LEFT JOIN media ON publication_media_relations.media_id = media.media_id
                  WHERE publications.title = :title";

    // Prepare the SQL statement
    $stmtImages = $pdo->prepare($sqlImages);
    // Bind parameters
    $stmtImages->bindParam(':title', $title, PDO::PARAM_STR);
    // Execute the query
    $stmtImages->execute();

    // Get the result
    $imageResults = $stmtImages->fetchAll(PDO::FETCH_ASSOC);
    // Extract media URLs and credentials into separate arrays
    $mediaUrls = array_column($imageResults, 'media_url');
    $credentials = array_column($imageResults, 'credentials');

    // Combine the data into a single array
    $data = [
        'title' => $textResult['title'],
        'subtitle' => $textResult['subtitle'],
        'text' => $textResult['text'],
        'url' => $textResult['url'],
        'category_id' => isset($textResult['category_id']) ? $textResult['category_id'] : null,

        'images' => $imageResults,
    ];

} catch (PDOException $e) {
    // Handle database errors
    echo "Database Error: " . $e->getMessage();
    // Additional actions can be taken, such as redirecting to an error page
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <!-- Meta tags for character set and viewport -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    
    <!-- Stylesheets for the page -->
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/mobile.css">
    
    <!-- Page title with PHP echo for dynamic content -->
    <title><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></title>
</head>

<body>

    <!-- Container for the entire content -->
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
                <!-- Single article content -->
                <div class="single-article">
                    <?php
                    
                    // Proposed code for handling article text with image markers
                    if (isset($textResult['text'])) {
                        $textWithMarkers = $textResult['text'];
                        $textWithMarkers = nl2br(htmlspecialchars($textResult['text']));
                        echo "<h1>" . $textResult['title'] . "</h1>";
                    }

                    if (isset($textResult['subtitle'])) {
                        echo "<p><b>" . $textResult['subtitle'] . "</b></p>";
                    }
                                    
                    // Regular expression pattern to match image markers
                    $pattern = "/{{img-(\d+)}}/";
                    preg_match_all($pattern, $textWithMarkers, $matches);

                    if (!empty($imageResults)) {

                        foreach ($matches[0] as $index => $marker) {
                            $imageNumber = $matches[1][$index] ?? null;

                            // Check if the index exists in arrays and is numeric
                            if ($imageNumber !== null && is_numeric($imageNumber) && isset($mediaUrls[$imageNumber - 1]) && isset($credentials[$imageNumber - 1])) {
                                $imageTag = "<figure>
                                <img src='" . $mediaUrls[$imageNumber - 1] . "' class='single-article-image image-full'>
                                <figcaption>
                                    " . $credentials[$imageNumber - 1] . "<br><br>
                                    <span class='hint'>
                                        Click on the image to view it in full size
                                    </span>
                                </figcaption>
                            </figure>";

                                $textWithMarkers = str_replace($marker, $imageTag, $textWithMarkers);
                            }
                        }
                    }

                    // Display the processed article text
                    echo $textWithMarkers;

                    // Added checks for the existence of keys in $textResult before using them
                    $categoryId = isset($textResult['category_id']) ? $textResult['category_id'] : null;
                    $subcategoryId = isset($textResult['subcategory_id']) ? $textResult['subcategory_id'] : null;

                    // Continue using $categoryId and $subcategoryId in your code...
                    
                    ?>

                    <!-- Modal window -->
                    <div id="myModal" class="modal">
                        <span class="close">&times;</span>
                        <div class="modal-content">
                            <img id="modalImage" src="img/articles/morning.jpg" alt="">
                        </div>
                    </div>

                </div>
            </main>

            <!-- ASIDE -->
            <?php include 'elems/aside.php' ?>
            <!-- ASIDE -->
        </div>
        <!-- CONTENT -->

        <!-- FOOTER -->
        <?php include 'elems/footer.php' ?>
        <!-- FOOTER -->

        <!-- Font Awesome icons script -->
        <script src="https://kit.fontawesome.com/78197913c0.js" crossorigin="anonymous"></script>
        <!-- Custom script for page functionality -->
        <script src="/assets/js/script.js"></script>
    </div>
</body>

</html>