<?php

require 'errors-handling.php';
require_once 'db.php';
require_once 'search-handler.php';

use MeiliSearch\Client;

// Создание индекса MeiliSearch
$client = new Client('http://localhost:7700', 'uiG6qPcWIAe8akP5UdLBjdiLCDYuW_3gbK2mXg-pzHM');
$index = $client->index('publications');

function processMeiliSearchUpdates() {
    $meilisearchUpdates = getMeiliSearchUpdates();

    foreach ($meilisearchUpdates as $update) {
        $operation = $update['operation'];
        $publicationId = $update['publication_id'];

        switch ($operation) {
            /* case 'insert':
                sendInsertUpdateToMeiliSearch($publicationId);
                break;
            case 'update':
                sendUpdateToMeiliSearch($publicationId);
                break; */
            case 'delete':
                sendDeleteToMeiliSearch($publicationId);
                break;
            // Добавьте логику для других возможных операций
        }
    }
}

function getMeiliSearchUpdates() {
    global $pdo;

    try {
        $sql = "SELECT operation, publication_id FROM meilisearch_updates";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
        return [];
    }
}

function sendDeleteToMeiliSearch($deleteId) {
    global $client, $index;

    try {
        // Ваш код для отправки соответствующего обновления в MeiliSearch
        $index->deleteDocument($deleteId);
        echo "Delete sent to MeiliSearch for publicationId: $deleteId";
    } catch (\Exception $e) {
        echo "MeiliSearch error: " . $e->getMessage();
    }
}

?>