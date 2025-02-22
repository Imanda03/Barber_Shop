<?php
// config.php
$host = "localhost";
$dbname = "barbershop_db";
$username = "kidncode";  // Change this to your database username
$password = "kidncode";      // Change this to your database password

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host", $username, $password);
    
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
    
    // Select the database
    $pdo->exec("USE `$dbname`");
    
    // Create tables
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('user', 'admin') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    $pdo->exec("CREATE TABLE IF NOT EXISTS services (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        duration INT NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    $pdo->exec("CREATE TABLE IF NOT EXISTS appointments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        service_id INT NOT NULL,
        appointment_date DATE NOT NULL,
        appointment_time TIME NOT NULL,
        status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");

    // Insert admin account if it doesn't exist
    $admin_email = "admin@gmail.com";
    $admin_password = password_hash("admin", PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$admin_email]);
    $adminExists = $stmt->fetchColumn();
    
    if (!$adminExists) {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')");
        $stmt->execute(['Admin', $admin_email, $admin_password]);
    }
    
    // Insert some default services if none exist
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM services");
    $stmt->execute();
    $servicesExist = $stmt->fetchColumn();
    
    if (!$servicesExist) {
        $defaultServices = [
            ['Haircut', 30.00, 30, 'Basic haircut service'],
            ['Shave', 20.00, 20, 'Professional shaving service'],
            ['Hair Color', 50.00, 60, 'Hair coloring service'],
            ['Beard Trim', 15.00, 15, 'Beard trimming and shaping']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO services (name, price, duration, description) VALUES (?, ?, ?, ?)");
        foreach ($defaultServices as $service) {
            $stmt->execute($service);
        }
    }

} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>