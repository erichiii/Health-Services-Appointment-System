<?php
require_once '../includes/admin_layout.php';

// Render the page
renderAdminLayout('Admin Users Management', function () {
?>
    <div style="text-align: center; padding: 4rem; color: #6b7280;">
        <i class="fas fa-users" style="font-size: 4rem; margin-bottom: 1.5rem; opacity: 0.5;"></i>
        <h2 style="margin: 0 0 1rem 0; color: #333;">Admin Users Management</h2>
        <p style="margin: 0 0 2rem 0; max-width: 600px; margin-left: auto; margin-right: auto;">
            This page will allow you to manage admin users, create new admin accounts, edit user details,
            and control access permissions. The same CRUD pattern from announcements and services will be implemented here.
        </p>
        <div style="display: flex; gap: 1rem; justify-content: center;">
            <a href="announcements.php" class="btn btn-primary">
                <i class="fas fa-bullhorn"></i> View Announcements Example
            </a>
            <a href="services.php" class="btn btn-secondary">
                <i class="fas fa-stethoscope"></i> View Services Example
            </a>
        </div>
    </div>
<?php
});
?>