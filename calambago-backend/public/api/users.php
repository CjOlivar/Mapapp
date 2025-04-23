<?php
require_once '../../src/controllers/UsersController.php';
require_once '../../src/config/database.php';

use src\Controllers\UsersController;
use src\Config\Database;

header('Content-Type: application/json');

$database = new Database();
$usersController = new UsersController($databaseConnection);
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $userId = intval($_GET['id']);
            $response = $usersController->getUser($userId);
        } else {
            $response = $userController->getUsers();
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $response = $usersController->registerUser($data);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        $userId = intval($_GET['id']);
        $response = $userController->updateUser($userId, $data);
        break;

    case 'DELETE':
        $userId = intval($_GET['id']);
        $response = $userController->deleteUser($userId);
        break;

    default:
        http_response_code(405);
        $response = ['message' => 'Method Not Allowed'];
        break;
}

echo json_encode($response);
?>