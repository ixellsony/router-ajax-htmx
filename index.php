<?php
// Configuration des routes
$routes = [
    '' => ['file' => 'home.php', 'title' => 'Accueil'],
    'page1' => ['file' => 'page1.php', 'title' => 'Page 1'],
    'page2' => ['file' => 'page2.php', 'title' => 'Page 2'],
    'contact' => ['file' => 'contact.php', 'title' => 'Contact'],
];

// Fonction pour obtenir la route actuelle
function getCurrentRoute() {
    $path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    return $path === '' ? '' : $path;
}

// Obtenir la route actuelle
$currentRoute = getCurrentRoute();

// Vérifier si la route existe, sinon utiliser la page 404
if (!isset($routes[$currentRoute])) {
    $currentRoute = '404';
    $routes[$currentRoute] = ['file' => '404.php', 'title' => 'Page non trouvée'];
    http_response_code(404);
} else {
    http_response_code(200);
}

// Vérifier si c'est une requête HTMX
$isHtmxRequest = isset($_SERVER['HTTP_HX_REQUEST']);

// Fonction pour générer le contenu
function generateContent($file) {
    ob_start();
    include $file;
    return ob_get_clean();
}

// Si c'est une requête HTMX, renvoyer seulement le contenu
if ($isHtmxRequest) {
    echo generateContent($routes[$currentRoute]['file']);
    exit;
}

// Pour les requêtes non-HTMX, afficher la page complète
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon App</title>
    <script src="https://unpkg.com/htmx.org@1.9.2" integrity="sha384-L6OqL9pRWyyFU3+/bjdSri+iIphTN/bvYyM37tICVyOJkWZLpP2vGn6VUEXgzg6h" crossorigin="anonymous"></script>
</head>
<body>
    <nav>
        <a href="/" hx-get="/" hx-swap="innerHTML" hx-trigger="click" hx-target="#content" hx-push-url="true">Home</a>
        <a href="page1" hx-get="page1" hx-swap="innerHTML" hx-trigger="click" hx-target="#content" hx-push-url="true">Page1</a>
    </nav>
    <div id="content">
        <?= generateContent($routes[$currentRoute]['file']) ?>
    </div>
</body>
</html>
