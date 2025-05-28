<?php

class UserController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
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
        $sql = $this->pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $sql->execute([$data['email']]);
        if ($sql->rowCount() > 0) {
            http_response_code(400);
            echo json_encode(['message' => 'Email already exists']);
            return;
        }
        $sql = $this->pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)");
        try{
            $sql->execute([$data['name'], $data['email'], password_hash($data['password'], PASSWORD_DEFAULT), 'user']);
            echo json_encode(['message' => 'User  created successfully']);
            return;
        }catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Error creating user: ' . $e->getMessage()]);
            return;
        }
    }

    public function login($data) {
        $sql = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $sql->execute([$data['email']]);
        $user = $sql->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($data['password'], $user['password_hash'])) {
            $token = bin2hex(random_bytes(16)); // Generate a token
            $expires_at = date('Y-m-d H:i:s', strtotime('+12 hour')); // Token valid for 1 hour
            $this->storeToken($user['id'], $token, $expires_at);
            http_response_code(200);
            echo json_encode(['token' => $token]);
        } else {
            http_response_code(401);
            echo json_encode(['message' => 'Invalid credentials']);
        }
    }

    public function get_user($user_id) {
        
        $sql = $this->pdo->prepare("SELECT id, name, email, role FROM users WHERE id = ?");
        $sql->execute([$user_id]);
        $user = $sql->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            echo json_encode($user);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'User not found']);
        }
    }


    private function storeToken($user_id, $token, $expires_at) {
        $sql = $this->pdo->prepare("INSERT INTO api_tokens (user_id, token, expires_at, created_at) VALUES (?, ?, ?, ?)");
        $sql->execute([$user_id, $token, $expires_at, date('Y-m-d H:i:s')]);
    }
    public function logout($token) {
        $sql = $this->pdo->prepare("DELETE FROM api_tokens WHERE token = ?");
        $sql->execute([$token]);
        echo json_encode(['message' => 'Logged out successfully']);
    }
    public function validateToken($token) {
        $sql = $this->pdo->prepare("SELECT user_id FROM api_tokens WHERE token = ? AND expires_at > NOW()");
        $sql->execute([$token]);
        return $sql->fetchColumn(); // Returns user_id if valid, false otherwise
    }
}

?>