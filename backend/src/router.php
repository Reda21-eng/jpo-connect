<?php
// Routeur principal du back-end JPO Connect

spl_autoload_register(function ($class) {
    $paths = ['models/', 'controllers/', 'services/', 'database/'];
    foreach ($paths as $path) {
        $file = __DIR__ . '/' . $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Exemple de routage basique (à adapter selon vos besoins)
$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

switch (true) {
    case preg_match('#^/api/users#', $uri):
        $controller = new UserController();
        // ...dispatch selon $method
        break;
    case preg_match('#^/api/jpos#', $uri):
        $controller = new JPOController();
        // ...dispatch selon $method
        break;
    // ...autres routes
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
}
