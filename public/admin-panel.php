<?php

require_once '../config/csp.php';
require 'errors-handling.php';
require 'db.php';

session_start();

// Redirect to login page if the user is not logged in
if (!isset($_SESSION['username']) && !isset($_SESSION['csrf_token'])) {
    header('Location: login.php');
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    exit();
}

// Check if there is a media_id in the session
if (isset($_SESSION['media_id'])) {
    $media_id = $_SESSION['media_id'];
    // Output $media_id on the page or perform necessary actions
}

// Get CSRF token from the session
$csrfTokenImage = $_SESSION['csrf_token'];

// Set PDO to throw exceptions for errors
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF token
    if (isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
        // Check if required fields are set
        if (isset($_FILES["file"]) && isset($_POST["media_type"]) && isset($_POST["alt"]) && isset($_POST["credentials"])) {
            $mediaType = $_POST["media_type"];
            $alt = $_POST["alt"];
            $credentials = $_POST["credentials"];
            $categoryName = $_POST["category_name"];
            $subcategoryName = $_POST["subcategory_name"];

            // Define the target directory
            $targetDirectory = '/var/www/art/public/assets/img/' . $categoryName . "/" . $subcategoryName . "/";
            $fileName = basename($_FILES["file"]["name"]);
            $targetFilePath = $targetDirectory . $fileName;

            // Create the target directory if it does not exist
            if (!file_exists($targetDirectory)) {
                mkdir($targetDirectory, 0777, true);
            }

            $fileName = basename($_FILES["file"]["name"]);
            $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
            $allowedTypes = array("jpg", "jpeg", "png", "webp");
            $uploadTimestamp = date('Y-m-d H:i:s');

            // Check if the file type is allowed
            if (in_array($fileType, $allowedTypes)) {
                // Move the uploaded file to the target directory
                if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFilePath)) {
                    // Remove the base directory from the file path
                    $targetFilePath = str_replace('/var/www/art/public/', '', $targetFilePath);

                    // Insert media information into the media table
                    try {
                        $sql = "INSERT INTO media (media_url, media_type, alt, credentials, upload_timestamp) 
                                VALUES (:media_url, :media_type, :alt, :credentials, :uploadTimestamp)";
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(':media_url', $targetFilePath, PDO::PARAM_STR);
                        $stmt->bindValue(':media_type', $mediaType, PDO::PARAM_STR);
                        $stmt->bindValue(':alt', $alt, PDO::PARAM_STR);
                        $stmt->bindValue(':credentials', $credentials, PDO::PARAM_STR);
                        $stmt->bindValue(':uploadTimestamp', $uploadTimestamp, PDO::PARAM_STR);

                        if ($stmt->execute()) {
                            // Store the last inserted media_id in the session
                            $_SESSION['media_id'] = $pdo->lastInsertId();
                        } else {
                            echo "Error inserting data into the media table.";
                        }
                    } catch (PDOException $e) {
                        echo "Error executing the SQL query: " . $e->getMessage();
                    }
                } else {
                    echo "Error moving the file: " . $_FILES["file"]["error"];
                }
            } else {
                echo "Unsupported file type. Please upload files in JPG, JPEG, PNG, or GIF format.";
            }
        } else {
            echo "Required fields are missing.";
        }
    } else {
        echo "CSRF Error!";
        exit();
    }

    // Query to get the latest 5 media entries
    $sql = "SELECT media_id FROM media ORDER BY upload_timestamp DESC LIMIT 5";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process article submission
    if (isset($_POST["media_id"]) && isset($_POST["title"]) && isset($_POST["subtitle"]) && isset($_POST["text"]) && isset($_POST["category_id"])) {
        $title = $_POST["title"];
        $subtitle = $_POST["subtitle"];
        $text = $_POST["text"];
        $categoryId = $_POST["category_id"];
        $mediaId = $_POST["media_id"];
        $currentDateTime = date('Y-m-d H:i:s');

        try {
            // SQL query to insert publication information into the publications table
            $sql = "INSERT INTO publications (title, subtitle, text, media_id, category_id, publication_date) 
                    VALUES (:title, :subtitle, :text, :media_id, :category_id, NOW())";

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':title', $title, PDO::PARAM_STR);
            $stmt->bindValue(':subtitle', $subtitle, PDO::PARAM_STR);
            $stmt->bindValue(':text', $text, PDO::PARAM_STR);
            $stmt->bindValue(':media_id', $mediaId, PDO::PARAM_INT);
            $stmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);

            if ($stmt->execute()) {
                echo "Data successfully inserted into the publications table.";

                // Get the category name and generate a readable URL
                $categoryName = getCategoryName($categoryId);
                $url = generateReadableURL($categoryName, $title);

                // Update the URL in the publications table
                $updateSql = "UPDATE publications SET url = :url WHERE title = :title";
                $updateStmt = $pdo->prepare($updateSql);
                $updateStmt->bindValue(':url', $url, PDO::PARAM_STR);
                $updateStmt->bindValue(':title', $title, PDO::PARAM_STR);

                if ($updateStmt->execute()) {
                    echo "URL updated successfully.";
                } else {
                    echo "Error updating the URL.";
                }
            } else {
                echo "Error inserting data into the publications table.";
            }
        } catch (PDOException $e) {
            echo "Error executing the SQL query: " . $e->getMessage();
        }
    }

    // Process media relations submission
    if (isset($_POST["media_id"]) && isset($_POST["publication_id"])) {
        $publicationId = $_POST["publication_id"];
        $mediaId = $_POST["media_id"];

        try {
            // SQL query to insert data into the publication_media_relations table
            $sql = "INSERT INTO publication_media_relations (publication_id, media_id) 
                    VALUES (:publication_id, :media_id)";

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':publication_id', $publicationId, PDO::PARAM_STR);
            $stmt->bindValue(':media_id', $mediaId, PDO::PARAM_INT);

            if ($stmt->execute()) {
                echo "Data successfully inserted into the publication_media_relations table.";
            } else {
                echo "Error inserting data into the publication_media_relations table.";
            }
        } catch (PDOException $e) {
            echo "Error executing the SQL query: " . $e->getMessage();
        }
    }

    // Get the last inserted publication_id
    $sql = "SELECT MAX(publication_id) as last_id FROM publications";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $lastPublicationId = $result['last_id'];

    // Process subcategories submission
    if (isset($_POST["publication_id"]) && isset($_POST["subcategory_id"])) {
        $publicationId = $_POST["publication_id"];
        $subcategoryId = $_POST["subcategory_id"];

        try {
            // SQL query to insert data into the publications_subcategories table
            $sql = "INSERT INTO publications_subcategories (publication_id, subcategory_id) 
                    VALUES (:publication_id, :subcategory_id)";

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':publication_id', $publicationId, PDO::PARAM_STR);
            $stmt->bindValue(':subcategory_id', $subcategoryId, PDO::PARAM_INT);

            if ($stmt->execute()) {
                echo "Data successfully inserted into the publications_subcategories table.";
            } else {
                echo "Error inserting data into the publications_subcategories table.";
            }
        } catch (PDOException $e) {
            echo "Error executing the SQL query: " . $e->getMessage();
        }
    }
}

// Function to get category name based on category_id
function getCategoryName($categoryId)
{
    $categories = [
        "1" => "visual",
        "2" => "literature",
        "3" => "events",
        "4" => "pubs"
    ];

    if (isset($categories[$categoryId])) {
        return $categories[$categoryId];
    } else {
        return "unknown";
    }
}

// Function to generate a readable URL based on category and title
function generateReadableURL($category, $title)
{
    $category = urlencode($category);
    $title = urlencode($title);

    return "/article.php?category=$category&title=$title";
}

// Get the last inserted publication_id
$sql = "SELECT MAX(publication_id) as last_id FROM publications";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$lastPublicationId = $result['last_id'];

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
                    <h3>Téléversez le(s) fichier(s)</h3><br><br>
                    <form id="media_upload" action="admin-panel.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrfTokenImage; ?>"></input>

                        <h4>Sélectionnez un fichier</h4><br>
                        <label for="file"></label>
                        <input type="file" name="file" id="file"><br><br>
                        <h4>Entrez type du fichier (photo, video, audio)</h4><br>
                        <label for="media_type"></label>
                        <input type="text" name="media_type" id="media_type" class="add-article"><br><br>
                        <h4>Entrez texte alternative du fishier</h4><br>
                        <label for="alt"></label>
                        <input type="text" name="alt" id="alt" class="add-article"><br><br>
                        <h4>Entrez l'information de fishier</h4><br>
                        <label for="credentials"></label>
                        <input type="text" name="credentials" id="credentials" class="add-article"><br><br>
                        <h4>Sélectionnez une catégorie</h4><br>
                        <label for="category_name"></label>
                        <select name="category_name" id="category_name" class="add-article" required>
                            <option value="">Select</option>
                            <option value="visual">Visuel</option>
                            <option value="literature">Littérature</option>
                            <option value="events">Événements</option>
                            <option value="pubs">Publications</option>
                            <!-- Ajoutez d'autres catégories au besoin -->
                        </select><br><br>
                        <h4>Sélectionnez une sous-catégorie</h4><br>
                        <label for="subcategory_name"></label>
                        <select name="subcategory_name" id="subcategory_name" class="add-article" required>
                            <option value="">Select</option>
                            <option value="painting">Painture</option>
                            <option value="sculpture">Sculpture</option>
                            <option value="photo">Photographie</option>
                            <option value="movies">Cinéma</option>
                            <option value="prose">Prose</option>
                            <option value="poetry">Poesie</option>
                            <option value="exhibitions">Expositions</option>
                            <option value="actions">Actions</option>
                            <option value="manifestations">Manifestations</option>
                            <option value="articles">Articles</option>
                            <option value="interviews">Interviews</option>

                        </select><br><br>
                        <button class="admin-button" type="submit">Envoyer</button><br><br>
                    </form>

                    <h4>Media_id de votre fichier téléverse :</h4><br>
                    <?php

                    if (!empty($files)) {
                        foreach ($files as $file) {
                            $mediaId = $file['media_id'];
                            echo $mediaId . ' ';
                        }
                    } else {
                        echo "Нет доступных файлов.";
                    }
                    ?>
                    <h3>Entrez les informations sur l'article</h3><br><br>
                    <form id="publication_upload" action="admin-panel.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrfTokenImage; ?>"></input>
                        <h4>Titre</h4><br>
                        <label for="title"></label>
                        <input type="text" name="title" id="title" class="add-article"><br><br>
                        <h4>Sous-titre</h4><br>
                        <label for="subtitle"></label>
                        <textarea type="text" name="subtitle" id="subtitle" class="add-article"></textarea><br><br>
                        <label for="text"></label>
                        <textarea name="text" id="text" class="add-article"></textarea><br><br>
                        <h4>Sélectionnez une catégorie</h4><br>
                        <label for="category_id"></label>
                        <select name="category_id" id="category_id" class="add-article" required>
                            <option value="1">Visuel</option>
                            <option value="2">Litérature</option>
                            <option value="3">Événements</option>
                            <option value="4">Publications</option>
                        </select><br><br>

                        <label for="media_id"></label>
                        <input type="text" name="media_id" id="media_id" class="add-article" required><br><br>
                        <button type="submit" class="admin-button">Envoyer</button><br><br>
                    </form>
                    <?php
                    if ($result) {
                        foreach ($result as $item) {
                            $lastPublicationId = $result['last_id'];
                            echo $lastPublicationId . ' ' . "<br><br>";
                        }
                    }
                    ?>
                    <h4>Entrez l'ID d'article et l'ID(s) de(s) fichier(s) media(s)</h4><br><br>
                    <form id="publication_media_relations" action="admin-panel.php" method="post"
                        enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrfTokenImage; ?>"></input>
                        <h4>ID de l'article</h4><br>
                        <label for="publication_id"></label>
                        <input type="text" name="publication_id" id="publication_id" class="add-article"><br><br>
                        <h4>ID(s) de le(s) fichier(s) media(s)</h4><br>
                        <label for="media_id"></label>
                        <input type="text" name="media_id" id="media_id" class="add-article"><br><br>
                        <button type="submit" class="admin-button">Envoyer</button><br><br>
                    </form>

                    <h4>Entrez l'ID d'article et une sous-categirie</h4><br>
                    <form id="publications_subcategories" action="admin-panel.php" method="post"
                        enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrfTokenImage; ?>"></input>
                        <label for="publication_id"></label>
                        <input type="text" name="publication_id" id="publication_id" class="add-article"><br><br>
                        <label for="subcategory_id"></label>
                        <select name="subcategory_id" id="subcategory_id" class="add-article" required>
                            <option value="1">Painture</option>
                            <option value="2">Sculpture</option>
                            <option value="3">Photographie</option>
                            <option value="4">Cinéma</option>
                            <option value="5">Prose</option>
                            <option value="6">Poesie</option>
                            <option value="7">Expositions</option>
                            <option value="8">Actions</option>
                            <option value="9">Manifestations</option>
                            <option value="10">Articles</option>
                            <option value="11">Interviews</option>
                            <!-- Ajoutez d'autres catégories au besoin -->
                        </select><br><br>

                        <button type="submit" class="admin-button">Envoyer</button><br><br><br><br>
                    </form>

                </div>

            </div>
        </main>
    </div>
    <!-- <a href="logout.php">Выйти</a> -->

    <script src="https://kit.fontawesome.com/78197913c0.js" crossorigin="anonymous"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const categorySelect = document.getElementById("category_name");
            const subcategorySelect = document.getElementById("subcategory_name");

            categorySelect.addEventListener("change", function () {
                let category = categorySelect.value; // Получаем выбранное значение категории


                // Удалите существующие опции
                subcategorySelect.innerHTML = "";

                // Динамически создайте опции подкатегорий на основе выбранной категории
                if (category === "visual") {
                    let option1 = document.createElement("option");
                    option1.value = "painting";
                    option1.text = "Painture";
                    subcategorySelect.appendChild(option1);

                    let option2 = document.createElement("option");
                    option2.value = "sculpture";
                    option2.text = "Sculpture";
                    subcategorySelect.appendChild(option2);

                    let option3 = document.createElement("option");
                    option3.value = "photo";
                    option3.text = "Photographie";
                    subcategorySelect.appendChild(option3);

                    let option4 = document.createElement("option");
                    option4.value = "movies";
                    option4.text = "Cinema";
                    subcategorySelect.appendChild(option4);

                } else if (category === "literature") {
                    let option5 = document.createElement("option");
                    option5.value = "prose";
                    option5.text = "Prose";
                    subcategorySelect.appendChild(option5);

                    let option6 = document.createElement("option");
                    option6.value = "poetry";
                    option6.text = "Poesie";
                    subcategorySelect.appendChild(option6);

                } else if (category === "events") {
                    let option7 = document.createElement("option");
                    option7.value = "exhibitions";
                    option7.text = "Expositions";
                    subcategorySelect.appendChild(option7);

                    let option8 = document.createElement("option");
                    option8.value = "actions";
                    option8.text = "Actions";
                    subcategorySelect.appendChild(option8);

                    let option9 = document.createElement("option");
                    option9.value = "manifestations";
                    option9.text = "Manifestations";
                    subcategorySelect.appendChild(option9);

                } else if (category === "pubs") {
                    let option10 = document.createElement("option");
                    option10.value = "articles";
                    option10.text = "Articles";
                    subcategorySelect.appendChild(option10);

                    let option11 = document.createElement("option");
                    option11.value = "interviews";
                    option11.text = "Interviews";
                    subcategorySelect.appendChild(option11);
                }
                // Добавьте другие опции подкатегорий в зависимости от категории
            })
        });
    </script>
    <script src="/assets/js/script.js"></script>



</body>

</html>