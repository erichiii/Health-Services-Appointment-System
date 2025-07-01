<?php
session_start();

// Include authentication functions
require_once '../includes/admin_functions.php';
require_once 'auth.php';

// Require admin login
requireAdminLogin();

// Handle logout
if (isset($_GET['logout'])) {
    adminLogout();
}

// Get current admin data
$admin = getCurrentAdmin();
$admin_username = $admin['full_name'] ?? $admin['username'] ?? 'Admin';

// Get dashboard statistics
$stats = getDashboardStats();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Village East Clinic</title>
    <link rel="stylesheet" href="../assets/layout.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Arimo:wght@400;700&family=Nunito:wght@200;400;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <div class="admin-wrapper">
        <!-- Admin Header -->
        <header class="admin-header">
            <div class="admin-header-content">
                <div class="admin-logo">
                    <h2><i class="fas fa-user-shield"></i> Village East Clinic Admin</h2>
                </div>
                <div class="admin-user-info">
                    <span>Welcome, <?php echo htmlspecialchars($admin_username); ?></span>
                    <span class="admin-role">(Administrator)</span>
                    <a href="?logout=1" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-container">
                <div class="dashboard-header">
                    <h1>Dashboard</h1>
                    <p class="dashboard-subtitle">Administrative control panel for Village East Clinic</p>
                </div>

                <div class="admin-progress-notice">
                    <div class="progress-icon">
                        <i class="fas fa-cogs"></i>
                    </div>
                    <h2>Dashboard Under Development</h2>
                    <p>We're building a comprehensive administrative interface. The dashboard will soon include:</p>
                    <ul class="admin-feature-list">
                        <li><i class="fas fa-check"></i> Services management and configuration</li>
                        <li><i class="fas fa-check"></i> Services schedule administration</li>
                        <li><i class="fas fa-check"></i> Appointment booking and management</li>
                        <li><i class="fas fa-check"></i> Announcements and content management</li>
                        <li><i class="fas fa-check"></i> Admin user management and permissions</li>
                    </ul>
                    <p>Thank you for your patience while we complete the development!</p>
                </div>

                <!-- Quick Stats Preview -->
                <div class="quick-stats">
                    <h3>Quick Overview</h3>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <i class="fas fa-stethoscope"></i>
                            <h4>Services</h4>
                            <p><?php echo number_format($stats['total_services']); ?> Active</p>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-calendar-alt"></i>
                            <h4>Today's Appointments</h4>
                            <p><?php echo number_format($stats['today_appointments']); ?> Scheduled</p>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-calendar-check"></i>
                            <h4>Total Appointments</h4>
                            <p><?php echo number_format($stats['total_appointments']); ?> All Time</p>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-clock"></i>
                            <h4>Pending Appointments</h4>
                            <p><?php echo number_format($stats['pending_appointments']); ?> Awaiting</p>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-bullhorn"></i>
                            <h4>Announcements</h4>
                            <p><?php echo number_format($stats['active_announcements']); ?> Active</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Nunito', sans-serif;
            background-color: #E1EDFA;
            min-height: 100vh;
        }

        .admin-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .admin-header {
            background: #33b6ff;
            color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .admin-header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-logo h2 {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 600;
        }

        .admin-user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .admin-role {
            opacity: 0.8;
            font-size: 0.9rem;
        }

        .logout-btn {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            transition: background 0.3s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .admin-main {
            flex: 1;
            padding: 2rem 0;
        }

        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .dashboard-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .dashboard-header h1 {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .dashboard-subtitle {
            font-size: 1.1rem;
            color: #7f8c8d;
            margin: 0;
        }

        .admin-progress-notice {
            background: white;
            border-radius: 12px;
            padding: 3rem;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 3rem;
        }

        .progress-icon {
            font-size: 3rem;
            color: #33b6ff;
            margin-bottom: 1.5rem;
        }

        .admin-progress-notice h2 {
            color: #333;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .admin-progress-notice p {
            color: #5a6c7d;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .admin-feature-list {
            list-style: none;
            padding: 0;
            margin: 2rem 0;
            text-align: left;
            display: inline-block;
        }

        .admin-feature-list li {
            color: #5a6c7d;
            margin-bottom: 0.8rem;
            display: flex;
            align-items: center;
        }

        .admin-feature-list li i {
            color: #33b6ff;
            margin-right: 0.8rem;
            font-size: 0.9rem;
        }

        .quick-stats {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .quick-stats h3 {
            color: #333;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            text-align: center;
            border-left: 4px solid #33b6ff;
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-card i {
            font-size: 2rem;
            color: #33b6ff;
            margin-bottom: 1rem;
        }

        .stat-card h4 {
            color: #333;
            margin: 0 0 0.5rem 0;
            font-weight: 600;
        }

        .stat-card p {
            color: #7f8c8d;
            margin: 0;
            font-style: italic;
        }

        @media (max-width: 768px) {
            .admin-header-content {
                padding: 1rem;
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .admin-container {
                padding: 0 1rem;
            }

            .dashboard-header h1 {
                font-size: 2rem;
            }

            .admin-progress-notice {
                padding: 2rem 1.5rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>

</html>