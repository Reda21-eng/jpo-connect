<?php
class Comment {
    private $conn;
    
    public function __construct($db) { 
        $this->conn = $db; 
    }

    public function add($content) {
        $query = "INSERT INTO comments (content, status, created_at) VALUES (:content, 'pending', NOW())";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':content', $content);
        $success = $stmt->execute();
        
        if ($success) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function getAll($includeModeration = false) {
        $query = "SELECT c.*, u.username as moderator_name 
                 FROM comments c 
                 LEFT JOIN users u ON c.moderator_id = u.id";
                 
        if (!$includeModeration) {
            $query .= " WHERE c.status = 'approved'";
        }
        
        $query .= " ORDER BY c.created_at DESC";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur SQL: " . $e->getMessage());
            throw $e;
        }
    }

    public function moderate($id, $status, $moderatorId) {
        if (!in_array($status, ['approved', 'rejected'])) {
            return false;
        }
        
        $query = "UPDATE comments 
                 SET status = :status, 
                     moderator_id = :moderator_id, 
                     moderated_at = NOW() 
                 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':moderator_id', $moderatorId);
        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM comments WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
