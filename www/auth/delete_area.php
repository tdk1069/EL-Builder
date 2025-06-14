<?php
require 'db.php';
session_start();

if (!isset($_SESSION['username'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? '';

if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No area ID provided']);
    exit;
}

// Verify ownership
$stmt = $pdo->prepare('SELECT * FROM areas WHERE id = ? AND username = ?');
$stmt->execute([$id, $_SESSION['username']]);
$area = $stmt->fetch();

if (!$area) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Area not found or not owned by user']);
    exit;
}

// Delete area
$delete = $pdo->prepare('DELETE FROM areas WHERE id = ?');
$delete->execute([$id]);

echo json_encode(['success' => true]);
