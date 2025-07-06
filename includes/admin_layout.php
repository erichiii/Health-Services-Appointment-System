<?php
// Include authentication and admin functions
require_once __DIR__ . '/admin_functions.php';
require_once __DIR__ . '/../admin/auth.php';

// Require admin login
requireAdminLogin();

// Get current admin data
$admin = getCurrentAdmin();
$admin_username = $admin['full_name'] ?? $admin['username'] ?? 'Admin';

// Handle logout
if (isset($_GET['logout'])) {
    adminLogout();
}

// Get current page for navigation highlighting
$current_page = basename($_SERVER['PHP_SELF']);

// Admin navigation items
$nav_items = [
    ['url' => 'dashboard.php', 'title' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt'],
    ['url' => 'announcements.php', 'title' => 'Announcements', 'icon' => 'fas fa-bullhorn'],
    ['url' => 'services.php', 'title' => 'Services', 'icon' => 'fas fa-stethoscope'],
    ['url' => 'schedules.php', 'title' => 'Schedules', 'icon' => 'fas fa-calendar'],
    ['url' => 'appointments.php', 'title' => 'Appointments', 'icon' => 'fas fa-calendar-check'],
    ['url' => 'users.php', 'title' => 'Admin Users', 'icon' => 'fas fa-users'],
    ['url' => 'settings.php', 'title' => 'Settings', 'icon' => 'fas fa-cog'],
];

function renderAdminLayout($page_title, $content_callback)
{
    global $admin_username, $nav_items, $current_page;
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($page_title); ?> - Village East Clinic Admin</title>
        <link rel="stylesheet" href="../assets/layout.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Arimo:wght@400;700&family=Nunito:wght@200;400;600;700&display=swap" rel="stylesheet">
        <style>
            body {
                margin: 0;
                padding: 0;
                font-family: 'Nunito', sans-serif;
                background-color: #E1EDFA;
                min-height: 100vh;
            }

            .admin-container {
                display: flex;
                min-height: 100vh;
            }

            /* Sidebar Styles */
            .admin-sidebar {
                width: 280px;
                background: #ffffff;
                box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
                transition: transform 0.3s ease;
                position: fixed;
                height: 100vh;
                z-index: 1000;
                overflow-y: auto;
            }

            .admin-sidebar.collapsed {
                transform: translateX(-280px);
            }

            .sidebar-header {
                background: #33b6ff;
                color: white;
                padding: 1.5rem;
                text-align: center;
            }

            .sidebar-header h2 {
                margin: 0;
                font-size: 1.2rem;
                font-weight: 600;
            }

            .sidebar-nav {
                padding: 1rem 0;
            }

            .nav-item {
                display: block;
                color: #5a6c7d;
                text-decoration: none;
                padding: 1rem 1.5rem;
                transition: all 0.3s;
                border-left: 3px solid transparent;
            }

            .nav-item:hover {
                background: #f8f9fa;
                color: #33b6ff;
                border-left-color: #33b6ff;
            }

            .nav-item.active {
                background: #e8f4ff;
                color: #33b6ff;
                border-left-color: #33b6ff;
                font-weight: 600;
            }

            .nav-item i {
                margin-right: 0.8rem;
                width: 18px;
                text-align: center;
            }

            .logout-nav {
                border-top: 1px solid #e9ecef;
                margin-top: 1rem;
                padding-top: 1rem;
            }

            .logout-nav .nav-item {
                color: #dc3545;
            }

            .logout-nav .nav-item:hover {
                background: #fdf2f2;
                border-left-color: #dc3545;
            }

            /* Main Content Styles */
            .admin-main {
                flex: 1;
                margin-left: 280px;
                transition: margin-left 0.3s ease;
                min-height: 100vh;
                display: flex;
                flex-direction: column;
            }

            .admin-main.expanded {
                margin-left: 0;
            }

            /* Header Styles */
            .admin-header {
                background: white;
                padding: 1rem 2rem;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .header-left {
                display: flex;
                align-items: center;
                gap: 1rem;
            }

            .sidebar-toggle {
                background: none;
                border: none;
                font-size: 1.2rem;
                color: #5a6c7d;
                cursor: pointer;
                padding: 0.5rem;
                border-radius: 4px;
                transition: background 0.3s;
            }

            .sidebar-toggle:hover {
                background: #f8f9fa;
                color: #33b6ff;
            }

            .page-title {
                font-size: 1.5rem;
                color: #333;
                margin: 0;
                font-weight: 600;
            }

            .header-right {
                display: flex;
                align-items: center;
                gap: 1rem;
            }

            .admin-info {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                color: #5a6c7d;
            }

            .admin-avatar {
                width: 32px;
                height: 32px;
                background: #33b6ff;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-weight: 600;
                font-size: 0.9rem;
            }

            /* Content Area */
            .admin-content {
                flex: 1;
                padding: 2rem;
                overflow-y: auto;
            }

            /* Cards and Components */
            .card {
                background: white;
                border-radius: 12px;
                padding: 1.5rem;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                margin-bottom: 1.5rem;
            }

            .card-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 1.5rem;
                padding-bottom: 1rem;
                border-bottom: 1px solid #e9ecef;
            }

            .card-title {
                font-size: 1.25rem;
                color: #333;
                margin: 0;
                font-weight: 600;
            }

            /* Buttons */
            .btn {
                display: inline-block;
                padding: 0.75rem 1.5rem;
                border: none;
                border-radius: 8px;
                text-decoration: none;
                font-weight: 600;
                text-align: center;
                cursor: pointer;
                transition: all 0.3s;
                font-size: 0.9rem;
            }

            .btn-primary {
                background: #33b6ff;
                color: white;
            }

            .btn-primary:hover {
                background: #2ba3e8;
                transform: translateY(-1px);
            }

            .btn-secondary {
                background: #6c757d;
                color: white;
            }

            .btn-secondary:hover {
                background: #5a6268;
            }

            .btn-success {
                background: #28a745;
                color: white;
            }

            .btn-success:hover {
                background: #218838;
            }

            .btn-danger {
                background: #dc3545;
                color: white;
            }

            .btn-danger:hover {
                background: #c82333;
            }

            .btn-sm {
                padding: 0.5rem 1rem;
                font-size: 0.8rem;
            }

            /* Alert Messages */
            .alert {
                padding: 1rem;
                border-radius: 8px;
                margin-bottom: 1.5rem;
            }

            .alert-success {
                background: #d4edda;
                color: #155724;
                border: 1px solid #c3e6cb;
            }

            .alert-error {
                background: #f8d7da;
                color: #721c24;
                border: 1px solid #f5c6cb;
            }

            .alert-info {
                background: #d1ecf1;
                color: #0c5460;
                border: 1px solid #bee5eb;
            }

            /* Mobile Responsive */
            @media (max-width: 768px) {
                .admin-sidebar {
                    transform: translateX(-280px);
                }

                .admin-sidebar.show {
                    transform: translateX(0);
                }

                .admin-main {
                    margin-left: 0;
                }

                .admin-header {
                    padding: 1rem;
                }

                .page-title {
                    font-size: 1.2rem;
                }

                .admin-content {
                    padding: 1rem;
                }
            }

            /* Overlay for mobile */
            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 999;
                display: none;
            }

            .sidebar-overlay.show {
                display: block;
            }
        </style>
    </head>

    <body>
        <div class="admin-container">
            <!-- Sidebar -->
            <aside class="admin-sidebar" id="adminSidebar">
                <div class="sidebar-header">
                    <h2>VEHAI-C ADMIN</h2>
                    <p style="margin: 0.5rem 0 0 0; font-size: 0.85rem; opacity: 0.9;">Administrator Panel</p>
                </div>
                <nav class="sidebar-nav">
                    <?php foreach ($nav_items as $item): ?>
                        <a href="<?php echo $item['url']; ?>"
                            class="nav-item <?php echo $current_page === $item['url'] ? 'active' : ''; ?>">
                            <i class="<?php echo $item['icon']; ?>"></i>
                            <?php echo $item['title']; ?>
                        </a>
                    <?php endforeach; ?>

                    <div class="logout-nav">
                        <a href="?logout=1" class="nav-item">
                            <i class="fas fa-sign-out-alt"></i>
                            Logout
                        </a>
                    </div>
                </nav>
            </aside>

            <!-- Sidebar Overlay for Mobile -->
            <div class="sidebar-overlay" id="sidebarOverlay"></div>

            <!-- Main Content -->
            <main class="admin-main" id="adminMain">
                <!-- Header -->
                <header class="admin-header">
                    <div class="header-left">
                        <button class="sidebar-toggle" id="sidebarToggle">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h1 class="page-title"><?php echo htmlspecialchars($page_title); ?></h1>
                    </div>
                    <div class="header-right">
                        <div class="admin-info">
                            <div class="admin-avatar">
                                <?php echo strtoupper(substr($admin_username, 0, 1)); ?>
                            </div>
                            <span><?php echo htmlspecialchars($admin_username); ?></span>
                        </div>
                    </div>
                </header>

                <!-- Content -->
                <div class="admin-content">
                    <?php
                    // Display flash messages
                    displayFlashMessage();

                    // Call the content callback
                    $content_callback();
                    ?>
                </div>
            </main>
        </div>

        <script>
            // Sidebar toggle functionality
            const sidebarToggle = document.getElementById('sidebarToggle');
            const adminSidebar = document.getElementById('adminSidebar');
            const adminMain = document.getElementById('adminMain');
            const sidebarOverlay = document.getElementById('sidebarOverlay');

            sidebarToggle.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    // Mobile: Toggle sidebar with overlay
                    adminSidebar.classList.toggle('show');
                    sidebarOverlay.classList.toggle('show');
                } else {
                    // Desktop: Toggle sidebar collapse
                    adminSidebar.classList.toggle('collapsed');
                    adminMain.classList.toggle('expanded');
                }
            });

            // Close sidebar when clicking overlay (mobile)
            sidebarOverlay.addEventListener('click', function() {
                adminSidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
            });

            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    // Reset mobile classes on desktop
                    adminSidebar.classList.remove('show');
                    sidebarOverlay.classList.remove('show');
                }
            });

            // Confirmation dialogs for delete actions
            function confirmDelete(message = 'Are you sure you want to delete this item?') {
                return confirm(message);
            }

            // Bulk action handlers
            function toggleBulkActions() {
                const checkboxes = document.querySelectorAll('input[type="checkbox"][name="ids[]"]');
                const bulkActions = document.querySelector('.bulk-actions');
                const checkedBoxes = Array.from(checkboxes).filter(cb => cb.checked);

                if (bulkActions) {
                    bulkActions.style.display = checkedBoxes.length > 0 ? 'block' : 'none';
                }
            }

            // Select all checkbox handler
            function toggleSelectAll() {
                const selectAll = document.getElementById('select-all');
                const checkboxes = document.querySelectorAll('input[type="checkbox"][name="ids[]"]');

                if (selectAll) {
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = selectAll.checked;
                    });
                    toggleBulkActions();
                }
            }

            // Initialize checkbox handlers when page loads
            document.addEventListener('DOMContentLoaded', function() {
                const checkboxes = document.querySelectorAll('input[type="checkbox"][name="ids[]"]');
                const selectAll = document.getElementById('select-all');

                checkboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', toggleBulkActions);
                });

                if (selectAll) {
                    selectAll.addEventListener('change', toggleSelectAll);
                }
            });
        </script>
    </body>

    </html>
<?php
}
?>