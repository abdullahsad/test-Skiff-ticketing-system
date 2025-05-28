<?php
    // echo "hello world";
    // die();
    require_once 'controllers/AuthController.php';
    require_once 'controllers/UserController.php';
    require_once 'controllers/DepartmentController.php';
    require_once 'controllers/TicketController.php';

    function handleRequest($pdo) {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = explode('/', trim($_SERVER['REQUEST_URI'], '/'));

        if ($uri[0] === 'register' && $method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $userController = new UserController($pdo);
            $userController->register($data);
        } elseif ($uri[0] === 'login' && $method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $userController = new UserController($pdo);
            $token = $userController->login($data);
        } elseif ($uri[0] === 'departments' && $method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $departmentController = new DepartmentController($pdo);
            $departmentController->create($data);
        } elseif (preg_match('/departments\/(\d+)/', $uri[0], $matches) && $method === 'PATCH') {
            $data = json_decode(file_get_contents('php://input'), true);
            $departmentController = new DepartmentController($pdo);
            $departmentController->update($matches[1], $data);
        } elseif (preg_match('/departments\/(\d+)/', $uri[0], $matches) && $method === 'DELETE') {
            $departmentController = new DepartmentController($pdo);
            $departmentController->delete($matches[1]);
        } elseif ($uri[0] === 'tickets' && $method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $ticketController = new TicketController($pdo);
            $ticketController->create($data);
        } elseif (preg_match('/tickets\/(\d+)\/status/', $uri[0], $matches) && $method === 'PATCH') {
            $data = json_decode(file_get_contents('php://input'), true);
            $ticketController = new TicketController($pdo);
            $ticketController->updateStatus($matches[1], $data);
        }else{
            http_response_code(404);
            echo json_encode(['message' => 'Not Found']);
        }
    }
?>
