<?php
require_once '../includes/admin_layout.php';

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

// Get data for dashboard sections
$recent_appointments = getAppointmentsAdmin(10, 0); // Get 10 most recent
$recent_announcements = getAnnouncementsAdmin(10, 0); // Get 10 most recent
$upcoming_services = getServiceSchedulesAdmin(10); // Get 10 upcoming

// Filter upcoming services to only show future dates
$upcoming_services = array_filter($upcoming_services, function ($schedule) {
    return $schedule['schedule_date'] >= date('Y-m-d');
});
$upcoming_services = array_slice($upcoming_services, 0, 10);

// Calculate programs count (services with category 'program')
$services = getServicesAdmin();
$program_count = count(array_filter($services, function ($service) {
    return $service['category'] === 'program' && $service['is_active'];
}));

// Render the dashboard
renderAdminLayout('Dashboard', function () use ($stats, $recent_appointments, $recent_announcements, $upcoming_services, $program_count) {
?>
    <style>
        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .metric-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            position: relative;
        }

        .metric-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            color: white;
            font-size: 1.5rem;
        }

        .metric-number {
            font-size: 3rem;
            font-weight: 700;
            margin: 0;
            color: #333;
        }

        .metric-label {
            font-size: 0.9rem;
            font-weight: 600;
            margin: 0.5rem 0 0 0;
            color: #666;
        }

        .metric-desc {
            font-size: 0.8rem;
            color: #999;
            margin: 0.25rem 0 0 0;
        }

        .icon-appointments {
            background: #3b82f6;
        }

        .icon-today {
            background: #f59e0b;
        }

        .icon-services {
            background: #10b981;
        }

        .icon-announcements {
            background: #8b5cf6;
        }

        .dashboard-sections {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .dashboard-section {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .section-header {
            padding: 1.25rem;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-title {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .expand-btn {
            background: none;
            border: none;
            color: #33b6ff;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 50%;
            transition: all 0.2s;
        }

        .expand-btn:hover {
            background: #e0f2fe;
        }

        .section-content {
            max-height: 400px;
            overflow-y: auto;
        }

        .section-content.collapsed {
            display: none;
        }

        .section-item {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #f1f5f9;
            cursor: pointer;
            transition: background 0.2s;
        }

        .section-item:hover {
            background: #f8fafc;
        }

        .section-item:last-child {
            border-bottom: none;
        }

        .item-primary {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }

        .item-secondary {
            font-size: 0.85rem;
            color: #64748b;
        }

        .item-meta {
            font-size: 0.8rem;
            color: #94a3b8;
            margin-top: 0.25rem;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-left: 0.5rem;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-confirmed {
            background: #d1fae5;
            color: #065f46;
        }

        .status-active {
            background: #dbeafe;
            color: #1e40af;
        }

        .empty-state {
            padding: 3rem 1rem;
            text-align: center;
            color: #64748b;
        }

        .empty-state i {
            font-size: 2rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        @media (max-width: 1024px) {
            .dashboard-sections {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .metrics-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .section-header {
                padding: 1rem;
            }

            .section-item {
                padding: 0.75rem 1rem;
            }
        }
    </style>

    <div class="dashboard-container">
        <!-- Metrics Cards -->
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-icon icon-appointments">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h2 class="metric-number"><?php echo number_format($stats['total_appointments']); ?></h2>
                <p class="metric-label">Total Appointments</p>
                <p class="metric-desc">All time appointments</p>
            </div>

            <div class="metric-card">
                <div class="metric-icon icon-today">
                    <i class="fas fa-clock"></i>
                </div>
                <h2 class="metric-number"><?php echo number_format($stats['today_appointments']); ?></h2>
                <p class="metric-label">Today's Appointments</p>
                <p class="metric-desc">Scheduled for today</p>
            </div>

            <div class="metric-card">
                <div class="metric-icon icon-services">
                    <i class="fas fa-stethoscope"></i>
                </div>
                <h2 class="metric-number"><?php echo number_format($stats['total_services']); ?></h2>
                <p class="metric-label">Active Services</p>
                <p class="metric-desc">Available services</p>
            </div>

            <div class="metric-card">
                <div class="metric-icon icon-announcements">
                    <i class="fas fa-bullhorn"></i>
                </div>
                <h2 class="metric-number"><?php echo number_format($stats['active_announcements']); ?></h2>
                <p class="metric-label">Active Announcements</p>
                <p class="metric-desc">Published announcements</p>
            </div>
        </div>

        <!-- Dashboard Sections -->
        <div class="dashboard-sections">
            <!-- Recent Appointments -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h3 class="section-title">
                        <i class="fas fa-calendar-check" style="color: #33b6ff;"></i>
                        Recent Appointments
                    </h3>
                    <button class="expand-btn" onclick="toggleSection('appointments')">
                        <i class="fas fa-plus" id="appointments-icon"></i>
                    </button>
                </div>
                <div class="section-content" id="appointments-content">
                    <?php if (empty($recent_appointments)): ?>
                        <div class="empty-state">
                            <i class="fas fa-calendar-check"></i>
                            <p>No recent appointments</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recent_appointments as $appointment): ?>
                            <div class="section-item" onclick="window.location.href='appointments.php?id=<?php echo $appointment['id']; ?>'">
                                <div class="item-primary"><?php echo htmlspecialchars($appointment['client_name']); ?></div>
                                <div class="item-secondary">
                                    <?php echo date('M j, Y', strtotime($appointment['schedule_date'] ?? $appointment['created_at'])); ?>
                                    <span class="status-badge status-<?php echo $appointment['status']; ?>">
                                        <?php echo ucfirst($appointment['status']); ?>
                                    </span>
                                </div>
                                <div class="item-meta"><?php echo htmlspecialchars($appointment['service_name'] ?? 'Service'); ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Announcements -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h3 class="section-title">
                        <i class="fas fa-bullhorn" style="color: #33b6ff;"></i>
                        Announcements
                    </h3>
                    <button class="expand-btn" onclick="toggleSection('announcements')">
                        <i class="fas fa-plus" id="announcements-icon"></i>
                    </button>
                </div>
                <div class="section-content" id="announcements-content">
                    <?php if (empty($recent_announcements)): ?>
                        <div class="empty-state">
                            <i class="fas fa-bullhorn"></i>
                            <p>No recent announcements</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recent_announcements as $announcement): ?>
                            <div class="section-item" onclick="window.location.href='announcements.php?action=edit&id=<?php echo $announcement['id']; ?>'">
                                <div class="item-primary"><?php echo htmlspecialchars($announcement['title']); ?></div>
                                <div class="item-secondary">
                                    <?php echo date('M j, Y', strtotime($announcement['announcement_date'])); ?>
                                    <?php if ($announcement['is_featured']): ?>
                                        <span class="status-badge status-active">Featured</span>
                                    <?php endif; ?>
                                </div>
                                <div class="item-meta"><?php echo htmlspecialchars(substr($announcement['content'], 0, 60)); ?>...</div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Upcoming Services -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h3 class="section-title">
                        <i class="fas fa-calendar-alt" style="color: #33b6ff;"></i>
                        Upcoming Services
                    </h3>
                    <button class="expand-btn" onclick="toggleSection('services')">
                        <i class="fas fa-plus" id="services-icon"></i>
                    </button>
                </div>
                <div class="section-content" id="services-content">
                    <?php if (empty($upcoming_services)): ?>
                        <div class="empty-state">
                            <i class="fas fa-calendar-alt"></i>
                            <p>No upcoming services</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($upcoming_services as $service): ?>
                            <div class="section-item" onclick="window.location.href='schedules.php?action=edit&id=<?php echo $service['id']; ?>'">
                                <div class="item-primary"><?php echo htmlspecialchars($service['service_name']); ?></div>
                                <div class="item-secondary">
                                    <?php echo date('M j, Y', strtotime($service['schedule_date'])); ?>
                                    <span class="status-badge status-active">
                                        <?php echo $service['max_appointments']; ?> Slots
                                    </span>
                                </div>
                                <div class="item-meta">
                                    <?php echo date('g:i A', strtotime($service['start_time'])); ?> -
                                    <?php echo date('g:i A', strtotime($service['end_time'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- System Overview -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">System Overview</h3>
            </div>
            <div style="display: grid; gap: 1rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: #f8f9fa; border-radius: 6px;">
                    <span style="color: #6b7280;">Total Database Records</span>
                    <strong style="color: #333;"><?php echo number_format($stats['total_appointments'] + $stats['total_services'] + $stats['active_announcements']); ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: #f8f9fa; border-radius: 6px;">
                    <span style="color: #6b7280;">Active Services</span>
                    <strong style="color: #333;"><?php echo number_format($stats['total_services']); ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: #f8f9fa; border-radius: 6px;">
                    <span style="color: #6b7280;">System Status</span>
                    <strong style="color: #10b981;">
                        <i class="fas fa-circle" style="font-size: 0.6rem; margin-right: 0.5rem;"></i>
                        Online
                    </strong>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleSection(sectionName) {
            const content = document.getElementById(sectionName + '-content');
            const icon = document.getElementById(sectionName + '-icon');

            if (content.classList.contains('collapsed')) {
                content.classList.remove('collapsed');
                icon.className = 'fas fa-minus';
            } else {
                content.classList.add('collapsed');
                icon.className = 'fas fa-plus';
            }
        }
    </script>
<?php
});
?>