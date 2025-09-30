<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Project Manager</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Project Manager Dashboard</h1>
            <div class="user-info">
                Welcome, <?php echo $_SESSION['username']; ?>!
                <button onclick="logout()" class="btn btn-secondary">Logout</button>
            </div>
        </header>

        <div class="dashboard-content">
            <!-- Project Management Section -->
            <section class="project-section">
                <h2>Projects</h2>
                <div class="form-section">
                    <h3>Add New Project</h3>
                    <form id="projectForm">
                        <div class="form-group">
                            <label for="projectTitle">Project Title:</label>
                            <input type="text" id="projectTitle" name="title" required>
                        </div>
                        <div class="form-group">
                            <label for="projectDescription">Description:</label>
                            <textarea name="description" id="projectDescription"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Project</button>
                    </form>
                </div>

                <div class="table-section">
                    <h3>Your Projects</h3>
                    <table id="projectsTable">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="projectsTableBody">
                            <!-- Projects will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Task Management Section -->
            <section class="task-section">
                <h2>Tasks</h2>
                <div class="form-section">
                    <h3>Add New Task</h3>
                    <form id="taskForm">
                        <div class="form-group">
                            <label for="taskProject">Project:</label>
                            <select name="project_id" id="taskProject" required>
                                <!-- Projects will be loader here -->
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="taskTitle">Task Title:</label>
                            <input type="text" id="taskTitle" name="title" required>
                        </div>
                        <div class="form-group">
                            <label for="taskDescription">Description:</label>
                            <textarea name="description" id="taskDescription"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="taskPriority">Priority:</label>
                            <select name="priority" id="taskPriority">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="dueDate">Due Date:</label>
                            <input type="date" id="dueDate" name="due_date" min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <button type="submit" class="btn btn-primary">Add Task</button>
                    </form>
                </div>

                <div class="table-section">
                    <h3>Your Tasks</h3>
                    <table id="tasksTable">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Project</th>
                                <th>Priority</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tasksTableBody">
                            <!-- Tasks will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>

    <script src="../../scripts/dashboard.js"></script>
</body>
</html>