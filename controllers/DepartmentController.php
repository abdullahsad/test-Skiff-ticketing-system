<?php

require_once 'models/Department.php';

class DepartmentController {
    private $department;

    public function __construct($pdo) {
        $this->department = new Department($pdo);
    }

    /**
     * Creates a new department.
     *
     * This method validates the provided department data, checks for duplicates,
     * and creates a new department if the data is valid and does not already exist.
     *
     * @param array $data An associative array containing the department data.
     *                     - 'name' (string): The name of the department. Must be at least 2 characters long.
     *
     * @return void Outputs a JSON response with the appropriate HTTP status code:
     *              - 400: If the department name is invalid.
     *              - 409: If a department with the same name already exists.
     *              - 201: If the department is created successfully.
     *              - 500: If there is an error while creating the department.
     */
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

    /**
     * Displays a list of all departments.
     *
     * This method retrieves all department records from the database
     * using the `findAll` method of the department model and returns
     * them as a JSON-encoded response.
     *
     * @return void Outputs a JSON-encoded array of department data.
     */
    public function index() {
        $departments = $this->department->findAll();
        echo json_encode($departments);
    }

    /**
     * Display the details of a specific department by its ID.
     *
     * @param int $id The ID of the department to retrieve.
     * 
     * @return void Outputs the department details as a JSON response if found,
     *              otherwise returns a 404 HTTP response with an error message.
     */
    public function show($id) {
        $department = $this->department->findById($id);
        if ($department) {
            echo json_encode($department);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Department not found']);
        }
    }

    /**
     * Updates the details of an existing department.
     *
     * @param int $id The ID of the department to update.
     * @param array $data An associative array containing the updated department data.
     *                     - 'name' (string): The new name of the department. Must be at least 2 characters long.
     *
     * @return void Outputs a JSON response with the result of the operation:
     *              - 400 Bad Request: If the 'name' field is missing or invalid.
     *              - 404 Not Found: If the department with the given ID does not exist.
     *              - 200 OK: If the department is successfully updated.
     *              - 500 Internal Server Error: If the update operation fails.
     */
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

    /**
     * Deletes a department by its ID.
     *
     * @param int $id The ID of the department to delete.
     * 
     * This method performs the following steps:
     * 1. Checks if the department with the given ID exists.
     *    - If not found, it returns a 404 HTTP response with a JSON message.
     * 2. Attempts to delete the department.
     *    - If successful, it returns a JSON message indicating success.
     *    - If deletion fails, it returns a 500 HTTP response with a JSON error message.
     * 
     * @return void Outputs a JSON response with the result of the operation.
     */
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
