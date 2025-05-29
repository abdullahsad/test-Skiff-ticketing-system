<?php

class User {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function create($name, $email, $password, $role) {
        $sql = $this->pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)");
        try {
            $sql->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $role]);
            return ['success' => true, 'user_id' => $this->pdo->lastInsertId()];
        } catch (PDOException $e) {
            return ['success' => false];
        }
    }

    public function findByEmail($email) {
        $sql = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $sql->execute([$email]);
        return $sql->fetch(PDO::FETCH_ASSOC);
    }

    public function findById($user_id) {
        $sql = $this->pdo->prepare("SELECT id, name, email, role FROM users WHERE id = ?");
        $sql->execute([$user_id]);
        return $sql->fetch(PDO::FETCH_ASSOC);
    }

    public function storeToken($user_id, $expires_at) {
        $token = bin2hex(random_bytes(16));
        $sql = $this->pdo->prepare("INSERT INTO api_tokens (user_id, token, expires_at, created_at) VALUES (?, ?, ?, ?)");
        $sql->execute([$user_id, $token, $expires_at, date('Y-m-d H:i:s')]);
        return $token;
    }

    public function deleteToken($token) {
        $sql = $this->pdo->prepare("DELETE FROM api_tokens WHERE token = ?");
        $sql->execute([$token]);
    }
}
?>
