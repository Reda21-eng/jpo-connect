<?php
// ...existing code...
class SiteContent {
    private int $id;
    private string $key_name;
    private string $value;
    // Getters, setters, constructeur, etc.
    public static function get($type) {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM site_content WHERE key_name = :type LIMIT 1');
        $stmt->bindParam(':type', $type);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public static function update($type, $content) {
        $db = Database::getInstance();
        $stmt = $db->prepare('UPDATE site_content SET value = :content WHERE key_name = :type');
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':type', $type);
        return $stmt->execute();
    }
}
// ...existing code...
