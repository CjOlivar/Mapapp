<?php

namespace src\Controllers;

use src\Models\Feedback;
use src\helpers\Response;

class FeedbackController
{
    public function submitFeedback($data)
    {
        if (!isset($data['message']) || empty($data['message'])) {
            Response::sendError("Feedback message is required.");
            return;
        }

        $feedback = new Feedback();
        $result = $feedback->create($data);

        if ($result) {
            Response::sendSuccess($result);
        } else {
            Response::sendError("Failed to submit feedback.");
        }
    }

    public function getFeedback()
    {
        $feedback = new Feedback();
        $results = $feedback->getAll();

        if ($results) {
            Response::sendSuccess($results);
        } else {
            Response::sendError("No feedback found.");
        }
    }
}
?>