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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Previous CSS styles remain the same until services-grid */
        :root {
            --primary-gradient: linear-gradient(135deg, #2c3e50, #3498db);
            --secondary-gradient: linear-gradient(135deg, #34495e, #2c3e50);
            --accent-color: #e74c3c;
            --text-primary: #2c3e50;
            --text-secondary: #7f8c8d;
            --border-radius: 10px;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
            --background: #f5f6fa;
            --text-dark: #212121;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        body {
            background: var(--background);
            color: var(--text-dark);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        nav {
            background: var(--primary-gradient);
            padding: 1rem;
            box-shadow: var(--card-shadow);
        }

        .nav-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .nav-links {
            display: flex;
            gap: 20px;
            list-style: none;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 5px;
            transition: var(--transition);
        }

        .nav-links a:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .nav-logout a {
            background: var(--accent-color);
            color: white;
            padding: 8px 16px;
            border-radius: 5px;
            text-decoration: none;
            transition: var(--transition);
        }

        .nav-logout a:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .welcome-section {
            background: var(--primary-gradient);
            color: white;
            padding: 40px;
            border-radius: var(--border-radius);
            margin: 20px 0;
            box-shadow: var(--card-shadow);
        }

        .welcome-section h2 {
            font-size: 2em;
            margin-bottom: 10px;
        }

        .section-title {
            font-size: 1.8em;
            color: var(--text-primary);
            margin: 30px 0 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #3498db;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin: 20px 0;
        }

        .service-card {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.2);
        }

        .service-image-container {
            position: relative;
            padding-top: 66.67%; /* 3:2 aspect ratio */
            overflow: hidden;
        }

        .service-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .default-service-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f5f6fa;
            color: var(--text-secondary);
        }

        .service-content {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .service-title {
            font-size: 1.4em;
            color: var(--text-primary);
            margin-bottom: 10px;
        }

        .service-price {
            color: var(--accent-color);
            font-size: 1.2em;
            font-weight: bold;
            margin: 10px 0;
        }

        .service-duration {
            color: var(--text-secondary);
            font-size: 0.9em;
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 10px;
        }

        .service-description {
            color: var(--text-secondary);
            line-height: 1.6;
            flex-grow: 1;
            margin-bottom: 15px;
        }

        .book-button {
            background: var(--primary-gradient);
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            text-align: center;
            transition: var(--transition);
            display: inline-block;
        }

        .book-button:hover {
            transform: translateY(-2px);
            box-shadow: var(--card-shadow);
        }

        /* Appointments section styles */
        .appointments-section {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            margin: 30px 0;
            box-shadow: var(--card-shadow);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th {
            background: var(--secondary-gradient);
            color: white;
            padding: 15px;
            text-align: left;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        tr:hover {
            background-color: #f8f9fa;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9em;
            font-weight: 500;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-confirmed {
            background: #d4edda;
            color: #155724;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 6px 12px;
            border-radius: 5px;
            text-decoration: none;
            transition: var(--transition);
            font-size: 0.9em;
        }

        .btn-edit {
            background: #3498db;
            color: white;
        }

        .btn-delete {
            background: var(--accent-color);
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .welcome-section {
                padding: 20px;
            }

            .services-grid {
                grid-template-columns: 1fr;
            }

            table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <nav>
        <div class="nav-content">
            <ul class="nav-links">
                <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="book_appointment.php"><i class="fas fa-calendar-plus"></i> Book Appointment</a></li>
                <li><a href="my_appointments.php"><i class="fas fa-calendar-check"></i> My Appointments</a></li>
            </ul>
            <div class="nav-logout">
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="welcome-section">
            <h2><i class="fas fa-user-circle"></i> Welcome back, <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></h2>
            <p>Ready for your next grooming experience? Book an appointment or manage your existing ones below.</p>
        </div>

        <h3 class="section-title"><i class="fas fa-cut"></i> Available Services</h3>
        <div class="services-grid">
            <?php foreach ($services as $service): ?>
            <div class="service-card">
                <div class="service-image-container">
                    <?php if (!empty($service['image_url'])): ?>
                        <img src="<?= htmlspecialchars($service['image_url']) ?>" 
                             alt="<?= htmlspecialchars($service['name']) ?>" 
                             class="service-image"
                             onerror="this.parentElement.innerHTML = '<div class=\'default-service-image\'><i class=\'fas fa-image fa-2x\'></i><br>Image not available</div>'">
                    <?php else: ?>
                        <div class="default-service-image">
                            <i class="fas fa-image fa-2x"></i><br>No image available
                        </div>
                    <?php endif; ?>
                </div>
                <div class="service-content">
                    <h4 class="service-title"><?= htmlspecialchars($service['name']) ?></h4>
                    <p class="service-price">
                        <i class="fas fa-tag"></i> $<?= htmlspecialchars(number_format($service['price'], 2)) ?>
                    </p>
                    <p class="service-duration">
                        <i class="fas fa-clock"></i> <?= htmlspecialchars($service['duration']) ?> minutes
                    </p>
                    <p class="service-description"><?= htmlspecialchars($service['description']) ?></p>
                    <a href="book_appointment.php?service_id=<?= $service['id'] ?>" class="book-button">
                        <i class="fas fa-calendar-plus"></i> Book Now
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="appointments-section">
            <h3 class="section-title"><i class="fas fa-calendar"></i> Your Upcoming Appointments</h3>
            <table>
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $appointment): ?>
                    <tr>
                        <td><?= htmlspecialchars($appointment['service_name']) ?></td>
                        <td><?= htmlspecialchars($appointment['appointment_date']) ?></td>
                        <td><?= htmlspecialchars($appointment['appointment_time']) ?></td>
                        <td>
                            <span class="status-badge status-<?= strtolower($appointment['status']) ?>">
                                <?= htmlspecialchars($appointment['status']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="edit_appointment.php?id=<?= $appointment['id'] ?>" class="btn btn-edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="delete_appointment.php?id=<?= $appointment['id'] ?>" 
                                   onclick="return confirm('Are you sure you want to cancel this appointment?')"
                                   class="btn btn-delete">
                                    <i class="fas fa-trash"></i> Cancel
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>