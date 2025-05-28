<?php

class Auth {
    public static function checkAuth() {
        session_start();
        if (!isset($_SESSION['token'])) {
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized']);
            exit();
        }
    }

    public static function checkAdmin() {
        $this->checkAuth();
        if ($_SESSION['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['message' => 'Forbidden']);
            exit();
        }
    }

    public static function invalidateToken() {
        session_start();
        session_destroy();
    }
}

?>
