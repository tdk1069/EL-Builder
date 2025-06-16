<?php
session_start();
$username = $_SESSION['username'] ?? '';

require 'auth/db.php'; // contains $pdo

$stmt = $pdo->prepare("SELECT * FROM monsters WHERE owner = ?");
$stmt->execute([$username]);
$monsters = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($monsters);
