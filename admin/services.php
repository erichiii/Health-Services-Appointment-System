<?php
require_once '../includes/admin_layout.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('error', 'Invalid security token. Please try again.');
        header('Location: services.php');
        exit;
    }

    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'create':
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $duration = (int)($_POST['duration'] ?? 30);
            $category = $_POST['category'] ?? 'appointment';

            if (empty($name) || empty($description)) {
                setFlashMessage('error', 'Please fill in all required fields.');
            } else {
                $result = createService($name, $description, $duration, $category);
                setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
            }
            break;

        case 'update':
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $duration = (int)($_POST['duration'] ?? 30);
            $category = $_POST['category'] ?? 'appointment';
            $is_active = isset($_POST['is_active']);

            if (empty($name) || empty($description)) {
                setFlashMessage('error', 'Please fill in all required fields.');
            } else {
                $result = updateService($id, $name, $description, $duration, $category, $is_active);
                setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
            }
            break;

        case 'delete':
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $result = deleteService($id);
                setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
            }
            break;

        case 'bulk_delete':
            $ids = $_POST['ids'] ?? [];
            if (!empty($ids)) {
                $result = bulkDeleteServices($ids);
                setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
            } else {
                setFlashMessage('error', 'No services selected.');
            }
            break;

        case 'bulk_activate':
            $ids = $_POST['ids'] ?? [];
            if (!empty($ids)) {
                $result = bulkToggleServiceStatus($ids, true);
                setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
            } else {
                setFlashMessage('error', 'No services selected.');
            }
            break;

        case 'bulk_deactivate':
            $ids = $_POST['ids'] ?? [];
            if (!empty($ids)) {
                $result = bulkToggleServiceStatus($ids, false);
                setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
            } else {
                setFlashMessage('error', 'No services selected.');
            }
            break;
    }

    header('Location: services.php');
    exit;
}

// Handle single item actions via GET
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = (int)$_GET['id'];

    if ($action === 'delete' && $id > 0) {
        $result = deleteService($id);
        setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
        header('Location: services.php');
        exit;
    }
}

// Pagination and filtering
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;
$search = trim($_GET['search'] ?? '');
$category_filter = $_GET['category'] ?? '';
$status_filter = $_GET['status'] ?? '';

// Get services
$services = getServicesAdmin();

// Apply filters
if (!empty($search) || !empty($category_filter) || !empty($status_filter)) {
    $services = array_filter($services, function ($service) use ($search, $category_filter, $status_filter) {
        $match_search = empty($search) ||
            stripos($service['name'], $search) !== false ||
            stripos($service['description'], $search) !== false;

        $match_category = empty($category_filter) || $service['category'] === $category_filter;

        $match_status = empty($status_filter) ||
            ($status_filter === 'active' && $service['is_active']) ||
            ($status_filter === 'inactive' && !$service['is_active']);

        return $match_search && $match_category && $match_status;
    });
}

// Pagination
$total_services = count($services);
$total_pages = ceil($total_services / $limit);
$services = array_slice($services, $offset, $limit);

// Get service for editing
$editing_service = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $editing_service = getServiceById((int)$_GET['id']);
}

// Render the page
renderAdminLayout('Services Management', function () use ($services, $editing_service, $page, $total_pages, $search, $category_filter, $status_filter, $total_services, $offset, $limit) {
    $csrf_token = generateCSRFToken();
    $categories = [
        'vaccine' => 'Vaccine',
        'program' => 'Health Program',
        'appointment' => 'General Appointment'
    ];
?>

    <!-- Header with Add Button -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h2 style="margin: 0; color: #333;">Services Management</h2>
            <p style="margin: 0.5rem 0 0 0; color: #6b7280;">Manage healthcare services and programs</p>
        </div>
        <a href="?action=create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Service
        </a>
    </div>

    <?php if (isset($_GET['action']) && ($_GET['action'] === 'create' || $_GET['action'] === 'edit')): ?>
        <!-- Create/Edit Form -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <h3 class="card-title">
                    <?php echo $editing_service ? 'Edit Service' : 'Create New Service'; ?>
                </h3>
                <a href="services.php" class="btn btn-secondary btn-sm">Cancel</a>
            </div>
            <form method="POST" style="padding: 1.5rem;">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="action" value="<?php echo $editing_service ? 'update' : 'create'; ?>">
                <?php if ($editing_service): ?>
                    <input type="hidden" name="id" value="<?php echo $editing_service['id']; ?>">
                <?php endif; ?>

                <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151; font-size: 0.9rem;">Service Name *</label>
                        <input type="text" name="name"
                            value="<?php echo htmlspecialchars($editing_service['name'] ?? ''); ?>"
                            required
                            style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151; font-size: 0.9rem;">Duration (minutes)</label>
                        <input type="number" name="duration" min="5" max="480"
                            value="<?php echo $editing_service['duration'] ?? 30; ?>"
                            style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151; font-size: 0.9rem;">Category</label>
                        <select name="category" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem; background: white;">
                            <?php foreach ($categories as $value => $label): ?>
                                <option value="<?php echo $value; ?>"
                                    <?php echo ($editing_service['category'] ?? 'appointment') === $value ? 'selected' : ''; ?>>
                                    <?php echo $label; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151; font-size: 0.9rem;">Description *</label>
                    <textarea name="description" rows="4" required
                        placeholder="Enter service description..."
                        style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem; resize: vertical; font-family: inherit;"><?php echo htmlspecialchars($editing_service['description'] ?? ''); ?></textarea>
                </div>

                <?php if ($editing_service): ?>
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: 600; color: #374151; cursor: pointer;">
                            <input type="checkbox" name="is_active"
                                <?php echo ($editing_service['is_active'] ?? true) ? 'checked' : ''; ?>
                                style="width: 18px; height: 18px;">
                            Active Service
                        </label>
                    </div>
                <?php endif; ?>

                <div style="display: flex; gap: 1rem; justify-content: flex-end; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
                    <a href="services.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <?php echo $editing_service ? 'Update Service' : 'Create Service'; ?>
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <!-- Filters and Search -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <form method="GET" style="display: grid; grid-template-columns: 1fr 150px 150px auto auto; gap: 1rem; align-items: end;">
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">Search</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                    placeholder="Search services..."
                    style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">Category</label>
                <select name="category" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px;">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $value => $label): ?>
                        <option value="<?php echo $value; ?>" <?php echo $category_filter === $value ? 'selected' : ''; ?>>
                            <?php echo $label; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
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
                <a href="services.php" class="btn btn-secondary">Clear</a>
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
                onclick="return confirmDelete('Are you sure you want to delete selected services?')">
                Delete Selected
            </button>
        </form>
    </div>

    <!-- Services Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Services (<?php echo number_format($total_services); ?>)</h3>
        </div>

        <?php if (empty($services)): ?>
            <div style="text-align: center; padding: 3rem; color: #6b7280;">
                <i class="fas fa-stethoscope" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                <h3 style="margin: 0 0 0.5rem 0; color: #9ca3af;">No services found</h3>
                <p style="margin: 0;">
                    <?php if (!empty($search) || !empty($category_filter) || !empty($status_filter)): ?>
                        Try adjusting your search criteria or <a href="services.php">clear filters</a>.
                    <?php else: ?>
                        <a href="?action=create" class="btn btn-primary">Create your first service</a>
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
                            <th style="padding: 1rem; text-align: left; font-weight: 600; color: #374151;">Service</th>
                            <th style="padding: 1rem; text-align: center; font-weight: 600; color: #374151;">Category</th>
                            <th style="padding: 1rem; text-align: center; font-weight: 600; color: #374151;">Duration</th>
                            <th style="padding: 1rem; text-align: center; font-weight: 600; color: #374151;">Status</th>
                            <th style="padding: 1rem; text-align: center; font-weight: 600; color: #374151;">Created</th>
                            <th style="padding: 1rem; text-align: center; font-weight: 600; color: #374151;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($services as $service): ?>
                            <tr style="border-bottom: 1px solid #f3f4f6;">
                                <td style="padding: 1rem;">
                                    <input type="checkbox" name="ids[]" value="<?php echo $service['id']; ?>"
                                        style="width: 18px; height: 18px;">
                                </td>
                                <td style="padding: 1rem;">
                                    <div>
                                        <div style="font-weight: 600; color: #333; margin-bottom: 0.25rem;">
                                            <?php echo htmlspecialchars($service['name']); ?>
                                        </div>
                                        <div style="font-size: 0.85rem; color: #6b7280; line-height: 1.4;">
                                            <?php echo htmlspecialchars(substr($service['description'], 0, 80)); ?>
                                            <?php if (strlen($service['description']) > 80): ?>...<?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding: 1rem; text-align: center;">
                                    <?php
                                    $category_colors = [
                                        'vaccine' => '#22c55e',
                                        'program' => '#f59e0b',
                                        'appointment' => '#3b82f6'
                                    ];
                                    $color = $category_colors[$service['category']] ?? '#6b7280';
                                    ?>
                                    <span style="background: <?php echo $color; ?>20; color: <?php echo $color; ?>; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">
                                        <?php echo $categories[$service['category']] ?? 'Unknown'; ?>
                                    </span>
                                </td>
                                <td style="padding: 1rem; text-align: center; color: #6b7280;">
                                    <?php echo $service['duration']; ?> min
                                </td>
                                <td style="padding: 1rem; text-align: center;">
                                    <?php if ($service['is_active']): ?>
                                        <span style="background: #d1fae5; color: #065f46; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">
                                            Active
                                        </span>
                                    <?php else: ?>
                                        <span style="background: #fee2e2; color: #991b1b; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">
                                            Inactive
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem; text-align: center; color: #6b7280; font-size: 0.85rem;">
                                    <?php echo date('M j, Y', strtotime($service['created_at'])); ?>
                                </td>
                                <td style="padding: 1rem; text-align: center;">
                                    <div style="display: flex; gap: 0.5rem; justify-content: center;">
                                        <a href="?action=edit&id=<?php echo $service['id']; ?>"
                                            class="btn btn-secondary btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" style="display: inline;"
                                            onsubmit="return confirmDelete('Are you sure you want to delete this service?')">
                                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $service['id']; ?>">
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
                <div style="padding: 1rem; border-top: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
                    <div style="color: #6b7280; font-size: 0.9rem;">
                        Showing <?php echo number_format($offset + 1); ?> to <?php echo number_format(min($offset + count($services), $total_services)); ?>
                        of <?php echo number_format($total_services); ?> services
                    </div>
                    <div style="display: flex; gap: 0.5rem;">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category_filter); ?>&status=<?php echo urlencode($status_filter); ?>"
                                class="btn btn-secondary btn-sm">Previous</a>
                        <?php endif; ?>

                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <?php if ($i == $page): ?>
                                <span class="btn btn-primary btn-sm"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category_filter); ?>&status=<?php echo urlencode($status_filter); ?>"
                                    class="btn btn-secondary btn-sm"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category_filter); ?>&status=<?php echo urlencode($status_filter); ?>"
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