<?php
require_once '../includes/admin_layout.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('error', 'Invalid security token. Please try again.');
        header('Location: announcements.php');
        exit;
    }

    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'create':
            $title = trim($_POST['title'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $announcement_date = $_POST['announcement_date'] ?? '';
            $is_featured = isset($_POST['is_featured']);

            if (empty($title) || empty($content) || empty($announcement_date)) {
                setFlashMessage('error', 'Please fill in all required fields.');
            } else {
                $result = createAnnouncement($title, $content, $announcement_date, $is_featured);
                setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
            }
            break;

        case 'update':
            $id = (int)($_POST['id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $announcement_date = $_POST['announcement_date'] ?? '';
            $is_featured = isset($_POST['is_featured']);
            $is_active = isset($_POST['is_active']);

            if (empty($title) || empty($content) || empty($announcement_date)) {
                setFlashMessage('error', 'Please fill in all required fields.');
            } else {
                $result = updateAnnouncement($id, $title, $content, $announcement_date, $is_featured, $is_active);
                setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
            }
            break;

        case 'delete':
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $result = deleteAnnouncement($id);
                setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
            }
            break;

        case 'bulk_delete':
            $ids = $_POST['ids'] ?? [];
            if (!empty($ids)) {
                $result = bulkDeleteAnnouncements($ids);
                setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
            } else {
                setFlashMessage('error', 'No announcements selected.');
            }
            break;

        case 'bulk_activate':
            $ids = $_POST['ids'] ?? [];
            if (!empty($ids)) {
                $result = bulkToggleAnnouncementStatus($ids, true);
                setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
            } else {
                setFlashMessage('error', 'No announcements selected.');
            }
            break;

        case 'bulk_deactivate':
            $ids = $_POST['ids'] ?? [];
            if (!empty($ids)) {
                $result = bulkToggleAnnouncementStatus($ids, false);
                setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
            } else {
                setFlashMessage('error', 'No announcements selected.');
            }
            break;
    }

    header('Location: announcements.php');
    exit;
}

// Handle single item actions via GET
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = (int)$_GET['id'];

    if ($action === 'delete' && $id > 0) {
        $result = deleteAnnouncement($id);
        setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
        header('Location: announcements.php');
        exit;
    }
}

// Pagination and filtering
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;
$search = trim($_GET['search'] ?? '');
$status_filter = $_GET['status'] ?? '';

// Get announcements with filtering
$announcements = getAnnouncementsAdmin();

// Apply filters
if (!empty($search) || !empty($status_filter)) {
    $announcements = array_filter($announcements, function ($announcement) use ($search, $status_filter) {
        $match_search = empty($search) ||
            stripos($announcement['title'], $search) !== false ||
            stripos($announcement['content'], $search) !== false;

        $match_status = empty($status_filter) ||
            ($status_filter === 'active' && $announcement['is_active']) ||
            ($status_filter === 'inactive' && !$announcement['is_active']) ||
            ($status_filter === 'featured' && $announcement['is_featured']);

        return $match_search && $match_status;
    });
}

// Pagination
$total_announcements = count($announcements);
$total_pages = ceil($total_announcements / $limit);
$announcements = array_slice($announcements, $offset, $limit);

// Get announcement for editing
$editing_announcement = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $editing_announcement = getAnnouncementById((int)$_GET['id']);
}

// Render the page
renderAdminLayout('Announcements Management', function () use ($announcements, $editing_announcement, $page, $total_pages, $search, $status_filter, $total_announcements, $offset, $limit) {
    $csrf_token = generateCSRFToken();
?>

    <!-- Header with Add Button -->
    <div style="display: flex; justify-content: between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h2 style="margin: 0; color: #333;">Announcements Management</h2>
            <p style="margin: 0.5rem 0 0 0; color: #6b7280;">Manage clinic announcements and notifications</p>
        </div>
        <a href="?action=create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Announcement
        </a>
    </div>

    <?php if (isset($_GET['action']) && ($_GET['action'] === 'create' || $_GET['action'] === 'edit')): ?>
        <!-- Create/Edit Form -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <h3 class="card-title">
                    <?php echo $editing_announcement ? 'Edit Announcement' : 'Create New Announcement'; ?>
                </h3>
                <a href="announcements.php" class="btn btn-secondary btn-sm">Cancel</a>
            </div>
            <form method="POST" style="padding: 1.5rem;">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="action" value="<?php echo $editing_announcement ? 'update' : 'create'; ?>">
                <?php if ($editing_announcement): ?>
                    <input type="hidden" name="id" value="<?php echo $editing_announcement['id']; ?>">
                <?php endif; ?>

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151; font-size: 0.9rem;">Title *</label>
                        <input type="text" name="title"
                            value="<?php echo htmlspecialchars($editing_announcement['title'] ?? ''); ?>"
                            required
                            style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151; font-size: 0.9rem;">Announcement Date *</label>
                        <input type="date" name="announcement_date"
                            value="<?php echo $editing_announcement['announcement_date'] ?? date('Y-m-d'); ?>"
                            required
                            style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem;">
                    </div>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151; font-size: 0.9rem;">Content *</label>
                    <textarea name="content" rows="6" required
                        placeholder="Enter announcement content..."
                        style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem; resize: vertical; font-family: inherit;"><?php echo htmlspecialchars($editing_announcement['content'] ?? ''); ?></textarea>
                </div>

                <div style="display: flex; gap: 2rem; margin-bottom: 1.5rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: 600; color: #374151; cursor: pointer;">
                        <input type="checkbox" name="is_featured"
                            <?php echo ($editing_announcement['is_featured'] ?? false) ? 'checked' : ''; ?>
                            style="width: 18px; height: 18px;">
                        Featured Announcement
                    </label>
                    <?php if ($editing_announcement): ?>
                        <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: 600; color: #374151; cursor: pointer;">
                            <input type="checkbox" name="is_active"
                                <?php echo ($editing_announcement['is_active'] ?? true) ? 'checked' : ''; ?>
                                style="width: 18px; height: 18px;">
                            Active
                        </label>
                    <?php endif; ?>
                </div>

                <div style="display: flex; gap: 1rem; justify-content: flex-end; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
                    <a href="announcements.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <?php echo $editing_announcement ? 'Update Announcement' : 'Create Announcement'; ?>
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <!-- Filters and Search -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <form method="GET" style="display: grid; grid-template-columns: 1fr 200px 150px auto; gap: 1rem; align-items: end;">
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">Search</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                    placeholder="Search title or content..."
                    style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">Status</label>
                <select name="status" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px;">
                    <option value="">All Status</option>
                    <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    <option value="featured" <?php echo $status_filter === 'featured' ? 'selected' : ''; ?>>Featured</option>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
            <div>
                <a href="announcements.php" class="btn btn-secondary">Clear</a>
            </div>
        </form>
    </div>

    <!-- Bulk Actions -->
    <div class="bulk-actions" style="display: none; margin-bottom: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 8px; border: 1px solid #e9ecef;">
        <form method="POST" style="display: flex; gap: 1rem; align-items: center;">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <span style="font-weight: 600; color: #333;">Bulk Actions:</span>
            <button type="submit" name="action" value="bulk_activate" class="btn btn-success btn-sm">
                Activate Selected
            </button>
            <button type="submit" name="action" value="bulk_deactivate" class="btn btn-secondary btn-sm">
                Deactivate Selected
            </button>
            <button type="submit" name="action" value="bulk_delete" class="btn btn-danger btn-sm"
                onclick="return confirmDelete('Are you sure you want to delete selected announcements?')">
                Delete Selected
            </button>
        </form>
    </div>

    <!-- Announcements Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Announcements (<?php echo number_format($total_announcements); ?>)</h3>
        </div>

        <?php if (empty($announcements)): ?>
            <div style="text-align: center; padding: 3rem; color: #6b7280;">
                <i class="fas fa-bullhorn" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                <h3 style="margin: 0 0 0.5rem 0; color: #9ca3af;">No announcements found</h3>
                <p style="margin: 0;">
                    <?php if (!empty($search) || !empty($status_filter)): ?>
                        Try adjusting your search criteria or <a href="announcements.php">clear filters</a>.
                    <?php else: ?>
                        <a href="?action=create" class="btn btn-primary">Create your first announcement</a>
                    <?php endif; ?>
                </p>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid #e5e7eb;">
                            <th style="padding: 1rem; text-align: left;">
                                <input type="checkbox" id="select-all" style="width: 18px; height: 18px;">
                            </th>
                            <th style="padding: 1rem; text-align: left; font-weight: 600; color: #374151;">Title</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 600; color: #374151;">Date</th>
                            <th style="padding: 1rem; text-align: center; font-weight: 600; color: #374151;">Status</th>
                            <th style="padding: 1rem; text-align: center; font-weight: 600; color: #374151;">Featured</th>
                            <th style="padding: 1rem; text-align: center; font-weight: 600; color: #374151;">Created</th>
                            <th style="padding: 1rem; text-align: center; font-weight: 600; color: #374151;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($announcements as $announcement): ?>
                            <tr style="border-bottom: 1px solid #f3f4f6;">
                                <td style="padding: 1rem;">
                                    <input type="checkbox" name="ids[]" value="<?php echo $announcement['id']; ?>"
                                        style="width: 18px; height: 18px;">
                                </td>
                                <td style="padding: 1rem;">
                                    <div>
                                        <div style="font-weight: 600; color: #333; margin-bottom: 0.25rem;">
                                            <?php echo htmlspecialchars($announcement['title']); ?>
                                        </div>
                                        <div style="font-size: 0.85rem; color: #6b7280; line-height: 1.4;">
                                            <?php echo htmlspecialchars(substr($announcement['content'], 0, 100)); ?>
                                            <?php if (strlen($announcement['content']) > 100): ?>...<?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding: 1rem; color: #6b7280;">
                                    <?php echo date('M j, Y', strtotime($announcement['announcement_date'])); ?>
                                </td>
                                <td style="padding: 1rem; text-align: center;">
                                    <?php if ($announcement['is_active']): ?>
                                        <span style="background: #d1fae5; color: #065f46; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">
                                            Active
                                        </span>
                                    <?php else: ?>
                                        <span style="background: #fee2e2; color: #991b1b; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">
                                            Inactive
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem; text-align: center;">
                                    <?php if ($announcement['is_featured']): ?>
                                        <i class="fas fa-star" style="color: #f59e0b;"></i>
                                    <?php else: ?>
                                        <i class="fas fa-star" style="color: #e5e7eb;"></i>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem; text-align: center; color: #6b7280; font-size: 0.85rem;">
                                    <?php echo date('M j, Y', strtotime($announcement['created_at'])); ?>
                                </td>
                                <td style="padding: 1rem; text-align: center;">
                                    <div style="display: flex; gap: 0.5rem; justify-content: center;">
                                        <a href="?action=edit&id=<?php echo $announcement['id']; ?>"
                                            class="btn btn-secondary btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" style="display: inline;"
                                            onsubmit="return confirmDelete('Are you sure you want to delete this announcement?')">
                                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $announcement['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div style="padding: 1rem; border-top: 1px solid #e5e7eb; display: flex; justify-content: between; align-items: center;">
                    <div style="color: #6b7280; font-size: 0.9rem;">
                        Showing <?php echo number_format($offset + 1); ?> to <?php echo number_format(min($offset + count($announcements), $total_announcements)); ?>
                        of <?php echo number_format($total_announcements); ?> announcements
                    </div>
                    <div style="display: flex; gap: 0.5rem;">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>"
                                class="btn btn-secondary btn-sm">Previous</a>
                        <?php endif; ?>

                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <?php if ($i == $page): ?>
                                <span class="btn btn-primary btn-sm"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>"
                                    class="btn btn-secondary btn-sm"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>"
                                class="btn btn-secondary btn-sm">Next</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script>
        // Add bulk action selection to the form
        function addSelectedToForm(form) {
            const checkboxes = document.querySelectorAll('input[type="checkbox"][name="ids[]"]:checked');
            checkboxes.forEach(checkbox => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = checkbox.value;
                form.appendChild(input);
            });
        }

        // Update bulk actions form submission
        document.querySelectorAll('.bulk-actions form').forEach(form => {
            form.addEventListener('submit', function(e) {
                addSelectedToForm(this);
            });
        });
    </script>
<?php
});
?>