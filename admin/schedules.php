<?php
require_once '../includes/admin_layout.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('error', 'Invalid security token. Please try again.');
        header('Location: schedules.php');
        exit;
    }

    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'create':
            $service_id = (int)($_POST['service_id'] ?? 0);
            $schedule_date = $_POST['schedule_date'] ?? '';
            $start_time = $_POST['start_time'] ?? '';
            $end_time = $_POST['end_time'] ?? '';
            $max_appointments = (int)($_POST['max_appointments'] ?? 0);
            $notes = trim($_POST['notes'] ?? '');

            if (empty($service_id) || empty($schedule_date) || empty($start_time) || empty($end_time) || $max_appointments <= 0) {
                setFlashMessage('error', 'Please fill in all required fields.');
            } else {
                $result = createServiceSchedule($service_id, $schedule_date, $start_time, $end_time, $max_appointments, $notes);
                setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
            }
            break;

        case 'update':
            $id = (int)($_POST['id'] ?? 0);
            $service_id = (int)($_POST['service_id'] ?? 0);
            $schedule_date = $_POST['schedule_date'] ?? '';
            $start_time = $_POST['start_time'] ?? '';
            $end_time = $_POST['end_time'] ?? '';
            $max_appointments = (int)($_POST['max_appointments'] ?? 0);
            $notes = trim($_POST['notes'] ?? '');
            $is_active = isset($_POST['is_active']);

            if (empty($service_id) || empty($schedule_date) || empty($start_time) || empty($end_time) || $max_appointments <= 0) {
                setFlashMessage('error', 'Please fill in all required fields.');
            } else {
                $result = updateServiceSchedule($id, $service_id, $schedule_date, $start_time, $end_time, $max_appointments, $notes, $is_active);
                setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
            }
            break;

        case 'delete':
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $result = deleteServiceSchedule($id);
                setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
            }
            break;

        case 'bulk_delete':
            $ids = $_POST['ids'] ?? [];
            if (!empty($ids)) {
                $result = bulkDeleteServiceSchedules($ids);
                setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
            } else {
                setFlashMessage('error', 'No schedules selected.');
            }
            break;
    }

    header('Location: schedules.php');
    exit;
}

// Pagination and filtering
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;
$search = trim($_GET['search'] ?? '');
$service_filter = $_GET['service'] ?? '';

// Get schedules and services
$all_schedules = getServiceSchedulesAdmin();
$services = getServicesAdmin();

// Apply filters
if (!empty($search) || !empty($service_filter)) {
    $all_schedules = array_filter($all_schedules, function ($schedule) use ($search, $service_filter) {
        $match_search = empty($search) ||
            stripos($schedule['service_name'], $search) !== false ||
            stripos($schedule['notes'], $search) !== false;

        $match_service = empty($service_filter) || $schedule['service_id'] == $service_filter;

        return $match_search && $match_service;
    });
}

// Pagination
$total_schedules = count($all_schedules);
$total_pages = ceil($total_schedules / $limit);
$schedules = array_slice($all_schedules, $offset, $limit);

// Get schedule for editing
$editing_schedule = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $editing_schedule = getServiceScheduleById((int)$_GET['id']);
}

// Render the page
renderAdminLayout('Schedules Management', function () use ($schedules, $editing_schedule, $page, $total_pages, $search, $service_filter, $total_schedules, $services, $offset, $limit) {
    $csrf_token = generateCSRFToken();
?>

    <!-- Header with Add Button -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h2 style="margin: 0; color: #333;">Schedules Management</h2>
            <p style="margin: 0.5rem 0 0 0; color: #6b7280;">Manage service schedules and time slots</p>
        </div>
        <a href="?action=create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Schedule
        </a>
    </div>

    <?php if (isset($_GET['action']) && ($_GET['action'] === 'create' || $_GET['action'] === 'edit')): ?>
        <!-- Create/Edit Form -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <h3 class="card-title">
                    <?php echo $editing_schedule ? 'Edit Schedule' : 'Create New Schedule'; ?>
                </h3>
                <a href="schedules.php" class="btn btn-secondary btn-sm">Cancel</a>
            </div>
            <form method="POST" style="padding: 1.5rem;">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="action" value="<?php echo $editing_schedule ? 'update' : 'create'; ?>">
                <?php if ($editing_schedule): ?>
                    <input type="hidden" name="id" value="<?php echo $editing_schedule['id']; ?>">
                <?php endif; ?>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151; font-size: 0.9rem;">Service *</label>
                        <select name="service_id" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem; background: white;">
                            <option value="">Select Service</option>
                            <?php foreach ($services as $service): ?>
                                <option value="<?php echo $service['id']; ?>"
                                    <?php echo ($editing_schedule && $editing_schedule['service_id'] == $service['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($service['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151; font-size: 0.9rem;">Schedule Date *</label>
                        <input type="date" name="schedule_date"
                            value="<?php echo $editing_schedule['schedule_date'] ?? date('Y-m-d'); ?>"
                            required
                            style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151; font-size: 0.9rem;">Start Time *</label>
                        <input type="time" name="start_time"
                            value="<?php echo $editing_schedule['start_time'] ?? '09:00'; ?>"
                            required
                            style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151; font-size: 0.9rem;">End Time *</label>
                        <input type="time" name="end_time"
                            value="<?php echo $editing_schedule['end_time'] ?? '17:00'; ?>"
                            required
                            style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151; font-size: 0.9rem;">Max Appointments *</label>
                        <input type="number" name="max_appointments" min="1" max="100"
                            value="<?php echo $editing_schedule['max_appointments'] ?? '10'; ?>"
                            required
                            style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem;">
                    </div>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151; font-size: 0.9rem;">Notes</label>
                    <textarea name="notes" rows="4"
                        placeholder="Add any notes about this schedule..."
                        style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem; resize: vertical; font-family: inherit;"><?php echo htmlspecialchars($editing_schedule['notes'] ?? ''); ?></textarea>
                </div>

                <?php if ($editing_schedule): ?>
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: 600; color: #374151; cursor: pointer;">
                            <input type="checkbox" name="is_active"
                                <?php echo ($editing_schedule['is_active'] ?? true) ? 'checked' : ''; ?>
                                style="width: 18px; height: 18px;">
                            Active
                        </label>
                    </div>
                <?php endif; ?>

                <div style="display: flex; gap: 1rem; justify-content: flex-end; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
                    <a href="schedules.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <?php echo $editing_schedule ? 'Update Schedule' : 'Create Schedule'; ?>
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
                    placeholder="Search service name or notes..."
                    style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">Service</label>
                <select name="service" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px;">
                    <option value="">All Services</option>
                    <?php foreach ($services as $service): ?>
                        <option value="<?php echo $service['id']; ?>" <?php echo $service_filter == $service['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($service['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
            <div>
                <a href="schedules.php" class="btn btn-secondary">Clear</a>
            </div>
        </form>
    </div>

    <!-- Bulk Actions -->
    <div class="bulk-actions" style="display: none; margin-bottom: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 8px; border: 1px solid #e9ecef;">
        <form method="POST" style="display: flex; gap: 1rem; align-items: center;">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <span style="font-weight: 600; color: #333;">Bulk Actions:</span>
            <button type="submit" name="action" value="bulk_delete" class="btn btn-danger btn-sm"
                onclick="return confirmDelete('Are you sure you want to delete selected schedules?')">
                Delete Selected
            </button>
        </form>
    </div>

    <!-- Schedules Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Schedules (<?php echo number_format($total_schedules); ?>)</h3>
        </div>

        <?php if (empty($schedules)): ?>
            <div style="text-align: center; padding: 3rem; color: #6b7280;">
                <i class="fas fa-calendar" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                <h3 style="margin: 0 0 0.5rem 0; color: #9ca3af;">No schedules found</h3>
                <p style="margin: 0;">
                    <?php if (!empty($search) || !empty($service_filter)): ?>
                        Try adjusting your search criteria or <a href="schedules.php">clear filters</a>.
                    <?php else: ?>
                        <a href="?action=create" class="btn btn-primary">Create your first schedule</a>
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
                            <th style="padding: 1rem; text-align: left; font-weight: 600; color: #374151;">Date</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 600; color: #374151;">Time</th>
                            <th style="padding: 1rem; text-align: center; font-weight: 600; color: #374151;">Appointments</th>
                            <th style="padding: 1rem; text-align: center; font-weight: 600; color: #374151;">Status</th>
                            <th style="padding: 1rem; text-align: center; font-weight: 600; color: #374151;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($schedules as $schedule): ?>
                            <tr style="border-bottom: 1px solid #f3f4f6;">
                                <td style="padding: 1rem;">
                                    <input type="checkbox" name="ids[]" value="<?php echo $schedule['id']; ?>"
                                        style="width: 18px; height: 18px;">
                                </td>
                                <td style="padding: 1rem;">
                                    <div>
                                        <div style="font-weight: 600; color: #333; margin-bottom: 0.25rem;">
                                            <?php echo htmlspecialchars($schedule['service_name']); ?>
                                        </div>
                                        <?php if (!empty($schedule['notes'])): ?>
                                            <div style="font-size: 0.85rem; color: #6b7280;">
                                                <?php echo htmlspecialchars(substr($schedule['notes'], 0, 50)); ?>
                                                <?php if (strlen($schedule['notes']) > 50): ?>...<?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td style="padding: 1rem; color: #6b7280;">
                                    <?php echo date('M j, Y', strtotime($schedule['schedule_date'])); ?>
                                </td>
                                <td style="padding: 1rem; color: #6b7280;">
                                    <?php echo date('g:i A', strtotime($schedule['start_time'])); ?> -
                                    <?php echo date('g:i A', strtotime($schedule['end_time'])); ?>
                                </td>
                                <td style="padding: 1rem; text-align: center;">
                                    <span style="background: #e0f2fe; color: #0369a1; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">
                                        <?php echo $schedule['confirmed_appointments'] ?? 0; ?>/<?php echo $schedule['max_appointments']; ?>
                                    </span>
                                </td>
                                <td style="padding: 1rem; text-align: center;">
                                    <?php if ($schedule['is_active']): ?>
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
                                    <div style="display: flex; gap: 0.5rem; justify-content: center;">
                                        <a href="?action=edit&id=<?php echo $schedule['id']; ?>"
                                            class="btn btn-secondary btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" style="display: inline;"
                                            onsubmit="return confirmDelete('Are you sure you want to delete this schedule?')">
                                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $schedule['id']; ?>">
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
                        Showing <?php echo number_format($offset + 1); ?> to <?php echo number_format(min($offset + count($schedules), $total_schedules)); ?>
                        of <?php echo number_format($total_schedules); ?> schedules
                    </div>
                    <div style="display: flex; gap: 0.5rem;">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&service=<?php echo urlencode($service_filter); ?>"
                                class="btn btn-secondary btn-sm">Previous</a>
                        <?php endif; ?>

                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <?php if ($i == $page): ?>
                                <span class="btn btn-primary btn-sm"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&service=<?php echo urlencode($service_filter); ?>"
                                    class="btn btn-secondary btn-sm"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&service=<?php echo urlencode($service_filter); ?>"
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