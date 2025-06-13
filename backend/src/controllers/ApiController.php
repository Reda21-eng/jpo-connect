<?php
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../models/Comment.php';
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../database/Database.php';

class ApiController {
    private $student;
    private $notification;
    private $comment;

    public function __construct() {
        // Configure CORS headers
        header('Access-Control-Allow-Origin: http://localhost:5173');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Content-Type: application/json');

        try {
            $db = Database::getInstance();
            $this->student = new Student($db);
            $this->notification = new Notification($db);
            $this->comment = new Comment($db);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
            exit;
        }
    }

    public function register($data) {
        try {
            if (empty($data['firstname']) || empty($data['lastname']) || empty($data['email'])) {
                http_response_code(400);
                return ['error' => 'Missing required fields'];
            }
            $result = $this->student->register($data);
            return ['success' => true, 'data' => $result];
        } catch (Exception $e) {
            http_response_code(500);
            return ['error' => 'Registration failed: ' . $e->getMessage()];
        }
    }

    public function getStudents() {
        try {
            $students = $this->student->getAll();
            return ['success' => true, 'data' => $students];
        } catch (Exception $e) {
            http_response_code(500);
            return ['error' => 'Failed to fetch students: ' . $e->getMessage()];
        }
    }

    public function getNotifications($student_id) {
        try {
            if (empty($student_id)) {
                http_response_code(400);
                return ['error' => 'Student ID is required'];
            }
            $notifications = $this->notification->getForStudent($student_id);
            return ['success' => true, 'data' => $notifications];
        } catch (Exception $e) {
            http_response_code(500);
            return ['error' => 'Failed to fetch notifications: ' . $e->getMessage()];
        }
    }

    public function addNotification($data) {
        try {
            if (empty($data['student_id']) || empty($data['message'])) {
                http_response_code(400);
                return ['error' => 'Student ID and message are required'];
            }
            $result = $this->notification->add($data['student_id'], $data['message']);
            return ['success' => true, 'data' => $result];
        } catch (Exception $e) {
            http_response_code(500);
            return ['error' => 'Failed to add notification: ' . $e->getMessage()];
        }
    }

    public function getComments() {
        try {
            $auth = new AuthService();
            $includeModeration = isset($_SESSION['user']) && $_SESSION['user']['role_id'] === 1;
            $comments = $this->comment->getAll($includeModeration);
            
            // Log pour le débogage
            error_log('Récupération des commentaires - includeModeration: ' . ($includeModeration ? 'true' : 'false'));
            error_log('Nombre de commentaires: ' . count($comments));
            
            return ['success' => true, 'data' => $comments];
        } catch (Exception $e) {
            error_log('Erreur lors de la récupération des commentaires: ' . $e->getMessage());
            http_response_code(500);
            return ['error' => 'Failed to fetch comments: ' . $e->getMessage()];
        }
    }    public function addComment($data) {
        try {
            if (empty($data['content'])) {
                http_response_code(400);
                return ['error' => 'Le contenu est requis'];
            }
            $result = $this->comment->add($data['content']);
            if ($result) {
                return ['success' => true, 'data' => $result];
            }
            return ['error' => 'Erreur lors de l\'ajout du commentaire'];
        } catch (Exception $e) {
            error_log('Erreur lors de l\'ajout du commentaire: ' . $e->getMessage());
            http_response_code(500);
            return ['error' => 'Erreur lors de l\'ajout du commentaire: ' . $e->getMessage()];
        }
    }

    public function searchStudents($term) {
        try {
            $students = $this->student->search($term);
            return ['success' => true, 'data' => $students];
        } catch (Exception $e) {
            http_response_code(500);
            return ['error' => 'Failed to search students: ' . $e->getMessage()];
        }
    }

    public function unregister($student_id) {
        try {
            $result = $this->student->unregister($student_id);
            return ['success' => $result];
        } catch (Exception $e) {
            http_response_code(500);
            return ['error' => 'Failed to unregister student: ' . $e->getMessage()];
        }
    }    public function createJPO($data) {
        $auth = new AuthService();
        if (!$auth->isAdmin()) {
            http_response_code(403);
            return ['error' => 'Accès refusé : administrateur requis'];
        }
        try {
            $db = Database::getInstance();
            $jpoModel = new JPO($db);
            $result = $jpoModel->create($data);
            return ['success' => $result];
        } catch (Exception $e) {
            http_response_code(500);
            return ['error' => 'Erreur lors de la création de la JPO : ' . $e->getMessage()];
        }
    }

    public function deleteComment($commentId) {
        try {
            $auth = new AuthService();
            if (!$auth->isAdmin()) {
                http_response_code(403);
                return ['error' => 'Accès refusé : seuls les administrateurs peuvent supprimer les commentaires'];
            }

            $result = $this->comment->delete($commentId);
            return ['success' => $result];
        } catch (Exception $e) {
            http_response_code(500);
            return ['error' => 'Erreur lors de la suppression : ' . $e->getMessage()];
        }
    }

    public function moderateComment($data) {
        try {
            $auth = new AuthService();
            if (!$auth->isAdmin()) {
                http_response_code(403);
                return ['error' => 'Accès refusé : seuls les administrateurs peuvent modérer les commentaires'];
            }

            if (empty($data['id']) || empty($data['status'])) {
                http_response_code(400);
                return ['error' => 'ID et statut requis'];
            }

            $result = $this->comment->moderate($data['id'], $data['status'], $_SESSION['user']['id']);
            return ['success' => $result];
        } catch (Exception $e) {
            http_response_code(500);
            return ['error' => 'Erreur lors de la modération : ' . $e->getMessage()];
        }
    }

    public function searchJPOs($term) {
        try {
            $db = Database::getInstance();
            $jpoModel = new JPO($db);
            $jpos = $jpoModel->search($term);
            return ['success' => true, 'data' => $jpos];
        } catch (Exception $e) {
            http_response_code(500);
            return ['error' => 'Erreur lors de la recherche des JPO : ' . $e->getMessage()];
        }
    }
}