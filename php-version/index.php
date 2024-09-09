<?php
// Configuration des routes
$routes = [
    '' => ['file' => 'home.php', 'HTMXOnly' => false],
    'page1' => ['file' => 'page1.php', 'HTMXOnly' => false],
];

// Page 404
function handle404() {
    http_response_code(404);
    include '404.php';
    exit;
}

// Obtenir la route actuelle
$currentRoute = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

// Vérifier si la route existe, sinon utiliser la page 404
if (!isset($routes[$currentRoute])) {
    handle404();
}

// Vérifier si la route est marquée HTMXOnly et si la requête provient de HTMX
if ($routes[$currentRoute]['HTMXOnly'] && !isset($_SERVER['HTTP_HX_REQUEST'])) {
    handle404();
}

// Générer le contenu
ob_start();
include $routes[$currentRoute]['file'];
$content = ob_get_clean();

// Si c'est une requête HTMX, renvoyer seulement le contenu
if (isset($_SERVER['HTTP_HX_REQUEST'])) {
    echo $content;
    exit;
}

// Pour les requêtes non-HTMX, afficher la page complète
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My App</title>
    <script src="https://unpkg.com/htmx.org@latest" integrity="sha384-L6OqL9pRWyyFU3+/bjdSri+iIphTN/bvYyM37tICVyOJkWZLpP2vGn6VUEXgzg6h" crossorigin="anonymous"></script>
</head>
<body>
    <nav>
        <a href="/" hx-get="/" hx-swap="innerHTML" hx-trigger="click" hx-target="#content" hx-push-url="true">Home</a>
        <a href="/page1" hx-get="/page1" hx-swap="innerHTML" hx-trigger="click" hx-target="#content" hx-push-url="true">Page1</a>
    </nav>
    <div id="content">
        <?= $content ?>
    </div>
</body>
</html>
