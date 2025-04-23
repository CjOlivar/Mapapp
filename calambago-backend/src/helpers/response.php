<?php

namespace src\helpers;

class Response
{
    public static function sendSuccess($data)
    {
        echo json_encode([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public static function sendError($message)
    {
        echo json_encode([
            'status' => 'error',
            'message' => $message
        ]);
    }
}

function sendResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

function sendSuccessResponse($message, $data = null) {
    $response = [
        'status' => 'success',
        'message' => $message,
        'data' => $data
    ];
    sendResponse($response);
}

function sendErrorResponse($message, $status = 400) {
    $response = [
        'status' => 'error',
        'message' => $message
    ];
    sendResponse($response, $status);

}
?>