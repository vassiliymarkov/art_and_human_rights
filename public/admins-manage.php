<?php
// Set Content Security Policy for font sources
require_once '../config/csp.php';

// Include error handling functions
require 'errors-handling.php';

// Include database connection functions
require 'db.php';

// Start session
session_start();

// Redirect to login page if the user is not authenticated or CSRF token is not set
if (!isset($_SESSION['username']) && !isset($_SESSION['csrf_token'])) {
    header('Location: login.php');
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    exit();
}

// Get CSRF token from the session
$csrfTokenAdmins = $_SESSION['csrf_token'];

// Process form submission if it's a POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if required POST parameters are set
    if (isset($_POST["username"]) && isset($_POST["name"]) && isset($_POST["email"]) && isset($_POST["password"]) && isset($_POST["password-confirm"])) {
        $username = $_POST["username"];
        $name = $_POST["name"];
        $email = $_POST["email"];
        $password = $_POST["password"];
        $passwordConfirm = $_POST["password-confirm"];

        // Check if both passwords match
        if ($password === $passwordConfirm) {
            // Insert admin data into the database using the function
            if (insertAdmin($pdo, $username, $name, $email, $password)) {
                // Successful addition of admin, redirect to admins-manage.php
                header("Location: admins-manage.php");
                exit();
            } else {
                // Error adding admin to the database
                echo "Error adding admin to the database.";
            }
        } else {
            // Passwords do not match
            echo "Passwords do not match. Please enter passwords again.";
        }
    }
}

// Show admins list if it's a GET request
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Fetch admins data from the database using the function
    $admins = getAdminsList($pdo);
}

// Function to insert a new admin
function insertAdmin($pdo, $username, $name, $email, $password)
{
    try {
        $sql = "INSERT INTO admins (username, name, email, password) VALUES (:username, :name, :email, :password)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':username', $username, PDO::PARAM_STR);
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':password', password_hash($password, PASSWORD_DEFAULT), PDO::PARAM_STR);

        return $stmt->execute();
    } catch (PDOException $e) {
        // Log database errors or handle the exception as needed
        echo "Database error: " . $e->getMessage();
        return false;
    }
}

// Process admin deletion if it's a POST request and 'delete-admin' parameter is set
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete-admin"])) {
    // Check if 'id' parameter is set in the POST request
    if (isset($_POST["id"])) {
        $adminId = $_POST["id"];

        // Call the function to delete admin
        if (deleteAdmin($pdo, $adminId)) {
            // Successful deletion, return text
            echo "Admin successfully deleted.";
        } else {
            // Error on admin deletion
            http_response_code(500); // Set status code 500 (Internal Server Error)
            echo "Error deleting admin.";
        }
    }
}

// Function to delete admin
function deleteAdmin($pdo, $adminId)
{
    try {
        $sql = "DELETE FROM admins WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $adminId, PDO::PARAM_INT);

        return $stmt->execute();
    } catch (PDOException $e) {
        // Log database errors or handle the exception as needed
        echo "Database error: " . $e->getMessage();
        return false;
    }
}

// Function to fetch admins list
function getAdminsList($pdo)
{
    $sql = "SELECT * FROM admins;";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/mobile.css">

    <title>Administrators</title>
</head>

<body>

    <div class="admin-header">
        <h2>ART and human rights admin panel</h2>
        <!-- Add admin panel functions here -->
    </div>
    <div class="container-admin">
        <main class="main-admin">
            <!-- ASIDE ADMIN -->
            <?php include 'elems/aside-admin.php' ?>
            <!-- ASIDE ADMIN -->
            <div class="add-content">
                <div class="add-form">
                    <h3>Add an Administrator</h3><br><br>
                    <form id="admins-manage" action="admins-manage.php" method="post" enctype="multipart/form-data"
                        autocomplete="off">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrfTokenAdmins; ?>">
                        <label for="username">Username</label><br><br>
                        <input type="text" id="username" name="username" class="add-article" autocomplete="off"><br><br>
                        <label for="name">Name</label><br><br>
                        <input type="text" id="name" name="name" class="add-article" required
                            autocomplete="off"><br><br>
                        <label for="email">Email</label><br><br>
                        <input type="email" id="email" name="email" class="add-article" required
                            autocomplete="off"><br><br>
                        <label for="password">Password</label><br><br>
                        <input type="password" id="password" name="password" class="add-article" required
                            autocomplete="off"><br><br>
                        <label for="password-confirm">Confirm Password</label><br><br>
                        <input type="password" id="password-confirm" name="password-confirm" class="add-article"
                            required autocomplete="off"><br><br>
                        <button type="submit" class="admin-button">Add</button>
                    </form>

                    <div class="add-form">
                        <table cellspacing="20px">
                            <colgroup>
                                <col id="name">
                                <col id="email">
                                <col id="edit">
                                <col id="delete">
                            </colgroup>

                            <tbody>
                                <?php foreach ($admins as $row): ?>
                                    <tr>
                                        <td>
                                            <?= $row['name'] ?>
                                        </td>
                                        <td>
                                            <?= $row['email'] ?>
                                        </td>
                                        <td><a href="edit-admin.php?name=<?= $row['name'] ?>" class="edit-link">Edit</a></td>
                                        <td><a href="#" class="delete-link"
                                                data-admin-id="<?= $row['id'] ?>">Delete</a></td>

                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="/assets/js/script.js"></script>

    <script>
        document.querySelectorAll('.delete-link').forEach(link => {
            link.addEventListener('click', function (event) {
                event.preventDefault();

                const adminId = this.getAttribute('data-admin-id');

                fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `delete-admin=true&id=${adminId}`,
                })
                .then(response => response.text())
                .then(data => {
                    if (data.includes('Admin successfully deleted.')) {
                        // Successful deletion, perform additional actions (if necessary)
                        console.log('Admin successfully deleted.');
                        // Remove the row from the DOM
                        const rowToRemove = this.closest('tr');
                        rowToRemove.remove();
                    } else {
                        // Error on admin deletion
                        console.error('Error deleting admin.');
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                });
            });
        });
    </script>

</body>

</html>