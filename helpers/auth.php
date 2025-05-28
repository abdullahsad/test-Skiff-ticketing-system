<?php

class Auth {
    public static function checkAuth($pdo) {
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized']);
            exit();
        }

        $token = str_replace('Bearer ', '', $headers['Authorization']);

        $stmt = $pdo->prepare("SELECT user_id FROM api_tokens WHERE token = ? AND expires_at > NOW()");
        $stmt->execute([$token]);
        $user_id = $stmt->fetchColumn();

        if (!$user_id) {
            http_response_code(401);
            echo json_encode(['message' => 'Invalid or expired token']);
            exit();
        }

        return $user_id;
    }

    public static function checkAdmin($pdo) {
        $user_id = self::checkAuth($pdo);

        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $role = $stmt->fetchColumn();

        if ($role !== 'admin') {
            http_response_code(403);
            echo json_encode(['message' => 'Forbidden']);
            exit();
        }

        return $user_id;
    }
}
