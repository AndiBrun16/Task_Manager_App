<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

if (is_logged_in()) {
    redirect('tasks.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                redirect('tasks.php');
            } else {
                $error = 'Incorrect password.';
            }
        } else {
            $error = 'User not found.';
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
    <title>SimpleDo</title>
    <link rel="stylesheet" href="css/style.css">

</head>
<body>
    <header>
        <a href ='#'><img src="images/logo.png" alt="SimpleDo Logo"></a>
        <ul class="navlist">
            <li><a href="Home">Home</a></li>
            <li><a href="#">About</a></li>
            <li><a href="#">Contact</a></li>
        </ul>
        <div class='right-content'>
            <a href="#" class='nav-btn'>Sign In</a>
            <div class='bx bx-menu' id='menu-icon'></div>
        </div>
    </header>
    <section class="hero" id='Home'>
        <div class="hero-text">
        <h2>#Master Your Day, One Task at a Time. </h2>
            <h1>-SimpleDo</h1>
            <p>Your simple task management solution. Note, Work and Complete any task you set out through your day!  </p>
            <div class='main-hero'>
                <a href="#" class='btn'>TRY IT NOW!</a>
            </div>
        </div>
        <div class='hero-img'>
            <img src="images/hero.svg" alt="">
        </div>
    </section>
    <section>
        <h2>Login</h2>
        <?php echo $error ? display_error($error) : ''; ?>
        <form method="post">
            <div>
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" name="login">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php">Register here</a>.</p>
    </section>
    <section>
        <h2>Login</h2>
        <?php echo $error ? display_error($error) : ''; ?>
        <form method="post">
            <div>
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" name="login">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php">Register here</a>.</p>
    </section>
    <section>
        <h2>Login</h2>
        <?php echo $error ? display_error($error) : ''; ?>
        <form method="post">
            <div>
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" name="login">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php">Register here</a>.</p>
    </section>
    <script src="js/script.js"></script>
</body>
</html>