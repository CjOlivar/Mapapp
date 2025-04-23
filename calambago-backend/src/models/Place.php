<?php

namespace src\Models;

use PDO;

class Place {
    private $conn;
    private $table = 'places';
    private $id;
    private $name;
    private $description;
    private $location;
    private $created_at;
    private $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table . " (name, description, location, created_at, updated_at) VALUES (:name, :description, :location, :created_at, :updated_at)";
        $stmt = $this->conn->prepare($query);
        
        $now = date('Y-m-d H:i:s');
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':location', $data['location']);
        $stmt->bindParam(':created_at', $now);
        $stmt->bindParam(':updated_at', $now);

        return $stmt->execute();
    }

    public function getAll() {
        $query = "SELECT * FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($id, $data) {
        $query = "UPDATE " . $this->table . " SET name = :name, description = :description, location = :location, updated_at = :updated_at WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':location', $data['location']);
        $stmt->bindParam(':updated_at', date('Y-m-d H:i:s'));

        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    public function find($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getLocation() {
        return $this->location;
    }

    public function getCreatedAt() {
        return $this->created_at;
    }

    public function getUpdatedAt() {
        return $this->updated_at;
    }

    public function setName($name) {
        $this->name = $name;
        $this->updated_at = date('Y-m-d H:i:s');
    }

    public function setDescription($description) {
        $this->description = $description;
        $this->updated_at = date('Y-m-d H:i:s');
    }

    public function setLocation($location) {
        $this->location = $location;
        $this->updated_at = date('Y-m-d H:i:s');
    }
}