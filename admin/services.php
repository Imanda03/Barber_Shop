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
            $stmt = $pdo->prepare("UPDATE services SET name = ?, price = ?, duration = ?, description = ? WHERE id = ?");
            $stmt->execute([
                $_POST['name'],
                $_POST['price'],
                $_POST['duration'],
                $_POST['description'],
                $_POST['id']
            ]);
            $_SESSION['success'] = "Service updated successfully";
        } else {
            // Add new service
            $stmt = $pdo->prepare("INSERT INTO services (name, price, duration, description) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $_POST['name'],
                $_POST['price'],
                $_POST['duration'],
                $_POST['description']
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
        <h2>Manage Services</h2>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="success-message">
                <?= htmlspecialchars($_SESSION['success']) ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Add/Edit Service Form -->
        <div class="form-container">
            <h3><?= $editService ? 'Edit Service' : 'Add New Service' ?></h3>
            <form method="POST" class="service-form">
                <?php if ($editService): ?>
                    <input type="hidden" name="id" value="<?= $editService['id'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="name">Service Name:</label>
                    <input type="text" id="name" name="name" required
                           value="<?= $editService ? htmlspecialchars($editService['name']) : '' ?>">
                </div>

                <div class="form-group">
                    <label for="price">Price ($):</label>
                    <input type="number" id="price" name="price" step="0.01" required
                           value="<?= $editService ? htmlspecialchars($editService['price']) : '' ?>">
                </div>

                <div class="form-group">
                    <label for="duration">Duration (minutes):</label>
                    <input type="number" id="duration" name="duration" required
                           value="<?= $editService ? htmlspecialchars($editService['duration']) : '' ?>">
                </div>

                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" rows="3"><?= $editService ? htmlspecialchars($editService['description']) : '' ?></textarea>
                </div>

                <button type="submit" class="btn"><?= $editService ? 'Update Service' : 'Add Service' ?></button>
                <?php if ($editService): ?>
                    <a href="services.php" class="btn btn-secondary">Cancel Edit</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Services List -->
        <div class="services-list">
            <h3>Current Services</h3>
            <table>
                <thead>
                    <tr>
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
                            <td><?= htmlspecialchars($service['name']) ?></td>
                            <td>$<?= htmlspecialchars(number_format($service['price'], 2)) ?></td>
                            <td><?= htmlspecialchars($service['duration']) ?> mins</td>
                            <td><?= htmlspecialchars($service['description']) ?></td>
                            <td>
                                <a href="?edit=<?= $service['id'] ?>" class="btn btn-small">Edit</a>
                                <a href="?delete=<?= $service['id'] ?>" 
                                   class="btn btn-small btn-danger"
                                   onclick="return confirm('Are you sure you want to delete this service?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>