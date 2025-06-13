<?php
require_once __DIR__ . '/src/database/Database.php';

try {
    $db = Database::getInstance();
    
    // Vérifier si la colonne status existe déjà
    $checkColumn = $db->query("SHOW COLUMNS FROM comments LIKE 'status'");
    if ($checkColumn->rowCount() == 0) {
        // Ajouter les nouvelles colonnes
        $alterTable = "ALTER TABLE comments
            ADD COLUMN status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending' AFTER content,
            ADD COLUMN moderator_id INT AFTER created_at,
            ADD COLUMN moderated_at TIMESTAMP NULL AFTER moderator_id";
            
        $db->exec($alterTable);
        
        // Ajouter la clé étrangère
        $addForeignKey = "ALTER TABLE comments
            ADD FOREIGN KEY (moderator_id) REFERENCES users(id)";
            
        $db->exec($addForeignKey);
        
        echo "La table comments a été mise à jour avec succès.\n";
    } else {
        echo "La structure de la table comments est déjà à jour.\n";
    }
    
} catch (PDOException $e) {
    echo "Erreur lors de la mise à jour de la base de données : " . $e->getMessage() . "\n";
}
