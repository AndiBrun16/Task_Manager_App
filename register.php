<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

// Inițializăm variabilele $error și $success
$error = '';
$success = '';

if (is_logged_in()) {
    redirect('tasks.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row) {
                $stmt_check_username = $conn->prepare("SELECT id FROM users WHERE username = ?");
                $stmt_check_username->bind_param("s", $username);
                $stmt_check_username->execute();
                $result_check_username = $stmt_check_username->get_result();
                if ($result_check_username->num_rows > 0) {
                    $error = 'Username already taken.';
                } else {
                    $error = 'Email address already registered.';
                }
                $stmt_check_username->close();
            }
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashed_password);
            if ($stmt->execute()) {
                $success = 'Registration successful.';
            } else {
                $error = 'Registration failed. Please try again.';
                // Adaugă această linie pentru a vedea eroarea MySQL în logurile serverului
                error_log("MySQL Error: " . $conn->error);
            }
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Task Manager</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <a href ='#'><img src="images/logo.png" alt="SimpleDo Logo"></a>
        <ul class="navlist">
            <li><a href="index.php#Home">Home</a></li>
            <li><a href="#">About</a></li>
            <li><a href="#">Contact</a></li>
        </ul>
        <div class='right-content'>
            <a href="index.php#login-section" class='nav-btn'>Sign In</a>
            <div class='bx bx-menu' id='menu-icon'></div>
        </div>
    </header>
    <section class='Register'>
        <div class="container">
            <h2>Register</h2>
            <?php echo $error ? display_error($error) : ''; ?>
            <?php echo isset($success) ? display_success($success) : ''; ?>
            <form method="post">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" name="register">Register</button>
            </form>
            <p>Already have an account? <a href="index.php#login-section">Log in here</a>.</p>
        </div>
    </section>
    <script src="js/script.js"></script>
</body>
</html>