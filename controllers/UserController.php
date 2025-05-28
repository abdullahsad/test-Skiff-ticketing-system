<?php

class UserController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function register($data) {
        //validate name
        if (empty($data['name']) || strlen($data['name']) < 3) {
            http_response_code(400);
            echo json_encode(['message' => 'Invalid name']);
            return;
        }
        //validate email
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['message' => 'Invalid email']);
            return;
        }
        //validate password
        if (empty($data['password_hash']) || strlen($data['password_hash']) < 6) {
            http_response_code(400);
            echo json_encode(['message' => 'Invalid password']);
            return;
        }
        $stmt = $this->pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$data['name'], $data['email'], password_hash($data['password_hash'], PASSWORD_DEFAULT), $data['role']]);
        echo json_encode(['message' => 'User  created successfully']);
    }

    public function login($data) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$data['email']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($data['password_hash'], $user['password_hash'])) {
            $token = bin2hex(random_bytes(16)); // Generate a token
            session_start();
            $_SESSION['token'] = $token;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role']; // Store user role in session
            http_response_code(200);
            echo json_encode(['token' => $token]);
        } else {
            http_response_code(401);
            echo json_encode(['message' => 'Invalid credentials']);
        }
    }
}

?>