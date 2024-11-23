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
$stmt = $pdo->prepare("SELECT a.*, s.name as service_name, s.price, s.duration, s.image_url 
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
    <title>My Appointments - Modern Barber Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --primary-light: #534bae;
            --primary-dark: #000051;
            --secondary: #ff6f00;
            --secondary-light: #ffa040;
            --secondary-dark: #c43e00;
            --text-light: #ffffff;
            --text-dark: #212121;
            --background: #f5f6fa;
            --primary-gradient: linear-gradient(135deg, #2c3e50, #3498db);
            --secondary-gradient: linear-gradient(135deg, #34495e, #2c3e50);
            --accent-color: #e74c3c;
            --text-primary: #2c3e50;
            --text-secondary: #7f8c8d;
            --border-radius: 10px;
            --card-bg: #ffffff;
            --success: #4caf50;
            --warning: #ff9800;
            --danger: #f44336;
            --border-radius: 12px;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --shadow-hover: 0 8px 15px rgba(0, 0, 0, 0.2);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
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

        /* Enhanced Navigation */
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

        /* Container Styles */
        .container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 2.5rem;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        /* Enhanced Table Styles */
        .appointments-table {
            width: 100%;
            background: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .appointments-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .appointments-table th {
            background: var(--primary);
            color: var(--text-light);
            padding: 1.2rem;
            text-align: left;
            font-weight: 500;
        }

        .appointments-table td {
            padding: 1.2rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .appointments-table tr:last-child td {
            border-bottom: none;
        }

        .appointments-table tr:hover {
            background: rgba(0, 0, 0, 0.02);
        }

        /* Status Badges */
        .status {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
            text-align: center;
            display: inline-block;
        }

        .status-confirmed {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .status-pending {
            background: #fff3e0;
            color: #ef6c00;
        }

        .status-cancelled {
            background: #ffebee;
            color: #c62828;
        }

       /* Modal Styles */
.modal {
    display: none; /* Hidden by default */
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.6); /* Background dim effect */
    padding: 20px;
    box-sizing: border-box;
}

.modal-content {
    background: var(--card-bg);
    margin: auto;
    padding: 2rem;
    border-radius: var(--border-radius);
    max-width: 600px;
    box-shadow: var(--shadow-hover);
    position: relative;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #eaeaea;
    padding-bottom: 1rem;
}

.modal-header h2 {
    margin: 0;
    font-size: 1.5rem;
    color: var(--text-primary);
}

.modal-header .close {
    font-size: 1.5rem;
    color: var(--text-secondary);
    cursor: pointer;
    transition: var(--transition);
}

.modal-header .close:hover {
    color: var(--accent-color);
}

.modal-body {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.modal-image {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 400px;
    background: #f9f9f9;
    border-radius: var(--border-radius);
    overflow: hidden;
}

.modal-image img {
    max-width: 100%;
    max-height: 100%;
    border-radius: var(--border-radius);
}

.modal-details {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.detail-label {
    font-weight: 500;
    color: var(--text-primary);
}

.detail-value {
    font-size: 0.95rem;
    color: var(--text-secondary);
}

.status-badge {
    padding: 0.3rem 1rem;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: bold;
}

.duration-badge {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.price-tag {
    color: var(--success);
    font-weight: bold;
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.no-image-placeholder {
    text-align: center;
    color: #ccc;
    font-size: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    width: 100%;
}

        /* View Button */
        .view-btn {
            background: var(--primary);
            color: var(--text-light);
            padding: 0.6rem 1.2rem;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        .view-btn:hover {
            background: var(--primary-light);
            transform: translateY(-2px);
        }

        .view-btn i {
            font-size: 0.9rem;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #666;
        }

        .empty-state i {
            font-size: 3rem;
            color: #ccc;
            margin-bottom: 1rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
            }

            .page-title {
                font-size: 2rem;
            }

            .appointments-table {
                overflow-x: auto;
            }

            .nav-content {
                padding: 0 1rem;
            }

              .modal-content {
        width: 90%;
        padding: 1.5rem;
    }

    .modal-image {
        height: 120px;
    }

    .price-tag {
        font-size: 1rem;
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
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-calendar-check"></i>
                My Appointments
            </h1>
        </div>
        
        <div class="appointments-table">
            <table>
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($appointments) > 0): ?>
                        <?php foreach ($appointments as $appointment): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <i class="fas fa-cut"></i>
                                        <?= htmlspecialchars($appointment['service_name']) ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <i class="far fa-calendar"></i>
                                        <?= date('M d, Y', strtotime($appointment['appointment_date'])) ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <i class="far fa-clock"></i>
                                        <?= date('h:i A', strtotime($appointment['appointment_time'])) ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="status status-<?= strtolower($appointment['status']) ?>">
                                        <?= ucfirst(htmlspecialchars($appointment['status'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="view-btn" onclick="openModal(<?= htmlspecialchars(json_encode($appointment)) ?>)">
                                        <i class="fas fa-eye"></i>
                                        View Details
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <i class="far fa-calendar-times"></i>
                                    <p>No appointments found.</p>
                                    <a href="book_appointment.php" class="view-btn" style="display: inline-flex; margin-top: 1rem;">
                                        <i class="fas fa-plus"></i>
                                        Book New Appointment
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
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
                    <div class="detail-label">Service:</div>
                    <div class="detail-value" id="modal-service"></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Date & Time:</div>
                    <div class="detail-value">
                        <span id="modal-date"></span> at <span id="modal-time"></span>
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Status:</div>
                    <div class="detail-value">
                        <span id="modal-status" class="status-badge"></span>
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Duration:</div>
                    <div class="detail-value duration-badge">
                        <i class="far fa-clock"></i>
                        <span id="modal-duration"></span>
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Price:</div>
                    <div class="detail-value price-tag">
                        <span id="modal-price"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <script>
       var modal = document.getElementById("appointmentModal");
var span = document.getElementsByClassName("close")[0];

// Function to open the modal
function openModal(appointment) {
    try {
        document.getElementById("modal-service").textContent = appointment.service_name || "N/A";
        document.getElementById("modal-date").textContent = new Date(appointment.appointment_date).toLocaleDateString('en-US', {
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
        }) || "N/A";
        document.getElementById("modal-time").textContent = appointment.appointment_time || "N/A";
        document.getElementById("modal-duration").textContent = (appointment.duration || "0") + " minutes";
        document.getElementById("modal-price").textContent = "$" + (appointment.price || "0.00");

        const statusElement = document.getElementById("modal-status");
        statusElement.textContent = appointment.status || "Unknown";
        statusElement.className = 'status-badge status-' + (appointment.status ? appointment.status.toLowerCase() : "unknown");

        const imageContainer = document.getElementById("modal-image-container");
        if (appointment.image_url) {
            imageContainer.innerHTML = `<img src="${appointment.image_url}" alt="${appointment.service_name}" 
                onerror="this.parentElement.innerHTML='<div class=\'no-image-placeholder\'><i class=\'far fa-image fa-3x\'></i></div>'">`;
        } else {
            imageContainer.innerHTML = '<div class="no-image-placeholder"><i class="far fa-image fa-3x"></i></div>';
        }

        modal.style.display = "block";
    } catch (error) {
        console.error("Error displaying modal data:", error);
        alert("An error occurred while opening the modal. Please try again.");
    }
}

// Close modal on click
span.onclick = function () {
    modal.style.display = "none";
};

// Close modal when clicking outside it
window.onclick = function (event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
};

// Close modal with Escape key
window.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        modal.style.display = "none";
    }
});
    </script>
</body>
</html>