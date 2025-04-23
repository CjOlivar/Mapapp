<?php
require_once '/../src/config/database.php';
require_once '/../src/controllers/PlacesController.php';
require_once '/../src/controllers/UsersController.php';
require_once '/../src/controllers/FeedbackController.php';

use src\Config\Database;
use src\Controllers\PlacesController;
use src\Controllers\UsersController;
use src\Controllers\FeedbackController;

// Initialize the application
$database = new Database();
$db = $database->getConnection();
header('Content-Type: application/json');
// Set up routing
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

switch ($requestUri) {
    case '/api/places':
        $controller = new PlacesController($db);
        handlePlacesRequest($controller, $requestMethod);
        break;

    case '/api/users':
        $controller = new UsersController($db);
        handleUsersRequest($controller, $requestMethod);
        break;

    case '/api/feedback':
        $controller = new FeedbackController($db);
        handleFeedbackRequest($controller, $requestMethod);
        break;

    default:
        http_response_code(404);
        echo json_encode(['message' => 'Not Found']);
        break;
}

function handlePlacesRequest($controller, $method) {
    switch ($method) {
        case 'GET':
            $controller->getPlaces();
            break;
        case 'POST':
            $controller->createPlace();
            break;
        case 'PUT':
            $controller->updatePlace();
            break;
        case 'DELETE':
            $controller->deletePlace();
            break;
        default:
            http_response_code(405);
            echo json_encode(['message' => 'Method Not Allowed']);
            break;
    }
}

function handleUsersRequest($controller, $method) {
    switch ($method) {
        case 'POST':
            $controller->registerUser();
            break;
        case 'GET':
            $controller->getUser();
            break;
        default:
            http_response_code(405);
            echo json_encode(['message' => 'Method Not Allowed']);
            break;
    }
}

function handleFeedbackRequest($controller, $method) {
    switch ($method) {
        case 'POST':
            $controller->submitFeedback();
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