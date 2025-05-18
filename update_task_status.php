<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

if (!is_logged_in()) {
    http_response_code(403);
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id']) && isset($_POST['is_ongoing'])) {
    $task_id = filter_var($_POST['task_id'], FILTER_VALIDATE_INT);
    $is_ongoing = $_POST['is_ongoing'] == 'true' ? 1 : 0; // Modificare aici: primim 'true' sau 'false' ca string
    $user_id = get_logged_in_user_id();

    if ($task_id !== false) {
        $stmt = $conn->prepare("UPDATE tasks SET is_ongoing = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("iii", $is_ongoing, $task_id, $user_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'DB error: ' . $conn->error]);
        }
        $stmt->close();
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid task ID']);
    }

    $conn->close();
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
}
?>