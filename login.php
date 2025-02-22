<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - Barber Appointment System</title>
    <link rel="stylesheet" href="login.css">
</head>
<style>
    /* CSS for Modal */
.modal {
    display: none; /* Hidden by default */
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
}

.modal-content {
    background-color: white;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 500px;
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}

/* Error Message Styling */
.error-message {
    color: red;
    font-size: 14px;
    margin-top: 10px;
}

</style>
<body>

    <div class="form-container">
        <h2>Login</h2>
        <h4>Welcome to the Barber Appointment System</h4>

        <form action="process_login.php" method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php">Register here</a></p>

        <!-- Display error message inline or in a modal if login fails -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal for displaying errors -->
    <div id="errorModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Error</h2>
            <p id="errorMessage"></p>
        </div>
    </div>

    <script>
        // Check if there's an error in session (from process_login.php)
        <?php if (isset($_SESSION['error'])): ?>
            const errorMessage = "<?php echo $_SESSION['error']; ?>";
            document.getElementById('errorMessage').innerText = errorMessage;
            document.getElementById('errorModal').style.display = "block";
            <?php unset($_SESSION['error']); ?> // Clear error after displaying
        <?php endif; ?>

        // Close modal when clicking the "X"
        document.querySelector('.close').onclick = function() {
            document.getElementById('errorModal').style.display = "none";
        }

        // Close modal if the user clicks outside of it
        window.onclick = function(event) {
            if (event.target === document.getElementById('errorModal')) {
                document.getElementById('errorModal').style.display = "none";
            }
        }
    </script>

</body>
</html>
