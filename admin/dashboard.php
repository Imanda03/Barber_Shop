<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Fetch all appointments
$stmt = $pdo->prepare("SELECT a.*, u.name as user_name, s.name as service_name 
    FROM appointments a 
    JOIN users u ON a.user_id = u.id 
    JOIN services s ON a.service_id = s.id 
    ORDER BY a.appointment_date, a.appointment_time");
$stmt->execute();
$appointments = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Barber Appointment System</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <nav>
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="services.php">Manage Services</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </nav>
    
    <div class="container">
        <h2>Admin Dashboard</h2>
        
        <h3>All Appointments</h3>
        <table>
            <tr>
                <th>Customer</th>
                <th>Service</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($appointments as $appointment): ?>
                <tr>
                    <td><?= htmlspecialchars($appointment['user_name']) ?></td>
                    <td><?= htmlspecialchars($appointment['service_name']) ?></td>
                    <td><?= htmlspecialchars($appointment['appointment_date']) ?></td>
                    <td><?= htmlspecialchars($appointment['appointment_time']) ?></td>
                    <td><?= htmlspecialchars($appointment['status']) ?></td>
                    <td>
                        <a href="update_status.php?id=<?= $appointment['id'] ?>&status=confirmed">Confirm</a>
                        <a href="update_status.php?id=<?= $appointment['id'] ?>&status=cancelled">Cancel</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <?php if (isset($_GET['message'])): ?>
    <p class="success-message"><?= htmlspecialchars($_GET['message']) ?></p>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
    <p class="error-message"><?= htmlspecialchars($_GET['error']) ?></p>
<?php endif; ?>
</body>
</html>

<!-- Stylesheet (style.css) -->
