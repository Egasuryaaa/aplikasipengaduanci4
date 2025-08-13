<?php
// Enable CORS for all origins
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get the raw POST data
$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true) ?? [];

// Set a default response
$response = [
    'status' => false,
    'message' => 'Invalid credentials',
    'data' => null
];

// Log the received data for debugging
$logFile = __DIR__ . '/../writable/logs/login_attempts.log';
file_put_contents(
    $logFile, 
    date('Y-m-d H:i:s') . ' - Received: ' . print_r($data, true) . "\n", 
    FILE_APPEND
);

// Simple hardcoded login check
if (isset($data['email_or_phone']) && isset($data['password'])) {
    if ($data['email_or_phone'] == 'user@example.com' && $data['password'] == 'password') {
        $response = [
            'status' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => [
                    'id' => 1,
                    'name' => 'Test User',
                    'email' => 'user@example.com',
                    'phone' => '1234567890',
                ],
                'token' => 'test_token_123456'
            ]
        ];
    }
}

// Output response
echo json_encode($response);
