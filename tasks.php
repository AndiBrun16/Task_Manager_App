<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!is_logged_in()) {
    redirect('index.php');
}

$user_id = get_logged_in_user_id();
$user = get_user_by_id($conn, $user_id);

$error = '';
$success = '';

// Afișează mesajul de succes din sesiune (dacă există)
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']); // Șterge mesajul din sesiune
}

// Adăugare task nou
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_task'])) {
    $task_description = trim($_POST['task_description']);
    if (!empty($task_description)) {
        $stmt = $conn->prepare("INSERT INTO tasks (user_id, task_description) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $task_description);
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Task added successfully.'; // Folosim o sesiune pentru mesajul de succes
        } else {
            $error = 'Failed to add task: ' . $conn->error;
        }
        $stmt->close();
    } else {
        $error = 'Task description cannot be empty.';
    }

    // Redirecționează după procesare
    if (empty($error)) {
        header("Location: tasks.php");
        exit();
    }
}

// Trecere task în/din "Done"
if (isset($_GET['toggle_done']) && isset($_GET['task_id']) && isset($_GET['board'])) {
    $task_id = filter_var($_GET['task_id'], FILTER_VALIDATE_INT);
    $board = $_GET['board'];
    $is_ongoing_value = ($board === 'ongoing') ? 1 : 0;

    if ($task_id !== false) {
        $stmt = $conn->prepare("UPDATE tasks SET is_done = NOT is_done, is_ongoing = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("iii", $is_ongoing_value, $task_id, $user_id);
        if (!$stmt->execute()) {
            $error = 'Failed to toggle task status: ' . $conn->error;
        }
        $stmt->close();
    } else {
        $error = 'Invalid task ID for toggling.';
    }
    redirect('tasks.php');
}

// Ștergere task
if (isset($_GET['remove']) && isset($_GET['task_id']) && isset($_GET['board'])) {
    $task_id = filter_var($_GET['task_id'], FILTER_VALIDATE_INT);

    if ($task_id !== false) {
        $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $task_id, $user_id);
        if (!$stmt->execute()) {
            $error = 'Failed to remove task: ' . $conn->error;
        }
        $stmt->close();
    } else {
        $error = 'Invalid task ID for removal.';
    }
    redirect('tasks.php');
}

// Obține toate taskurile userului
$tasks = [];
$stmt = $conn->prepare("SELECT id, task_description, is_done, is_ongoing FROM tasks WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $tasks[] = $row;
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tasks</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="header_user">

    <?php if ($user && $user['username']): ?>
        <h1>Hello, <?php echo htmlspecialchars($user['username']); ?>!</h1>
    <?php endif; ?>
   
    </div>

    <form class='add_task' method="post">
        <div>
            <label for="task_description">Add New Task:</label>
            <input class='add_description'type="text" id="task_description" name="task_description" required>
        </div>
        <button class='add_task_input'type="submit" name="add_task">Add Task</button>
    </form>

    <div class="task-board-container">
        <div class="task-board" id="my-tasks-board">
            <h3>My Tasks</h3>
            <?php echo $error ? display_error($error) : ''; ?>
            <?php echo $success ? display_success($success) : ''; ?>
            <ul class="task-list" id="my-tasks-list">
                <?php foreach ($tasks as $task): ?>
                    <?php if (!$task['is_ongoing']): ?>
                        <li class="task-item" draggable="true" data-task-id="<?php echo $task['id']; ?>">
                            <span style="<?php echo $task['is_done'] ? 'text-decoration: line-through; color: #888;' : ''; ?>">
                                <?php echo htmlspecialchars($task['task_description']); ?>
                            </span>
                            <div class="task-actions">
                                <button onclick="window.location.href='tasks.php?toggle_done=1&task_id=<?php echo $task['id']; ?>&board=my'">
                                    <?php echo $task['is_done'] ? 'Undone' : 'Done'; ?>
                                </button>
                                <button onclick="window.location.href='tasks.php?remove=1&task_id=<?php echo $task['id']; ?>&board=my'">
                                    Remove
                                </button>
                            </div>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="task-board" id="ongoing-tasks-board">
            <h3>Ongoing Tasks</h3>
            <ul class="task-list" id="ongoing-tasks-list">
                <?php foreach ($tasks as $task): ?>
                    <?php if ($task['is_ongoing']): ?>
                        <li class="task-item" draggable="true" data-task-id="<?php echo $task['id']; ?>">
                            <span style="<?php echo $task['is_done'] ? 'text-decoration: line-through; color: #888;' : ''; ?>">
                                <?php echo htmlspecialchars($task['task_description']); ?>
                            </span>
                            <div class="task-actions">
                                <button onclick="window.location.href='tasks.php?toggle_done=1&task_id=<?php echo $task['id']; ?>&board=ongoing'">
                                    <?php echo $task['is_done'] ? 'Undone' : 'Done'; ?>
                                </button>
                                <button onclick="window.location.href='tasks.php?remove=1&task_id=<?php echo $task['id']; ?>&board=ongoing'">
                                    Remove
                                </button>
                            </div>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class='logout'>                  
    <button onclick="window.location.href='logout.php'">Logout</button>
     </div>                       
<script src="js/tasks.js"></script>
</body>
</html>