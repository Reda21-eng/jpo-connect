<?php
// Désactiver l'affichage des erreurs
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Rediriger les erreurs vers un fichier de log
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

// Fonction pour gérer les erreurs fatales
function shutdownHandler() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Internal Server Error']);
        error_log('Fatal Error: ' . $error['message'] . ' in ' . $error['file'] . ' on line ' . $error['line']);
    }
}
register_shutdown_function('shutdownHandler');

// Gestionnaire d'exceptions personnalisé
function exceptionHandler($e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Internal Server Error']);
    error_log('Uncaught Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
}
set_exception_handler('exceptionHandler');
