<?php
/**
 * Simple POST Test
 */

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Log the request
error_log("POST Test - Method: " . $_SERVER['REQUEST_METHOD']);
error_log("POST Test - Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'not set'));

// Get raw input
$rawInput = file_get_contents('php://input');
error_log("POST Test - Raw input: " . $rawInput);

// Parse JSON
$input = json_decode($rawInput, true);
error_log("POST Test - Parsed input: " . print_r($input, true));

// Check for errors
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode([
        'success' => false,
        'error' => 'JSON parse error: ' . json_last_error_msg(),
        'raw_input' => $rawInput
    ]);
    exit();
}

// Check required fields
if (!isset($input['action'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Action is required',
        'received' => $input
    ]);
    exit();
}

if (!isset($input['message'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Message is required',
        'received' => $input
    ]);
    exit();
}

// Success response
echo json_encode([
    'success' => true,
    'data' => [
        'action' => $input['action'],
        'message' => $input['message'],
        'context' => $input['context'] ?? null,
        'timestamp' => date('Y-m-d H:i:s')
    ]
]);
?> 