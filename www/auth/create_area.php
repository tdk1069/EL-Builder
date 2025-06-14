<?php
require 'db.php';
session_start();

if (!isset($_SESSION['username'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

// Sanitize input
$id = isset($data['id']) && is_numeric($data['id']) ? (int)$data['id'] : null;
$username = $_SESSION['username'];
$name = trim($data['name'] ?? '');
$basePath = trim($data['basePath'] ?? '');
$levelRange = trim($data['levelRange'] ?? '');
$description = trim($data['description'] ?? '');

// Validate required fields
if (!$name || !$basePath) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Name and base path are required']);
    exit;
}

try {
    if ($id) {
        // Update existing area
        $stmt = $pdo->prepare("UPDATE areas SET name = ?, basePath = ?, levelRange = ?, description = ? WHERE id = ? AND username = ?");
        $stmt->execute([$name, $basePath, $levelRange, $description, $id, $username]);
    } else {
        // Insert new area
        $stmt = $pdo->prepare("INSERT INTO areas (username, name, basePath, levelRange, description) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$username, $name, $basePath, $levelRange, $description]);
    }

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
