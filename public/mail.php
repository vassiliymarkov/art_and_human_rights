<?php
// Check if the request is coming from the expected referer URL
if ($_SERVER['HTTP_REFERER'] !== 'http://art/contact.php') {
    header('HTTP/1.0 403 Forbidden');
    die('Access Forbidden');
}

// Sanitize and validate input data from the contact form
$name = htmlspecialchars($_POST['name']);
$max_length = 50;

// Check the length of the name
if (strlen($name) > $max_length) {
    // Redirect to an error page for name length exceeding
    header('Location: error-name.php');
    exit; // Terminate script execution
}

$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

// Validate the email address
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Invalid email address. Please enter a correct address.");
}

$subject = htmlspecialchars($_POST['subject']);
$max_length = 100;

// Check the length of the subject
if (strlen($subject) > $max_length) {
    // Redirect to an error page for subject length exceeding
    header('Location: error-subject.php');
    exit; // Terminate script execution
}

$message = htmlspecialchars($_POST['message']);
$max_length = 1500;

// Check the length of the message
if (strlen($message) > $max_length) {
    // Redirect to an error page for message length exceeding
    header('Location: error-message.php');
    exit; // Terminate script execution
}

session_start();

// Check CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    // Handle CSRF error
    die("Invalid CSRF token");
}

// Include PHPMailer library
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../vendor/phpmailer/phpmailer/src/Exception.php';
require '../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '../vendor/phpmailer/phpmailer/src/SMTP.php';

// Create a new PHPMailer instance
$mail = new PHPMailer;
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->Port = 587;
$mail->SMTPAuth = true;
$mail->Username = 'vmstudio72@gmail.com';
$mail->Password = 'mivl qvyt qdlm amal';
$mail->setFrom('vmstudio72@gmail.com', 'ART and human rights');
$mail->addAddress('vassiliy.markov.via@gmail.com', 'Recipient Name');

// Add reply-to address
if ($mail->addReplyTo($_POST['email'], $_POST['name'])) {
    $mail->Subject = 'Formulaire de contact ART and human rights';
    $mail->isHTML(false);
    $mail->Body = <<<EOT
E-mail: {$_POST['email']}
Nom: {$_POST['name']}
Sujet: {$_POST['subject']}
Message: {$_POST['message']}
EOT;

    // Send the email and handle success or error
    if ($mail->send()) {
        header('Location: success-mail.php');
        exit();
    } else {
        echo 'Error: ' . $mail->ErrorInfo;
    }
} else {
    echo 'Invalid reply-to address';
}

// Unset the CSRF token after processing
unset($_SESSION['csrf_token']);
?>