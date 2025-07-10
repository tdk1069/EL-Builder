<?php
require 'auth/db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
  http_response_code(403);
  echo json_encode(["error" => "Not logged in."]);
  exit;
}

$username = $_SESSION['username'];
$data = $_POST;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($data['action'])) {
    $action = $data['action'];

    try {
      if ($action === 'add') {
        $stmt = $pdo->prepare("INSERT INTO monsters (owner, set_class, set_race, set_gender, set_level, set_short, set_spells, set_name, set_alignment, set_long) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
          $username,
          $data['set_class'],
          $data['set_race'],
          $data['set_gender'],
          $data['set_level'],
          $data['set_short'],
          $data['set_spells'],
          $data['set_name'],
          $data['set_alignment'],
          $data['set_long']
        ]);
        echo json_encode(["success" => true, "id" => $pdo->lastInsertId()]);
        exit;
      } elseif ($action === 'update' && isset($data['id'])) {
        $stmt = $pdo->prepare("UPDATE monsters SET set_class=?, set_race=?, set_gender=?, set_level=?, set_short=?, set_spells=?, set_name=?, set_long=?, set_alignment=? WHERE id=? AND owner=?");
        $stmt->execute([
          $data['set_class'],
          $data['set_race'],
          $data['set_gender'],
          $data['set_level'],
          $data['set_short'],
          $data['set_spells'],
          $data['set_name'],
          $data['set_long'],
          $data['set_alignment'],
          $data['id'],
          $username
        ]);
        echo json_encode(["success" => true, "id" => $data['id']]);
        exit;
      }
    } catch (Exception $e) {
      http_response_code(500);
      echo json_encode(["error" => $e->getMessage()]);
      exit;
    }
  }
}

http_response_code(400);
echo json_encode(["error" => "Invalid request."]);
