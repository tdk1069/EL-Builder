<?php
require 'auth/db.php';
$data = json_decode(file_get_contents("php://input"), true);

$stmt = $pdo->prepare("
  INSERT INTO rooms (area_id, x, y, z, short_desc, long_desc, smell, exits, items)
  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
  ON DUPLICATE KEY UPDATE
    short_desc = VALUES(short_desc),
    long_desc = VALUES(long_desc),
    smell = VALUES(smell),
    exits = VALUES(exits)
");
$stmt->execute([
  $data['area_id'],
  $data['x'],
  $data['y'],
  $data['z'],
  $data['short'],
  $data['long'],
  $data['smell'],
  json_encode($data['exits']),
  json_encode([]),
]);
