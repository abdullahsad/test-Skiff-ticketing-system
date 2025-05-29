<?php

class Department {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Create a new department
    public function create($name) {
        $sql = $this->pdo->prepare("INSERT INTO departments (name) VALUES (?)");
        return $sql->execute([$name]);
    }

    // Read one department by id
    public function findById($id) {
        $sql = $this->pdo->prepare("SELECT id, name FROM departments WHERE id = ?");
        $sql->execute([$id]);
        return $sql->fetch(PDO::FETCH_ASSOC);
    }

    // Read one department by name
    public function findByName($name) {
        $sql = $this->pdo->prepare("SELECT id, name FROM departments WHERE name = ?");
        $sql->execute([$name]);
        return $sql->fetch(PDO::FETCH_ASSOC);
    }

    // Read all departments
    public function findAll() {
        $sql = $this->pdo->query("SELECT id, name FROM departments ORDER BY name ASC");
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update a department's name by id
    public function update($id, $name) {
        $sql = $this->pdo->prepare("UPDATE departments SET name = ? WHERE id = ?");
        return $sql->execute([$name, $id]);
    }

    // Delete a department by id
    public function delete($id) {
        $sql = $this->pdo->prepare("DELETE FROM departments WHERE id = ?");
        return $sql->execute([$id]);
    }
}
