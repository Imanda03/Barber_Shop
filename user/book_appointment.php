<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../login.php');
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
    
    // Check if the time slot is available
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments 
        WHERE service_id = ? AND appointment_date = ? AND appointment_time = ? 
        AND status != 'cancelled'");
    $stmt->execute([$service_id, $date, $time]);
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        $stmt = $pdo->prepare("INSERT INTO appointments (user_id, service_id, appointment_date, appointment_time) 
            VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $service_id, $date, $time]);
        header('Location: dashboard.php?success=1');
    } else {
        $error = "This time slot is already booked. Please choose another time.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Book Appointment - Modern Barber Shop</title>
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

        /* Navbar Styles */
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
        /* Container */
        .container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .page-title {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .error {
            background: var(--danger);
            color: var(--text-light);
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .booking-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin: 2rem 0;
        }

        .booking-form {
            background: var(--card-bg);
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
            font-weight: 500;
        }

        select, input {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e0e0e0;
            border-radius: var(--border-radius);
            background: #f8f9fa;
            transition: var(--transition);
            font-size: 1rem;
        }

        select:focus, input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(26, 35, 126, 0.1);
        }

        button {
            background: var(--primary);
            color: var(--text-light);
            padding: 1rem;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            width: 100%;
            font-size: 1.1rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: var(--transition);
        }

        button:hover {
            background: var(--primary-light);
            transform: translateY(-2px);
        }

        .service-preview {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            display: none;
        }

        .service-preview.active {
            display: block;
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .service-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }

        .default-image-placeholder {
            width: 100%;
            height: 300px;
            background: linear-gradient(135deg, #f5f5f5, #e0e0e0);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #666;
            font-size: 1.1rem;
        }

        .default-image-placeholder i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #999;
        }

        .service-details {
            padding: 2rem;
        }

        .service-details h3 {
            color: var(--primary);
            font-size: 1.8rem;
            margin-bottom: 1rem;
        }

        .service-info {
            display: grid;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .price {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--secondary);
        }

        .duration {
            color: var(--text-dark);
            opacity: 0.8;
        }

        .description {
            line-height: 1.8;
            color: var(--text-dark);
            opacity: 0.9;
        }

        @media (max-width: 968px) {
            .booking-container {
                grid-template-columns: 1fr;
            }

            .container {
                padding: 0 1rem;
            }

            .page-title {
                font-size: 2rem;
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
        <h1 class="page-title">
            <i class="fas fa-calendar-plus"></i>
            Book Your Appointment
        </h1>
        
        <?php if (isset($error)): ?>
            <div class="error">
                <i class="fas fa-exclamation-circle"></i>
                <?= $error ?>
            </div>
        <?php endif; ?>
        
        <div class="booking-container">
            <div class="booking-form">
                <form method="POST">
                    <div class="form-group">
                        <label for="service_id">
                            <i class="fas fa-cut"></i> Select a Service:
                        </label>
                        <select name="service_id" id="service_id" required>
                            <option value="">Choose your service</option>
                            <?php foreach ($services as $service): ?>
                                <option value="<?= $service['id'] ?>" 
                                        data-image="<?= htmlspecialchars($service['image_url']) ?>"
                                        data-name="<?= htmlspecialchars($service['name']) ?>"
                                        data-price="<?= htmlspecialchars($service['price']) ?>"
                                        data-duration="<?= htmlspecialchars($service['duration']) ?>"
                                        data-description="<?= htmlspecialchars($service['description']) ?>">
                                    <?= htmlspecialchars($service['name']) ?> - $<?= htmlspecialchars($service['price']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="date">
                            <i class="fas fa-calendar-alt"></i> Select Date:
                        </label>
                        <input type="date" id="date" name="date" required min="<?= date('Y-m-d') ?>">
                    </div>

                    <div class="form-group">
                        <label for="time">
                            <i class="fas fa-clock"></i> Select Time:
                        </label>
                        <input type="time" id="time" name="time" required min="09:00" max="17:00" step="1800">
                    </div>
                    
                    <button type="submit">
                        <i class="fas fa-check-circle"></i>
                        Confirm Booking
                    </button>
                </form>
            </div>

            <div class="service-preview" id="servicePreview">
                <div id="serviceImage"></div>
                <div class="service-details">
                    <h3 id="serviceName"></h3>
                    <div class="service-info">
                        <div class="info-item price">
                            <i class="fas fa-tag"></i>
                            $<span id="servicePrice"></span>
                        </div>
                        <div class="info-item duration">
                            <i class="fas fa-clock"></i>
                            <span id="serviceDuration"></span> minutes
                        </div>
                    </div>
                    <p class="description" id="serviceDescription"></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('service_id').addEventListener('change', function() {
            const preview = document.getElementById('servicePreview');
            const selectedOption = this.options[this.selectedIndex];
            
            if (this.value) {
                // Update service details
                document.getElementById('serviceName').textContent = selectedOption.dataset.name;
                document.getElementById('servicePrice').textContent = selectedOption.dataset.price;
                document.getElementById('serviceDuration').textContent = selectedOption.dataset.duration;
                document.getElementById('serviceDescription').textContent = selectedOption.dataset.description;
                
                // Update image
                const imageContainer = document.getElementById('serviceImage');
                if (selectedOption.dataset.image) {
                    imageContainer.innerHTML = `
                        <img src="${selectedOption.dataset.image}" 
                             alt="${selectedOption.dataset.name}" 
                             class="service-image"
                             onerror="this.parentElement.innerHTML='<div class=\'default-image-placeholder\'><i class=\'fas fa-image\'></i>Image not available</div>'">
                    `;
                } else {
                    imageContainer.innerHTML = '<div class="default-image-placeholder"><i class="fas fa-image"></i>No image available</div>';
                }
                
                preview.classList.add('active');
            } else {
                preview.classList.remove('active');
            }
        });

        // Add time input formatting
        const timeInput = document.getElementById('time');
        timeInput.addEventListener('change', function() {
            const time = this.value;
            const hour = parseInt(time.split(':')[0]);
            if (hour < 9 || hour >= 17) {
                alert('Please select a time between 9:00 AM and 5:00 PM');
                this.value = '';
            }
        });
        document.addEventListener('DOMContentLoaded', function() {
    // Function to get URL parameters
    function getUrlParameter(name) {
        name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
        var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
        var results = regex.exec(location.search);
        return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
    }

    // Get service_id from URL
    const serviceId = getUrlParameter('service_id');
    
    // If service_id exists in URL, auto-select the service
    if (serviceId) {
        const serviceSelect = document.querySelector('select[name="service_id"]'); // Adjust selector based on your form
        
        if (serviceSelect) {
            // Set the value
            serviceSelect.value = serviceId;
            
            // Trigger change event in case you have any dependent fields
            const event = new Event('change');
            serviceSelect.dispatchEvent(event);
        }
    }
});
    </script>
</body>
</html>