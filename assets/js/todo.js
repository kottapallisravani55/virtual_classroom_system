document.addEventListener("DOMContentLoaded", function () {
    // Fetch the logged-in user's ID from PHP

    const taskInput = document.getElementById("new-task");
    const todoList = document.getElementById("todo-list");
    const maxTasks = 3;

    // Load saved tasks for the logged-in user from localStorage
    function loadTasks() {
        const tasks = JSON.parse(localStorage.getItem(`tasks_${userId}`)) || [];
        tasks.forEach((task) => addTaskToDOM(task.text, task.timestamp));
    }

    // Save tasks for the logged-in user to localStorage
    function saveTasks() {
        const tasks = [];
        document.querySelectorAll("#todo-list li").forEach((li) => {
            tasks.push({
                text: li.querySelector(".task-text").textContent,
                timestamp: li.querySelector(".timestamp").textContent,
            });
        });
        localStorage.setItem(`tasks_${userId}`, JSON.stringify(tasks));
    }

    // Add task to the DOM
    function addTaskToDOM(taskText, timestamp) {
        const listItem = document.createElement("li");
        listItem.className = "list-group-item d-flex justify-content-between align-items-center";

        // Task text and timestamp
        listItem.innerHTML = `
            <div>
                <span class="task-text">${taskText}</span>
                <small class="timestamp text-muted">(${timestamp})</small>
            </div>
            <button class="btn btn-danger btn-sm" onclick="removeTask(this)">Delete</button>
        `;

        todoList.appendChild(listItem);
    }

    // Add new task
    window.addTask = function () {
        const task = taskInput.value.trim();
        const tasksCount = document.querySelectorAll("#todo-list li").length;

        if (!task) {
            alert("Please enter a task.");
            return;
        }

        if (tasksCount >= maxTasks) {
            alert(`You can only add up to ${maxTasks} tasks per day.`);
            return;
        }

        const timestamp = new Date().toLocaleString(); // Current timestamp
        addTaskToDOM(task, timestamp);
        saveTasks(); // Save updated tasks to localStorage
        taskInput.value = ""; // Clear input
    };

    // Remove task
    window.removeTask = function (button) {
        button.parentElement.remove();
        saveTasks(); // Save updated tasks to localStorage
    };

    // Load tasks for the logged-in user on page load
    loadTasks();
});
