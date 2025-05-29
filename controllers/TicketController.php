<?php

require_once 'models/Ticket.php';
require_once 'models/Department.php';

class TicketController {
    private $ticket;
    private $department;

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->ticket = new Ticket($pdo);
        $this->department = new Department($pdo);
    }

    /**
     * Creates a new ticket.
     *
     * @param array $data An associative array containing the ticket details:
     *                    - 'title' (string): The title of the ticket (minimum 3 characters).
     *                    - 'description' (string): The description of the ticket (minimum 5 characters).
     *                    - 'department_id' (int): The ID of the department associated with the ticket.
     * @param int $user_id The ID of the user creating the ticket.
     *
     * @return void Outputs a JSON response with the appropriate HTTP status code:
     *              - 400: If the title, description, or department ID is invalid or missing.
     *              - 404: If the specified department does not exist.
     *              - 201: If the ticket is successfully created.
     *              - 500: If there is an error while creating the ticket.
     */
    public function create($data, $user_id) {
        if (empty($data['title']) || strlen(trim($data['title'])) < 3) {
            http_response_code(400);
            echo json_encode(['message' => 'Invalid title']);
            return;
        }
        if (empty($data['description']) || strlen(trim($data['description'])) < 5) {
            http_response_code(400);
            echo json_encode(['message' => 'Invalid description']);
            return;
        }
        if (empty($data['department_id'])) {
            http_response_code(400);
            echo json_encode(['message' => 'Missing department']);
            return;
        } else{
            $department = $this->department->findById($data['department_id']);
            if (!$department) {
                http_response_code(404);
                echo json_encode(['message' => 'Department not found']);
                return;
            }
        }

        $result = $this->ticket->create(
            trim($data['title']), 
            trim($data['description']), 
            $user_id, 
            $data['department_id']
        );

        if ($result) {
            http_response_code(201);
            echo json_encode(['message' => 'Ticket created successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to create ticket']);
        }
    }

    /**
     * Creates a new ticket with associated files.
     *
     * @param array $data An associative array containing ticket details:
     *                    - 'title' (string): The title of the ticket (minimum 3 characters).
     *                    - 'description' (string): The description of the ticket (minimum 5 characters).
     *                    - 'department_id' (int): The ID of the department associated with the ticket.
     * @param array $files An array of files to be associated with the ticket.
     * @param int $user_id The ID of the user creating the ticket.
     *
     * @return void Outputs a JSON response with the result of the operation:
     *              - 400 Bad Request: If required fields are missing or invalid.
     *              - 404 Not Found: If the specified department does not exist.
     *              - 201 Created: If the ticket is successfully created.
     *              - 500 Internal Server Error: If ticket creation fails.
     */
    public function createWithFiles($data, $files, $user_id) {
        if (empty($data['title']) || strlen(trim($data['title'])) < 3) {
            http_response_code(400);
            echo json_encode(['message' => 'Invalid title']);
            return;
        }
        if (empty($data['description']) || strlen(trim($data['description'])) < 5) {
            http_response_code(400);
            echo json_encode(['message' => 'Invalid description']);
            return;
        }
        if (empty($data['department_id'])) {
            http_response_code(400);
            echo json_encode(['message' => 'Missing department']);
            return;
        } else{
            $department = $this->department->findById($data['department_id']);
            if (!$department) {
                http_response_code(404);
                echo json_encode(['message' => 'Department not found']);
                return;
            }
        }

        $result = $this->ticket->createWithFiles(
            trim($data['title']), 
            trim($data['description']), 
            $user_id, 
            $data['department_id'], 
            $files
        );

        if ($result) {
            http_response_code(201);
            echo json_encode(['message' => 'Ticket created successfully with files']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to create ticket.']);
        }
    }

    /**
     * Assigns a ticket to an agent.
     *
     * @param int $ticket_id The ID of the ticket to be assigned.
     * @param array $data An associative array containing the agent ID with the key 'agent_id'.
     *
     * @return void Outputs a JSON response with the result of the operation:
     * - 400 Bad Request if 'agent_id' is missing in the $data array.
     * - 404 Not Found if the ticket with the given ID does not exist.
     * - 200 OK with a success message if the ticket is assigned successfully.
     * - 500 Internal Server Error if the ticket assignment fails.
     */
    public function assign($ticket_id, $data) {
        if (empty($data['agent_id'])) {
            http_response_code(400);
            echo json_encode(['message' => 'Agent id required']);
            return;
        }

        $ticket = $this->ticket->findById($ticket_id);
        if (!$ticket) {
            http_response_code(404);
            echo json_encode(['message' => 'Ticket not found']);
            return;
        }
        $result = $this->ticket->assign($ticket_id, $data['agent_id']);

        if ($result) {
            echo json_encode(['message' => 'Ticket assigned successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to assign ticket']);
        }
    }

    /**
     * Change the status of a ticket.
     *
     * @param int $ticket_id The ID of the ticket to update.
     * @param array $data An associative array containing the new status.
     *                     - 'status' (string): The new status value. Must be one of 'open', 'in_progress', or 'closed'.
     *
     * @return void Outputs a JSON response with the result of the operation.
     *              - HTTP 400: If the 'status' field is missing or invalid.
     *              - HTTP 404: If the ticket with the given ID is not found.
     *              - HTTP 200: If the ticket status is successfully updated.
     */
    public function changeStatus($ticket_id, $data) {
        if (empty($data['status'])) {
            http_response_code(400);
            echo json_encode(['message' => 'Status required']);
            return;
        }
        $valid_status = ['open', 'in_progress', 'closed'];
        if (!in_array($data['status'], $valid_status)) {
            http_response_code(400);
            echo json_encode(['message' => 'Invalid status value']);
            return;
        }

        $ticket = $this->ticket->findById($ticket_id);
        if (!$ticket) {
            http_response_code(404);
            echo json_encode(['message' => 'Ticket not found']);
            return;
        }

        $result = $this->ticket->changeStatus($ticket_id, $data['status']);

        if ($result) {
            echo json_encode(['message' => 'Ticket status updated successfully']);
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Invalid status value or update failed']);
        }
    }

    /**
     * Adds a note to a specific ticket.
     *
     * @param int $ticket_id The ID of the ticket to which the note will be added.
     * @param int $user_id The ID of the user adding the note.
     * @param array $data An associative array containing the note data. 
     *                    Must include a 'note' key with the note content.
     *
     * @return void Outputs a JSON response with the result of the operation:
     *              - 400 Bad Request if the note is empty.
     *              - 404 Not Found if the ticket does not exist.
     *              - 201 Created if the note is successfully added.
     *              - 500 Internal Server Error if adding the note fails.
     */
    public function addNote($ticket_id, $user_id, $data) {
        if (empty($data['note'])) {
            http_response_code(400);
            echo json_encode(['message' => 'Note cannot be empty']);
            return;
        }

        $ticket = $this->ticket->findById($ticket_id);
        if (!$ticket) {
            http_response_code(404);
            echo json_encode(['message' => 'Ticket not found']);
            return;
        }

        $result = $this->ticket->addNote($ticket_id, $user_id, trim($data['note']));

        if ($result) {
            http_response_code(201);
            echo json_encode(['message' => 'Note added successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to add note']);
        }
    }

    /**
     * Handles the retrieval of tickets based on filters and user roles.
     *
     * @param array $filer An associative array containing filter criteria:
     *                     - 'department_id' (optional): Filter by department ID.
     *                     - 'status' (optional): Filter by ticket status.
     *                     - 'title' (optional): Filter by ticket title.
     *                     - 'assigned_id' (optional): Filter by assigned user ID.
     * @param string $role The role of the user making the request. Expected values:
     *                     - 'admin': Admin role.
     *                     - 'agent': Agent role.
     *                     - Other: Treated as a regular user.
     *
     * @return void Outputs a JSON response:
     *              - HTTP 200: On success, returns an array of tickets with their notes and attachments.
     *              - HTTP 404: If no tickets are found, returns a message indicating no tickets were found.
     */
    public function index($filer, $role) {
        $filters = [];
        if (!empty($filer['department_id'])) {
            $filters['department_id'] = $filer['department_id'];
        }
        if (!empty($filer['status'])) {
            $filters['status'] = $filer['status'];
        }
        if (!empty($filer['title'])) {
            $filters['title'] = $filer['title'];
        }
        if (!empty($filer['assigned_id'])) {
            $filters['assigned_id'] = $filer['assigned_id'];
        }

        if ($role !== 'admin' && $role !== 'agent') {
            $user_id = Auth::checkAuth($this->pdo);
            $filters['user_id'] = $user_id;
            $tickets = $this->ticket->findAll($filters);
        } else {
            $tickets = $this->ticket->findAll($filters);
        }
        if (empty($tickets)) {
            http_response_code(404);
            echo json_encode(['message' => 'No tickets found']);
            return;
        } else{
            foreach ($tickets as &$ticket) {
                $ticket['notes'] = $this->ticket->getNotes($ticket['id']);
                $ticket['attachments'] = $this->ticket->getAttachments($ticket['id']);
            }
            http_response_code(200);
            echo json_encode($tickets);
        }
    }

    /**
     * Display the details of a specific ticket.
     *
     * @param int $ticket_id The ID of the ticket to retrieve.
     *
     * @return void Outputs the ticket details as a JSON response.
     * 
     * - If the ticket is not found, responds with HTTP 404 and a JSON error message.
     * - If the user is not authorized to view the ticket, responds with HTTP 403 and a JSON error message.
     * - If the ticket is found and the user is authorized, responds with HTTP 200 and the ticket details, 
     *   including notes and attachments, as a JSON object.
     */
    public function show($ticket_id) {
        $role = Auth::getRole($this->pdo);
        $ticket = $this->ticket->findById($ticket_id);
        if (!$ticket) {
            http_response_code(404);
            echo json_encode(['message' => 'Ticket not found']);
            return;
        } else{
            if ($role !== 'admin' && $role !== 'agent') {
                $user_id = Auth::checkAuth($this->pdo);
                if ($ticket['user_id'] !== $user_id) {
                    http_response_code(403);
                    echo json_encode(['message' => 'Forbidden']);
                    return;
                }
            }
        }
        $ticket['notes'] = $this->ticket->getNotes($ticket_id);
        $ticket['attachments'] = $this->ticket->getAttachments($ticket_id);
        http_response_code(200);
        echo json_encode($ticket);
    }

    /**
     * Deletes a ticket by its ID.
     *
     * This method retrieves a ticket by its ID and deletes it if it exists.
     * If the ticket is not found, a 404 HTTP response code is returned with
     * an appropriate error message. If the deletion fails, a 500 HTTP response
     * code is returned with an error message.
     *
     * @param int $ticket_id The ID of the ticket to be deleted.
     *
     * @return void Outputs a JSON response indicating the result of the operation:
     *              - On success: {"message": "Ticket deleted successfully"}
     *              - If ticket not found: {"message": "Ticket not found"}
     *              - On failure: {"message": "Failed to delete ticket"}
     */
    public function delete($ticket_id) {
        $ticket = $this->ticket->findById($ticket_id);
        if (!$ticket) {
            http_response_code(404);
            echo json_encode(['message' => 'Ticket not found']);
            return;
        }

        $result = $this->ticket->delete($ticket_id);

        if ($result) {
            echo json_encode(['message' => 'Ticket deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to delete ticket']);
        }
    }
}
