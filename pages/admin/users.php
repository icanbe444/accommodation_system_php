<?php
// This file is included in dashboard.php when menu=users and user is admin

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
                    $_SESSION['success_message'] = "User added successfully!";
                } else {
                    $_SESSION['error_message'] = "Error adding user. Email may already exist.";
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
                    // Update without changing password
                    $query = "UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("sssi", $name, $email, $role, $id);
                }
                
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "User updated successfully!";
                } else {
                    $_SESSION['error_message'] = "Error updating user.";
                }
            }
        } elseif ($_POST['action'] === 'delete_user') {
            $id = $_POST['id'];
            
            // Don't allow deleting yourself
            if ($id == $_SESSION['admin_id']) {
                $_SESSION['error_message'] = "You cannot delete your own account.";
            } else {
                $query = "DELETE FROM users WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "User deleted successfully!";
                } else {
                    $_SESSION['error_message'] = "Error deleting user.";
                }
            }
        }
        
        header("Location: " . BASE_URL . "?page=admin&menu=users");
        exit;
    }
}

// Get edit user data
$edit_user = null;
if ($action === 'edit' && $edit_id) {
    $query = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_user = $result->fetch_assoc();
}

// Get all users
$users_query = "SELECT * FROM users ORDER BY created_at DESC";
$users_result = $conn->query($users_query);
$users = $users_result->fetch_all(MYSQLI_ASSOC);
?>

<style>
    .users-container { display: flex; gap: 30px; }
    .users-form { flex: 1; }
    .users-list { flex: 1; }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #0099CC; }
    .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
    .form-group input:focus, .form-group select:focus { outline: none; border-color: #0099CC; }
    .form-buttons { display: flex; gap: 10px; }
    .form-buttons button { flex: 1; padding: 10px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
    .form-buttons .btn-submit { background-color: #0099CC; color: white; }
    .form-buttons .btn-cancel { background-color: #ddd; color: #333; }
    .users-table { width: 100%; border-collapse: collapse; }
    .users-table th { background-color: #0099CC; color: white; padding: 12px; text-align: left; }
    .users-table td { padding: 12px; border-bottom: 1px solid #ddd; }
    .users-table tr:hover { background-color: #f9f9f9; }
    .role-badge { padding: 4px 8px; border-radius: 3px; font-size: 12px; font-weight: bold; }
    .role-admin { background-color: #ff9800; color: white; }
    .role-staff { background-color: #4CAF50; color: white; }
</style>

<div class="users-container">
    <!-- ADD/EDIT USER FORM -->
    <div class="users-form">
        <h3 style="margin-bottom: 20px; color: #0099CC;"><?php echo $edit_user ? 'Edit User' : 'Add New User'; ?></h3>
        
        <form method="POST">
            <input type="hidden" name="action" value="<?php echo $edit_user ? 'update_user' : 'add_user'; ?>">
            <?php if ($edit_user): ?>
                <input type="hidden" name="id" value="<?php echo $edit_user['id']; ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="name" value="<?php echo $edit_user ? htmlspecialchars($edit_user['name']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" value="<?php echo $edit_user ? htmlspecialchars($edit_user['email']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label>Password <?php echo $edit_user ? '(leave blank to keep current)' : '*'; ?></label>
                <input type="password" name="password" <?php echo !$edit_user ? 'required' : ''; ?>>
            </div>
            
            <div class="form-group">
                <label>Role *</label>
                <select name="role" required>
                    <option value="staff" <?php echo (!$edit_user || $edit_user['role'] === 'staff') ? 'selected' : ''; ?>>Staff</option>
                    <option value="admin" <?php echo ($edit_user && $edit_user['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>
            
            <div class="form-buttons">
                <button type="submit" class="btn-submit"><?php echo $edit_user ? 'Update User' : 'Add User'; ?></button>
                <?php if ($edit_user): ?>
                    <a href="<?php echo BASE_URL; ?>?page=admin&menu=users" class="btn-cancel" style="text-align: center; text-decoration: none; padding: 10px;">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <!-- USERS LIST -->
    <div class="users-list">
        <h3 style="margin-bottom: 20px; color: #0099CC;">All Users (<?php echo count($users); ?>)</h3>
        
        <table class="users-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <span class="role-badge role-<?php echo $user['role']; ?>">
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
            </tbody>
        </table>
    </div>
</div>
