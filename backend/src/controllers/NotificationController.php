<?php
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../models/Database.php';

class NotificationController {
    private $db;
    private $notification;

    public function __construct() {
        // Configure CORS headers
        header('Access-Control-Allow-Origin: http://localhost:5173');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Content-Type: application/json');

        try {
            $this->db = Database::getInstance();
            $this->notification = new Notification($this->db);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
            exit;
        }
    }

    // Example method for managing notifications
    public function sendNotification($data) {
        try {
            if (empty($data['student_id']) || empty($data['message'])) {
                http_response_code(400);
                return json_encode(['error' => 'Student ID and message are required']);
            }
            $result = $this->notification->add($data['student_id'], $data['message']);
            return json_encode(['success' => true, 'data' => $result]);
        } catch (Exception $e) {
            http_response_code(500);
            return json_encode(['error' => 'Failed to send notification: ' . $e->getMessage()]);
        }
    }
}