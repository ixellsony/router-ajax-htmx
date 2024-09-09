<?php
// Configuration des routes
$routes = [
    '' => ['file' => 'pages/home.php', 'HTMXOnly' => false],
    'page1' => ['file' => 'pages/page1.php', 'HTMXOnly' => false],
    'login' => ['file' => 'pages/login.php', 'HTMXOnly' => false],
];

// Page 404
function handle404() {
    http_response_code(404);
    include '404.php';
    exit;
}

// Obtenir la route actuelle
$currentRoute = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

// Vérifier si la route fait partie du dossier 'media'
if (strpos($currentRoute, 'media/') === 0) {
    // Si le fichier existe dans le dossier 'media', on le sert directement
    if (file_exists($currentRoute)) {
        header('Content-Type: ' . mime_content_type($currentRoute));
        readfile($currentRoute);
        exit;
    } else {
        handle404();
    }
}

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


        <?= $content ?>
