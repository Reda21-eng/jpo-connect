<?php
class Notification {
    private $conn;
    public function __construct($db) { $this->conn = $db; }
    public function getForStudent($student_id) {
        $query = "SELECT * FROM notifications WHERE student_id = :student_id ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function add($student_id, $message) {
        $query = "INSERT INTO notifications (student_id, message) VALUES (:student_id, :message)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->bindParam(':message', $message);
        return $stmt->execute();
    }
}
