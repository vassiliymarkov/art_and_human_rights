<?php

require 'errors-handling.php';
require 'db.php';

session_start();

if (isset($_SESSION['media_id'])) {
    $media_id = $_SESSION['media_id'];
}

?>


<!DOCTYPE html>
<html>
<head>
    <!-- Здесь могут быть другие мета-теги и стили -->
    <meta http-equiv="refresh" content="3;url=admin-panel.php">
</head>
<body>
    <p>Операция успешно завершена. Вы будете перенаправлены обратно через 3 секунды.</p>
    <!-- Здесь можно добавить другие сообщения или контент, который вы хотите отобразить -->
</body>
</html>