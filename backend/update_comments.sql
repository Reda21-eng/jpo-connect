USE jpo_connect;

-- Supprime l'ancienne table si elle existe
DROP TABLE IF EXISTS comments;

-- Crée la nouvelle table comments avec toutes les colonnes nécessaires
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    moderator_id INT,
    moderated_at TIMESTAMP NULL,
    FOREIGN KEY (moderator_id) REFERENCES users(id)
);
