<?php
require_once '../includes/admin_layout.php';

// Helper: Get current admin ID
function getCurrentAdminId()
{
    $admin = getCurrentAdmin();
    return $admin['id'] ?? null;
}

// Helper: Log admin action
function logAdminAction($action, $target_id, $changes)
{
    global $pdo;
    $admin_id = getCurrentAdminId();
    $sql = "INSERT INTO admin_action_audit_log (admin_id, action, target_table, target_id, changes) VALUES (?, ?, 'admin_users', ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$admin_id, $action, $target_id, json_encode($changes)]);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('error', 'Invalid security token. Please try again.');
        header('Location: users.php');
        exit;
    }
    $action = $_POST['action'] ?? '';
    $current_admin_id = getCurrentAdminId();

    switch ($action) {
        case 'create':
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $full_name = trim($_POST['full_name'] ?? '');
            $password = $_POST['password'] ?? '';
            $is_active = isset($_POST['is_active']);
            if (empty($username) || empty($email) || empty($full_name) || empty($password)) {
                setFlashMessage('error', 'All fields including password are required.');
            } else {
                $result = createAdminUser($username, $email, $password, $full_name);
                if ($result['success']) {
                    // Set active status if needed
                    if (!$is_active) {
                        updateAdminUser($result['id'], $username, $email, $full_name, false);
                    }
                    logAdminAction('create', $result['id'], ['username' => $username, 'email' => $email, 'full_name' => $full_name, 'is_active' => $is_active]);
                }
                setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
            }
            break;
        case 'update':
            $id = (int)($_POST['id'] ?? 0);
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $full_name = trim($_POST['full_name'] ?? '');
            $is_active = isset($_POST['is_active']);
            $password = $_POST['password'] ?? '';
            if (empty($username) || empty($email) || empty($full_name)) {
                setFlashMessage('error', 'All fields except password are required.');
            } else {
                $before = getAdminUserById($id);
                $result = updateAdminUser($id, $username, $email, $full_name, $is_active);
                if ($result['success'] && !empty($password)) {
                    updateAdminPassword($id, $password);
                }
                $after = getAdminUserById($id);
                logAdminAction('edit', $id, ['before' => $before, 'after' => $after]);
                setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
            }
            break;
        case 'delete':
            $id = (int)($_POST['id'] ?? 0);
            if ($id == $current_admin_id) {
                setFlashMessage('error', 'You cannot delete your own account.');
            } else {
                $before = getAdminUserById($id);
                $result = deleteAdminUser($id);
                if ($result['success']) {
                    logAdminAction('delete', $id, ['before' => $before]);
                }
                setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
            }
            break;
    }
    header('Location: users.php');
    exit;
}

// Pagination and filtering
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;
$search = trim($_GET['search'] ?? '');
$status_filter = $_GET['status'] ?? '';

// Get users
$all_users = getAdminUsers();
if (!empty($search) || !empty($status_filter)) {
    $all_users = array_filter($all_users, function ($user) use ($search, $status_filter) {
        $match_search = empty($search) ||
            stripos($user['username'], $search) !== false ||
            stripos($user['email'], $search) !== false ||
            stripos($user['full_name'], $search) !== false;
        $match_status = empty($status_filter) ||
            ($status_filter === 'active' && $user['is_active']) ||
            ($status_filter === 'inactive' && !$user['is_active']);
        return $match_search && $match_status;
    });
}
$total_users = count($all_users);
$total_pages = ceil($total_users / $limit);
$users = array_slice($all_users, $offset, $limit);

// Get user for editing
$editing_user = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $editing_user = getAdminUserById((int)$_GET['id']);
}

// Get audit log for a user
function getUserAuditLog($user_id)
{
    global $pdo;
    $sql = "SELECT l.*, a.username as admin_username FROM admin_action_audit_log l LEFT JOIN admin_users a ON l.admin_id = a.id WHERE l.target_table = 'admin_users' AND l.target_id = ? ORDER BY l.action_time DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

// Render the page
renderAdminLayout('Admin Users Management', function () use ($users, $editing_user, $page, $total_pages, $search, $status_filter, $total_users, $offset, $limit) {
    $csrf_token = generateCSRFToken();
    $current_admin_id = getCurrentAdminId();
?>
    <!-- Header with Add Button -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h2 style="margin: 0; color: #333;">Admin Users Management</h2>
            <p style="margin: 0.5rem 0 0 0; color: #6b7280;">Manage admin accounts and permissions</p>
        </div>
        <a href="?action=create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Admin User
        </a>
    </div>

    <?php if (isset($_GET['action']) && ($_GET['action'] === 'create' || $_GET['action'] === 'edit')): ?>
        <!-- Create/Edit Form -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <h3 class="card-title">
                    <?php echo $editing_user ? 'Edit Admin User' : 'Create New Admin User'; ?>
                </h3>
                <a href="users.php" class="btn btn-secondary btn-sm">Cancel</a>
            </div>
            <form method="POST" style="padding: 1.5rem;">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="action" value="<?php echo $editing_user ? 'update' : 'create'; ?>">
                <?php if ($editing_user): ?>
                    <input type="hidden" name="id" value="<?php echo $editing_user['id']; ?>">
                <?php endif; ?>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151; font-size: 0.9rem;">Username *</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($editing_user['username'] ?? ''); ?>" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151; font-size: 0.9rem;">Email *</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($editing_user['email'] ?? ''); ?>" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem;">
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151; font-size: 0.9rem;">Full Name *</label>
                        <input type="text" name="full_name" value="<?php echo htmlspecialchars($editing_user['full_name'] ?? ''); ?>" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151; font-size: 0.9rem;">Password <?php echo $editing_user ? '(leave blank to keep unchanged)' : '*'; ?></label>
                        <input type="password" name="password" <?php echo $editing_user ? '' : 'required'; ?> placeholder="<?php echo $editing_user ? 'Leave blank to keep current password' : 'Enter password'; ?>" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem;">
                    </div>
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: 600; color: #374151; cursor: pointer;">
                        <input type="checkbox" name="is_active" <?php echo ($editing_user['is_active'] ?? true) ? 'checked' : ''; ?> style="width: 18px; height: 18px;">
                        Active
                    </label>
                </div>
                <div style="display: flex; gap: 1rem; justify-content: flex-end; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
                    <a href="users.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary"><?php echo $editing_user ? 'Update User' : 'Create User'; ?></button>
                </div>
            </form>
        </div>
        <?php if ($editing_user): ?>
            <!-- Audit Log for User -->
            <div class="card" style="margin-bottom: 2rem;">
                <div class="card-header">
                    <h3 class="card-title">Audit Log for <?php echo htmlspecialchars($editing_user['username']); ?></h3>
                </div>
                <div style="max-height: 300px; overflow-y: auto;">
                    <?php $logs = getUserAuditLog($editing_user['id']); ?>
                    <?php if (empty($logs)): ?>
                        <div style="color: #6b7280; padding: 1rem;">No audit log entries for this user.</div>
                    <?php else: ?>
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="border-bottom: 2px solid #e5e7eb;">
                                    <th style="padding: 0.5rem; text-align: left;">Action</th>
                                    <th style="padding: 0.5rem; text-align: left;">By</th>
                                    <th style="padding: 0.5rem; text-align: left;">When</th>
                                    <th style="padding: 0.5rem; text-align: left;">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                    <tr style="border-bottom: 1px solid #f3f4f6;">
                                        <td style="padding: 0.5rem;"><?php echo ucfirst($log['action']); ?></td>
                                        <td style="padding: 0.5rem;"><?php echo htmlspecialchars($log['admin_username'] ?? 'Unknown'); ?></td>
                                        <td style="padding: 0.5rem;"><?php echo date('M j, Y H:i', strtotime($log['action_time'])); ?></td>
                                        <td style="padding: 0.5rem; font-size: 0.9em;">
                                            <pre style="white-space: pre-wrap; word-break: break-all; background: #f8fafc; padding: 0.5rem; border-radius: 4px; margin: 0;"><?php echo htmlspecialchars($log['changes']); ?></pre>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Filters and Search -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <form method="GET" style="display: grid; grid-template-columns: 1fr 150px auto auto; gap: 1rem; align-items: end;">
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">Search</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search username, email, or name..." style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">Status</label>
                <select name="status" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px;">
                    <option value="">All Status</option>
                    <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
            <div>
                <a href="users.php" class="btn btn-secondary">Clear</a>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Admin Users (<?php echo number_format($total_users); ?>)</h3>
        </div>
        <?php if (empty($users)): ?>
            <div style="text-align: center; padding: 3rem; color: #6b7280;">
                <i class="fas fa-users" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                <h3 style="margin: 0 0 0.5rem 0; color: #9ca3af;">No admin users found</h3>
                <p style="margin: 0;">
                    <a href="?action=create" class="btn btn-primary">Create your first admin user</a>
                </p>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid #e5e7eb;">
                            <th style="padding: 1rem; text-align: left; font-weight: 600; color: #374151;">Username</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 600; color: #374151;">Email</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 600; color: #374151;">Full Name</th>
                            <th style="padding: 1rem; text-align: center; font-weight: 600; color: #374151;">Status</th>
                            <th style="padding: 1rem; text-align: center; font-weight: 600; color: #374151;">Last Login</th>
                            <th style="padding: 1rem; text-align: center; font-weight: 600; color: #374151;">Created</th>
                            <th style="padding: 1rem; text-align: center; font-weight: 600; color: #374151;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr style="border-bottom: 1px solid #f3f4f6;">
                                <td style="padding: 1rem; font-weight: 600; color: #333;">
                                    <?php echo htmlspecialchars($user['username']); ?>
                                </td>
                                <td style="padding: 1rem; color: #6b7280;">
                                    <?php echo htmlspecialchars($user['email']); ?>
                                </td>
                                <td style="padding: 1rem; color: #6b7280;">
                                    <?php echo htmlspecialchars($user['full_name']); ?>
                                </td>
                                <td style="padding: 1rem; text-align: center;">
                                    <?php if ($user['is_active']): ?>
                                        <span style="background: #d1fae5; color: #065f46; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">Active</span>
                                    <?php else: ?>
                                        <span style="background: #fee2e2; color: #991b1b; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem; text-align: center; color: #6b7280; font-size: 0.85rem;">
                                    <?php echo $user['last_login'] ? date('M j, Y H:i', strtotime($user['last_login'])) : '-'; ?>
                                </td>
                                <td style="padding: 1rem; text-align: center; color: #6b7280; font-size: 0.85rem;">
                                    <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                                </td>
                                <td style="padding: 1rem; text-align: center;">
                                    <div style="display: flex; gap: 0.5rem; justify-content: center;">
                                        <a href="?action=edit&id=<?php echo $user['id']; ?>" class="btn btn-secondary btn-sm" title="Edit"><i class="fas fa-edit"></i></a>
                                        <?php if ($user['id'] != $current_admin_id): ?>
                                            <form method="POST" style="display: inline;" onsubmit="return confirmDelete('Are you sure you want to delete this admin user?')">
                                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm" title="Delete"><i class="fas fa-trash"></i></button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div style="padding: 1rem; border-top: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
                    <div style="color: #6b7280; font-size: 0.9rem;">
                        Showing <?php echo number_format($offset + 1); ?> to <?php echo number_format(min($offset + count($users), $total_users)); ?> of <?php echo number_format($total_users); ?> admin users
                    </div>
                    <div style="display: flex; gap: 0.5rem;">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>" class="btn btn-secondary btn-sm">Previous</a>
                        <?php endif; ?>
                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <?php if ($i == $page): ?>
                                <span class="btn btn-primary btn-sm"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>" class="btn btn-secondary btn-sm"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>" class="btn btn-secondary btn-sm">Next</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
<?php
});
?>