<?php
// ...existing code...
class AuthService {
    // Méthodes pour l'authentification, gestion des rôles, etc.

    public function isAdmin() {
        error_log('Check isAdmin - Session: ' . print_r($_SESSION, true));
        
        if (!isset($_SESSION['user'])) {
            error_log('Session user non définie');
            return false;
        }
        
        if (!isset($_SESSION['user']['role_id'])) {
            error_log('Role_id non défini dans la session user');
            return false;
        }
        
        $isAdmin = $_SESSION['user']['role_id'] === 1;
        error_log('isAdmin result: ' . ($isAdmin ? 'true' : 'false'));
        return $isAdmin;
    }
}
// ...existing code...
