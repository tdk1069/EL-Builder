<?php
require 'auth/db.php';

$areaId = $_GET['area_id'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM rooms WHERE area_id = ?");
$stmt->execute([$areaId]);
echo json_encode($stmt->fetchAll());
