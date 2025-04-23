<?php

namespace src\Controllers;

use src\Models\User;
use src\Helpers\Response;

class UsersController
{
    public function registerUser($data)
    {
        $user = new User();
        $result = $user->create($data);

        if ($result) {
            Response::sendSuccess($result);
        } else {
            Response::sendError("User registration failed.");
        }
    }

    public function loginUser($email, $password)
    {
        $user = new User();
        $result = $user->login($email, $password);

        if ($result) {
            Response::sendSuccess($result);
        } else {
            Response::sendError("Invalid email or password.");
        }
    }

    public function getUser($id)
    {
        $user = new User();
        $result = $user->find($id);

        if ($result) {
            Response::sendSuccess($result);
        } else {
            Response::sendError("User not found.");
        }
    }

    public function getUsers()
    {
        $user = new User();
        $result = $user->getAll();
        Response::sendSuccess($result);
    }
}