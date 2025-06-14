<?php
require 'auth/db.php'; // adjust path as needed
session_start();

header('Content-Type: application/json');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['success' => false, 'error' => 'Invalid JSON input']);
    exit;
}

$areaId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$areaId) {
    echo json_encode(['success' => false, 'error' => 'Missing area ID']);
    exit;
}

// Ensure required data is present
if (!isset($input['grid'], $input['currentX'], $input['currentY'], $input['currentZ'])) {
    echo json_encode(['success' => false, 'error' => 'Missing area data']);
    exit;
}

try {
    $dataJson = json_encode($input);
    
    // Check if a room entry for this area already exists
    $stmt = $pdo->prepare("SELECT id FROM rooms WHERE area_id = ?");
    $stmt->execute([$areaId]);
    $existing = $stmt->fetch();

    if ($existing) {
        // Update
        $stmt = $pdo->prepare("UPDATE rooms SET data = ?, updated_at = NOW() WHERE area_id = ?");
        $stmt->execute([$dataJson, $areaId]);
    } else {
        // Insert
        $stmt = $pdo->prepare("INSERT INTO rooms (area_id, username, data) VALUES (?, ?, ?)");
        $stmt->execute([$areaId, $_SESSION['username'] ?? 'unknown', $dataJson]);
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
