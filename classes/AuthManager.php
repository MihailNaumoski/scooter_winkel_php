<?php
class AuthManager {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function login($username, $password) {
        $stmt = $this->db->prepare("SELECT id, username, password, name FROM personnel WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if($user && password_verify($password, $user['password'])) {
            $roles = $this->getUserRoles($user['id']);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['roles'] = $roles;
            return true;
        }
        return false;
    }

    private function getUserRoles($userId) {
        $stmt = $this->db->prepare("SELECT role FROM personnel_roles WHERE personnel_id = ?");
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll();

        $roles = [];
        foreach($rows as $row) {
            $roles[] = $row['role'];
        }
        return $roles;
    }

    public function logout() {
        session_destroy();
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    // check rol
    public function hasRole($requiredRole) {
        if(!$this->isLoggedIn()) {
            return false;
        }

        $roles = $_SESSION['roles'] ?? [];

        if(in_array('Management', $roles)) {
            return true;
        }

        return in_array($requiredRole, $roles);
    }

    public function requireLogin() {
        if(!$this->isLoggedIn()) {
            header('Location: /login.php');
            exit;
        }
    }

    public function requireRole($requiredRole) {
        $this->requireLogin();

        if(!$this->hasRole($requiredRole)) {
            die("Geen toegang. Je hebt de rol '$requiredRole' nodig.");
        }
    }
}
