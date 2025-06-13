<?php
// ...existing code...
class JPO {
    private int $id;
    private string $title;
    private string $description;
    private string $date;
    private int $capacity;
    private int $registered_count;
    private bool $is_active;
    private $conn;

    public function __construct($db) { 
        $this->conn = $db; 
    }

    // CREATE
    public function create($data) {
        $stmt = $this->conn->prepare("INSERT INTO jpos (title, description, date, capacity, author_id) VALUES (:title, :description, :date, :capacity, :author_id)");
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':date', $data['date']);
        $stmt->bindParam(':capacity', $data['capacity']);
        $stmt->bindParam(':author_id', $data['user_id']);
        return $stmt->execute();
    }

    // READ
    public function getAll() {
        $stmt = $this->conn->query("SELECT jpos.*, users.username AS author FROM jpos LEFT JOIN users ON jpos.author_id = users.id ORDER BY date DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT jpos.*, users.username AS author FROM jpos LEFT JOIN users ON jpos.author_id = users.id WHERE jpos.id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function search($term) {
        $query = "SELECT * FROM jpos WHERE title LIKE :term OR description LIKE :term ORDER BY date DESC";
        $stmt = $this->conn->prepare($query);
        $likeTerm = "%$term%";
        $stmt->bindParam(':term', $likeTerm);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // UPDATE
    public function update($id, $data) {
        $stmt = $this->conn->prepare("UPDATE jpos SET title = :title, description = :description, date = :date, capacity = :capacity WHERE id = :id");
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':date', $data['date']);
        $stmt->bindParam(':capacity', $data['capacity']);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // DELETE
    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM jpos WHERE id = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
// ...existing code...
