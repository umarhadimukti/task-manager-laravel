/**
 * Task Management System - Dashboard Script
 * This script handles all dashboard functionality including:
 * - User authentication verification
 * - Task management (create, read, update, delete)
 * - User management (admin/manager features)
 * - Activity logs
 * - UI interactions
 */

// Check if user is logged in when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Verify user authentication
    if (!isAuthenticated()) {
        window.location.href = 'index.html';
        return;
    }

    // Initialize the dashboard
    initializeDashboard();
});

/**
 * Initialize the dashboard components and load data
 */
function initializeDashboard() {
    // Load current user info
    loadCurrentUserInfo();
    
    // Set up event listeners for navigation
    setupNavigationListeners();
    
    // Load tasks data
    loadTasks();
    
    // Initialize modals
    initializeModals();
}

/**
 * Load and display current user information
 */
function loadCurrentUserInfo() {
    const currentUser = getCurrentUser(); // From auth.js
    
    if (currentUser) {
        document.getElementById('userName').textContent = currentUser.name;
        document.getElementById('userRole').textContent = currentUser.role.charAt(0).toUpperCase() + currentUser.role.slice(1);
        
        // Show/hide sections based on user role
        configureUIForUserRole(currentUser.role);
    }
}

/**
 * Configure UI elements based on user role
 * @param {string} role - User role (admin, manager, staff)
 */
function configureUIForUserRole(role) {
    const adminSections = document.querySelectorAll('.admin-section');
    const managerSections = document.querySelectorAll('.manager-section');
    const staffSections = document.querySelectorAll('.staff-section');
    
    // Hide all role-specific sections by default
    adminSections.forEach(section => section.style.display = 'none');
    managerSections.forEach(section => section.style.display = 'none');
    staffSections.forEach(section => section.style.display = 'none');
    
    // Show sections based on role
    switch (role) {
        case 'admin':
            adminSections.forEach(section => section.style.display = 'block');
            managerSections.forEach(section => section.style.display = 'block');
            break;
        case 'manager':
            managerSections.forEach(section => section.style.display = 'block');
            break;
        case 'staff':
            staffSections.forEach(section => section.style.display = 'block');
            break;
    }
}

/**
 * Set up event listeners for navigation elements
 */
function setupNavigationListeners() {
    // Navigation menu listeners
    document.getElementById('tasksNav').addEventListener('click', (e) => {
        e.preventDefault();
        showView('tasksView');
    });
    
    document.getElementById('usersNav')?.addEventListener('click', (e) => {
        e.preventDefault();
        showView('usersView');
        loadUsers();
    });
    
    document.getElementById('logsNav')?.addEventListener('click', (e) => {
        e.preventDefault();
        showView('logsView');
        loadActivityLogs();
    });
    
    // Logout button
    document.getElementById('logoutBtn').addEventListener('click', (e) => {
        e.preventDefault();
        logout(); // From auth.js
        window.location.href = 'index.html';
    });
    
    // Create buttons
    document.getElementById('createTaskBtn').addEventListener('click', showCreateTaskModal);
    document.getElementById('createUserBtn')?.addEventListener('click', showCreateUserModal);
}

/**
 * Show the selected view and hide others
 * @param {string} viewId - ID of the view to show
 */
function showView(viewId) {
    // Hide all views
    document.getElementById('tasksView').style.display = 'none';
    document.getElementById('usersView').style.display = 'none';
    document.getElementById('logsView').style.display = 'none';
    
    // Show selected view
    document.getElementById(viewId).style.display = 'block';
    
    // Update active navigation
    document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
    
    // Set active navigation based on view
    switch (viewId) {
        case 'tasksView':
            document.getElementById('tasksNav').classList.add('active');
            break;
        case 'usersView':
            document.getElementById('usersNav').classList.add('active');
            break;
        case 'logsView':
            document.getElementById('logsNav').classList.add('active');
            break;
    }
}

/**
 * Initialize modal functionality
 */
function initializeModals() {
    // Task modal
    const taskModal = new bootstrap.Modal(document.getElementById('taskModal'));
    document.getElementById('saveTaskBtn').addEventListener('click', saveTask);
    
    // User modal (if accessible)
    const userModal = document.getElementById('userModal');
    if (userModal) {
        const userModalInstance = new bootstrap.Modal(userModal);
        document.getElementById('saveUserBtn').addEventListener('click', saveUser);
    }
    
    // Task detail modal
    const taskDetailModal = new bootstrap.Modal(document.getElementById('taskDetailModal'));
    document.getElementById('editTaskBtn').addEventListener('click', editTaskFromDetail);
    document.getElementById('deleteTaskBtn').addEventListener('click', deleteTaskFromDetail);
}

// ==========================================
// TASKS MANAGEMENT
// ==========================================

/**
 * Load and display all tasks
 */
function loadTasks() {
    // Clear task containers
    document.getElementById('pendingTasks').innerHTML = '';
    document.getElementById('inProgressTasks').innerHTML = '';
    document.getElementById('doneTasks').innerHTML = '';
    
    // Mock API call - replace with actual API call
    fetchTasks()
        .then(tasks => {
            // Filter tasks by status
            const pendingTasks = tasks.filter(task => task.status === 'pending');
            const inProgressTasks = tasks.filter(task => task.status === 'in_progress');
            const doneTasks = tasks.filter(task => task.status === 'done');
            
            // Update counters
            document.getElementById('pendingCount').textContent = pendingTasks.length;
            document.getElementById('inProgressCount').textContent = inProgressTasks.length;
            document.getElementById('doneCount').textContent = doneTasks.length;
            
            // Render tasks in each column
            renderTasks(pendingTasks, 'pendingTasks');
            renderTasks(inProgressTasks, 'inProgressTasks');
            renderTasks(doneTasks, 'doneTasks');
        })
        .catch(error => {
            console.error('Error loading tasks:', error);
            showNotification('Error loading tasks. Please try again later.', 'danger');
        });
}

/**
 * Render tasks in the specified container
 * @param {Array} tasks - Array of task objects
 * @param {string} containerId - ID of the container element
 */
function renderTasks(tasks, containerId) {
    const container = document.getElementById(containerId);
    
    // Clear container
    container.innerHTML = '';
    
    if (tasks.length === 0) {
        const emptyMessage = document.createElement('div');
        emptyMessage.className = 'text-center text-muted py-3';
        emptyMessage.innerHTML = '<i class="fas fa-info-circle"></i> No tasks available';
        container.appendChild(emptyMessage);
        return;
    }
    
    // Sort tasks by due date (sooner first)
    tasks.sort((a, b) => new Date(a.dueDate) - new Date(b.dueDate));
    
    // Create task cards
    tasks.forEach(task => {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const dueDate = new Date(task.dueDate);
        dueDate.setHours(0, 0, 0, 0);
        
        const dueSoon = dueDate <= new Date(today.getTime() + 2 * 24 * 60 * 60 * 1000) && dueDate >= today;
        const overdue = dueDate < today;
        
        const card = document.createElement('div');
        card.className = 'card task-card mb-3';
        card.setAttribute('data-task-id', task.id);
        
        let statusBadgeClass;
        switch (task.status) {
            case 'pending':
                statusBadgeClass = 'bg-warning';
                break;
            case 'in_progress':
                statusBadgeClass = 'bg-info';
                break;
            case 'done':
                statusBadgeClass = 'bg-success';
                break;
            default:
                statusBadgeClass = 'bg-secondary';
        }
        
        card.innerHTML = `
            <div class="card-body">
                <h5 class="card-title">${task.title}</h5>
                <p class="card-text text-truncate">${task.description}</p>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted">
                            ${overdue ? '<span class="due-date-warning"><i class="fas fa-exclamation-triangle"></i> Overdue</span>' : 
                              dueSoon ? '<span class="text-warning"><i class="fas fa-clock"></i> Due soon</span>' : 
                              '<span><i class="fas fa-calendar"></i> ' + formatDate(task.dueDate) + '</span>'}
                        </small>
                    </div>
                    <div>
                        <span class="badge ${statusBadgeClass}">${capitalizeFirstLetter(task.status.replace('_', ' '))}</span>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-muted">Assigned to: ${task.assignedTo ? task.assignedTo.name : 'Unassigned'}</small>
                </div>
            </div>
        `;
        
        // Add click event to show task details
        card.addEventListener('click', () => showTaskDetail(task.id));
        
        container.appendChild(card);
    });
}

/**
 * Show task creation modal
 */
function showCreateTaskModal() {
    // Reset form
    document.getElementById('taskForm').reset();
    document.getElementById('taskId').value = '';
    document.getElementById('taskModalLabel').textContent = 'Create New Task';
    
    // If user is manager or admin, load users for assignment
    const currentUser = getCurrentUser();
    if (currentUser.role === 'admin' || currentUser.role === 'manager') {
        loadUsersForAssignment();
    }
    
    // Set default due date to tomorrow
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    document.getElementById('taskDueDate').value = formatDateForInput(tomorrow);
    
    // Show modal
    const taskModal = new bootstrap.Modal(document.getElementById('taskModal'));
    taskModal.show();
}

/**
 * Load users for task assignment dropdown
 */
function loadUsersForAssignment() {
    const assignedToSelect = document.getElementById('assignedTo');
    assignedToSelect.innerHTML = '<option value="">Loading users...</option>';
    
    // Mock API call - replace with actual API call
    fetchUsers()
        .then(users => {
            // Filter active users
            const activeUsers = users.filter(user => user.status === 'active');
            
            // Clear and populate dropdown
            assignedToSelect.innerHTML = '<option value="">Select user...</option>';
            
            activeUsers.forEach(user => {
                const option = document.createElement('option');
                option.value = user.id;
                option.textContent = `${user.name} (${capitalizeFirstLetter(user.role)})`;
                assignedToSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error loading users for assignment:', error);
            assignedToSelect.innerHTML = '<option value="">Error loading users</option>';
        });
}

/**
 * Save task (create or update)
 */
function saveTask() {
    // Validate form
    const form = document.getElementById('taskForm');
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }
    
    const taskId = document.getElementById('taskId').value;
    const taskData = {
        title: document.getElementById('taskTitle').value,
        description: document.getElementById('taskDescription').value,
        status: document.getElementById('taskStatus').value,
        dueDate: document.getElementById('taskDueDate').value,
        assignedTo: document.getElementById('assignedTo')?.value || null
    };
    
    // Add current user as creator for new tasks
    if (!taskId) {
        taskData.createdBy = getCurrentUser().id;
    }
    
    // Mock API call - replace with actual API call
    const apiCall = taskId ? updateTask(taskId, taskData) : createTask(taskData);
    
    apiCall
        .then(() => {
            // Hide modal
            const taskModal = bootstrap.Modal.getInstance(document.getElementById('taskModal'));
            taskModal.hide();
            
            // Show success message
            showNotification(`Task successfully ${taskId ? 'updated' : 'created'}!`, 'success');
            
            // Reload tasks
            loadTasks();
            
            // Log activity
            logActivity(`Task ${taskId ? 'updated' : 'created'}: ${taskData.title}`);
        })
        .catch(error => {
            console.error(`Error ${taskId ? 'updating' : 'creating'} task:`, error);
            showNotification(`Error ${taskId ? 'updating' : 'creating'} task. Please try again.`, 'danger');
        });
}

/**
 * Show task detail modal
 * @param {string} taskId - Task ID
 */
function showTaskDetail(taskId) {
    // Mock API call - replace with actual API call
    getTask(taskId)
        .then(task => {
            // Populate modal with task details
            document.getElementById('detailTaskTitle').textContent = task.title;
            document.getElementById('detailTaskDescription').textContent = task.description;
            document.getElementById('detailTaskAssignee').textContent = task.assignedTo ? task.assignedTo.name : 'Unassigned';
            document.getElementById('detailTaskDueDate').textContent = formatDate(task.dueDate);
            document.getElementById('detailTaskCreator').textContent = task.createdBy ? task.createdBy.name : 'Unknown';
            
            // Set status badge
            const statusBadge = document.getElementById('detailTaskStatus');
            statusBadge.textContent = capitalizeFirstLetter(task.status.replace('_', ' '));
            
            switch (task.status) {
                case 'pending':
                    statusBadge.className = 'badge bg-warning';
                    break;
                case 'in_progress':
                    statusBadge.className = 'badge bg-info';
                    break;
                case 'done':
                    statusBadge.className = 'badge bg-success';
                    break;
                default:
                    statusBadge.className = 'badge bg-secondary';
            }
            
            // Store task ID for edit/delete operations
            document.getElementById('taskDetailModal').setAttribute('data-task-id', taskId);
            
            // Check permissions for edit/delete buttons
            const currentUser = getCurrentUser();
            const canEdit = currentUser.role === 'admin' || 
                           currentUser.role === 'manager' || 
                           (task.assignedTo && task.assignedTo.id === currentUser.id);
                           
            document.getElementById('editTaskBtn').style.display = canEdit ? 'block' : 'none';
            document.getElementById('deleteTaskBtn').style.display = (currentUser.role === 'admin' || currentUser.role === 'manager') ? 'block' : 'none';
            
            // Show modal
            const taskDetailModal = new bootstrap.Modal(document.getElementById('taskDetailModal'));
            taskDetailModal.show();
        })
        .catch(error => {
            console.error('Error loading task details:', error);
            showNotification('Error loading task details. Please try again later.', 'danger');
        });
}

/**
 * Open edit task modal from task detail view
 */
function editTaskFromDetail() {
    const taskId = document.getElementById('taskDetailModal').getAttribute('data-task-id');
    
    // Hide detail modal
    const taskDetailModal = bootstrap.Modal.getInstance(document.getElementById('taskDetailModal'));
    taskDetailModal.hide();
    
    // Mock API call - replace with actual API call
    getTask(taskId)
        .then(task => {
            // Populate form for editing
            document.getElementById('taskId').value = task.id;
            document.getElementById('taskTitle').value = task.title;
            document.getElementById('taskDescription').value = task.description;
            document.getElementById('taskStatus').value = task.status;
            document.getElementById('taskDueDate').value = formatDateForInput(task.dueDate);
            
            // Load users for assignment if needed
            const currentUser = getCurrentUser();
            if (currentUser.role === 'admin' || currentUser.role === 'manager') {
                loadUsersForAssignment().then(() => {
                    if (task.assignedTo) {
                        document.getElementById('assignedTo').value = task.assignedTo.id;
                    }
                });
            }
            
            // Update modal title
            document.getElementById('taskModalLabel').textContent = 'Edit Task';
            
            // Show modal
            const taskModal = new bootstrap.Modal(document.getElementById('taskModal'));
            taskModal.show();
        })
        .catch(error => {
            console.error('Error loading task for editing:', error);
            showNotification('Error loading task for editing. Please try again later.', 'danger');
        });
}

/**
 * Delete task from task detail view
 */
function deleteTaskFromDetail() {
    const taskId = document.getElementById('taskDetailModal').getAttribute('data-task-id');
    
    if (confirm('Are you sure you want to delete this task? This action cannot be undone.')) {
        // Mock API call - replace with actual API call
        deleteTask(taskId)
            .then(() => {
                // Hide detail modal
                const taskDetailModal = bootstrap.Modal.getInstance(document.getElementById('taskDetailModal'));
                taskDetailModal.hide();
                
                // Show success message
                showNotification('Task successfully deleted!', 'success');
                
                // Reload tasks
                loadTasks();
                
                // Log activity
                logActivity('Task deleted');
            })
            .catch(error => {
                console.error('Error deleting task:', error);
                showNotification('Error deleting task. Please try again later.', 'danger');
            });
    }
}

// ==========================================
// USERS MANAGEMENT
// ==========================================

/**
 * Load and display users
 */
function loadUsers() {
    const usersTableBody = document.getElementById('usersTableBody');
    usersTableBody.innerHTML = '<tr><td colspan="5" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading users...</td></tr>';
    
    // Mock API call - replace with actual API call
    fetchUsers()
        .then(users => {
            usersTableBody.innerHTML = '';
            
            if (users.length === 0) {
                usersTableBody.innerHTML = '<tr><td colspan="5" class="text-center">No users found</td></tr>';
                return;
            }
            
            users.forEach(user => {
                const row = document.createElement('tr');
                
                // Check if current user is admin for action buttons
                const currentUser = getCurrentUser();
                const isAdmin = currentUser.role === 'admin';
                
                row.innerHTML = `
                    <td>${user.name}</td>
                    <td>${user.email}</td>
                    <td>${capitalizeFirstLetter(user.role)}</td>
                    <td>
                        <span class="badge ${user.status === 'active' ? 'bg-success' : 'bg-danger'}">
                            ${capitalizeFirstLetter(user.status)}
                        </span>
                    </td>
                    <td class="admin-section" ${isAdmin ? '' : 'style="display:none"'}>
                        <button class="btn btn-sm btn-outline-primary edit-user-btn" data-user-id="${user.id}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger delete-user-btn" data-user-id="${user.id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                
                usersTableBody.appendChild(row);
            });
            
            // Add event listeners to edit/delete buttons
            document.querySelectorAll('.edit-user-btn').forEach(button => {
                button.addEventListener('click', () => editUser(button.getAttribute('data-user-id')));
            });
            
            document.querySelectorAll('.delete-user-btn').forEach(button => {
                button.addEventListener('click', () => deleteUser(button.getAttribute('data-user-id')));
            });
            
            // Set up search functionality
            setupUserSearch();
        })
        .catch(error => {
            console.error('Error loading users:', error);
            usersTableBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Error loading users. Please try again later.</td></tr>';
        });
}

/**
 * Set up user search functionality
 */
function setupUserSearch() {
    const searchInput = document.getElementById('userSearchInput');
    searchInput.addEventListener('input', () => {
        const searchTerm = searchInput.value.toLowerCase();
        const rows = document.querySelectorAll('#usersTableBody tr');
        
        rows.forEach(row => {
            const name = row.cells[0]?.textContent.toLowerCase() || '';
            const email = row.cells[1]?.textContent.toLowerCase() || '';
            const role = row.cells[2]?.textContent.toLowerCase() || '';
            
            if (name.includes(searchTerm) || email.includes(searchTerm) || role.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
}

/**
 * Show create user modal
 */
function showCreateUserModal() {
    // Reset form
    document.getElementById('userForm').reset();
    document.getElementById('userId').value = '';
    document.getElementById('userNameInput').value = '';
    document.getElementById('userEmail').value = '';
    document.getElementById('userPassword').value = '';
    document.getElementById('userRoleInput').value = 'staff';
    document.getElementById('userStatus').checked = true;
    
    // Update modal title
    document.getElementById('userModalLabel').textContent = 'Create New User';
    
    // Show modal
    const userModal = new bootstrap.Modal(document.getElementById('userModal'));
    userModal.show();
}

/**
 * Edit user
 * @param {string} userId - User ID
 */
function editUser(userId) {
    // Mock API call - replace with actual API call
    getUser(userId)
        .then(user => {
            // Populate form for editing
            document.getElementById('userId').value = user.id;
            document.getElementById('userNameInput').value = user.name;
            document.getElementById('userEmail').value = user.email;
            document.getElementById('userPassword').value = ''; // Don't populate password
            document.getElementById('userRoleInput').value = user.role;
            document.getElementById('userStatus').checked = user.status === 'active';
            
            // Update modal title
            document.getElementById('userModalLabel').textContent = 'Edit User';
            
            // Show modal
            const userModal = new bootstrap.Modal(document.getElementById('userModal'));
            userModal.show();
        })
        .catch(error => {
            console.error('Error loading user for editing:', error);
            showNotification('Error loading user for editing. Please try again later.', 'danger');
        });
}

/**
 * Save user (create or update)
 */
function saveUser() {
    // Validate form
    const form = document.getElementById('userForm');
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }
    
    const userId = document.getElementById('userId').value;
    const userData = {
        name: document.getElementById('userNameInput').value,
        email: document.getElementById('userEmail').value,
        role: document.getElementById('userRoleInput').value,
        status: document.getElementById('userStatus').checked ? 'active' : 'inactive'
    };
    
    // Add password for new users or if password field is not empty
    const password = document.getElementById('userPassword').value;
    if (password) {
        userData.password = password;
    }
    
    // Mock API call - replace with actual API call
    const apiCall = userId ? updateUser(userId, userData) : createUser(userData);
    
    apiCall
        .then(() => {
            // Hide modal
            const userModal = bootstrap.Modal.getInstance(document.getElementById('userModal'));
            userModal.hide();
            
            // Show success message
            showNotification(`User successfully ${userId ? 'updated' : 'created'}!`, 'success');
            
            // Reload users
            loadUsers();
            
            // Log activity
            logActivity(`User ${userId ? 'updated' : 'created'}: ${userData.name}`);
        })
        .catch(error => {
            console.error(`Error ${userId ? 'updating' : 'creating'} user:`, error);
            showNotification(`Error ${userId ? 'updating' : 'creating'} user. Please try again.`, 'danger');
        });
}

/**
 * Delete user
 * @param {string} userId - User ID
 */
function deleteUser(userId) {
    // Prevent accidental deletion
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        // Mock API call - replace with actual API call
        deleteUser(userId)
            .then(() => {
                // Show success message
                showNotification('User successfully deleted!', 'success');
                
                // Reload users
                loadUsers();
                
                // Log activity
                logActivity('User deleted');
            })
            .catch(error => {
                console.error('Error deleting user:', error);
                showNotification('Error deleting user. Please try again later.', 'danger');
            });
    }
}

// ==========================================
// ACTIVITY LOGS
// ==========================================

/**
 * Load and display activity logs
 */
function loadActivityLogs() {
    const logsTableBody = document.getElementById('logsTableBody');
    logsTableBody.innerHTML = '<tr><td colspan="4" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading logs...</td></tr>';
    
    // Get filter date if set
    const dateFilter = document.getElementById('logDateFilter').value;
    
    // Mock API call - replace with actual API call
    fetchActivityLogs(dateFilter)
        .then(logs => {
            logsTableBody.innerHTML = '';
            
            if (logs.length === 0) {
                logsTableBody.innerHTML = '<tr><td colspan="4" class="text-center">No activity logs found</td></tr>';
                return;
            }
            
            // Sort logs by timestamp (newest first)
            logs.sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp));
            
            logs.forEach(log => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${log.user ? log.user.name : 'System'}</td>
                    <td>${log.action}</td>
                    <td>${log.description}</td>
                    <td>${formatDateTime(log.timestamp)}</td>
                `;
                logsTableBody.appendChild(row);
            });
        })
        .catch(error => {
            console.error('Error loading activity logs:', error);
            logsTableBody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Error loading logs. Please try again later.</td></tr>';
        });
    
    // Set up filter reset button
    document.getElementById('resetLogFilter').addEventListener('click', () => {
        document.getElementById('logDateFilter').value = '';
        loadActivityLogs();
    });
}

/**
 * Log a new activity
 * @param {string} description - Activity description
 * @param {string} action - Action type (default: 'general')
 */
function logActivity(description, action = 'general') {
    // Get current user
    const currentUser = getCurrentUser();
    
    // Create log data
    const logData = {
        userId: currentUser.id,
        action: action,
        description: description,
        timestamp: new Date().toISOString()
    };
    
    // Mock API call - replace with actual API call
    createActivityLog(logData)
        .catch(error => {
            console.error('Error logging activity:', error);
        });
}
