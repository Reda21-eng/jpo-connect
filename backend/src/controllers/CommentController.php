<?php
require_once __DIR__ . '/../models/Comment.php';
require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '/../services/AuthService.php';

class CommentController {
    private $db;
    private $comment;
    private $auth;    public function __construct() {
        try {
            $this->db = Database::getInstance();
            $this->comment = new Comment($this->db);
            $this->auth = new AuthService();
        } catch (Exception $e) {
            error_log('CommentController Error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
            exit;
        }
    }    public function getComments() {
        try {
            $includeModeration = $this->auth->isAdmin();
            error_log('Getting comments, isAdmin: ' . ($includeModeration ? 'true' : 'false'));
            
            $comments = $this->comment->getAll($includeModeration);
            error_log('Found ' . count($comments) . ' comments');
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $comments]);
        } catch (Exception $e) {
            error_log('GetComments Error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch comments: ' . $e->getMessage()]);
        }
    }

    public function addComment($data) {
        try {
            if (empty($data['content'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Le contenu est requis']);
                return;
            }
            $result = $this->comment->add($data['content']);
            echo json_encode(['success' => true, 'data' => $result]);
        } catch (Exception $e) {
            error_log('AddComment Error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Failed to add comment: ' . $e->getMessage()]);
        }
    }

    public function moderateComment($data) {
        try {
            if (!$this->auth->isAdmin()) {
                http_response_code(403);
                echo json_encode(['error' => 'Accès refusé : seuls les administrateurs peuvent modérer les commentaires']);
                return;
            }

            if (empty($data['id']) || empty($data['status'])) {
                http_response_code(400);
                echo json_encode(['error' => 'ID et statut requis']);
                return;
            }

            $result = $this->comment->moderate($data['id'], $data['status'], $_SESSION['user']['id']);
            echo json_encode(['success' => $result]);
        } catch (Exception $e) {
            error_log('ModerateComment Error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Failed to moderate comment: ' . $e->getMessage()]);
        }
    }

    public function deleteComment($id) {
        try {
            if (!$this->auth->isAdmin()) {
                http_response_code(403);
                echo json_encode(['error' => 'Accès refusé : seuls les administrateurs peuvent supprimer les commentaires']);
                return;
            }
            $result = $this->comment->delete($id);
            echo json_encode(['success' => $result]);
        } catch (Exception $e) {
            error_log('DeleteComment Error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete comment: ' . $e->getMessage()]);
        }
    }
}