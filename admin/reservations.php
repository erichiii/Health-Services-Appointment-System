<?php
require_once '../includes/admin_layout.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('error', 'Invalid security token. Please try again.');
        header('Location: reservations.php');
        exit;
    }

    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'update_status':
            $id = (int)($_POST['id'] ?? 0);
            $status = $_POST['status'] ?? '';
            $notes = trim($_POST['notes'] ?? '');

            if ($id > 0 && !empty($status)) {
                $result = updateReservationStatus($id, $status, $notes);
                setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
            }
            break;

        case 'delete':
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $result = deleteReservation($id);
                setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
            }
            break;

        case 'bulk_delete':
            $ids = $_POST['ids'] ?? [];
            if (!empty($ids)) {
                $result = bulkDeleteReservations($ids);
                setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
            } else {
                setFlashMessage('error', 'No reservations selected.');
            }
            break;

        case 'bulk_schedule':
            $ids = $_POST['ids'] ?? [];
            if (!empty($ids)) {
                $result = bulkUpdateReservationStatus($ids, 'scheduled');
                setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
            } else {
                setFlashMessage('error', 'No reservations selected.');
            }
            break;

        case 'bulk_cancel':
            $ids = $_POST['ids'] ?? [];
            if (!empty($ids)) {
                $result = bulkUpdateReservationStatus($ids, 'cancelled');
                setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
            } else {
                setFlashMessage('error', 'No reservations selected.');
            }
            break;

        case 'convert_to_appointment':
            $reservation_id = (int)($_POST['reservation_id'] ?? 0);
            $service_schedule_id = (int)($_POST['service_schedule_id'] ?? 0);
            $appointment_time = $_POST['appointment_time'] ?? '';

            if ($reservation_id > 0 && $service_schedule_id > 0 && !empty($appointment_time)) {
                $result = convertReservationToAppointment($reservation_id, $service_schedule_id, $appointment_time);
                setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
            } else {
                setFlashMessage('error', 'Please fill in all required fields.');
            }
            break;
    }

    header('Location: reservations.php');
    exit;
}

// Pagination and filtering
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;
$search = trim($_GET['search'] ?? '');
$status_filter = $_GET['status'] ?? '';
$category_filter = $_GET['category'] ?? '';

// Get reservations
$all_reservations = getReservationsAdmin(1000, 0); // Get more for filtering

// Apply filters
if (!empty($search) || !empty($status_filter) || !empty($category_filter)) {
    $all_reservations = array_filter($all_reservations, function ($reservation) use ($search, $status_filter, $category_filter) {
        $match_search = empty($search) ||
            stripos($reservation['client_name'], $search) !== false ||
            stripos($reservation['email_address'], $search) !== false ||
            stripos($reservation['contact_number'], $search) !== false ||
            stripos($reservation['service_name'], $search) !== false ||
            stripos($reservation['vehai_id'], $search) !== false;

        $match_status = empty($status_filter) || $reservation['status'] === $status_filter;
        $match_category = empty($category_filter) || $reservation['service_category'] === $category_filter;

        return $match_search && $match_status && $match_category;
    });
}

// Pagination
$total_reservations = count($all_reservations);
$total_pages = ceil($total_reservations / $limit);
$reservations = array_slice($all_reservations, $offset, $limit);

// Get reservation for editing
$editing_reservation = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $editing_reservation = getReservationById((int)$_GET['id']);
}

// Get reservation for converting to appointment
$converting_reservation = null;
$available_schedules = [];
if (isset($_GET['action']) && $_GET['action'] === 'convert' && isset($_GET['id'])) {
    $converting_reservation = getReservationById((int)$_GET['id']);
    if ($converting_reservation) {
        // Get available schedules for this service
        $available_schedules = getServiceSchedulesAdmin();
        $available_schedules = array_filter($available_schedules, function ($schedule) use ($converting_reservation) {
            return $schedule['service_id'] == $converting_reservation['service_id'] &&
                $schedule['schedule_date'] >= date('Y-m-d') &&
                $schedule['is_active'];
        });
    }
}

// Render the page
renderAdminLayout('Reservations Management', function () use ($reservations, $editing_reservation, $converting_reservation, $available_schedules, $page, $total_pages, $search, $status_filter, $category_filter, $total_reservations, $offset, $limit) {
    $csrf_token = generateCSRFToken();
?>

    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h2 style="margin: 0; color: #333;">Reservations Management</h2>
            <p style="margin: 0.5rem 0 0 0; color: #6b7280;">Manage client reservation requests</p>
        </div>
    </div>

    <?php if (isset($_GET['action']) && $_GET['action'] === 'edit' && $editing_reservation): ?>
        <!-- Edit Reservation Status Form -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <h3 class="card-title">Update Reservation Status</h3>
                <a href="reservations.php" class="btn btn-secondary btn-sm">Cancel</a>
            </div>
            <form method="POST" style="padding: 1.5rem;">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="id" value="<?php echo $editing_reservation['id']; ?>">

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; margin-bottom: 1.5rem;">
                    <div>
                        <h4 style="margin: 0 0 1rem 0; color: #374151; font-weight: 600; font-size: 1.1rem;">Reservation Details</h4>
                        <div style="background: #f9fafb; padding: 1.5rem; border-radius: 8px; border: 1px solid #e5e7eb;">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div>
                                    <p style="margin: 0 0 0.75rem 0; font-size: 0.9rem; color: #6b7280;">
                                        <strong style="color: #374151; font-weight: 600;">Client:</strong><br>
                                        <span style="color: #111827; font-size: 1rem;"><?php echo htmlspecialchars($editing_reservation['client_name']); ?></span>
                                    </p>
                                    <p style="margin: 0 0 0.75rem 0; font-size: 0.9rem; color: #6b7280;">
                                        <strong style="color: #374151; font-weight: 600;">Health ID:</strong><br>
                                        <span style="color: #111827; font-size: 1rem;"><?php echo htmlspecialchars($editing_reservation['vehai_id'] ?? 'N/A'); ?></span>
                                    </p>
                                    <p style="margin: 0 0 0.75rem 0; font-size: 0.9rem; color: #6b7280;">
                                        <strong style="color: #374151; font-weight: 600;">Email:</strong><br>
                                        <span style="color: #111827; font-size: 1rem;"><?php echo htmlspecialchars($editing_reservation['email_address'] ?? 'N/A'); ?></span>
                                    </p>
                                    <p style="margin: 0 0 0.75rem 0; font-size: 0.9rem; color: #6b7280;">
                                        <strong style="color: #374151; font-weight: 600;">Phone:</strong><br>
                                        <span style="color: #111827; font-size: 1rem;"><?php echo htmlspecialchars($editing_reservation['contact_number']); ?></span>
                                    </p>
                                </div>
                                <div>
                                    <p style="margin: 0 0 0.75rem 0; font-size: 0.9rem; color: #6b7280;">
                                        <strong style="color: #374151; font-weight: 600;">Service:</strong><br>
                                        <span style="color: #111827; font-size: 1rem;"><?php echo htmlspecialchars($editing_reservation['service_name']); ?></span>
                                    </p>
                                    <p style="margin: 0 0 0.75rem 0; font-size: 0.9rem; color: #6b7280;">
                                        <strong style="color: #374151; font-weight: 600;">Category:</strong><br>
                                        <span style="color: #111827; font-size: 1rem;"><?php echo ucfirst($editing_reservation['service_category']); ?></span>
                                    </p>
                                    <p style="margin: 0 0 0.75rem 0; font-size: 0.9rem; color: #6b7280;">
                                        <strong style="color: #374151; font-weight: 600;">Subcategory:</strong><br>
                                        <span style="color: #111827; font-size: 1rem;"><?php echo htmlspecialchars($editing_reservation['service_subcategory']); ?></span>
                                    </p>
                                    <p style="margin: 0 0 0.75rem 0; font-size: 0.9rem; color: #6b7280;">
                                        <strong style="color: #374151; font-weight: 600;">Preferred Date:</strong><br>
                                        <span style="color: #111827; font-size: 1rem;"><?php echo date('M j, Y', strtotime($editing_reservation['preferred_date'])); ?></span>
                                    </p>
                                    <p style="margin: 0; font-size: 0.9rem; color: #6b7280;">
                                        <strong style="color: #374151; font-weight: 600;">Preferred Time:</strong><br>
                                        <span style="color: #111827; font-size: 1rem;"><?php echo date('g:i A', strtotime($editing_reservation['preferred_time'])); ?></span>
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
                                <option value="pending" <?php echo $editing_reservation['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="scheduled" <?php echo $editing_reservation['status'] === 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                                <option value="cancelled" <?php echo $editing_reservation['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151; font-size: 0.9rem;">Notes</label>
                    <textarea name="notes" rows="4"
                        placeholder="Add any notes or comments about this reservation..."
                        style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem; resize: vertical; font-family: inherit;"><?php echo htmlspecialchars($editing_reservation['notes'] ?? ''); ?></textarea>
                </div>

                <div style="display: flex; gap: 1rem; justify-content: flex-end; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
                    <a href="reservations.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Reservation</button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['action']) && $_GET['action'] === 'convert' && $converting_reservation): ?>
        <!-- Convert to Appointment Form -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <h3 class="card-title">Convert Reservation to Appointment</h3>
                <a href="reservations.php" class="btn btn-secondary btn-sm">Cancel</a>
            </div>
            <form method="POST" style="display: grid; gap: 1rem;">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="action" value="convert_to_appointment">
                <input type="hidden" name="reservation_id" value="<?php echo $converting_reservation['id']; ?>">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div>
                        <h4 style="margin: 0 0 1rem 0; color: #333;">Reservation Details</h4>
                        <div style="background: #f8f9fa; padding: 1rem; border-radius: 6px;">
                            <p><strong>Client:</strong> <?php echo htmlspecialchars($converting_reservation['client_name']); ?></p>
                            <p><strong>Service:</strong> <?php echo htmlspecialchars($converting_reservation['service_name']); ?></p>
                            <p><strong>Preferred Date:</strong> <?php echo date('M j, Y', strtotime($converting_reservation['preferred_date'])); ?></p>
                            <p><strong>Preferred Time:</strong> <?php echo date('g:i A', strtotime($converting_reservation['preferred_time'])); ?></p>
                        </div>
                    </div>
                    <div>
                        <div style="margin-bottom: 1rem;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">Available Schedule *</label>
                            <select name="service_schedule_id" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem;">
                                <option value="">Select a schedule</option>
                                <?php foreach ($available_schedules as $schedule): ?>
                                    <option value="<?php echo $schedule['id']; ?>">
                                        <?php echo date('M j, Y', strtotime($schedule['schedule_date'])); ?> -
                                        <?php echo date('g:i A', strtotime($schedule['start_time'])); ?> to
                                        <?php echo date('g:i A', strtotime($schedule['end_time'])); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">Appointment Time *</label>
                            <input type="time" name="appointment_time" required
                                value="<?php echo date('H:i', strtotime($converting_reservation['preferred_time'])); ?>"
                                style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem;">
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <a href="reservations.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Convert to Appointment</button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <!-- Filters and Search -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <form method="GET" style="display: grid; grid-template-columns: 1fr 150px 150px 150px auto; gap: 1rem; align-items: end;">
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">Search</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                    placeholder="Search client name, email, phone, or health ID..."
                    style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">Status</label>
                <select name="status" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px;">
                    <option value="">All Status</option>
                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="scheduled" <?php echo $status_filter === 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                    <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">Category</label>
                <select name="category" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px;">
                    <option value="">All Categories</option>
                    <option value="vaccine" <?php echo $category_filter === 'vaccine' ? 'selected' : ''; ?>>Vaccine</option>
                    <option value="program" <?php echo $category_filter === 'program' ? 'selected' : ''; ?>>Program</option>
                    <option value="general" <?php echo $category_filter === 'general' ? 'selected' : ''; ?>>General</option>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
            <div>
                <a href="reservations.php" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>

    <!-- Reservations Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                Reservations (<?php echo number_format($total_reservations); ?>)
            </h3>
            <div style="display: flex; gap: 0.5rem;">
                <button type="button" class="btn btn-primary btn-sm" onclick="bulkAction('bulk_schedule')">
                    <i class="fas fa-calendar-check"></i> Schedule Selected
                </button>
                <button type="button" class="btn btn-warning btn-sm" onclick="bulkAction('bulk_cancel')">
                    <i class="fas fa-times"></i> Cancel Selected
                </button>
                <button type="button" class="btn btn-danger btn-sm" onclick="bulkAction('bulk_delete')">
                    <i class="fas fa-trash"></i> Delete Selected
                </button>
            </div>
        </div>

        <?php if (empty($reservations)): ?>
            <div style="text-align: center; padding: 3rem; color: #6b7280;">
                <i class="fas fa-calendar-plus" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                <p style="font-size: 1.1rem; margin-bottom: 0.5rem;">No reservations found</p>
                <p style="font-size: 0.9rem;">Reservations will appear here when clients submit reservation requests.</p>
            </div>
        <?php else: ?>
            <form id="bulk-form" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="action" id="bulk-action" value="">

                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 2px solid #e5e7eb;">
                                <th style="padding: 1rem; text-align: left;">
                                    <input type="checkbox" id="select-all" onchange="toggleAll(this)" style="width: 18px; height: 18px;">
                                </th>
                                <th style="padding: 1rem; text-align: center; font-weight: 600; color: #374151;">ID</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: #374151;">Client</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: #374151;">Service</th>
                                <th style="padding: 1rem; text-align: center; font-weight: 600; color: #374151;">Category</th>
                                <th style="padding: 1rem; text-align: center; font-weight: 600; color: #374151;">Preferred Date/Time</th>
                                <th style="padding: 1rem; text-align: center; font-weight: 600; color: #374151;">Status</th>
                                <th style="padding: 1rem; text-align: center; font-weight: 600; color: #374151;">Created</th>
                                <th style="padding: 1rem; text-align: center; font-weight: 600; color: #374151;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservations as $reservation): ?>
                                <tr style="border-bottom: 1px solid #f3f4f6;">
                                    <td style="padding: 1rem;">
                                        <input type="checkbox" name="ids[]" value="<?php echo $reservation['id']; ?>" class="row-checkbox" style="width: 18px; height: 18px;">
                                    </td>
                                    <td style="padding: 1rem; text-align: center; color: #6b7280; font-weight: 600;">
                                        #<?php echo $reservation['id']; ?>
                                    </td>
                                    <td style="padding: 1rem;">
                                        <div>
                                            <div style="font-weight: 600; color: #333; margin-bottom: 0.25rem;">
                                                <?php echo htmlspecialchars($reservation['client_name']); ?>
                                            </div>
                                            <div style="font-size: 0.85rem; color: #6b7280; line-height: 1.4;">
                                                <?php if ($reservation['vehai_id']): ?>
                                                    ID: <?php echo htmlspecialchars($reservation['vehai_id']); ?><br>
                                                <?php endif; ?>
                                                <?php echo htmlspecialchars($reservation['contact_number']); ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="padding: 1rem;">
                                        <div>
                                            <div style="font-weight: 600; color: #333; margin-bottom: 0.25rem;">
                                                <?php echo htmlspecialchars($reservation['service_name']); ?>
                                            </div>
                                            <div style="font-size: 0.85rem; color: #6b7280; line-height: 1.4;">
                                                <?php echo htmlspecialchars($reservation['service_subcategory']); ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="padding: 1rem; text-align: center;">
                                        <?php
                                        $category_colors = [
                                            'vaccine' => '#22c55e',
                                            'program' => '#f59e0b',
                                            'general' => '#3b82f6'
                                        ];
                                        $color = $category_colors[$reservation['service_category']] ?? '#6b7280';
                                        ?>
                                        <span style="background: <?php echo $color; ?>20; color: <?php echo $color; ?>; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">
                                            <?php echo ucfirst($reservation['service_category']); ?>
                                        </span>
                                    </td>
                                    <td style="padding: 1rem; text-align: center;">
                                        <div>
                                            <div style="font-weight: 600; color: #333; margin-bottom: 0.25rem;">
                                                <?php echo date('M j, Y', strtotime($reservation['preferred_date'])); ?>
                                            </div>
                                            <div style="font-size: 0.85rem; color: #6b7280;">
                                                <?php echo date('g:i A', strtotime($reservation['preferred_time'])); ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="padding: 1rem; text-align: center;">
                                        <?php
                                        $status_colors = [
                                            'pending' => ['bg' => '#fef3c7', 'text' => '#92400e'],
                                            'scheduled' => ['bg' => '#d1fae5', 'text' => '#065f46'],
                                            'cancelled' => ['bg' => '#fee2e2', 'text' => '#dc2626']
                                        ];
                                        $status_color = $status_colors[$reservation['status']] ?? ['bg' => '#f3f4f6', 'text' => '#6b7280'];
                                        ?>
                                        <span style="background: <?php echo $status_color['bg']; ?>; color: <?php echo $status_color['text']; ?>; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">
                                            <?php echo ucfirst($reservation['status']); ?>
                                        </span>
                                    </td>
                                    <td style="padding: 1rem; text-align: center; color: #6b7280; font-size: 0.85rem;">
                                        <?php echo date('M j, Y', strtotime($reservation['created_at'])); ?>
                                    </td>
                                    <td style="padding: 1rem; text-align: center;">
                                        <div style="display: flex; gap: 0.5rem; justify-content: center;">
                                            <a href="?action=edit&id=<?php echo $reservation['id']; ?>" class="btn btn-secondary btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($reservation['status'] === 'pending'): ?>
                                                <a href="?action=convert&id=<?php echo $reservation['id']; ?>" class="btn btn-success btn-sm" title="Convert">
                                                    <i class="fas fa-calendar-plus"></i>
                                                </a>
                                            <?php endif; ?>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="deleteReservation(<?php echo $reservation['id']; ?>)" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </form>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div style="padding: 1rem; border-top: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
                    <div style="color: #6b7280; font-size: 0.9rem;">
                        Showing <?php echo number_format($offset + 1); ?> to <?php echo number_format(min($offset + $limit, $total_reservations)); ?>
                        of <?php echo number_format($total_reservations); ?> reservations
                    </div>
                    <div style="display: flex; gap: 0.5rem;">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&category=<?php echo urlencode($category_filter); ?>"
                                class="btn btn-secondary btn-sm">Previous</a>
                        <?php endif; ?>

                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <?php if ($i == $page): ?>
                                <span class="btn btn-primary btn-sm"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&category=<?php echo urlencode($category_filter); ?>"
                                    class="btn btn-secondary btn-sm"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&category=<?php echo urlencode($category_filter); ?>"
                                class="btn btn-secondary btn-sm">Next</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>



    <script>
        function toggleAll(checkbox) {
            const checkboxes = document.querySelectorAll('.row-checkbox');
            checkboxes.forEach(cb => cb.checked = checkbox.checked);
        }

        function bulkAction(action) {
            const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
            if (checkedBoxes.length === 0) {
                alert('Please select at least one reservation.');
                return;
            }

            let message = '';
            switch (action) {
                case 'bulk_schedule':
                    message = 'Are you sure you want to mark the selected reservations as scheduled?';
                    break;
                case 'bulk_cancel':
                    message = 'Are you sure you want to cancel the selected reservations?';
                    break;
                case 'bulk_delete':
                    message = 'Are you sure you want to delete the selected reservations? This action cannot be undone.';
                    break;
            }

            if (confirm(message)) {
                document.getElementById('bulk-action').value = action;
                document.getElementById('bulk-form').submit();
            }
        }

        function deleteReservation(id) {
            if (confirm('Are you sure you want to delete this reservation? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
<?php
});
?>