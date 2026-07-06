<?php
session_start();
include '../config.php'; // Sesuaikan lokasi config.php

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$role = $_SESSION['role'] ?? 'viewer';

if ($action == 'get') {
    $stmt = $pdo->query("SELECT content FROM notes WHERE id = 1");
    echo json_encode(['content' => $stmt->fetchColumn()]);
} 
elseif ($action == 'save' && $role == 'super_admin') {
    $content = $_POST['content'] ?? '';
    $stmt = $pdo->prepare("UPDATE notes SET content = ? WHERE id = 1");
    $stmt->execute([$content]);
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
}