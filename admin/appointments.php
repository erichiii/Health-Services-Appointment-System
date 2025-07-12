<?php
require_once '../includes/admin_layout.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('error', 'Invalid security token. Please try again.');
        header('Location: appointments.php');
        exit;
    }

    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'update_status':
            $id = (int)($_POST['id'] ?? 0);
            $status = $_POST['status'] ?? '';
            $notes = trim($_POST['notes'] ?? '');

            if ($id > 0 && !empty($status)) {
                $result = updateAppointmentStatus($id, $status, $notes);
                setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
            }
            break;

        case 'delete':
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $result = deleteAppointment($id);
                setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
            }
            break;

        case 'bulk_delete':
            $ids = $_POST['ids'] ?? [];
            if (!empty($ids)) {
                $result = bulkDeleteAppointments($ids);
                setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
            } else {
                setFlashMessage('error', 'No appointments selected.');
            }
            break;

        case 'bulk_confirm':
            $ids = $_POST['ids'] ?? [];
            if (!empty($ids)) {
                $result = bulkUpdateAppointmentStatus($ids, 'confirmed');
                setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
            } else {
                setFlashMessage('error', 'No appointments selected.');
            }
            break;

        case 'bulk_complete':
            $ids = $_POST['ids'] ?? [];
            if (!empty($ids)) {
                $result = bulkUpdateAppointmentStatus($ids, 'completed');
                setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
            } else {
                setFlashMessage('error', 'No appointments selected.');
            }
            break;

        case 'bulk_cancel':
            $ids = $_POST['ids'] ?? [];
            if (!empty($ids)) {
                $result = bulkUpdateAppointmentStatus($ids, 'cancelled');
                setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
            } else {
                setFlashMessage('error', 'No appointments selected.');
            }
            break;
    }

    header('Location: appointments.php');
    exit;
}

// Pagination and filtering
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;
$search = trim($_GET['search'] ?? '');
$status_filter = $_GET['status'] ?? '';

// Get appointments
$all_appointments = getAppointmentsAdmin(1000, 0); // Get more for filtering

// Apply filters
if (!empty($search) || !empty($status_filter)) {
    $all_appointments = array_filter($all_appointments, function ($appointment) use ($search, $status_filter) {
        $match_search = empty($search) ||
            stripos($appointment['client_name'], $search) !== false ||
            stripos($appointment['client_email'], $search) !== false ||
            stripos($appointment['service_name'], $search) !== false;

        $match_status = empty($status_filter) || $appointment['status'] === $status_filter;

        return $match_search && $match_status;
    });
}

// Pagination
$total_appointments = count($all_appointments);
$total_pages = ceil($total_appointments / $limit);
$appointments = array_slice($all_appointments, $offset, $limit);

// Get appointment for editing
$editing_appointment = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $editing_appointment = getAppointmentById((int)$_GET['id']);
}

// Render the page
renderAdminLayout('Appointments Management', function () use ($appointments, $editing_appointment, $page, $total_pages, $search, $status_filter, $total_appointments, $offset, $limit) {
    $csrf_token = generateCSRFToken();
?>

    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h2 style="margin: 0; color: #333;">Appointments Management</h2>
            <p style="margin: 0.5rem 0 0 0; color: #6b7280;">Manage client appointments and bookings</p>
        </div>
    </div>

    <?php if (isset($_GET['action']) && $_GET['action'] === 'edit'): ?>
        <!-- Edit Appointment Status Form -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <h3 class="card-title">Update Appointment Status</h3>
                <a href="appointments.php" class="btn btn-secondary btn-sm">Cancel</a>
            </div>
            <form method="POST" style="padding: 1.5rem;">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="id" value="<?php echo $editing_appointment['id']; ?>">

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; margin-bottom: 1.5rem;">
                    <div>
                        <h4 style="margin: 0 0 1rem 0; color: #374151; font-weight: 600; font-size: 1.1rem;">Appointment Details</h4>
                        <div style="background: #f9fafb; padding: 1.5rem; border-radius: 8px; border: 1px solid #e5e7eb;">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div>
                                    <p style="margin: 0 0 0.75rem 0; font-size: 0.9rem; color: #6b7280;">
                                        <strong style="color: #374151; font-weight: 600;">Client:</strong><br>
                                        <span style="color: #111827; font-size: 1rem;"><?php echo htmlspecialchars($editing_appointment['client_name']); ?></span>
                                    </p>
                                    <p style="margin: 0 0 0.75rem 0; font-size: 0.9rem; color: #6b7280;">
                                        <strong style="color: #374151; font-weight: 600;">Email:</strong><br>
                                        <span style="color: #111827; font-size: 1rem;"><?php echo htmlspecialchars($editing_appointment['client_email'] ?? 'N/A'); ?></span>
                                    </p>
                                    <p style="margin: 0; font-size: 0.9rem; color: #6b7280;">
                                        <strong style="color: #374151; font-weight: 600;">Phone:</strong><br>
                                        <span style="color: #111827; font-size: 1rem;"><?php echo htmlspecialchars($editing_appointment['client_phone'] ?? 'N/A'); ?></span>
                                    </p>
                                </div>
                                <div>
                                    <p style="margin: 0 0 0.75rem 0; font-size: 0.9rem; color: #6b7280;">
                                        <strong style="color: #374151; font-weight: 600;">Service:</strong><br>
                                        <span style="color: #111827; font-size: 1rem;"><?php echo htmlspecialchars($editing_appointment['service_name']); ?></span>
                                    </p>
                                    <p style="margin: 0 0 0.75rem 0; font-size: 0.9rem; color: #6b7280;">
                                        <strong style="color: #374151; font-weight: 600;">Date:</strong><br>
                                        <span style="color: #111827; font-size: 1rem;"><?php echo date('M j, Y', strtotime($editing_appointment['schedule_date'])); ?></span>
                                    </p>
                                    <p style="margin: 0; font-size: 0.9rem; color: #6b7280;">
                                        <strong style="color: #374151; font-weight: 600;">Time:</strong><br>
                                        <span style="color: #111827; font-size: 1rem;"><?php echo date('g:i A', strtotime($editing_appointment['appointment_time'] ?? $editing_appointment['start_time'])); ?></span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h4 style="margin: 0 0 1rem 0; color: #374151; font-weight: 600; font-size: 1.1rem;">Update Status</h4>
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151; font-size: 0.9rem;">Status *</label>
                            <select name="status" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem; background: white;">
                                <option value="pending" <?php echo $editing_appointment['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="confirmed" <?php echo $editing_appointment['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="completed" <?php echo $editing_appointment['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?php echo $editing_appointment['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151; font-size: 0.9rem;">Notes</label>
                    <textarea name="notes" rows="4"
                        placeholder="Add any notes or comments about this appointment..."
                        style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem; resize: vertical; font-family: inherit;"><?php echo htmlspecialchars($editing_appointment['notes'] ?? ''); ?></textarea>
                </div>

                <div style="display: flex; gap: 1rem; justify-content: flex-end; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
                    <a href="appointments.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Appointment</button>
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
                    placeholder="Search client name, email, or service..."
                    style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">Status</label>
                <select name="status" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px;">
                    <option value="">All Status</option>
                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                    <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
            <div>
                <a href="appointments.php" class="btn btn-secondary">Clear</a>
            </div>
        </form>
    </div>

    <!-- Bulk Actions -->
    <div class="bulk-actions" style="display: none; margin-bottom: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 8px; border: 1px solid #e9ecef;">
        <form method="POST" style="display: flex; gap: 1rem; align-items: center;">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <span style="font-weight: 600; color: #333;">Bulk Actions:</span>
            <button type="submit" name="action" value="bulk_confirm" class="btn btn-success btn-sm">
                Confirm Selected
            </button>
            <button type="submit" name="action" value="bulk_complete" class="btn btn-primary btn-sm">
                Complete Selected
            </button>
            <button type="submit" name="action" value="bulk_cancel" class="btn btn-secondary btn-sm">
                Cancel Selected
            </button>
            <button type="submit" name="action" value="bulk_delete" class="btn btn-danger btn-sm"
                onclick="return confirmDelete('Are you sure you want to delete selected appointments?')">
                Delete Selected
            </button>
        </form>
    </div>

    <!-- Appointments Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Appointments (<?php echo number_format($total_appointments); ?>)</h3>
        </div>

        <?php if (empty($appointments)): ?>
            <div style="text-align: center; padding: 3rem; color: #6b7280;">
                <i class="fas fa-calendar-check" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                <h3 style="margin: 0 0 0.5rem 0; color: #9ca3af;">No appointments found</h3>
                <p style="margin: 0;">
                    <?php if (!empty($search) || !empty($status_filter)): ?>
                        Try adjusting your search criteria or <a href="appointments.php">clear filters</a>.
                    <?php else: ?>
                        No appointments have been booked yet.
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
                            <th style="padding: 1rem; text-align: left; font-weight: 600; color: #374151;">Client</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 600; color: #374151;">Service</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 600; color: #374151;">Date & Time</th>
                            <th style="padding: 1rem; text-align: center; font-weight: 600; color: #374151;">Status</th>
                            <th style="padding: 1rem; text-align: center; font-weight: 600; color: #374151;">Booked</th>
                            <th style="padding: 1rem; text-align: center; font-weight: 600; color: #374151;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $appointment): ?>
                            <tr style="border-bottom: 1px solid #f3f4f6;">
                                <td style="padding: 1rem;">
                                    <input type="checkbox" name="ids[]" value="<?php echo $appointment['id']; ?>"
                                        style="width: 18px; height: 18px;">
                                </td>
                                <td style="padding: 1rem;">
                                    <div>
                                        <div style="font-weight: 600; color: #333; margin-bottom: 0.25rem;">
                                            <?php echo htmlspecialchars($appointment['client_name']); ?>
                                        </div>
                                        <div style="font-size: 0.85rem; color: #6b7280;">
                                            <?php if (!empty($appointment['client_email'])): ?>
                                                <i class="fas fa-envelope" style="margin-right: 0.25rem;"></i>
                                                <?php echo htmlspecialchars($appointment['client_email']); ?>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($appointment['client_phone'])): ?>
                                            <div style="font-size: 0.85rem; color: #6b7280;">
                                                <i class="fas fa-phone" style="margin-right: 0.25rem;"></i>
                                                <?php echo htmlspecialchars($appointment['client_phone']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td style="padding: 1rem;">
                                    <div style="font-weight: 600; color: #333;">
                                        <?php echo htmlspecialchars($appointment['service_name']); ?>
                                    </div>
                                </td>
                                <td style="padding: 1rem;">
                                    <div style="font-weight: 600; color: #333; margin-bottom: 0.25rem;">
                                        <?php echo date('M j, Y', strtotime($appointment['schedule_date'])); ?>
                                    </div>
                                    <div style="font-size: 0.85rem; color: #6b7280;">
                                        <?php echo date('g:i A', strtotime($appointment['appointment_time'] ?? $appointment['start_time'])); ?>
                                    </div>
                                </td>
                                <td style="padding: 1rem; text-align: center;">
                                    <?php
                                    $status_colors = [
                                        'pending' => ['bg' => '#fef3c7', 'text' => '#92400e'],
                                        'confirmed' => ['bg' => '#d1fae5', 'text' => '#065f46'],
                                        'completed' => ['bg' => '#dbeafe', 'text' => '#1e40af'],
                                        'cancelled' => ['bg' => '#fee2e2', 'text' => '#991b1b']
                                    ];
                                    $colors = $status_colors[$appointment['status']] ?? ['bg' => '#f3f4f6', 'text' => '#374151'];
                                    ?>
                                    <span style="background: <?php echo $colors['bg']; ?>; color: <?php echo $colors['text']; ?>; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">
                                        <?php echo ucfirst($appointment['status']); ?>
                                    </span>
                                </td>
                                <td style="padding: 1rem; text-align: center; color: #6b7280; font-size: 0.85rem;">
                                    <?php echo date('M j, Y', strtotime($appointment['created_at'])); ?>
                                </td>
                                <td style="padding: 1rem; text-align: center;">
                                    <div style="display: flex; gap: 0.5rem; justify-content: center;">
                                        <a href="?action=edit&id=<?php echo $appointment['id']; ?>"
                                            class="btn btn-secondary btn-sm" title="Edit Status">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" style="display: inline;"
                                            onsubmit="return confirmDelete('Are you sure you want to delete this appointment?')">
                                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $appointment['id']; ?>">
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
                        Showing <?php echo number_format($offset + 1); ?> to <?php echo number_format(min($offset + count($appointments), $total_appointments)); ?>
                        of <?php echo number_format($total_appointments); ?> appointments
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