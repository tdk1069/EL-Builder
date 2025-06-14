<?php
require 'db.php';
session_start();

$data = json_decode(file_get_contents("php://input"), true);
$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

if (!$username || !$password) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Username and password required"]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT password FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row || !password_verify($password, $row['password'])) {
        http_response_code(401);
        echo json_encode(["success" => false, "error" => "Invalid credentials"]);
        exit;
    }

    $_SESSION['username'] = $username;
    echo json_encode(["success" => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Database error"]);
}

