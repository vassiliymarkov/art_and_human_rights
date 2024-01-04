<?php

error_reporting(E_ERROR);

// Disable displaying errors on screen
ini_set('display_errors', 0);

// Log errors to a file (make sure to set the correct path)
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log'); // Используем __DIR__ для получения текущей директории
// Set error handler
set_error_handler('errorHandler');

// Set exception handler
set_exception_handler('exceptionHandler');

// Function to handle errors
function errorHandler($errno, $errstr, $errfile, $errline) {
    // Write the error to the file
    logError("Error [$errno]: $errstr in $errfile on line $errline");
}

// Function to handle exceptions
function exceptionHandler($exception) {
    // Write the exception to the file
    logError("Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine());
}

// Function to log errors to a file
function logError($message) {
    $logFile = __DIR__ . '/../logs/error.log'; // Используем __DIR__ для получения текущей директории

    // Format the error message with date and time
    $logMessage = "[" . date("Y-m-d H:i:s") . "] " . $message . PHP_EOL;

    // Write the message to the file
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}

?>