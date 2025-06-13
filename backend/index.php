<?php
// backend/index.php

// Inclure la configuration
require_once __DIR__ . '/config.php';

// Démarrer la session pour toutes les requêtes
session_set_cookie_params([
    'secure' => false, // à mettre à true en production avec HTTPS
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

// CORS headers adaptés pour credentials: 'include'
$allowed_origin = 'http://localhost:5173';
header('Access-Control-Allow-Origin: ' . $allowed_origin);
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    require_once __DIR__ . '/src/database/Database.php';
    require_once __DIR__ . '/src/controllers/ApiController.php';
    require_once __DIR__ . '/src/models/JPO.php';
    require_once __DIR__ . '/src/models/User.php';
    require_once __DIR__ . '/src/services/AuthService.php';
    require_once __DIR__ . '/src/controllers/CommentController.php';

    $controller = new ApiController();
    $method = $_SERVER['REQUEST_METHOD'];
    $path = $_GET['path'] ?? '';
    $input = json_decode(file_get_contents('php://input'), true);

    switch ($path) {
        case 'register':
            // Ne pas double-encoder la réponse
            $result = $controller->register($input);
            if (is_string($result)) {
                echo $result;
            } else {
                echo json_encode($result);
            }
            break;
        case 'students':
            $result = $controller->getStudents();
            echo json_encode($result);
            break;
        case 'notifications':
            $student_id = $_GET['student_id'] ?? null;
            $result = $controller->getNotifications($student_id);
            if (is_string($result)) {
                echo $result;
            } else {
                echo json_encode($result);
            }
            break;
        case 'add-notification':
            $result = $controller->addNotification($input);
            if (is_string($result)) {
                echo $result;
            } else {
                echo json_encode($result);
            }
            break;
        case 'comments':
            $commentController = new CommentController();
            if ($method === 'GET') {
                $commentController->getComments();
            } else if ($method === 'DELETE' && isset($_GET['id'])) {
                $commentController->deleteComment($_GET['id']);
            }
            break;
        case 'add-comment':
            $result = $controller->addComment($input);
            if (is_string($result)) {
                echo $result;
            } else {
                echo json_encode($result);
            }
            break;
        case 'search':
            $term = $_GET['term'] ?? '';
            $result = $controller->searchJPOs($term);
            echo json_encode($result);
            break;
        case 'jpos':
            $db = Database::getInstance();
            $jpoModel = new JPO($db);
            if ($method === 'GET') {
                if (isset($_GET['id'])) {
                    $jpo = $jpoModel->getById($_GET['id']);
                    echo json_encode(['success' => true, 'data' => $jpo]);
                } else {
                    $jpos = $jpoModel->getAll();
                    echo json_encode(['success' => true, 'data' => $jpos]);
                }
            } else if ($method === 'PUT' && isset($_GET['id'])) {
                $auth = new AuthService();
                if (!$auth->isAdmin()) {
                    http_response_code(403);
                    echo json_encode(['error' => 'Accès refusé : administrateur requis']);
                    break;
                }
                $result = $jpoModel->update($_GET['id'], $input);
                echo json_encode(['success' => $result]);
            } else if ($method === 'DELETE' && isset($_GET['id'])) {
                $auth = new AuthService();
                if (!$auth->isAdmin()) {
                    http_response_code(403);
                    echo json_encode(['error' => 'Accès refusé : administrateur requis']);
                    break;
                }
                $result = $jpoModel->delete($_GET['id']);
                echo json_encode(['success' => $result]);
            }
            break;
        case 'create-jpo':
            $result = $controller->createJPO($input);
            echo json_encode($result);
            break;
        case 'unregister':
            $result = $controller->unregister($input['student_id']);
            echo json_encode($result);
            break;
        case 'register-admin':
            require_once __DIR__ . '/src/models/User.php';
            $db = Database::getInstance();
            $userModel = new User($db);
            $result = $userModel->registerAdmin($input);
            echo json_encode(['success' => $result]);
            break;
        case 'register-user':
            require_once __DIR__ . '/src/models/User.php';
            $db = Database::getInstance();
            $userModel = new User($db);
            $result = $userModel->registerUser($input);
            if (is_array($result) && isset($result['error'])) {
                echo json_encode(['success' => false, 'error' => $result['error']]);
            } else {
                echo json_encode(['success' => $result]);
            }
            break;        case 'login':
            error_log('Tentative de connexion - Données reçues : ' . json_encode($input));
            
            if (empty($input['email']) || empty($input['password'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Email et mot de passe requis']);
                break;
            }

            $db = Database::getInstance();
            $userModel = new User($db);
            $user = $userModel->login($input);
            
            if ($user) {
                error_log('Connexion réussie pour : ' . $user['email']);
                $_SESSION['user'] = $user;
                error_log('Session créée : ' . json_encode($_SESSION));
                echo json_encode(['success' => true, 'user' => $user]);
            } else {
                error_log('Échec de connexion : identifiants invalides');
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Identifiants invalides']);
            }
            break;
        case 'moderate-comment':
            if ($method === 'POST') {
                $result = $controller->moderateComment($input);
                echo json_encode($result);
            }
            break;
        case 'stats':
            if (method_exists($controller, 'getStats')) {
                $result = $controller->getStats();
                echo json_encode($result);
            } else {
                require_once __DIR__ . '/src/controllers/DashboardController.php';
                $dashboard = new DashboardController();
                echo $dashboard->getStats();
            }
            break;
        case 'site-content':
            require_once __DIR__ . '/src/models/SiteContent.php';
            // $db = Database::getInstance();
            // $siteContent = new SiteContent($db);
            if ($method === 'GET' && isset($_GET['type'])) {
                $data = SiteContent::get($_GET['type']);
                echo json_encode(['success' => true, 'data' => $data]);
            } else if ($method === 'PUT') {
                $input = json_decode(file_get_contents('php://input'), true);
                $ok = SiteContent::update($input['type'], $input['content']);
                echo json_encode(['success' => $ok]);
            }
            break;
        case 'roles':
            require_once __DIR__ . '/src/models/Role.php';
            // $db = Database::getInstance();
            // $roleModel = new Role($db);
            $roles = Role::getAll();
            echo json_encode(['success' => true, 'data' => $roles]);
            break;
        case 'users':
            require_once __DIR__ . '/src/models/User.php';
            // $db = Database::getInstance();
            // $userModel = new User($db);
            if ($method === 'GET') {
                $users = User::getAll();
                echo json_encode(['success' => true, 'data' => $users]);
            } else if ($method === 'PUT') {
                $input = json_decode(file_get_contents('php://input'), true);
                $db = Database::getInstance();
                $userModel = new User($db);
                $ok = $userModel->updateRole($input['id'], $input['role']);
                echo json_encode(['success' => $ok]);
            }
            break;
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Invalid endpoint']);
            break;
    }
} catch (Exception $e) {
    error_log('Server Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal Server Error']);
}
