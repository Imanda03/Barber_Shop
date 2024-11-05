<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../login.php');
    exit();
}

$appointment_id = $_GET['id'] ?? null;

// Verify the appointment belongs to the current user
$stmt = $pdo->prepare("SELECT id FROM appointments WHERE id = ? AND user_id = ?");
$stmt->execute([$appointment_id, $_SESSION['user_id']]);
$appointment = $stmt->fetch();

if ($appointment) {
    // Instead of actually deleting, update status to 'cancelled'
    $stmt = $pdo->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ?");
    $stmt->execute([$appointment_id]);
}

header('Location: dashboard.php?success=1');
exit();
?>