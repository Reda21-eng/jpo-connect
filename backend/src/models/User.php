<?php
// ...existing code...
class User {
    private int $id;
    private string $username;
    private string $password;
    private string $email;
    private int $role_id;
    private string $created_at;
    // Getters, setters, constructeur, etc.
    private $conn;
    public function __construct($db) { $this->conn = $db; }

    public function registerAdmin($data) {
        // On suppose que le rôle admin a l'ID 1
        $query = "INSERT INTO users (username, email, password, role_id) VALUES (:username, :email, :password, 1)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $data['username']);
        $stmt->bindParam(':email', $data['email']);
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt->bindParam(':password', $hashedPassword);
        return $stmt->execute();
    }

    public function registerUser($data) {
        // Par défaut, rôle user (id=2)
        try {
            $query = "INSERT INTO users (username, email, password, role_id) VALUES (:username, :email, :password, 2)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $data['username']);
            $stmt->bindParam(':email', $data['email']);
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->execute();
            return true;
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) { // violation de contrainte UNIQUE
                return ['error' => 'Email ou nom d\'utilisateur déjà utilisé'];
            }
            return ['error' => 'Erreur lors de l\'inscription : ' . $e->getMessage()];
        }
    }    public function login($data) {
        error_log("Tentative de connexion pour : " . $data['email']);

        $query = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $data['email']);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($data['password'], $user['password'])) {
            error_log("Mot de passe vérifié pour l'utilisateur : " . $user['username'] . " (role_id: " . $user['role_id'] . ")");
            // On ne retourne pas le mot de passe
            unset($user['password']);
            return $user;
        }

        error_log("Échec de connexion pour : " . $data['email']);
        return false;
    }

    public function updateRole($userId, $roleId) {
        $query = "UPDATE users SET role_id = :role_id WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':role_id', $roleId, \PDO::PARAM_INT);
        $stmt->bindParam(':id', $userId, \PDO::PARAM_INT);
        return $stmt->execute();
    }

    public static function getAll() {
        $db = Database::getInstance();
        $stmt = $db->query('SELECT * FROM users');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
// ...existing code...
