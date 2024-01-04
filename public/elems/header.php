<header>
  <div class="menu-wraper">
    <div class="menu-container">
      <div class="upper-menu menu">
        <div class="menu-icon">
          <span></span>
        </div>
        <nav class="menu-body">
          <ul class="menu-list">
            <li><a href="http://art" class="menu-link fa-solid fa-house"
                data-category="home"></a></li>
            <li><a href="http:///art/category.php?category=visual" class="menu-link"
                data-category="visual" id="visual">l'art visuel</a>
              <span class="menu-arrow"></span>
              <ul class="menu-sublist">
                <li>
                  <a href="http://art/subcategory.php?category=visual&subcategory=painting"
                    class="menu-sublink" data-category="painting">peinture</a>
                </li>
                <li>
                  <a href="http://art/subcategory.php?category=visual&subcategory=sculpture"
                    class="menu-sublink">sculpture</a>
                </li>
                <li>
                  <a href="http://art/subcategory.php?category=visual&subcategory=photo"
                    class="menu-sublink">photographie</a>
                </li>
                <li>
                  <a href="http://art/subcategory.php?category=visual&subcategory=movies"
                    class="menu-sublink">cinéma</a>
                </li>
              </ul>
            </li>
            <li><a href="http://art/category.php?category=literature"
                class="menu-link">litérature</a>
              <span class="menu-arrow"></span>
              <ul class="menu-sublist">
                <li>
                  <a href="http://art/subcategory.php?category=literature&subcategory=prose"
                    class="menu-sublink">prose</a>
                </li>
                <li>
                  <a href="http://art/subcategory.php?category=literature&subcategory=poetry"
                    class="menu-sublink">poetry</a>
                </li>
              </ul>
            </li>
            <li><a href="http://art/category.php?category=events" class="menu-link">événements</a>
              <span class="menu-arrow"></span>
              <ul class="menu-sublist">
                <li>
                  <a href="http://art/subcategory.php?category=events&subcategory=exhibitions"
                    class="menu-sublink">expositions</a>
                </li>
                <li>
                  <a href="http://art/subcategory.php?category=events&subcategory=actions"
                    class="menu-sublink">actions</a>
                </li>
                <li>
                  <a href="http://art/subcategory.php?category=events&subcategory=manifestations"
                    class="menu-sublink">manifestations</a>
                </li>
              </ul>
            </li>
            <li><a href="http://art/category.php?category=pubs"
                class="menu-link">publications</a>
              <span class="menu-arrow"></span>
              <ul class="menu-sublist">
                <li>
                  <a href="http://art/subcategory.php?category=pubs&subcategory=articles"
                    class="menu-sublink">articles</a>
                </li>
                <li>
                  <a href="http://art/subcategory.php?category=pubs&subcategory=interviews"
                    class="menu-sublink">interviews</a>
                </li>
              </ul>
            </li>
            <li>
              <a href="about.php" class="menu-link">à propos</a>
            </li>
            <li>
              <a href="contact.php" class="menu-link">nous contacter</a>
            </li>
          </ul>
        </nav>
      </div>
    </div>
  </div>
  <form id="search-form" action="../search-handler.php" method="get">
    <input id="search" type="text" name="q" placeholder="Retrouver...">
    <button id="search-button" type="submit"></button>
  </form>

  </div>
</header>

<div class="subheader">
  <div class="social-links">
    <a href="https://www.facebook.com/www.ahrca.org/" target="_blank"><i
        class="fa-brands fa-facebook subheader-link"></i></a>
    <i class="fa-brands fa-youtube subheader-link"></i>
    <a href="https://twitter.com/SergeyIgnatyev1" target="_blank"><i
        class="fa-brands fa-x-twitter subheader-link"></i></a>
    <a href="https://www.flickr.com/photos/80528537@N08/" target="_blank"><i
        class="fa-brands fa-flickr subheader-link"></i></a>
    <a href="https://www.instagram.com/art_ahrca/" target="_blank"><i
        class="fa-brands fa-square-instagram subheader-link"></i></a>
  </div>
  <div class="languages-switchers">
    <a href="#" id="lang-en" class="subheader-link">EN</a> <a href="#" id="lang-ru" class="subheader-link">RU</a>
  </div>
</div>

<!-- Контейнер для результатов поиска -->
<div id="search-results-container"></div>

<script src="/assets/js/search.js"></script>