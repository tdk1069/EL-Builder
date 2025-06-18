<?php
session_start();
$username = $_SESSION['username'] ?? '';

require 'auth/db.php'; // contains $pdo

$stmt = $pdo->prepare("SELECT * FROM obj WHERE owner = ?");
$stmt->execute([$username]);
$objects = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($objects);
