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

        $sql = $pdo->prepare("SELECT user_id FROM api_tokens WHERE token = ? AND expires_at > NOW()");
        $sql->execute([$token]);
        $user_id = $sql->fetchColumn();

        if (!$user_id) {
            http_response_code(401);
            echo json_encode(['message' => 'Invalid or expired token']);
            exit();
        }

        return $user_id;
    }

    public static function checkAdmin($pdo) {
        $user_id = self::checkAuth($pdo);
        $sql = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $sql->execute([$user_id]);
        $role = $sql->fetchColumn();

        if ($role !== 'admin') {
            http_response_code(403);
            echo json_encode(['message' => 'Forbidden']);
            exit();
        }
        return $user_id;
    }

    public static function checkAdminOrAgent($pdo) {
        $user_id = self::checkAuth($pdo);
        $sql = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $sql->execute([$user_id]);
        $role = $sql->fetchColumn();

        if ($role !== 'admin' && $role !== 'agent') {
            http_response_code(403);
            echo json_encode(['message' => 'Forbidden']);
            exit();
        }
        return $user_id;
    }

    public static function getToken($pdo) {
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            return null;
        }
        $token = str_replace('Bearer ', '', $headers['Authorization']);
        return $token;
    }

    public static function getRole($pdo) {
        $user_id = self::checkAuth($pdo);
        $sql = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $sql->execute([$user_id]);
        return $sql->fetchColumn();
    }
}
