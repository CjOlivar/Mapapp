<?php
require_once '../../src/controllers/PlacesController.php';

use src\Controllers\PlacesController;

header('Content-Type: application/json');

$placesController = new PlacesController();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $response = $placesController->getPlace($id);
        } else {
            $response = $placesController->getPlaces();
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $response = $placesController->createPlace($data);
        break;

    case 'PUT':
        $id = intval($_GET['id']);
        $data = json_decode(file_get_contents('php://input'), true);
        $response = $placesController->updatePlace($id, $data);
        break;

    case 'DELETE':
        $id = intval($_GET['id']);
        $response = $placesController->deletePlace($id);
        break;

    default:
        http_response_code(405);
        $response = ['message' => 'Method Not Allowed'];
        break;
}

echo json_encode($response);
?>