<?php
require_once 'session_config.php'; // Pastikan sesi dimulai

header('Content-Type: application/json');

$response = [
    'loggedIn' => false,
    'userName' => null,
    'userRole' => null
];

if (isset($_SESSION['UserID']) && isset($_SESSION['name']) && isset($_SESSION['role'])) {
    $response['loggedIn'] = true;
    $response['userName'] = $_SESSION['name'];
    $response['userRole'] = $_SESSION['role'];
}

echo json_encode($response);
?>