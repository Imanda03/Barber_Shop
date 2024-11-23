<!DOCTYPE html>
<html>
<head>
    <title>Login - Barber Appointment System</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="form-container">
        <h2>Login</h2>
        <h4>Welcome to the Barber Appointment System</h4>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        
        <form action="process_login.php" method="POST" class="">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</body>
</html>
