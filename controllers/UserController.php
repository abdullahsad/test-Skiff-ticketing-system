<?php

require_once 'models/User.php';

class UserController {
    private $pdo;
    private $user;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->user = new User($pdo);
    }

    /**
     * Registers a new user.
     *
     * Validates the provided user data, checks for existing users with the same email,
     * and creates a new user if all validations pass.
     *
     * @param array $data An associative array containing the following keys:
     *  - 'name' (string): The name of the user. Must be at least 3 characters long.
     *  - 'email' (string): The email address of the user. Must be a valid email format.
     *  - 'password' (string): The password for the user. Must be at least 6 characters long.
     *
     * @return void Outputs a JSON response with the appropriate HTTP status code:
     *  - 400: If any validation fails (invalid name, email, or password, or email already exists).
     *  - 201: If the user is successfully created.
     *  - 500: If there is a database error during user creation.
     */
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

    /**
     * Handles user login functionality.
     *
     * @param array $data An associative array containing the user's login credentials:
     *                    - 'email' (string): The user's email address.
     *                    - 'password' (string): The user's password.
     *
     * @return void Outputs a JSON response with the following HTTP status codes:
     *              - 400: If the email is invalid or missing.
     *              - 401: If the credentials are invalid.
     *              - 200: If the login is successful, returns a token in the response.
     *
     * The function performs the following steps:
     * 1. Validates the email format.
     * 2. Checks if the user exists and verifies the password.
     * 3. If successful, generates a token valid for 12 hours and returns it.
     */
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
        } else{
            $expires_at = date('Y-m-d H:i:s', strtotime('+12 hour')); 
            $token = $this->user->storeToken($user['id'], $expires_at);
            http_response_code(200);
            echo json_encode(['token' => $token]);
        }
    }

    /**
     * Retrieves a user by their ID and returns the user data in JSON format.
     *
     * @param int $user_id The ID of the user to retrieve.
     * 
     * @return void Outputs the user data as JSON if found, or a 404 response with an error message if not found.
     */
    public function get_user($user_id) {
        $user = $this->user->findById($user_id);
        if ($user) {
            echo json_encode($user);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'User not found']);
        }
    }

    /**
     * Logs out a user by deleting their API token from the database.
     *
     * @param string $token The API token to be deleted.
     * 
     * This method removes the specified token from the `api_tokens` table,
     * effectively logging out the user associated with the token. Upon
     * successful deletion, a JSON response is returned indicating the
     * logout was successful.
     */
    public function logout($token) {
        $sql = $this->pdo->prepare("DELETE FROM api_tokens WHERE token = ?");
        $sql->execute([$token]);
        echo json_encode(['message' => 'Logged out successfully']);
    }
}

?>