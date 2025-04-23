<?php

namespace src\Models;

use PDO;

class Feedback {
    private $conn;
    private $table = 'feedback';

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table . " (user_id, place_id, message, created_at) VALUES (:user_id, :place_id, :message, :created_at)";
        $stmt = $this->conn->prepare($query);
        
        $now = date('Y-m-d H:i:s');
        $stmt->bindParam(':user_id', $data['user_id']);
        $stmt->bindParam(':place_id', $data['place_id']);
        $stmt->bindParam(':message', $data['message']);
        $stmt->bindParam(':created_at', $now);

        return $stmt->execute();
    }

    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getByPlaceId($place_id) {
        $query = "SELECT * FROM " . $this->table . " WHERE place_id = :place_id ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':place_id', $place_id);
        $stmt->execute();
        return $stmt;
    }
}