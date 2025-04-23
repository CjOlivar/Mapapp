<?php

namespace src\controllers;

use src\Models\Feedback;
use src\Helpers\Response;
use PDO;
use Exception;

class FeedbackController
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function submitFeedback($userId, $placeId, $feedback)
    {
        try {
            // Validate required fields
            if (!isset($data['message']) || empty($data['message'])) {
                Response::sendError("Feedback message is required.");
                return;
            }

            $feedback = new Feedback($this->db);
            $result = $feedback->create($data);

            if ($result) {
                Response::sendSuccess("Feedback submitted successfully.");
            } else {
                Response::sendError("Failed to submit feedback.");
            }
        } catch (Exception $e) {
            Response::sendError("An error occurred: " . $e->getMessage());
        }
    }

    public function getFeedback(): void
    {
        try {
            $feedback = new Feedback($this->db);
            $results = $feedback->getAll();

            if ($results) {
                Response::sendSuccess($results);
            } else {
                Response::sendError("No feedback found.");
            }
        } catch (Exception $e) {
            Response::sendError("An error occurred: " . $e->getMessage());
        }
    }
}
?>