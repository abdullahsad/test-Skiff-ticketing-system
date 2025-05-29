<?php
    // echo "hello world";
    // die();
    require_once 'controllers/UserController.php';
    require_once 'controllers/DepartmentController.php';
    require_once 'controllers/TicketController.php';
    require_once 'middleware/RateLimiter.php';
    require_once 'helpers/auth.php';

    /**
     * Handles incoming HTTP requests and routes them to the appropriate controller and method.
     *
     * @param PDO $pdo The PDO instance for database interactions.
     * @param Redis $redis The Redis instance for caching and rate limiting.
     *
     * The function performs the following:
     * - Parses the request URI and HTTP method.
     * - Implements rate limiting for IP addresses and specific user actions.
     * - Routes requests to various endpoints based on the URI and HTTP method:
     *   - User-related endpoints:
     *     - POST /register: Registers a new user.
     *     - POST /login: Logs in a user.
     *     - POST /logout: Logs out a user.
     *     - GET /get-user: Retrieves the authenticated user's details.
     *   - Department-related endpoints:
     *     - POST /departments: Creates a new department (Admin only).
     *     - GET /departments: Lists all departments (Admin only).
     *     - GET /departments/{id}: Retrieves details of a specific department (Admin only).
     *     - PATCH /departments/{id}: Updates a specific department (Admin only).
     *     - DELETE /departments/{id}: Deletes a specific department (Admin only).
     *   - Ticket-related endpoints:
     *     - POST /tickets: Creates a new ticket (supports JSON and multipart/form-data).
     *     - GET /tickets: Lists all tickets (role-based access).
     *     - GET /tickets/{id}: Retrieves details of a specific ticket.
     *     - DELETE /tickets/{id}: Deletes a specific ticket (Admin only).
     *     - POST /assign-ticket/{id}: Assigns a ticket to a user (Admin or Agent only).
     *     - POST /change-ticket-status/{id}: Changes the status of a ticket (Admin or Agent only).
     *     - POST /add-notes-to-ticket/{id}: Adds notes to a ticket.
     * - Returns appropriate HTTP responses for success, errors, or invalid routes.
     *
     * @throws Exception If rate limiting is exceeded or authentication/authorization fails.
     */
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
