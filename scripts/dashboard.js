// Load projects and tasks when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadProjects();
    loadTasks();
});

// Project management
async function loadProjects() {
    try {
        const response = await fetch('/project-management/api/projects.php');
        const data = await response.json();

        if (data.success) {
            displayProjects(data.projects);
            populateProjectDropdown(data.projects);
        }
    } catch (error) {
        console.error('Error loading projects:', error);
    }
}

function displayProjects(projects) {
    const tbody = document.getElementById('projectsTableBody');
    tbody.innerHTML = '';

    projects.forEach(project => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${project.title}</td>
            <td>${project.description || "-"}</td>
            <td>
                <select onchange="updateProjectStatus(${project.id}, this.value)">
                    <option value="pending" ${project.status === 'pending' ? 'selected' : ''}>Pending</option>
                    <option value="in progress" ${project.status === 'in progress' ? 'selected' : ''}>In Progress</option>
                    <option value="completed" ${project.status === 'completed' ? 'selected' : ''}>Completed</option>
                </select>
            </td>
            <td>
                <button onclick="deleteProject(${project.id})" class="btn btn-danger">Delete</button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function populateProjectDropdown(projects) {
    const select = document.getElementById('taskProject');
    select.innerHTML = '<option value="">Select a project</option>';

    projects.forEach(project => {
        const option = document.createElement('option');
        option.value = project.id;
        option.textContent = project.title;
        select.appendChild(option);
    });
}

// Task management
async function loadTasks() {
    try {
        const response = await fetch('/project-management/api/tasks.php');
        const data = await response.json();

        if (data.success) {
            displayTasks(data.tasks);
        }
    } catch (error) {
        console.error('Error loading tasks:', error);
    }
}

function displayTasks(tasks) {
    const tbody = document.getElementById('tasksTableBody');
    tbody.innerHTML = '';

    tasks.forEach(task => {
        // FIXED: due_date (not due_data)
        let dueDateDisplay = '-';
        if (task.due_date) {
            try {
                const dueDate = new Date(task.due_date + 'T00:00:00');
                if (!isNaN(dueDate.getTime())) {
                    dueDateDisplay = dueDate.toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric'
                    });
                }
            } catch (e) {
                dueDateDisplay = task.due_date; // Fallback to raw value
            }
        }

        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${task.title}</td>
            <td>${task.project_title}</td>
            <td><span class="priority ${task.priority}">${task.priority}</span></td>
            <td>${dueDateDisplay}</td>
            <td>
                <select onchange="updateTaskStatus(${task.id}, this.value)">
                    <option value="pending" ${task.status === 'pending' ? 'selected' : ''}>Pending</option>
                    <option value="in progress" ${task.status === 'in progress' ? 'selected' : ''}>In Progress</option>
                    <option value="completed" ${task.status === 'completed' ? 'selected' : ''}>Completed</option>
                </select>
            </td>
            <td>
                <button onclick="deleteTask(${task.id})" class="btn btn-danger">Delete</button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Form handlers
document.getElementById('projectForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = {
        title: document.getElementById('projectTitle').value,
        description: document.getElementById('projectDescription').value
    };

    try {
        const response = await fetch('/project-management/api/projects.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        });

        const data = await response.json();

        if (data.success) {
            alert('Project created successfully!');
            document.getElementById('projectForm').reset();
            loadProjects();
            loadTasks();
        } else {
            // FIXED: message (not messsage)
            alert('Error creating project: ' + data.message);
        }
    } catch(error) {
        console.error('Error:', error);
        alert('Error creating project');
    }
});

document.getElementById('taskForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    // FIXED: due_date (not due_data)
    const formData = {
        project_id: document.getElementById('taskProject').value,
        title: document.getElementById('taskTitle').value,
        description: document.getElementById('taskDescription').value,
        priority: document.getElementById('taskPriority').value,
        due_date: document.getElementById('dueDate').value
    };

    try {
        const response = await fetch('/project-management/api/tasks.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();

        if (data.success) {
            alert('Task created successfully!');
            document.getElementById('taskForm').reset();
            loadTasks();
        } else {
            alert('Error creating task: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error creating task');
    }
});

// Update functions
async function updateProjectStatus(projectId, status) {
    try {
        const response = await fetch('/project-management/api/projects.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: projectId, status: status })
        });

        const data = await response.json();
        if (data.success) {
            loadProjects();
        }
    } catch (error) {
        console.error('Error updating project:', error);
    }
}

// FIXED: updateTaskStatus (not UpdateTaskStatus - case sensitive)
async function updateTaskStatus(taskId, status) {
    try {
        const response = await fetch('/project-management/api/tasks.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: taskId, status: status })
        });

        const data = await response.json();
        if (data.success) {
            loadTasks();
        }
    } catch (error) {
        console.error('Error updating task:', error);
    }
}

// Delete functions
async function deleteProject(projectId) {
    // FIXED: associated (not aassociated)
    if (confirm('Are you sure you want to delete this project? All associated tasks will also be deleted.')) {
        try {
            const response = await fetch('/project-management/api/projects.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: projectId })
            });

            const data = await response.json();
            if (data.success) {
                loadProjects();
                loadTasks();
            }
        } catch (error) {
            console.error('Error deleting project:', error);
        }
    }
}

// FIXED: Add missing deleteTask function
async function deleteTask(taskId) {
    if (confirm('Are you sure you want to delete this task?')) {
        try {
            const response = await fetch('/project-management/api/tasks.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: taskId })
            });

            const data = await response.json();
            if (data.success) {
                loadTasks();
            }
        } catch (error) {
            console.error('Error deleting task:', error);
        }
    }
}

// Include the logout function from script.js
function logout() {
    const formData = new FormData();
    formData.append('action', 'logout');

    fetch('/project-management/api/auth.php', {
        method: 'POST',
        body: formData
    }).then(response => response.json()).then(data => {
        if (data.success) {
            window.location.href = '/project-management/pages/html/login.html';
        }
    });
}