<?php
session_start();
require_once '../config.php';

// Ensure the user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}
try {
    $check = $pdo->query("SHOW COLUMNS FROM services LIKE 'image_url'");
    if ($check->rowCount() == 0) {
        // Column doesn't exist, so add it
        $pdo->exec("ALTER TABLE services ADD COLUMN image_url VARCHAR(255) DEFAULT NULL");
        $_SESSION['success'] = "Database updated successfully: Added image_url column";
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Database update failed: " . $e->getMessage();
}

// Check if the id and status are provided in the URL
if (isset($_GET['id']) && isset($_GET['status'])) {
    $appointment_id = $_GET['id'];
    $status = $_GET['status'];

    // Validate the status value
    if (in_array($status, ['confirmed', 'cancelled'])) {
        // Update the appointment status
        $stmt = $pdo->prepare("UPDATE appointments SET status = ? WHERE id = ?");
        if ($stmt->execute([$status, $appointment_id])) {
            header("Location: dashboard.php?message=Status updated successfully");
        } else {
            header("Location: dashboard.php?error=Failed to update status");
        }
        exit();
    } else {
        header("Location: dashboard.php?error=Invalid status");
        exit();
    }
} else {
    header("Location: dashboard.php?error=Missing parameters");
    exit();
}
?>
