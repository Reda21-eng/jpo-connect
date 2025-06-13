<?php
class Student {
    private $conn;
    public function __construct($db) { $this->conn = $db; }

    public function register($data) {
        // Vérifier si l'étudiant existe déjà
        $stmt = $this->conn->prepare("SELECT id FROM students WHERE email = :email");
        $stmt->bindParam(':email', $data['email']);
        $stmt->execute();
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($student) {
            $student_id = $student['id'];
        } else {
            $query = "INSERT INTO students (firstname, lastname, email) VALUES (:firstname, :lastname, :email)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':firstname', $data['firstname']);
            $stmt->bindParam(':lastname', $data['lastname']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->execute();
            $student_id = $this->conn->lastInsertId();
        }
        // Si jpo_id fourni, inscrire à la JPO
        if (!empty($data['jpo_id'])) {
            $stmt = $this->conn->prepare("INSERT IGNORE INTO registrations (user_id, jpo_id) VALUES (:student_id, :jpo_id)");
            $stmt->bindParam(':student_id', $student_id);
            $stmt->bindParam(':jpo_id', $data['jpo_id']);
            $stmt->execute();
        }
        return true;
    }

    public function getAll() {
        $stmt = $this->conn->query("SELECT * FROM students ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function search($term) {
        $query = "SELECT * FROM students WHERE firstname LIKE :term OR lastname LIKE :term OR email LIKE :term ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $likeTerm = "%$term%";
        $stmt->bindParam(':term', $likeTerm);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function unregister($student_id) {
        $stmt = $this->conn->prepare("DELETE FROM students WHERE id = :id");
        $stmt->bindParam(':id', $student_id);
        return $stmt->execute();
    }
}
