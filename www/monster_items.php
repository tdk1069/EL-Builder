<?php
session_start();
$username = $_SESSION['username'] ?? '';

header('Content-Type: application/json');
require 'auth/db.php'; // $pdo

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Load items assigned to monster_id
    $monster_id = isset($_GET['monster_id']) ? (int) $_GET['monster_id'] : 0;
    if (!$monster_id) {
        echo json_encode(['error' => 'Invalid monster_id']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT object_id FROM monsterObj WHERE monster_id = ?");
    $stmt->execute([$monster_id]);
    $assigned = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode($assigned);
    exit;
}

if ($method === 'POST') {
    // Save assigned items for monster_id
    $input = json_decode(file_get_contents('php://input'), true);
    $monster_id = $input['monster_id'] ?? 0;
    $items = $input['items'] ?? [];

    if (!$monster_id || !is_array($items)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input']);
        exit;
    }

    // Start transaction for safety
    $pdo->beginTransaction();

    try {
        // Delete existing assignments for monster
        $stmt = $pdo->prepare("DELETE FROM monsterObj WHERE monster_id = ?");
        $stmt->execute([$monster_id]);

        // Insert new assignments
        $stmt = $pdo->prepare("INSERT INTO monsterObj (monster_id, object_id) VALUES (?, ?)");
        foreach ($items as $objId) {
            $stmt->execute([$monster_id, $objId]);
        }

        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }

    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
