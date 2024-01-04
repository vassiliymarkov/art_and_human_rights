<?php

// Set Content Security Policy for font sources
require_once '../config/csp.php';

// Include error handling file
require 'errors-handling.php';

// Start a new session or resume the existing session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Function to generate a CSRF token
function generate_csrf_token()
{
    // Check if the session is started
    if (session_status() == PHP_SESSION_ACTIVE) {
        // Generate a random token using 32 bytes of random data
        $token = bin2hex(random_bytes(32));

        // Save the generated token in the session
        $_SESSION['csrf_token'] = $token;

        // Return the generated token
        return $token;
    } else {
        // Handle the case when the session is not started
        return false;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/mobile.css">
    <title>nous contacter</title>
</head>

<body>

    <div class="container">
        <!-- HEADER -->
        <?php include 'elems/header.php' ?>
        <!-- HEADER -->

        <div class="stub">

        </div>

        <div class="content">
            <main class="main-static">

                <div class="single-article">
                    <h2>Ecrivez nous</h2>
                    <div id="contact-form">
                        <p>
                            Pour nous envoyer un message, veuillez remplir tous les champs du formulaire
                        </p>
                        <form action="mail.php" method="post" id="contact" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            <label for="name">Votre nom</label><span class="required">*</span><br>
                            <input type="text" class="contact" id="name" name="name" required><br><br>
                            <label for="subject">Sujet</label><span class="required">*</span><br>
                            <input type="text" class="contact" id="subject" name="subject" required><br><br>
                            <label for="email">Votre emal</label><span class="required">*</span><br>
                            <input type="email" class="contact" id="email" name="email" required><br><br>
                            <label for="message">Votre message</label><span class="required">*</span><br>
                            <textarea class="contact" id="message" name="message" required></textarea><br>
                            <button type="submit" value="Envoyer" id="send">Envoyer</button>
                        </form>
                    </div>
                </div>

            </main>

        </div>

        <!-- FOOTER -->
        <?php include 'elems/footer.php' ?>
        <!-- FOOTER -->
        <script>
            // Attach an event listener to the form with the id 'contact' for the submit event
                document.getElementById('contact').addEventListener('submit', function (event) {
                // Get values from the form fields
                let nameValue = document.getElementById('name').value;
                let subjectValue = document.getElementById('subject').value;
                let emailValue = document.getElementById('email').value;
                let messageValue = document.getElementById('message').value;

                // Validate that only letters and spaces are entered for the "Name" field
                let regexName = /^[a-zA-Zа-яА-ЯёЁ ]*$/;
                if (!regexName.test(nameValue)) {
                    alert("You can only use letters and spaces in the name field.");
                    event.preventDefault(); // Prevent form submission
                    return; // Exit the function
                }

                // Validate that only letters, numbers, and spaces are entered for the "Subject" field
                let regexSubject = /^[a-zA-Zа-яА-ЯёЁ0-9 ]*$/;
                if (!regexSubject.test(subjectValue)) {
                    alert("You can only use letters, numbers, and spaces in the subject field.");
                    event.preventDefault(); // Prevent form submission
                    return;
                }

                // Validate that a valid email address is entered for the "Email" field
                let regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!regexEmail.test(emailValue)) {
                    alert("Please enter a valid email address.");
                    event.preventDefault(); // Prevent form submission
                    return;
                }

                // Validate that only letters, numbers, spaces, and certain punctuation are entered for the "Message" field
                let regexMessage = /^[a-zA-Zа-яА-ЯёЁ0-9\s.,!?]*$/;
                if (!regexMessage.test(messageValue)) {
                    alert("You can only use letters, numbers, spaces, and certain punctuation in the text field.");
                    event.preventDefault(); // Prevent form submission
                }
            });
        </script>

        <script src="https://kit.fontawesome.com/78197913c0.js" crossorigin="anonymous"></script>
        <script src="/assets/js/script.js"></script>
</body>

</html>