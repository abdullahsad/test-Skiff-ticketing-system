<?php

class Department {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function create($name) {
        $sql = $this->pdo->prepare("INSERT INTO departments (name) VALUES (?)");
        return $sql->execute([$name]);
    }

    public function findById($id) {
        $sql = $this->pdo->prepare("SELECT id, name FROM departments WHERE id = ?");
        $sql->execute([$id]);
        return $sql->fetch(PDO::FETCH_ASSOC);
    }

    public function findByName($name) {
        $sql = $this->pdo->prepare("SELECT id, name FROM departments WHERE name = ?");
        $sql->execute([$name]);
        return $sql->fetch(PDO::FETCH_ASSOC);
    }

    public function findAll() {
        $sql = $this->pdo->query("SELECT id, name FROM departments ORDER BY name ASC");
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($id, $name) {
        $sql = $this->pdo->prepare("UPDATE departments SET name = ? WHERE id = ?");
        return $sql->execute([$name, $id]);
    }

    public function delete($id) {
        $sql = $this->pdo->prepare("DELETE FROM departments WHERE id = ?");
        return $sql->execute([$id]);
    }
}
