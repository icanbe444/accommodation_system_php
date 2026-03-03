<?php
session_start();

// Set timezone to Nigeria (GMT+1)
date_default_timezone_set('Africa/Lagos');

if (!isset($_SESSION['admin_id'])) {
    header("Location: " . BASE_URL . "?page=login");
    exit;
}

// Check if user is admin
if ($_SESSION['admin_role'] !== 'admin') {
    $_SESSION['error_message'] = "You don't have permission to access this page.";
    header("Location: " . BASE_URL . "?page=admin");
    exit;
}

require_once(__DIR__ . '/../../includes/functions.php');

$action = $_GET['action'] ?? '';
$edit_id = $_GET['id'] ?? '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add_user') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $role = trim($_POST['role'] ?? 'staff');

            if (!$name || !$email || !$password) {
                $_SESSION['error_message'] = "Please fill in all required fields.";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                $query = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ssss", $name, $email, $hashed_password, $role);

                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "User created successfully!";
                    header("Location: " . BASE_URL . "?page=admin&menu=users");
                    exit;
                } else {
                    $_SESSION['error_message'] = "Error creating user. Email may already exist.";
                }
            }
        } elseif ($_POST['action'] === 'update_user') {
            $id = $_POST['id'];
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $role = trim($_POST['role'] ?? 'staff');
            $password = trim($_POST['password'] ?? '');

            if (!$name || !$email) {
                $_SESSION['error_message'] = "Please fill in all required fields.";
            } else {
                if ($password) {
                    // Update with new password
                    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                    $query = "UPDATE users SET name = ?, email = ?, password = ?, role = ? WHERE id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("ssssi", $name, $email, $hashed_password, $role, $id);
                } else {
                    // Update without password
                    $query = "UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("sssi", $name, $email, $role, $id);
                }

                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "User updated successfully!";
                    header("Location: " . BASE_URL . "?page=admin&menu=users");
                    exit;
                } else {
                    $_SESSION['error_message'] = "Error updating user.";
                }
            }
        } elseif ($_POST['action'] === 'delete_user') {
            $id = $_POST['id'];

            // Prevent deleting the last admin
            $admin_count_query = "SELECT COUNT(*) as count FROM users WHERE role = 'admin'";
            $admin_count_result = $conn->query($admin_count_query);
            $admin_count = $admin_count_result->fetch_assoc()['count'];

            if ($admin_count <= 1 && $id == $_SESSION['admin_id']) {
                $_SESSION['error_message'] = "Cannot delete the last admin user.";
            } else {
                $query = "DELETE FROM users WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $id);

                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "User deleted successfully!";
                    header("Location: " . BASE_URL . "?page=admin&menu=users");
                    exit;
                } else {
                    $_SESSION['error_message'] = "Error deleting user.";
                }
            }
        }
    }
}

// Get all users
$users_query = "SELECT * FROM users ORDER BY created_at DESC";
$users_result = $conn->query($users_query);
$users = $users_result->fetch_all(MYSQLI_ASSOC);

// Get user for editing
$edit_user = null;
if ($action === 'edit' && $edit_id) {
    $edit_query = "SELECT * FROM users WHERE id = ?";
    $edit_stmt = $conn->prepare($edit_query);
    $edit_stmt->bind_param("i", $edit_id);
    $edit_stmt->execute();
    $edit_result = $edit_stmt->get_result();
    $edit_user = $edit_result->fetch_assoc();
}
?>

<style>
    .users-container {
        max-width: 1000px;
        margin: 0 auto;
    }

    .users-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .users-header h2 {
        margin: 0;
        color: #333;
        font-size: 1.75rem;
        font-weight: 700;
        padding-bottom: 1rem;
        border-bottom: 2px solid #0099CC;
        flex: 1;
    }
</style>

<div class="users-container">
    <div class="users-header">
        <h2>User Management</h2>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-error"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

    <!-- ADD/EDIT USER FORM -->
    <?php if ($action === 'add' || ($action === 'edit' && $edit_user)): ?>
        <div class="card" style="margin-bottom: 2rem; background-color: #f8f9fa;">
            <h3><?php echo ($action === 'add') ? 'Add New User' : 'Edit User'; ?></h3>
            <form method="POST">
                <input type="hidden" name="action" value="<?php echo ($action === 'add') ? 'add_user' : 'update_user'; ?>">
                <?php if ($action === 'edit'): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_user['id']; ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" id="name" name="name" value="<?php echo ($edit_user ? htmlspecialchars($edit_user['name']) : ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" value="<?php echo ($edit_user ? htmlspecialchars($edit_user['email']) : ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="password">Password <?php echo ($action === 'add') ? '*' : '(leave blank to keep current)'; ?></label>
                    <input type="password" id="password" name="password" <?php echo ($action === 'add') ? 'required' : ''; ?>>
                </div>

                <div class="form-group">
                    <label for="role">Role *</label>
                    <select id="role" name="role" required>
                        <option value="staff" <?php echo ($edit_user && $edit_user['role'] === 'staff') ? 'selected' : ''; ?>>Staff</option>
                        <option value="admin" <?php echo ($edit_user && $edit_user['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>

                <button type="submit" class="btn"><?php echo ($action === 'add') ? 'Create User' : 'Update User'; ?></button>
                <a href="<?php echo BASE_URL; ?>?page=admin&menu=users" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    <?php endif; ?>

    <!-- USERS TABLE -->
    <div class="card">
        <a href="<?php echo BASE_URL; ?>?page=admin&menu=users&action=add" class="btn" style="margin-bottom: 1rem;">Add New User</a>

        <div class="table-wrapper">
            <table>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <span style="padding: 0.25rem 0.75rem; border-radius: 4px; font-weight: 500;
                                background-color: <?php echo ($user['role'] === 'admin') ? '#d4edda' : '#d1ecf1'; ?>;
                                color: <?php echo ($user['role'] === 'admin') ? '#155724' : '#0c5460'; ?>;">
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </td>
                        <td><?php echo date('Y-m-d H:i', strtotime($user['created_at'])); ?></td>
                        <td>
                            <a href="<?php echo BASE_URL; ?>?page=admin&menu=users&action=edit&id=<?php echo $user['id']; ?>" class="btn" style="padding: 0.5rem 0.75rem; font-size: 0.9rem;">Edit</a>
                            <?php if ($user['id'] !== $_SESSION['admin_id']): ?>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this user?');">
                                    <input type="hidden" name="action" value="delete_user">
                                    <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" class="btn btn-danger" style="padding: 0.5rem 0.75rem; font-size: 0.9rem;">Delete</button>
                                </form>
                            <?php else: ?>
                                <span class="btn btn-secondary" style="padding: 0.5rem 0.75rem; font-size: 0.9rem; opacity: 0.5; cursor: not-allowed;">Delete</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</div>
