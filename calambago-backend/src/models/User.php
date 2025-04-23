<?php

namespace src\Models;

class User {
    private static $users = [];
    private static $nextId = 1;

    public function create($data) {
        $data['id'] = self::$nextId++;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        self::$users[] = $data;
        return $data;
    }

    public function find($id) {
        foreach (self::$users as $user) {
            if ($user['id'] == $id) {
                $userCopy = $user;
                unset($userCopy['password']);
                return $userCopy;
            }
        }
        return false;
    }

    public function login($email, $password) {
        foreach (self::$users as $user) {
            if ($user['email'] === $email && password_verify($password, $user['password'])) {
                $userCopy = $user;
                unset($userCopy['password']);
                return $userCopy;
            }
        }
        return false;
    }

    public function getAll() {
        $users = [];
        foreach (self::$users as $user) {
            $userCopy = $user;
            unset($userCopy['password']);
            $users[] = $userCopy;
        }
        return $users;
    }
}