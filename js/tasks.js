const taskItems = document.querySelectorAll('.task-item');
const myTasksBoard = document.getElementById('my-tasks-board');
const myTasksList = document.getElementById('my-tasks-list');
const ongoingTasksBoard = document.getElementById('ongoing-tasks-board');
const ongoingTasksList = document.getElementById('ongoing-tasks-list');

let draggedItem = null;

taskItems.forEach(item => {
    item.addEventListener('dragstart', (e) => {
        draggedItem = e.target;
        e.target.classList.add('dragging');
    });

    item.addEventListener('dragend', () => {
        if (draggedItem) {
            draggedItem.classList.remove('dragging');
            draggedItem = null;
            myTasksBoard.classList.remove('dragover');
            ongoingTasksBoard.classList.remove('dragover');
        }
    });
});

myTasksBoard.addEventListener('dragover', (e) => {
    e.preventDefault();
    myTasksBoard.classList.add('dragover');
});

myTasksBoard.addEventListener('dragleave', () => {
    myTasksBoard.classList.remove('dragover');
});

myTasksBoard.addEventListener('drop', (e) => {
    if (draggedItem && draggedItem.parentNode !== myTasksList) {
        const taskId = draggedItem.dataset.taskId;
        myTasksList.appendChild(draggedItem);
        updateTaskStatus(taskId, false); // Mută în "My Tasks" (is_ongoing = 0)
        updateButtonLinks(draggedItem, 'my', taskId); // Actualizează link-urile butoanelor
    }
    myTasksBoard.classList.remove('dragover');
});

ongoingTasksBoard.addEventListener('dragover', (e) => {
    e.preventDefault();
    ongoingTasksBoard.classList.add('dragover');
});

ongoingTasksBoard.addEventListener('dragleave', () => {
    ongoingTasksBoard.classList.remove('dragover');
});

ongoingTasksBoard.addEventListener('drop', (e) => {
    if (draggedItem && draggedItem.parentNode !== ongoingTasksList) {
        const taskId = draggedItem.dataset.taskId;
        ongoingTasksList.appendChild(draggedItem);
        updateTaskStatus(taskId, true); // Mută în "Ongoing Tasks" (is_ongoing = 1)
        updateButtonLinks(draggedItem, 'ongoing', taskId); // Actualizează link-urile butoanelor
    }
    ongoingTasksBoard.classList.remove('dragover');
});

function updateTaskStatus(taskId, isOngoing) {
    fetch('update_task_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `task_id=${encodeURIComponent(taskId)}&is_ongoing=${isOngoing}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            console.error('Server error:', data.error);
        } else if (data.success) {
            console.log('Task status updated successfully');
        }
    })
    .catch(error => {
        console.error('Error updating task status:', error);
    });
}

function updateButtonLinks(taskItem, board, taskId) {
    const doneButton = taskItem.querySelector('button:nth-child(1)');
    const removeButton = taskItem.querySelector('button:nth-child(2)');

    doneButton.onclick = function() {
        window.location.href = `tasks.php?toggle_done=1&task_id=${taskId}&board=${board}`;
    };
    removeButton.onclick = function() {
        window.location.href = `tasks.php?remove=1&task_id=${taskId}&board=${board}`;
    };
}