<?php
require_once '../../src/controllers/FeedbackController.php';

use src\Controllers\FeedbackController;

header('Content-Type: application/json');

$feedbackController = new FeedbackController($someDependency); // Replace $someDependency with the actual required argument

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['user_id']) && isset($data['place_id']) && isset($data['feedback'])) {
        $userId = $data['user_id'];
        $placeId = $data['place_id'];
        $feedback = $data['feedback'];

        $result = $feedbackController->submitFeedback($userId, $placeId, $feedback);
        
        if ($result) {
            echo json_encode(['status' => 'success', 'message' => 'Feedback submitted successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to submit feedback.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid input.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
}
?>