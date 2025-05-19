<?php
session_start();

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function get_logged_in_user_id() {
    return $_SESSION['user_id'] ?? null;
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

function display_error($message) {
    echo '<div class="error">' . htmlspecialchars($message) . '</div>';
}

function display_success($message) {
    if (!empty($message)) {
        echo '<div class="success">' . htmlspecialchars($message) . '</div>';
    }
}

function get_user_by_id($conn, $user_id) {
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    return $user;
}
?>