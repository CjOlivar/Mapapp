<?php

namespace src\Controllers;

use src\Models\User;
use src\Helpers\Response;
use PDO;

class UsersController
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function registerUser($data)
    {
        // Logic for registering a new user
        $user = new User($this->db);
        $result = $user->create($data);
        
        if ($result) {
            Response::sendSuccess("User registered successfully.");
        } else {
            Response::sendError("User registration failed.");
        }
    }

    public function loginUser($email, $password)
    {
        // Logic for user login
        $user = new User($this->db);
        $result = $user->login($email, $password);
        
        if ($result) {
            Response::sendSuccess("Login successful.", $result);
        } else {
            Response::sendError("Invalid email or password.");
        }
    }

    public function getUser($id)
    {
        // Logic for retrieving user information
        $user = new User($this->db);
        $result = $user->find($id);
        
        if ($result) {
            Response::sendSuccess("User retrieved successfully.", $result);
        } else {
            Response::sendError("User not found.");
        }
    }
}