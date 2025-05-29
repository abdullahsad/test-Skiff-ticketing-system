<?php
    // echo "hello world";
    // die();
    require_once 'controllers/UserController.php';
    require_once 'controllers/DepartmentController.php';
    require_once 'controllers/TicketController.php';
    require_once 'middleware/RateLimiter.php';
    require_once 'helpers/auth.php';

    function handleRequest($pdo, $redis) {
        $method = $_SERVER['REQUEST_METHOD'];
        $script_name = dirname($_SERVER['SCRIPT_NAME']);

        if ($script_name == '/') {
            $script_name = '';
        }

        $path = str_replace($script_name, '', $_SERVER['REQUEST_URI']);
        $path = trim(parse_url($path, PHP_URL_PATH), '/');
        $uri = explode('/', $path);

        $rate_limiter = new RateLimiter($redis, 5, 60);
        $ip = $_SERVER['REMOTE_ADDR'];
        $rate_limiter->handle("rate:ip:$ip");


        if ($uri[0] === 'register' && $method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $userController = new UserController($pdo);
            $userController->register($data);
        } elseif ($uri[0] === 'login' && $method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $userController = new UserController($pdo);
            $userController->login($data);
        } elseif ($uri[0] === 'logout' && $method === 'POST') {
            $user_id = Auth::checkAuth($pdo);
            $token = Auth::getToken($pdo);
            $userController = new UserController($pdo);
            $userController->logout($token);
        } elseif ($uri[0] === 'get-user' && $method === 'GET') {
            $user_id = Auth::checkAuth($pdo);
            $userController = new UserController($pdo);
            $userController->get_user($user_id);
        } elseif ($uri[0] === 'departments' && $method === 'POST') {
            Auth::checkAdmin($pdo);
            $data = json_decode(file_get_contents('php://input'), true);
            $departmentController = new DepartmentController($pdo);
            $departmentController->create($data);
        } elseif ($uri[0] === 'departments' && $method === 'GET' && count($uri) == 1) {
            Auth::checkAdmin($pdo);
            $departmentController = new DepartmentController($pdo);
            $departmentController->index();
        } elseif ($uri[0] === 'departments' && $method === 'GET' && count($uri) == 2) {
            Auth::checkAdmin($pdo);
            $departmentController = new DepartmentController($pdo);
            $departmentController->show($uri[1]);
        } elseif ($uri[0] === 'departments' && $method === 'PATCH' && count($uri) == 2) {
            Auth::checkAdmin($pdo);
            $data = json_decode(file_get_contents('php://input'), true);
            $departmentController = new DepartmentController($pdo);
            $departmentController->update($uri[1], $data);
        } elseif ($uri[0] === 'departments' && $method === 'DELETE' && count($uri) == 2) {
            Auth::checkAdmin($pdo);
            $departmentController = new DepartmentController($pdo);
            $departmentController->delete($uri[1]);
        } elseif ($uri[0] === 'tickets' && $method === 'POST' && count($uri) == 1) {
            $user_id = Auth::checkAuth($pdo);

            //rate limit ticket creation by user
            $rate_limiter = new RateLimiter($redis, 3, 60);
            $rate_limiter->handle("rate:user:$user_id");

            $content_type = isset($_SERVER['CONTENT_TYPE']) ? trim($_SERVER['CONTENT_TYPE']) : '';
            if ($content_type == 'application/json') {
                $data = json_decode(file_get_contents('php://input'), true);
                $ticketController = new TicketController($pdo);
                $ticketController->create($data, $user_id);
            }
            elseif (strpos($content_type, 'multipart/form-data') !== false) {
                $data = $_POST;
                $files = $_FILES['attachment'];
                $ticketController = new TicketController($pdo);
                $ticketController->createWithFiles($data, $files, $user_id);
            }
            else {
                http_response_code(400);
                echo json_encode(['message' => 'Invalid Content-Type']);
            }
        } elseif ($uri[0] === 'tickets' && $method === 'GET' && count($uri) == 1) {
            $role = Auth::getRole($pdo);
            $ticketController = new TicketController($pdo);
            $ticketController->index($_GET, $role);
        } elseif ($uri[0] === 'tickets' && $method === 'GET' && count($uri) == 2) {
            $ticketController = new TicketController($pdo);
            $ticketController->show($uri[1]);
        } elseif ($uri[0] === 'assign-ticket' && $method === 'POST' && count($uri) == 2) {
            $user_id = Auth::checkAdminOrAgent($pdo);
            $data = json_decode(file_get_contents('php://input'), true);
            $ticketController = new TicketController($pdo);
            $ticketController->assign($uri[1], $data);
        } elseif ($uri[0] === 'change-ticket-status' && $method === 'POST' && count($uri) == 2) {
            $user_id = Auth::checkAdminOrAgent($pdo);
            $data = json_decode(file_get_contents('php://input'), true);
            $ticketController = new TicketController($pdo);
            $ticketController->changeStatus($uri[1], $data);
        } elseif ($uri[0] === 'tickets' && $method === 'DELETE' && count($uri) == 2) {
            Auth::checkAdmin($pdo);
            $ticketController = new TicketController($pdo);
            $ticketController->delete($uri[1]);
        }
        elseif ($uri[0] === 'add-notes-to-ticket' && $method === 'POST' && count($uri) == 2) {
            $user_id = Auth::checkAuth($pdo);
            $data = json_decode(file_get_contents('php://input'), true);
            $ticketController = new TicketController($pdo);
            $ticketController->addNote($uri[1], $user_id, $data);
        }
        else{
            http_response_code(404);
            echo json_encode(['message' => 'Page Not Found']);
        }
    }
?>
