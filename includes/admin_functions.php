<?php
require_once 'db_connection.php';

// Admin Authentication Functions

/**
 * Authenticate admin user
 */
function authenticateAdmin($username, $password)
{
    global $pdo;

    try {
        $sql = "SELECT id, username, email, password_hash, full_name, is_active 
                FROM admin_users 
                WHERE (username = ? OR email = ?) AND is_active = 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Update last login
            $update_sql = "UPDATE admin_users SET last_login = CURRENT_TIMESTAMP WHERE id = ?";
            $update_stmt = $pdo->prepare($update_sql);
            $update_stmt->execute([$user['id']]);

            return $user;
        }

        return false;
    } catch (PDOException $e) {
        error_log("Authentication error: " . $e->getMessage());
        return false;
    }
}

/**
 * Create admin session
 */
function createAdminSession($admin_id)
{
    global $pdo;

    try {
        // Generate session token
        $session_token = bin2hex(random_bytes(32));
        $expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));

        // Clean up old sessions for this user
        $cleanup_sql = "DELETE FROM admin_sessions WHERE admin_id = ? OR expires_at < NOW()";
        $cleanup_stmt = $pdo->prepare($cleanup_sql);
        $cleanup_stmt->execute([$admin_id]);

        // Insert new session
        $sql = "INSERT INTO admin_sessions (admin_id, session_token, expires_at) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$admin_id, $session_token, $expires_at]);

        return $session_token;
    } catch (PDOException $e) {
        error_log("Session creation error: " . $e->getMessage());
        return false;
    }
}

/**
 * Validate admin session
 */
function validateAdminSession($session_token)
{
    global $pdo;

    try {
        $sql = "SELECT au.id, au.username, au.email, au.full_name
                FROM admin_sessions ads
                JOIN admin_users au ON ads.admin_id = au.id
                WHERE ads.session_token = ? AND ads.expires_at > NOW() AND au.is_active = 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$session_token]);

        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Session validation error: " . $e->getMessage());
        return false;
    }
}

/**
 * Destroy admin session
 */
function destroyAdminSession($session_token)
{
    global $pdo;

    try {
        $sql = "DELETE FROM admin_sessions WHERE session_token = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$session_token]);

        return true;
    } catch (PDOException $e) {
        error_log("Session destruction error: " . $e->getMessage());
        return false;
    }
}

// Services Management Functions

/**
 * Get all services for admin
 */
function getServicesAdmin()
{
    global $pdo;

    try {
        $sql = "SELECT * FROM services ORDER BY name";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching services: " . $e->getMessage());
        return [];
    }
}

/**
 * Create new service
 */
function createService($name, $description, $duration, $category = 'appointment')
{
    global $pdo;

    try {
        $sql = "INSERT INTO services (name, description, duration, category) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $description, $duration, $category]);

        return ['success' => true, 'message' => 'Service created successfully!', 'id' => $pdo->lastInsertId()];
    } catch (PDOException $e) {
        error_log("Error creating service: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to create service.'];
    }
}

/**
 * Update service
 */
function updateService($id, $name, $description, $duration, $category = 'appointment', $is_active = true)
{
    global $pdo;

    try {
        $sql = "UPDATE services 
                SET name = ?, description = ?, duration = ?, category = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $description, $duration, $category, $is_active ? 1 : 0, $id]);

        return ['success' => true, 'message' => 'Service updated successfully!'];
    } catch (PDOException $e) {
        error_log("Error updating service: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to update service.'];
    }
}

/**
 * Get service by ID
 */
function getServiceById($id)
{
    global $pdo;

    try {
        $sql = "SELECT * FROM services WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error fetching service: " . $e->getMessage());
        return null;
    }
}

/**
 * Delete service
 */
function deleteService($id)
{
    global $pdo;

    try {
        $sql = "DELETE FROM services WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);

        return ['success' => true, 'message' => 'Service deleted successfully!'];
    } catch (PDOException $e) {
        error_log("Error deleting service: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to delete service.'];
    }
}

// Service Schedules Management Functions

/**
 * Get all service schedules for admin
 */
function getServiceSchedulesAdmin($limit = null)
{
    global $pdo;

    $sql = "SELECT ss.*, s.name as service_name, 
                   COUNT(a.id) as total_appointments,
                   SUM(CASE WHEN a.status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_appointments
            FROM service_schedules ss
            JOIN services s ON ss.service_id = s.id
            LEFT JOIN appointments a ON ss.id = a.service_schedule_id
            GROUP BY ss.id, s.name
            ORDER BY ss.schedule_date DESC, ss.start_time";

    if ($limit) {
        $sql .= " LIMIT $limit";
    }

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching service schedules: " . $e->getMessage());
        return [];
    }
}

/**
 * Create service schedule
 */
function createServiceSchedule($service_id, $schedule_date, $start_time, $end_time, $max_appointments, $notes = '')
{
    global $pdo;

    try {
        $sql = "INSERT INTO service_schedules (service_id, schedule_date, start_time, end_time, max_appointments, notes) 
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$service_id, $schedule_date, $start_time, $end_time, $max_appointments, $notes]);

        return ['success' => true, 'message' => 'Service schedule created successfully!', 'id' => $pdo->lastInsertId()];
    } catch (PDOException $e) {
        error_log("Error creating service schedule: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to create service schedule.'];
    }
}

/**
 * Update service schedule
 */
function updateServiceSchedule($id, $service_id, $schedule_date, $start_time, $end_time, $max_appointments, $notes = '', $is_active = true)
{
    global $pdo;

    try {
        $sql = "UPDATE service_schedules 
                SET service_id = ?, schedule_date = ?, start_time = ?, end_time = ?, max_appointments = ?, notes = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$service_id, $schedule_date, $start_time, $end_time, $max_appointments, $notes, $is_active ? 1 : 0, $id]);

        return ['success' => true, 'message' => 'Service schedule updated successfully!'];
    } catch (PDOException $e) {
        error_log("Error updating service schedule: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to update service schedule.'];
    }
}

/**
 * Get service schedule by ID
 */
function getServiceScheduleById($id)
{
    global $pdo;

    try {
        $sql = "SELECT ss.*, s.name as service_name
                FROM service_schedules ss
                JOIN services s ON ss.service_id = s.id
                WHERE ss.id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error fetching service schedule: " . $e->getMessage());
        return null;
    }
}

// Appointments Management Functions

/**
 * Get all appointments for admin
 */
function getAppointmentsAdmin($limit = 50, $offset = 0, $status = null)
{
    global $pdo;

    $sql = "SELECT a.*, s.name as service_name, ss.schedule_date, ss.start_time, ss.end_time
            FROM appointments a
            JOIN service_schedules ss ON a.service_schedule_id = ss.id
            JOIN services s ON ss.service_id = s.id";

    $params = [];

    if ($status) {
        $sql .= " WHERE a.status = ?";
        $params[] = $status;
    }

    $sql .= " ORDER BY ss.schedule_date DESC, a.appointment_time DESC
              LIMIT $limit OFFSET $offset";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching appointments: " . $e->getMessage());
        return [];
    }
}

/**
 * Update appointment status
 */
function updateAppointmentStatus($id, $status, $notes = '')
{
    global $pdo;

    try {
        $sql = "UPDATE appointments 
                SET status = ?, notes = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$status, $notes, $id]);

        return ['success' => true, 'message' => 'Appointment status updated successfully!'];
    } catch (PDOException $e) {
        error_log("Error updating appointment: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to update appointment.'];
    }
}

/**
 * Get appointment by ID
 */
function getAppointmentById($id)
{
    global $pdo;

    try {
        $sql = "SELECT a.*, s.name as service_name, ss.schedule_date, ss.start_time, ss.end_time
                FROM appointments a
                JOIN service_schedules ss ON a.service_schedule_id = ss.id
                JOIN services s ON ss.service_id = s.id
                WHERE a.id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error fetching appointment: " . $e->getMessage());
        return null;
    }
}

/**
 * Delete appointment
 */
function deleteAppointment($id)
{
    global $pdo;

    try {
        $sql = "DELETE FROM appointments WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);

        return ['success' => true, 'message' => 'Appointment deleted successfully!'];
    } catch (PDOException $e) {
        error_log("Error deleting appointment: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to delete appointment.'];
    }
}

/**
 * Bulk delete appointments
 */
function bulkDeleteAppointments($ids)
{
    global $pdo;

    try {
        if (empty($ids)) {
            return ['success' => false, 'message' => 'No appointments selected.'];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "DELETE FROM appointments WHERE id IN ($placeholders)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($ids);

        $count = $stmt->rowCount();
        return ['success' => true, 'message' => "$count appointments deleted successfully!"];
    } catch (PDOException $e) {
        error_log("Error bulk deleting appointments: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to delete appointments.'];
    }
}

/**
 * Bulk update appointment status
 */
function bulkUpdateAppointmentStatus($ids, $status)
{
    global $pdo;

    try {
        if (empty($ids)) {
            return ['success' => false, 'message' => 'No appointments selected.'];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "UPDATE appointments SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id IN ($placeholders)";
        $params = array_merge([$status], $ids);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $count = $stmt->rowCount();
        return ['success' => true, 'message' => "$count appointments updated successfully!"];
    } catch (PDOException $e) {
        error_log("Error bulk updating appointments: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to update appointments.'];
    }
}

// Announcements Management Functions

/**
 * Get all announcements for admin
 */
function getAnnouncementsAdmin($limit = null, $offset = 0)
{
    global $pdo;

    $sql = "SELECT * FROM announcements ORDER BY created_at DESC";

    if ($limit) {
        $sql .= " LIMIT $limit OFFSET $offset";
    }

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching announcements: " . $e->getMessage());
        return [];
    }
}

/**
 * Create new announcement
 */
function createAnnouncement($title, $content, $announcement_date, $is_featured = false)
{
    global $pdo;

    try {
        $sql = "INSERT INTO announcements (title, content, announcement_date, is_featured) 
                VALUES (?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$title, $content, $announcement_date, $is_featured ? 1 : 0]);

        return ['success' => true, 'message' => 'Announcement created successfully!', 'id' => $pdo->lastInsertId()];
    } catch (PDOException $e) {
        error_log("Error creating announcement: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to create announcement.'];
    }
}

/**
 * Update announcement
 */
function updateAnnouncement($id, $title, $content, $announcement_date, $is_featured = false, $is_active = true)
{
    global $pdo;

    try {
        $sql = "UPDATE announcements 
                SET title = ?, content = ?, announcement_date = ?, is_featured = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$title, $content, $announcement_date, $is_featured ? 1 : 0, $is_active ? 1 : 0, $id]);

        return ['success' => true, 'message' => 'Announcement updated successfully!'];
    } catch (PDOException $e) {
        error_log("Error updating announcement: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to update announcement.'];
    }
}

/**
 * Delete announcement
 */
function deleteAnnouncement($id)
{
    global $pdo;

    try {
        $sql = "DELETE FROM announcements WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);

        return ['success' => true, 'message' => 'Announcement deleted successfully!'];
    } catch (PDOException $e) {
        error_log("Error deleting announcement: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to delete announcement.'];
    }
}

/**
 * Get announcement by ID
 */
function getAnnouncementById($id)
{
    global $pdo;

    try {
        $sql = "SELECT * FROM announcements WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error fetching announcement: " . $e->getMessage());
        return null;
    }
}

/**
 * Bulk delete announcements
 */
function bulkDeleteAnnouncements($ids)
{
    global $pdo;

    try {
        if (empty($ids)) {
            return ['success' => false, 'message' => 'No announcements selected.'];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "DELETE FROM announcements WHERE id IN ($placeholders)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($ids);

        $count = $stmt->rowCount();
        return ['success' => true, 'message' => "$count announcements deleted successfully!"];
    } catch (PDOException $e) {
        error_log("Error bulk deleting announcements: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to delete announcements.'];
    }
}

/**
 * Bulk toggle announcement status
 */
function bulkToggleAnnouncementStatus($ids, $is_active)
{
    global $pdo;

    try {
        if (empty($ids)) {
            return ['success' => false, 'message' => 'No announcements selected.'];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "UPDATE announcements SET is_active = ?, updated_at = CURRENT_TIMESTAMP WHERE id IN ($placeholders)";
        $params = array_merge([$is_active ? 1 : 0], $ids);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $count = $stmt->rowCount();
        $status = $is_active ? 'activated' : 'deactivated';
        return ['success' => true, 'message' => "$count announcements $status successfully!"];
    } catch (PDOException $e) {
        error_log("Error bulk updating announcements: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to update announcements.'];
    }
}

// Admin Users Management Functions

/**
 * Get all admin users
 */
function getAdminUsers()
{
    global $pdo;

    try {
        $sql = "SELECT id, username, email, full_name, is_active, last_login, created_at FROM admin_users ORDER BY full_name";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching admin users: " . $e->getMessage());
        return [];
    }
}

/**
 * Create new admin user
 */
function createAdminUser($username, $email, $password, $full_name)
{
    global $pdo;

    try {
        // Check if username or email already exists
        $check_sql = "SELECT id FROM admin_users WHERE username = ? OR email = ?";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute([$username, $email]);

        if ($check_stmt->fetch()) {
            return ['success' => false, 'message' => 'Username or email already exists.'];
        }

        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO admin_users (username, email, password_hash, full_name) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username, $email, $password_hash, $full_name]);

        return ['success' => true, 'message' => 'Admin user created successfully!', 'id' => $pdo->lastInsertId()];
    } catch (PDOException $e) {
        error_log("Error creating admin user: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to create admin user.'];
    }
}

/**
 * Update admin user
 */
function updateAdminUser($id, $username, $email, $full_name, $is_active = true)
{
    global $pdo;

    try {
        // Check if username or email already exists (excluding current user)
        $check_sql = "SELECT id FROM admin_users WHERE (username = ? OR email = ?) AND id != ?";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute([$username, $email, $id]);

        if ($check_stmt->fetch()) {
            return ['success' => false, 'message' => 'Username or email already exists.'];
        }

        $sql = "UPDATE admin_users 
                SET username = ?, email = ?, full_name = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username, $email, $full_name, $is_active ? 1 : 0, $id]);

        return ['success' => true, 'message' => 'Admin user updated successfully!'];
    } catch (PDOException $e) {
        error_log("Error updating admin user: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to update admin user.'];
    }
}

/**
 * Update admin password
 */
function updateAdminPassword($id, $new_password)
{
    global $pdo;

    try {
        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);

        $sql = "UPDATE admin_users SET password_hash = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$password_hash, $id]);

        return ['success' => true, 'message' => 'Password updated successfully!'];
    } catch (PDOException $e) {
        error_log("Error updating password: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to update password.'];
    }
}

/**
 * Get admin user by ID
 */
function getAdminUserById($id)
{
    global $pdo;

    try {
        $sql = "SELECT id, username, email, full_name, is_active, last_login, created_at FROM admin_users WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error fetching admin user: " . $e->getMessage());
        return null;
    }
}

/**
 * Delete admin user
 */
function deleteAdminUser($id)
{
    global $pdo;

    try {
        // Don't allow deleting the last admin
        $count = $pdo->query("SELECT COUNT(*) FROM admin_users WHERE is_active = 1")->fetchColumn();
        if ($count <= 1) {
            return ['success' => false, 'message' => 'Cannot delete the last active admin user.'];
        }

        $sql = "DELETE FROM admin_users WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);

        return ['success' => true, 'message' => 'Admin user deleted successfully!'];
    } catch (PDOException $e) {
        error_log("Error deleting admin user: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to delete admin user.'];
    }
}

/**
 * Bulk delete services
 */
function bulkDeleteServices($ids)
{
    global $pdo;

    try {
        if (empty($ids)) {
            return ['success' => false, 'message' => 'No services selected.'];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "DELETE FROM services WHERE id IN ($placeholders)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($ids);

        $count = $stmt->rowCount();
        return ['success' => true, 'message' => "$count services deleted successfully!"];
    } catch (PDOException $e) {
        error_log("Error bulk deleting services: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to delete services.'];
    }
}

/**
 * Bulk toggle service status
 */
function bulkToggleServiceStatus($ids, $is_active)
{
    global $pdo;

    try {
        if (empty($ids)) {
            return ['success' => false, 'message' => 'No services selected.'];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "UPDATE services SET is_active = ?, updated_at = CURRENT_TIMESTAMP WHERE id IN ($placeholders)";
        $params = array_merge([$is_active ? 1 : 0], $ids);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $count = $stmt->rowCount();
        $status = $is_active ? 'activated' : 'deactivated';
        return ['success' => true, 'message' => "$count services $status successfully!"];
    } catch (PDOException $e) {
        error_log("Error bulk updating services: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to update services.'];
    }
}

/**
 * Delete service schedule
 */
function deleteServiceSchedule($id)
{
    global $pdo;

    try {
        $sql = "DELETE FROM service_schedules WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);

        return ['success' => true, 'message' => 'Service schedule deleted successfully!'];
    } catch (PDOException $e) {
        error_log("Error deleting service schedule: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to delete service schedule.'];
    }
}

/**
 * Bulk delete service schedules
 */
function bulkDeleteServiceSchedules($ids)
{
    global $pdo;

    try {
        if (empty($ids)) {
            return ['success' => false, 'message' => 'No schedules selected.'];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "DELETE FROM service_schedules WHERE id IN ($placeholders)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($ids);

        $count = $stmt->rowCount();
        return ['success' => true, 'message' => "$count schedules deleted successfully!"];
    } catch (PDOException $e) {
        error_log("Error bulk deleting schedules: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to delete schedules.'];
    }
}

// Dashboard Statistics

/**
 * Get dashboard statistics
 */
function getDashboardStats()
{
    global $pdo;

    try {
        // Total appointments
        $total_appointments = $pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();

        // Pending appointments
        $pending_appointments = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'pending'")->fetchColumn();

        // Today's appointments
        $today_appointments = $pdo->query("SELECT COUNT(*) FROM appointments a 
                                          JOIN service_schedules ss ON a.service_schedule_id = ss.id 
                                          WHERE ss.schedule_date = CURDATE()")->fetchColumn();

        // Total services
        $total_services = $pdo->query("SELECT COUNT(*) FROM services WHERE is_active = 1")->fetchColumn();

        // Active announcements
        $active_announcements = $pdo->query("SELECT COUNT(*) FROM announcements WHERE is_active = 1")->fetchColumn();

        // Reservation statistics
        $total_reservations = $pdo->query("SELECT COUNT(*) FROM reservations")->fetchColumn();
        $pending_reservations = $pdo->query("SELECT COUNT(*) FROM reservations WHERE status = 'pending'")->fetchColumn();

        return [
            'total_appointments' => $total_appointments,
            'pending_appointments' => $pending_appointments,
            'today_appointments' => $today_appointments,
            'total_services' => $total_services,
            'active_announcements' => $active_announcements,
            'total_reservations' => $total_reservations,
            'pending_reservations' => $pending_reservations
        ];
    } catch (PDOException $e) {
        error_log("Error fetching dashboard stats: " . $e->getMessage());
        return [
            'total_appointments' => 0,
            'pending_appointments' => 0,
            'today_appointments' => 0,
            'total_services' => 0,
            'active_announcements' => 0,
            'total_reservations' => 0,
            'pending_reservations' => 0
        ];
    }
}

// Reservation Management Functions

/**
 * Get all reservations for admin
 */
function getReservationsAdmin($limit = 50, $offset = 0, $status = null)
{
    global $pdo;

    try {
        $sql = "SELECT r.*, s.name as service_name 
                FROM reservations r 
                LEFT JOIN services s ON r.service_id = s.id";

        $params = [];

        if ($status) {
            $sql .= " WHERE r.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY r.created_at DESC";

        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching reservations: " . $e->getMessage());
        return [];
    }
}

/**
 * Get reservation by ID
 */
function getReservationById($id)
{
    global $pdo;

    try {
        $sql = "SELECT r.*, s.name as service_name 
                FROM reservations r 
                LEFT JOIN services s ON r.service_id = s.id 
                WHERE r.id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error fetching reservation: " . $e->getMessage());
        return null;
    }
}

/**
 * Update reservation status
 */
function updateReservationStatus($id, $status, $notes = '')
{
    global $pdo;

    try {
        $sql = "UPDATE reservations 
                SET status = ?, notes = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$status, $notes, $id]);

        return ['success' => true, 'message' => 'Reservation status updated successfully!'];
    } catch (PDOException $e) {
        error_log("Error updating reservation status: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to update reservation status.'];
    }
}

/**
 * Delete reservation
 */
function deleteReservation($id)
{
    global $pdo;

    try {
        $sql = "DELETE FROM reservations WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);

        return ['success' => true, 'message' => 'Reservation deleted successfully!'];
    } catch (PDOException $e) {
        error_log("Error deleting reservation: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to delete reservation.'];
    }
}

/**
 * Bulk delete reservations
 */
function bulkDeleteReservations($ids)
{
    global $pdo;

    try {
        if (empty($ids)) {
            return ['success' => false, 'message' => 'No reservations selected.'];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "DELETE FROM reservations WHERE id IN ($placeholders)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($ids);

        $count = $stmt->rowCount();
        return ['success' => true, 'message' => "$count reservations deleted successfully!"];
    } catch (PDOException $e) {
        error_log("Error bulk deleting reservations: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to delete reservations.'];
    }
}

/**
 * Bulk update reservation status
 */
function bulkUpdateReservationStatus($ids, $status)
{
    global $pdo;

    try {
        if (empty($ids)) {
            return ['success' => false, 'message' => 'No reservations selected.'];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "UPDATE reservations SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id IN ($placeholders)";
        $params = array_merge([$status], $ids);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $count = $stmt->rowCount();
        return ['success' => true, 'message' => "$count reservations updated to $status successfully!"];
    } catch (PDOException $e) {
        error_log("Error bulk updating reservations: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to update reservations.'];
    }
}

/**
 * Convert reservation to appointment
 */
function convertReservationToAppointment($reservation_id, $service_schedule_id, $appointment_time)
{
    global $pdo;

    try {
        $pdo->beginTransaction();

        // Get reservation details
        $reservation = getReservationById($reservation_id);
        if (!$reservation) {
            throw new Exception('Reservation not found');
        }

        // Create appointment
        $sql = "INSERT INTO appointments (service_schedule_id, client_name, client_email, client_phone, appointment_time, status, notes) 
                VALUES (?, ?, ?, ?, ?, 'confirmed', ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $service_schedule_id,
            $reservation['client_name'],
            $reservation['email_address'],
            $reservation['contact_number'],
            $appointment_time,
            $reservation['notes']
        ]);

        $appointment_id = $pdo->lastInsertId();

        // Update reservation status and link to appointment
        $sql = "UPDATE reservations 
                SET status = 'scheduled', appointment_id = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$appointment_id, $reservation_id]);

        $pdo->commit();

        return ['success' => true, 'message' => 'Reservation converted to appointment successfully!', 'appointment_id' => $appointment_id];
    } catch (Exception $e) {
        $pdo->rollback();
        error_log("Error converting reservation to appointment: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to convert reservation to appointment.'];
    }
}

/**
 * Create new reservation (for admin use)
 */
function createReservation($service_id, $service_category, $service_subcategory, $vehai_id, $client_name, $date_of_birth, $contact_number, $email_address, $home_address, $preferred_date, $preferred_time, $notes = '')
{
    global $pdo;

    try {
        $sql = "INSERT INTO reservations (service_id, service_category, service_subcategory, vehai_id, client_name, date_of_birth, contact_number, email_address, home_address, preferred_date, preferred_time, notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $service_id,
            $service_category,
            $service_subcategory,
            $vehai_id,
            $client_name,
            $date_of_birth,
            $contact_number,
            $email_address,
            $home_address,
            $preferred_date,
            $preferred_time,
            $notes
        ]);

        return ['success' => true, 'message' => 'Reservation created successfully!', 'id' => $pdo->lastInsertId()];
    } catch (PDOException $e) {
        error_log("Error creating reservation: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to create reservation.'];
    }
}
