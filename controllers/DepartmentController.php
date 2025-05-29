<?php

require_once 'models/Department.php';

class DepartmentController {
    private $department;

    public function __construct($pdo) {
        $this->department = new Department($pdo);
    }

    public function create($data) {
        if (empty($data['name']) || strlen(trim($data['name'])) < 2) {
            http_response_code(400);
            echo json_encode(['message' => 'Invalid department name']);
            return;
        }
        $existing = $this->department->findByName(trim($data['name']));
        if ($existing) {
            http_response_code(409);
            echo json_encode(['message' => 'Department already exists']);
            return;
        }
        $result = $this->department->create(trim($data['name']));
        if ($result) {
            http_response_code(201);
            echo json_encode(['message' => 'Department created successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to create department']);
        }
    }

    public function index() {
        $departments = $this->department->findAll();
        echo json_encode($departments);
    }

    public function show($id) {
        $department = $this->department->findById($id);
        if ($department) {
            echo json_encode($department);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Department not found']);
        }
    }

    public function update($id, $data) {
        if (empty($data['name']) || strlen(trim($data['name'])) < 2) {
            http_response_code(400);
            echo json_encode(['message' => 'Invalid department name']);
            return;
        }

        $existing = $this->department->findById($id);
        if (!$existing) {
            http_response_code(404);
            echo json_encode(['message' => 'Department not found']);
            return;
        }

        $result = $this->department->update($id, trim($data['name']));
        if ($result) {
            echo json_encode(['message' => 'Department updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to update department']);
        }
    }

    public function delete($id) {
        $existing = $this->department->findById($id);
        if (!$existing) {
            http_response_code(404);
            echo json_encode(['message' => 'Department not found']);
            return;
        }

        $result = $this->department->delete($id);
        if ($result) {
            echo json_encode(['message' => 'Department deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to delete department']);
        }
    }
}
