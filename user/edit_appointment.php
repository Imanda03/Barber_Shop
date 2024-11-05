<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../login.php');
    exit();
}

$appointment_id = $_GET['id'] ?? null;

// Verify the appointment belongs to the current user
$stmt = $pdo->prepare("SELECT a.*, s.name as service_name 
    FROM appointments a 
    JOIN services s ON a.service_id = s.id 
    WHERE a.id = ? AND a.user_id = ?");
$stmt->execute([$appointment_id, $_SESSION['user_id']]);
$appointment = $stmt->fetch();

if (!$appointment) {
    header('Location: dashboard.php');
    exit();
}

// Fetch services
$stmt = $pdo->prepare("SELECT * FROM services");
$stmt->execute();
$services = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $service_id = $_POST['service_id'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    
    // Check if the time slot is available (excluding current appointment)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments 
        WHERE service_id = ? AND appointment_date = ? AND appointment_time = ? 
        AND status != 'cancelled' AND id != ?");
    $stmt->execute([$service_id, $date, $time, $appointment_id]);
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        $stmt = $pdo->prepare("UPDATE appointments 
            SET service_id = ?, appointment_date = ?, appointment_time = ? 
            WHERE id = ? AND user_id = ?");
        $stmt->execute([$service_id, $date, $time, $appointment_id, $_SESSION['user_id']]);
        header('Location: dashboard.php?success=1');
        exit();
    } else {
        $error = "This time slot is already booked. Please choose another time.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Appointment - Barber Appointment System</title>
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
        <h2>Edit Appointment</h2>
        
        <?php if (isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <select name="service_id" required>
                <option value="">Select a Service</option>
                <?php foreach ($services as $service): ?>
                    <option value="<?= $service['id'] ?>" 
                        <?= ($service['id'] == $appointment['service_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($service['name']) ?> - $<?= htmlspecialchars($service['price']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <input type="date" name="date" required min="<?= date('Y-m-d') ?>" 
                value="<?= htmlspecialchars($appointment['appointment_date']) ?>">
            
            <input type="time" name="time" required min="09:00" max="17:00" step="1800" 
                value="<?= htmlspecialchars($appointment['appointment_time']) ?>">
            
            <button type="submit">Update Appointment</button>
            <a href="dashboard.php" class="button">Cancel</a>
        </form>
    </div>
</body>
</html>
