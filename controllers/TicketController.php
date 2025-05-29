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
            }
            http_response_code(200);
            echo json_encode($tickets);
        }
    }

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
        http_response_code(200);
        echo json_encode($ticket);
    }

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
