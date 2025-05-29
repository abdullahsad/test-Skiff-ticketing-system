<?php

require_once 'models/User.php';

class UserController {
    private $pdo;
    private $user;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->user = new User($pdo);
    }

    public function register($data) {
        if (empty($data['name']) || strlen($data['name']) < 3) {
            http_response_code(400);
            echo json_encode(['message' => 'Invalid name']);
            return;
        }
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['message' => 'Invalid email']);
            return;
        }
        if (empty($data['password']) || strlen($data['password']) < 6) {
            http_response_code(400);
            echo json_encode(['message' => 'Invalid password']);
            return;
        }

        $existing_user = $this->user->findByEmail($data['email']);
        if ($existing_user) {
            http_response_code(400);
            echo json_encode(['message' => 'Email already exists']);
            return;
        }

        $user = $this->user->create($data['name'], $data['email'], $data['password'], 'user');
        if ($user['success']) {
            http_response_code(201);
            echo json_encode(['message' => 'User created successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Error creating user: Database error']);
        }
    }

    public function login($data) {
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['message' => 'Invalid email']);
            return;
        }
        $user = $this->user->findByEmail($data['email']);
        if (!$user || !password_verify($data['password'], $user['password_hash'])) {
            http_response_code(401);
            echo json_encode(['message' => 'Invalid credentials']);
            return;
        }else{
            $expires_at = date('Y-m-d H:i:s', strtotime('+12 hour')); 
            $token = $this->user->storeToken($user['id'], $expires_at);
            http_response_code(200);
            echo json_encode(['token' => $token]);
        }
    }

    public function get_user($user_id) {
        $user = $this->user->findById($user_id);
        if ($user) {
            echo json_encode($user);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'User not found']);
        }
    }

    public function logout($token) {
        $sql = $this->pdo->prepare("DELETE FROM api_tokens WHERE token = ?");
        $sql->execute([$token]);
        echo json_encode(['message' => 'Logged out successfully']);
    }
}

?>