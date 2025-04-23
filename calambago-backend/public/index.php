<?php
require_once __DIR__ . '/../src/helpers/response.php';
require_once __DIR__ . '/../src/controllers/PlacesController.php';
require_once __DIR__ . '/../src/controllers/UsersController.php';
require_once __DIR__ . '/../src/controllers/FeedbackController.php';

use src\Controllers\PlacesController;
use src\Controllers\UsersController;
use src\Controllers\FeedbackController;

header('Content-Type: application/json');
// Set up routing
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

switch ($requestUri) {
    case '/api/places':
        $controller = new PlacesController();
        handlePlacesRequest($controller, $requestMethod);
        break;

    case '/api/users':
        $controller = new UsersController();
        handleUsersRequest($controller, $requestMethod);
        break;

    case '/api/feedback':
        $controller = new FeedbackController();
        handleFeedbackRequest($controller, $requestMethod);
        break;

    default:
        http_response_code(404);
        echo json_encode(['message' => 'Not Found']);
        break;
}

function handlePlacesRequest($controller, $method) {
    $data = json_decode(file_get_contents('php://input'), true);
    switch ($method) {
        case 'GET':
            $controller->getPlaces();
            break;
        case 'POST':
            $controller->createPlace($data);
            break;
        case 'PUT':
            $id = $_GET['id'] ?? null;
            $controller->updatePlace($id, $data);
            break;
        case 'DELETE':
            $id = $_GET['id'] ?? null;
            $controller->deletePlace($id);
            break;
        default:
            http_response_code(405);
            echo json_encode(['message' => 'Method Not Allowed']);
            break;
    }
}

function handleUsersRequest($controller, $method) {
    $data = json_decode(file_get_contents('php://input'), true);
    switch ($method) {
        case 'POST':
            $controller->registerUser($data);
            break;
        case 'GET':
            $id = $_GET['id'] ?? null;
            if ($id) {
                $controller->getUser($id);
            } else {
                $controller->getUsers();
            }
            break;
        default:
            http_response_code(405);
            echo json_encode(['message' => 'Method Not Allowed']);
            break;
    }
}

function handleFeedbackRequest($controller, $method) {
    $data = json_decode(file_get_contents('php://input'), true);
    switch ($method) {
        case 'POST':
            $controller->submitFeedback($data);
            break;
        case 'GET':
            $controller->getFeedback();
            break;
        default:
            http_response_code(405);
            echo json_encode(['message' => 'Method Not Allowed']);
            break;
    }
}
?>