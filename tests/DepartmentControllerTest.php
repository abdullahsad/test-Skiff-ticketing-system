<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../controllers/DepartmentController.php';
require_once __DIR__ . '/../models/Department.php';

class DepartmentControllerTest extends TestCase {
    private $pdoMock;
    private $departmentMock;
    private $controller;

    protected function setUp(): void {
        $this->pdoMock = $this->createMock(PDO::class);
        $this->departmentMock = $this->createMock(Department::class);
        $this->controller = new DepartmentController($this->pdoMock);

        // Inject the mock into the controller
        $reflection = new ReflectionClass($this->controller);
        $property = $reflection->getProperty('department');
        $property->setAccessible(true);
        $property->setValue($this->controller, $this->departmentMock);
    }

    public function testCreateWithInvalidName() {
        $this->expectOutputString(json_encode(['message' => 'Invalid department name']));
        $this->controller->create(['name' => '']);
        $this->assertEquals(400, http_response_code());
    }

    public function testCreateWithExistingDepartment() {
        $this->departmentMock->method('findByName')->willReturn(['id' => 1, 'name' => 'HR']);
        $this->expectOutputString(json_encode(['message' => 'Department already exists']));
        $this->controller->create(['name' => 'HR']);
        $this->assertEquals(409, http_response_code());
    }

    public function testCreateSuccess() {
        $this->departmentMock->method('findByName')->willReturn(null);
        $this->departmentMock->method('create')->willReturn(true);
        $this->expectOutputString(json_encode(['message' => 'Department created successfully']));
        $this->controller->create(['name' => 'Finance']);
        $this->assertEquals(201, http_response_code());
    }

    public function testDeleteSuccess() {
        $this->departmentMock->method('findById')->willReturn(['id' => 1, 'name' => 'HR']);
        $this->departmentMock->method('delete')->willReturn(true);
        $this->expectOutputString(json_encode(['message' => 'Department deleted successfully']));
        $this->controller->delete(1);
    }
}