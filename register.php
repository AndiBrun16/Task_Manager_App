<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

if (is_logged_in()) {
    redirect('tasks.php');
}

$error = '';
$success = '';

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
                $success = 'Registration successful. You can now <a href="index.php">log in</a>.';
            } else {
                $error = 'Registration failed. Please try again.';
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
    <div>
        <h2>Register</h2>
        <?php echo $error ? display_error($error) : ''; ?>
        <?php echo isset($success) ? display_success($success) : ''; ?>
        <form method="post">
            <div>
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div>
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" name="register">Register</button>
        </form>
        <p>Already have an account? <a href="index.php">Log in here</a>.</p>
    </div>
    <script src="js/script.js"></script>
</body>
</html>