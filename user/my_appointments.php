<?php
session_start();
require_once '../config.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Fetch appointments for the logged-in user
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT a.*, s.name as service_name 
    FROM appointments a 
    JOIN services s ON a.service_id = s.id 
    WHERE a.user_id = :user_id 
    ORDER BY a.appointment_date, a.appointment_time");
$stmt->execute(['user_id' => $user_id]);
$appointments = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Appointments - Barber Appointment System</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <nav>
        <ul>
             <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="book_appointment.php">Book Appointment</a></li>
            <!-- <li><a href="my_appointments.php">My Appointments</a></li> -->
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </nav>
    
    <div class="container">
        <h2>My Appointments</h2>
        
        <table>
            <tr>
                <th>Service</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
            </tr>
            <?php if (count($appointments) > 0): ?>
                <?php foreach ($appointments as $appointment): ?>
                    <tr>
                        <td><?= htmlspecialchars($appointment['service_name']) ?></td>
                        <td><?= htmlspecialchars($appointment['appointment_date']) ?></td>
                        <td><?= htmlspecialchars($appointment['appointment_time']) ?></td>
                        <td><?= htmlspecialchars($appointment['status']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">No appointments found.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
</body>
</html>
