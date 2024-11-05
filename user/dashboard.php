<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../login.php');
    exit();
}

// Fetch user's appointments
$stmt = $pdo->prepare("SELECT a.*, s.name as service_name 
    FROM appointments a 
    JOIN services s ON a.service_id = s.id 
    WHERE a.user_id = ? 
    ORDER BY a.appointment_date, a.appointment_time");
$stmt->execute([$_SESSION['user_id']]);
$appointments = $stmt->fetchAll();

// Fetch services
$stmt = $pdo->prepare("SELECT * FROM services");
$stmt->execute();
$services = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Dashboard - Barber Appointment System</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <nav>
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="book_appointment.php">Book Appointment</a></li>
            <li><a href="my_appointments.php">My Appointments</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </nav>
    
    <div class="container">
        <h2>Welcome to Your Dashboard</h2>
        
        <h3>Available Services</h3>
        <div class="services-grid">
            <?php foreach ($services as $service): ?>
                <div class="service-card">
                    <h4><?= htmlspecialchars($service['name']) ?></h4>
                    <p>Price: $<?= htmlspecialchars($service['price']) ?></p>
                    <p>Duration: <?= htmlspecialchars($service['duration']) ?> minutes</p>
                    <p><?= htmlspecialchars($service['description']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
        
        <h3>Your Upcoming Appointments</h3>
        <table>
            <tr>
                <th>Service</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($appointments as $appointment): ?>
                <tr>
                    <td><?= htmlspecialchars($appointment['service_name']) ?></td>
                    <td><?= htmlspecialchars($appointment['appointment_date']) ?></td>
                    <td><?= htmlspecialchars($appointment['appointment_time']) ?></td>
                    <td><?= htmlspecialchars($appointment['status']) ?></td>
                    <td>
                        <a href="edit_appointment.php?id=<?= $appointment['id'] ?>">Edit</a>
                        <a href="delete_appointment.php?id=<?= $appointment['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>