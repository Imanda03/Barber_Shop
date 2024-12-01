<?php
// admin/services.php
session_start();
require_once '../config.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Handle service deletion
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $_SESSION['success'] = "Service deleted successfully";
        header('Location: services.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Cannot delete service: It may have associated appointments";
    }
}

// Handle service addition/update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        if (isset($_POST['id'])) {
            // Update existing service
            $stmt = $pdo->prepare("UPDATE services SET name = ?, price = ?, duration = ?, description = ?, image_url = ? WHERE id = ?");
            $stmt->execute([
                $_POST['name'],
                $_POST['price'],
                $_POST['duration'],
                $_POST['description'],
                $_POST['image_url'],
                $_POST['id']
            ]);
            $_SESSION['success'] = "Service updated successfully";
        } else {
            // Add new service
            $stmt = $pdo->prepare("INSERT INTO services (name, price, duration, description, image_url) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['name'],
                $_POST['price'],
                $_POST['duration'],
                $_POST['description'],
                $_POST['image_url']
            ]);
            $_SESSION['success'] = "Service added successfully";
        }
        header('Location: services.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error saving service: " . $e->getMessage();
    }
}

// Fetch all services
$stmt = $pdo->query("SELECT * FROM services ORDER BY name");
$services = $stmt->fetchAll();

// Fetch service for editing if ID is provided
$editService = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editService = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Services - Barber Appointment System</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #2c3e50, #3498db);
            --secondary-gradient: linear-gradient(135deg, #34495e, #2c3e50);
            --success-color: #4CAF50;
            --danger-color: #f44336;
            --border-radius: 10px;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .page-header {
            background: var(--primary-gradient);
            color: white;
            padding: 25px;
            border-radius: var(--border-radius);
            margin-bottom: 30px;
            box-shadow: var(--card-shadow);
        }

        .page-header h2 {
            margin: 0;
            font-size: 28px;
        }

        .section-title {
            color: #2c3e50;
            font-size: 24px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #3498db;
        }

        /* Form Styling */
        .form-container {
            background: white;
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
            outline: none;
        }

        /* Image Preview */
        .image-preview {
            margin-top: 15px;
            background: #f8f9fa;
            padding: 15px;
            border-radius: var(--border-radius);
            text-align: center;
        }

        .service-image {
            max-width: 150px;
            max-height: 150px;
            object-fit: cover;
            border-radius: 5px;
            box-shadow: var(--card-shadow);
        }

        /* Table Styling */
        .services-list {
            background: white;
            padding: 30px;
            border-radius: var(--border-radius);
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
            vertical-align: middle;
        }

        tr:hover {
            background-color: #f8f9fa;
        }

        /* Button Styling */
        .btn {
            display: inline-block;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            margin-right: 10px;
            text-align: center;
        }

        .btn-primary {
            background: var(--primary-gradient);
            color: white;
        }

        .btn-secondary {
            background: #95a5a6;
            color: white;
        }

        .btn-danger {
            background: var(--danger-color);
            color: white;
        }

        .btn-small {
            padding: 8px 16px;
            font-size: 14px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--card-shadow);
        }

        /* Message Styling */
        .message {
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            animation: slideDown 0.5s ease-out;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid var(--success-color);
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid var(--danger-color);
        }

        @keyframes slideDown {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Description cell styling */
        .description-cell {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Action buttons container */
        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            padding: 8px 16px;
            border-radius: 5px;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .edit-btn {
            background: #3498db;
        }

        .delete-btn {
            background: #e74c3c;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .form-container,
            .services-list {
                padding: 15px;
            }

            table {
                display: block;
                overflow-x: auto;
            }

            .btn {
                padding: 10px 20px;
            }
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
        <div class="page-header">
            <h2><i class="fas fa-cut"></i> Manage Services</h2>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="message success-message">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['success']) ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="message error-message">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Add/Edit Service Form -->
        <div class="form-container">
            <h3 class="section-title">
                <i class="fas <?= $editService ? 'fa-edit' : 'fa-plus-circle' ?>"></i>
                <?= $editService ? 'Edit Service' : 'Add New Service' ?>
            </h3>
            <form method="POST" class="service-form">
                <?php if ($editService): ?>
                    <input type="hidden" name="id" value="<?= $editService['id'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="name"><i class="fas fa-tag"></i> Service Name:</label>
                    <input type="text" id="name" name="name" required
                           value="<?= $editService ? htmlspecialchars($editService['name']) : '' ?>"
                           placeholder="Enter service name">
                </div>

                <div class="form-group">
                    <label for="price"><i class="fas fa-dollar-sign"></i> Price:</label>
                    <input type="number" id="price" name="price" step="0.01" required
                           value="<?= $editService ? htmlspecialchars($editService['price']) : '' ?>"
                           placeholder="Enter price">
                </div>

                <div class="form-group">
                    <label for="duration"><i class="fas fa-clock"></i> Duration (minutes):</label>
                    <input type="number" id="duration" name="duration" required
                           value="<?= $editService ? htmlspecialchars($editService['duration']) : '' ?>"
                           placeholder="Enter duration in minutes">
                </div>

                <div class="form-group">
                    <label for="description"><i class="fas fa-align-left"></i> Description:</label>
                    <textarea id="description" name="description" rows="3" 
                              placeholder="Enter service description"><?= $editService ? htmlspecialchars($editService['description']) : '' ?></textarea>
                </div>

               <div class="form-group">
    <label for="image_url"><i class="fas fa-image"></i> Image URL:</label>
    <input type="url" id="image_url" name="image_url" 
           value="<?= ($editService && isset($editService['image_url'])) ? htmlspecialchars($editService['image_url']) : '' ?>"
           placeholder="Enter image URL"
           onchange="previewImage(this)">
    <div id="image-preview" class="image-preview">
        <?php if ($editService && isset($editService['image_url']) && $editService['image_url']): ?>
            <img src="<?= htmlspecialchars($editService['image_url']) ?>" 
                 alt="Service preview" 
                 class="service-image">
        <?php endif; ?>
    </div>
</div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas <?= $editService ? 'fa-save' : 'fa-plus' ?>"></i>
                    <?= $editService ? 'Update Service' : 'Add Service' ?>
                </button>
                <?php if ($editService): ?>
                    <a href="services.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel Edit
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Services List -->
        <div class="services-list">
            <h3 class="section-title"><i class="fas fa-list"></i> Current Services</h3>
            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Service Name</th>
                        <th>Price</th>
                        <th>Duration</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($services as $service): ?>
                        <tr>
                            <td>
                                <?php if ($service['image_url']): ?>
                                    <img src="<?= htmlspecialchars($service['image_url']) ?>" 
                                         alt="<?= htmlspecialchars($service['name']) ?>" 
                                         class="service-image">
                                <?php else: ?>
                                    <i class="fas fa-image fa-2x" style="color: #ccc;"></i>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($service['name']) ?></td>
                            <td>$<?= htmlspecialchars(number_format($service['price'], 2)) ?></td>
                            <td><?= htmlspecialchars($service['duration']) ?> mins</td>
                            <td class="description-cell"><?= htmlspecialchars($service['description']) ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="?edit=<?= $service['id'] ?>" class="action-btn edit-btn">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="?delete=<?= $service['id'] ?>" 
                                       class="action-btn delete-btn"
                                       onclick="return confirm('Are you sure you want to delete this service?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    function previewImage(input) {
        const preview = document.getElementById('image-preview');
        preview.innerHTML = '';
        
        if (input.value) {
            const img = document.createElement('img');
            img.src = input.value;
            img.alt = 'Service preview';
            img.className = 'service-image';
            img.onerror = function() {
                preview.innerHTML = '<p><i class="fas fa-exclamation-circle"></i> Invalid image URL</p>';
            };
            preview.appendChild(img);
        }
    }

    // Auto-hide messages after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const messages = document.querySelectorAll('.message');
        messages.forEach(message => {
            setTimeout(() => {
                message.style.opacity = '0';
                setTimeout(() => message.remove(), 500);
            }, 5000);
        });
    });
    </script>
</body>
</html>