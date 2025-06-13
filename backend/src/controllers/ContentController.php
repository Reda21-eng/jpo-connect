<?php
require_once __DIR__ . '/../models/Database.php';

class ContentController {
    private $db;

    public function __construct() {
        // Configure CORS headers
        header('Access-Control-Allow-Origin: http://localhost:5173');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Content-Type: application/json');

        try {
            $this->db = Database::getInstance();
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
            exit;
        }
    }

    // Example method for managing editable content
    public function getContent($type) {
        try {
            if (empty($type)) {
                http_response_code(400);
                return json_encode(['error' => 'Content type is required']);
            }
            // Placeholder logic for fetching content (sessions, practical info)
            $content = []; // Replace with actual logic
            return json_encode(['success' => true, 'data' => $content]);
        } catch (Exception $e) {
            http_response_code(500);
            return json_encode(['error' => 'Failed to fetch content: ' . $e->getMessage()]);
        }
    }
}