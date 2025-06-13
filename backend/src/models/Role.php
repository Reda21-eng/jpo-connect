<?php
// ...existing code...
class Role {
    private int $id;
    private string $name;
    // Getters, setters, constructeur, etc.

    public static function getAll() {
        $db = Database::getInstance();
        $stmt = $db->query('SELECT * FROM roles');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
// ...existing code...
