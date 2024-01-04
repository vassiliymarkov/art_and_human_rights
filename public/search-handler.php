<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';
require 'errors-handling.php';
require 'db.php';

use MeiliSearch\Client;

$userQuery = $_GET['q'] ?? '';

// Если запроса нет, пропускаем выполнение логики поиска
if (empty($userQuery)) {
    echo "No query provided";
    exit(); // или другая логика, если необходимо
}

echo "User query: " . $userQuery;

try {
    // Создание индекса MeiliSearch
    $client = new Client('http://localhost:7700', 'uiG6qPcWIAe8akP5UdLBjdiLCDYuW_3gbK2mXg-pzHM');
    $index = $client->index('publications');

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL-запрос для извлечения данных из базы (замените на свой запрос)
    $sql = "SELECT publication_id, title, subtitle, text, url, publication_date FROM publications";

    // Подготовка запроса
    $stmt = $pdo->prepare($sql);

    // Выполнение запроса
    $stmt->execute();

    // Получение результатов запроса
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Проверка наличия результатов
    if (count($result) > 0) {
        // Перебор результатов и добавление их в индекс MeiliSearch
        foreach (array_chunk($result, 100) as $chunk) {
            $documents = [];
            foreach ($chunk as $row) {
                $documents[] = [
                    'publication_id' => $row['publication_id'],
                    'title' => $row['title'],
                    'subtitle' => $row['subtitle'],
                    'text' => $row['text'],
                    'url' => $row['url'],
                    'publication_date' => $row['publication_date'],
                    // ... другие поля
                ];
            }

            // Добавление документов в индекс MeiliSearch
            $index->addDocuments($documents);
        }
    } else {
        echo "0 results";
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
} catch (\Exception $e) {
    echo "MeiliSearch error: " . $e->getMessage();
} finally {
    // Закрытие соединения с базой данных (необязательно для PDO)
    $pdo = null;
}

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
    <?php
$userQuery = $_GET['q'] ?? '';

try {
    // Если есть поисковой запрос, выполняем поиск
    if (!empty($userQuery)) {
        $searchResult = $index->search($userQuery);

        // Выводим результаты красиво
        echo '<h3 class="search-results-header">Résultats de recherche pour : &laquo;' . $userQuery . '&raquo;</h3><br><br>';
        
        $hits = $searchResult->getHits();
        $hitsCount = count($hits);
        
        foreach ($hits as $index => $hit) {
            echo '<div class="search-results">';
            echo '<a href="' . $hit['url'] . '"><h3>' . $hit['title'] . '</h3></a><br>';
            echo '<a href="' . $hit['url'] . '"<p>' . $hit['subtitle'] . '</p></a>';
            echo '</div>';
        
            // Проверка, не последний ли элемент
            if ($hitsCount > 1) {
                echo '<hr>';
            }
        }
        
        // Если нет результатов
        if (empty($searchResult->getHits())) {
            echo '<p>No results found for "' . $userQuery . '"</p>';
        }
    } else {
        // Если нет поискового запроса, выводим форму для ввода
        echo '<h2>Search</h2>';
        echo '<form action="search-handler.php" method="get">';
        echo '<input type="text" name="query" placeholder="Enter your search query">';
        echo '<input type="submit" value="Search">';
        echo '</form>';
    }

} catch (\Exception $e) {
    echo 'MeiliSearch error: ' . $e->getMessage();
}

?>
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