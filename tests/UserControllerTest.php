<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../controllers/UserController.php';

class UserControllerTest extends TestCase {
    private $pdo;
    private $userController;

    protected function setUp(): void {
        $this->pdo = $this->createMock(PDO::class);
        $this->userController = new UserController($this->pdo);
    }

    public function testLoginSuccess() {
        $data = [
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('fetch')->willReturn([
            'id' => 1,
            'email' => 'test@example.com',
            'password_hash' => password_hash('password123', PASSWORD_DEFAULT)
        ]);

        $this->pdo->method('prepare')->willReturn($stmt);

        ob_start();
        $this->userController->login($data);
        $output = ob_get_clean();

        $this->assertStringContainsString('"token":"', $output);
        $this->assertStringNotContainsString('"message":"Invalid credentials"', $output);
    }

    public function testLoginInvalidCredentials() {
        $data = [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ];

        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('fetch')->willReturn([
            'id' => 1,
            'email' => 'test@example.com',
            'password_hash' => password_hash('password123', PASSWORD_DEFAULT)
        ]);

        $this->pdo->method('prepare')->willReturn($stmt);

        ob_start();
        $this->userController->login($data);
        $output = ob_get_clean();

        $this->assertStringContainsString('"message":"Invalid credentials"', $output);
    }

    public function testLoginUserNotFound() {
        $data = [
            'email' => 'nonexistent@example.com',
            'password' => 'password123'
        ];

        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('fetch')->willReturn(false);

        $this->pdo->method('prepare')->willReturn($stmt);

        ob_start();
        $this->userController->login($data);
        $output = ob_get_clean();

        $this->assertStringContainsString('"message":"Invalid credentials"', $output);
    }

    public function testRegisterSuccess() {
        $data = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123'
        ];

        $stmtCheck = $this->createMock(PDOStatement::class);
        $stmtCheck->method('rowCount')->willReturn(0);

        $stmtInsert = $this->createMock(PDOStatement::class);
        $stmtInsert->expects($this->once())->method('execute')->with($this->callback(function ($params) use ($data) {
            return $params[0] === $data['name']
                && $params[1] === $data['email']
                && password_verify($data['password'], $params[2])
                && $params[3] === 'user';
        }));

        $this->pdo->method('prepare')->willReturnOnConsecutiveCalls($stmtCheck, $stmtInsert);

        ob_start();
        $this->userController->register($data);
        $output = ob_get_clean();

        $this->assertStringContainsString('"message":"User created successfully"', $output);
    }

    public function testRegisterInvalidName() {
        $data = [
            'name' => 'Jo',
            'email' => 'john.doe@example.com',
            'password' => 'password123'
        ];

        ob_start();
        $this->userController->register($data);
        $output = ob_get_clean();

        $this->assertStringContainsString('"message":"Invalid name"', $output);
    }

    public function testRegisterInvalidEmail() {
        $data = [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => 'password123'
        ];

        ob_start();
        $this->userController->register($data);
        $output = ob_get_clean();

        $this->assertStringContainsString('"message":"Invalid email"', $output);
    }

    public function testRegisterInvalidPassword() {
        $data = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => '123' // Invalid password
        ];

        ob_start();
        $this->userController->register($data);
        $output = ob_get_clean();

        $this->assertStringContainsString('"message":"Invalid password"', $output);
    }
}
