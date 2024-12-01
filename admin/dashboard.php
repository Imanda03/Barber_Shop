<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Initialize appointments array
$appointments = [];
$error = null;

try {
    // Fetch all appointments with additional details
    $stmt = $pdo->prepare("SELECT a.*, u.name as user_name, s.name as service_name, s.price, s.duration, s.image_url 
        FROM appointments a 
        JOIN users u ON a.user_id = u.id 
        JOIN services s ON a.service_id = s.id 
        ORDER BY a.appointment_date, a.appointment_time");
    $stmt->execute();
    $appointments = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Database error: Unable to fetch appointments. Please try again later.";
    error_log("Database error in admin dashboard: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Barber Appointment System</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .dashboard-header {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .dashboard-header h2 {
            margin: 0;
            font-size: 28px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        th {
            background: linear-gradient(135deg, #34495e, #2c3e50);
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

        .btn {
            padding: 8px 16px;
            border-radius: 5px;
            margin-right: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
        }

        .btn-confirm {
            background-color: #4CAF50;
            color: white;
        }

        .btn-cancel {
            background-color: #f44336;
            color: white;
        }

        .btn-view {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
        }

        .btn:hover:not(.disabled) {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .no-data-message {
            text-align: center;
            padding: 40px;
            background: #f8f9fa;
            border-radius: 10px;
            margin: 20px 0;
            color: #6c757d;
        }

        .no-data-message i {
            font-size: 48px;
            margin-bottom: 20px;
            color: #dee2e6;
        }

        .no-data-message h3 {
            margin-bottom: 10px;
            color: #495057;
        }

        .no-data-message p {
            margin-bottom: 20px;
        }

        .refresh-btn {
            display: inline-block;
            padding: 10px 20px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .refresh-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .error-banner {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .error-banner i {
            margin-right: 10px;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.7);
            animation: fadeIn 0.3s ease-in-out;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            width: 90%;
            max-width: 900px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            overflow: hidden;
            animation: slideIn 0.3s ease-out;
        }

        .modal-header {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            padding: 20px 30px;
            position: relative;
        }

        .modal-body {
            display: flex;
            padding: 0;
            min-height: 400px;
        }

        .modal-image {
            flex: 0 0 40%;
            background-color: #f8f9fa;
            position: relative;
            overflow: hidden;
        }

        .modal-details {
            flex: 1;
            padding: 30px;
        }

        .detail-item {
            margin-bottom: 25px;
        }

        .detail-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .detail-value {
            font-size: 16px;
            color: #2c3e50;
            font-weight: 500;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .status-pending {
            background-color: #ffeeba;
            color: #856404;
        }

        .status-confirmed {
            background-color: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }

        .close {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: white;
            font-size: 28px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .no-image-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            background-color: #f8f9fa;
            color: #dee2e6;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideIn {
            from { transform: translateY(-100px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Success/Error messages */
        .message {
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
            animation: slideDown 0.5s ease-out;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @keyframes slideDown {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Modal image styling */
        .modal-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .duration-badge, .price-tag {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .duration-badge i, .price-tag i {
            color: #6c757d;
        }
    </style>
</head>
<body>
    <nav>
        <ul class="nav-links">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="services.php">Manage Services</a></li>
        </ul>
        <div class="nav-logout">
            <a href="../logout.php">Logout</a>
        </div>
    </nav>
    
    <div class="container">
        <div class="dashboard-header">
            <h2>Admin Dashboard</h2>
        </div>

        <?php if ($error): ?>
        <div class="error-banner">
            <div>
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
            <a href="dashboard.php" class="refresh-btn">
                <i class="fas fa-sync-alt"></i> Retry
            </a>
        </div>
        <?php endif; ?>
        
        <?php if (empty($appointments) && !$error): ?>
        <div class="no-data-message">
            <i class="far fa-calendar-times"></i>
            <h3>No Appointments Found</h3>
            <p>There are currently no appointments in the system.</p>
            <a href="dashboard.php" class="refresh-btn">
                <i class="fas fa-sync-alt"></i> Refresh Page
            </a>
        </div>
        <?php else: ?>
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
                    <td>
                        <span class="status-badge status-<?= strtolower($appointment['status']) ?>">
                            <?= htmlspecialchars($appointment['status']) ?>
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-view" onclick="openModal(<?= htmlspecialchars(json_encode($appointment)) ?>)">
                            View
                        </button>
                        <a href="update_status.php?id=<?= $appointment['id'] ?>&status=confirmed" 
                           class="btn btn-confirm <?= $appointment['status'] === 'confirmed' || $appointment['status'] === 'cancelled' ? 'disabled' : '' ?>"
                           <?= $appointment['status'] === 'confirmed' || $appointment['status'] === 'cancelled' ? 'onclick="return false;"' : '' ?>>
                            Confirm
                        </a>
                        <a href="update_status.php?id=<?= $appointment['id'] ?>&status=cancelled" 
                           class="btn btn-cancel <?= $appointment['status'] === 'confirmed' || $appointment['status'] === 'cancelled' ? 'disabled' : '' ?>"
                           <?= $appointment['status'] === 'confirmed' || $appointment['status'] === 'cancelled' ? 'onclick="return false;"' : '' ?>>
                            Cancel
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>

    <!-- Modal -->
    <div id="appointmentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Appointment Details</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <div class="modal-image">
                    <div id="modal-image-container">
                        <!-- Image will be inserted here via JavaScript -->
                    </div>
                </div>
                <div class="modal-details">
                    <div class="detail-item">
                        <div class="detail-label">Customer</div>
                        <div class="detail-value" id="modal-customer"></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Service</div>
                        <div class="detail-value" id="modal-service"></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Date & Time</div>
                        <div class="detail-value">
                            <span id="modal-date"></span> at <span id="modal-time"></span>
                        </div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Status</div>
                        <div class="detail-value">
                            <span id="modal-status" class="status-badge"></span>
                        </div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Duration</div>
                        <div class="detail-value duration-badge">
                            <i class="far fa-clock"></i>
                            <span id="modal-duration"></span>
                        </div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Price</div>
                        <div class="detail-value price-tag" id="modal-price"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($_GET['message'])): ?>
        <div id="success-message" class="message success-message"><?= htmlspecialchars($_GET['message']) ?></div>
    <?php endif; ?>
    
    <?php if (isset($_GET['error'])): ?>
        <div id="error-message" class="message error-message"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <script>
        var modal = document.getElementById("appointmentModal");
        var span = document.getElementsByClassName("close")[0];
        
        function openModal(appointment) {
            // Populate modal data
            document.getElementById("modal-customer").textContent = appointment.user_name;
            document.getElementById("modal-service").textContent = appointment.service_name;
            document.getElementById("modal-date").textContent = new Date(appointment.appointment_date).toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            document.getElementById("modal-time").textContent = appointment.appointment_time;
            document.getElementById("modal-duration").textContent = appointment.duration + " minutes";
            document.getElementById("modal-price").textContent = "$" + appointment.price;
            
            // Handle status badge
            const statusElement = document.getElementById("modal-status");
            statusElement.textContent = appointment.status;
            statusElement.className = 'status-badge status-' + appointment.status.toLowerCase();
            
            // Handle service image
            const imageContainer = document.getElementById("modal-image-container");
            if (appointment.image_url) {
                imageContainer.innerHTML = `<img src="${appointment.image_url}" alt="${appointment.service_name}" onerror="this.parentElement.innerHTML='<div class=\'no-image-placeholder\'><i class=\'far fa-image fa-3x\'></i></div>'">`
            } else {
                imageContainer.innerHTML = '<div class="no-image-placeholder"><i class="far fa-image fa-3x"></i></div>';
            }
            
            modal.style.display = "block";
        }
        
        span.onclick = function() {
            modal.style.display = "none";
        }
        
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        // Auto-hide messages after 5 seconds
        const messages = document.querySelectorAll('.message');
        messages.forEach(message => {
            setTimeout(() => {
                message.style.display = 'none';
            }, 5000);
        });
    </script>
</body>
</html>