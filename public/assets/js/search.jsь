function handleMeiliSearch() {
    // Получите контейнер для результатов поиска
    let searchResultsContainer = document.getElementById('search-results-container');

    // Парсим данные из строки запроса URL
    const urlParams = new URLSearchParams(window.location.search);
    const searchTerm = urlParams.get('q');

    // Если есть поисковый запрос, вызовите MeiliSearch API для получения результатов
    if (searchTerm) {
        const meiliSearchEndpoint = 'http://localhost:7700';
        const indexName = 'publications';

        fetch(`${meiliSearchEndpoint}/indexes/${indexName}/search?q=${encodeURIComponent(searchTerm)}`)
            .then(response => response.json())
            .then(data => {
                console.log('Ответ от MeiliSearch:', data);
                const results = data.hits;

                if (results && results.length > 0) {
                    results.forEach(function (result, index) {
                        console.log('Результат поиска:', result);

                        const category = result.name; // оставляем category_name, если это поле правильно представляет категорию
                        const title = result.title;

                        if (category !== undefined && title !== undefined) {
                            var resultElement = document.createElement('div');
                            resultElement.textContent = `${index + 1}. ${title}`;

                            var linkElement = document.createElement('a');
                            linkElement.href = `http://localhost/art/public_html/article.php?category=${encodeURIComponent(category)}&title=${encodeURIComponent(title)}`;
                            linkElement.appendChild(resultElement);

                            searchResultsContainer.appendChild(linkElement);

                            console.log('Результаты MeiliSearch:', results);
                        }
                    });
                } else {
                    console.error(`Отсутствует category_name или title в результатах поиска (${index}).`);
                }
            })
            .catch(error => {
                console.error('Ошибка при вызове API MeiliSearch:', error);
            });
    } else {
        console.error('Поисковый запрос отсутствует в URL.');
    }
}

// Вызываем функцию при загрузке страницы
document.addEventListener('DOMContentLoaded', handleMeiliSearch);