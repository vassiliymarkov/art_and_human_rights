<?php
// Include error handling and database connection files
require 'errors-handling.php';
require 'db.php';

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get media type, alt, and credentials from the form
    $mediaType = $_POST["media_type"];
    $alt = $_POST["alt"];
    $credentials = $_POST["credentials"];

    // Get selected category and subcategory values
    $category = $_POST["category"];
    $subcategory = $_POST["subcategory"];

    // Define the upload directory based on the structure
    $targetDirectory = "C:/wamp64/www/art/assets/img/" . $category . "/" . $subcategory . "/";

    // Get file information
    $fileName = basename($_FILES["file"]["name"]);
    $targetFilePath = $targetDirectory . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

    // Allowed file types
    $allowedTypes = array("jpg", "jpeg", "png", "webp");

    // Check if the file type is allowed
    if (in_array($fileType, $allowedTypes)) {
        // Move the file to the selected directory
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFilePath)) {
            // Connect to the database using PDO
            try {
                // SQL query to insert media information into the media table
                $sql = "INSERT INTO media (media_url, media_type, alt, credentials) VALUES (:media_url, :media_type, :alt, :credentials)";
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':media_url', $targetFilePath, PDO::PARAM_STR);
                $stmt->bindValue(':media_type', $mediaType, PDO::PARAM_STR);
                $stmt->bindValue(':alt', $alt, PDO::PARAM_STR);
                $stmt->bindValue(':credentials', $credentials, PDO::PARAM_STR);

                // Check if the execution was successful
                if ($stmt->execute()) {
                    echo "File successfully uploaded, and its data has been added to the database.";
                } else {
                    echo "Error inserting data into the database.";
                }
            } catch (PDOException $e) {
                echo "Database connection error: " . $e->getMessage();
            }
        } else {
            echo "Error moving the file.";
        }
    } else {
        echo "Unsupported file type. Please upload files in JPG, JPEG, PNG, or GIF format.";
    }
}

?>