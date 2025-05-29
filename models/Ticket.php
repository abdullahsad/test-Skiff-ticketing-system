<?php
class Ticket {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function create($title, $description, $user_id, $department_id) {
        $sql = $this->pdo->prepare(
            "INSERT INTO tickets (title, description, user_id, department_id, status, created_at) 
            VALUES (?, ?, ?, ?, 'open', NOW())"
        );
        return $sql->execute([$title, $description, $user_id, $department_id]);
    }

    public function createWithFiles($title, $description, $user_id, $department_id, $files) {
        $this->pdo->beginTransaction();
        try {
            // echo "Creating ticket with files";
            $this->create($title, $description, $user_id, $department_id);
            $ticket_id = $this->pdo->lastInsertId();
            if(!empty($files)){
                for ($i = 0; $i < count($files['name']); $i++) {
                    if ($files['error'][$i] === UPLOAD_ERR_OK) {
                        $name = $files['name'][$i];
                        $tmp_name = $files['tmp_name'][$i];
                        $file_name = uniqid() . '-' . basename($name);
                        $file_path = 'uploads/' . $file_name;

                        move_uploaded_file($tmp_name, $file_path);

                        $sql = $this->pdo->prepare(
                            "INSERT INTO ticket_attachments (ticket_id, file_path, uploaded_at, uploaded_by) VALUES (?, ?, NOW(), ?)"
                        );
                        $sql->execute([$ticket_id, $file_path, $user_id]);
                    }
                }
            }
            
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            // echo $e->getMessage();
            $this->pdo->rollBack();
            return false;
        }
    }

    public function findById($id) {
        $sql = $this->pdo->prepare("SELECT * FROM tickets WHERE id = ?");
        $sql->execute([$id]);
        return $sql->fetch(PDO::FETCH_ASSOC);
    }

    public function findAll($filters = []) {
        $query = "";
        $params = [];

        if (!empty($filters['user_id'])) {
            $query .= " AND user_id = ?";
            $params[] = $filters['user_id'];
        }
        if (!empty($filters['department_id'])) {
            $query .= " AND department_id = ?";
            $params[] = $filters['department_id'];
        }
        if (!empty($filters['status'])) {
            $query .= " AND status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['title'])) {
            $query .= " AND title LIKE ?";
            $params[] = '%' . $filters['title'] . '%';
        }
        if (!empty($filters['assigned_id'])) {
            $query .= " AND assigned_id = ?";
            $params[] = $filters['assigned_id'];
        }

        if (empty($query)) {
            $query = "SELECT * FROM tickets";
        } else {
            $query = ltrim($query, " AND ");
            $query = "SELECT * FROM tickets WHERE " . $query;
        }

        $sql = $this->pdo->prepare($query . " ORDER BY created_at DESC");
        $sql->execute($params);
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public function assign($ticket_id, $agent_user_id) {
        $sql = $this->pdo->prepare("UPDATE tickets SET assigned_id = ? WHERE id = ?");
        return $sql->execute([$agent_user_id, $ticket_id]);
    }

    public function changeStatus($ticket_id, $status) {
        $allowed = ['open', 'in_progress', 'closed'];
        if (!in_array($status, $allowed)) {
            return false;
        }
        $sql = $this->pdo->prepare("UPDATE tickets SET status = ? WHERE id = ?");
        return $sql->execute([$status, $ticket_id]);
    }

    public function delete($id) {
        $sql = $this->pdo->prepare("DELETE FROM tickets WHERE id = ?");
        return $sql->execute([$id]);
    }

    public function addNote($ticket_id, $user_id, $note) {
        $sql = $this->pdo->prepare(
            "INSERT INTO ticket_notes (ticket_id, user_id, note, created_at) VALUES (?, ?, ?, NOW())"
        );
        return $sql->execute([$ticket_id, $user_id, $note]);
    }

    public function getNotes($ticket_id) {
        $sql = $this->pdo->prepare("SELECT * FROM ticket_notes WHERE ticket_id = ? ORDER BY created_at ASC");
        $sql->execute([$ticket_id]);
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAttachments($ticket_id) {
        $sql = $this->pdo->prepare("SELECT * FROM ticket_attachments WHERE ticket_id = ?");
        $sql->execute([$ticket_id]);
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }
}
