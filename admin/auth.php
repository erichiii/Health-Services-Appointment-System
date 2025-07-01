<?php
session_start();

// Include required files
require_once '../includes/admin_functions.php';

/**
 * Check if admin is logged in
 */
function isAdminLoggedIn()
{
    if (!isset($_SESSION['admin_token'])) {
        return false;
    }

    $admin = validateAdminSession($_SESSION['admin_token']);
    if ($admin) {
        // Update session data
        $_SESSION['admin_data'] = $admin;
        return true;
    } else {
        // Invalid session, clear it
        unset($_SESSION['admin_token']);
        unset($_SESSION['admin_data']);
        return false;
    }
}

/**
 * Require admin login - redirect to login if not authenticated
 */
function requireAdminLogin()
{
    if (!isAdminLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Get current admin data
 */
function getCurrentAdmin()
{
    if (isAdminLoggedIn()) {
        return $_SESSION['admin_data'];
    }
    return null;
}

/**
 * Admin logout
 */
function adminLogout()
{
    if (isset($_SESSION['admin_token'])) {
        destroyAdminSession($_SESSION['admin_token']);
    }

    session_destroy();
    header('Location: login.php');
    exit;
}

/**
 * Check admin permission level
 */
function hasAdminPermission($required_role = 'admin')
{
    $admin = getCurrentAdmin();
    if (!$admin) return false;

    $roles = ['staff' => 1, 'admin' => 2, 'super_admin' => 3];
    $user_level = $roles[$admin['role']] ?? 0;
    $required_level = $roles[$required_role] ?? 2;

    return $user_level >= $required_level;
}

/**
 * Get admin navigation items based on role
 */
function getAdminNavigation()
{
    $admin = getCurrentAdmin();
    if (!$admin) return [];

    $nav = [
        [
            'url' => 'dashboard.php',
            'title' => 'Dashboard',
            'icon' => 'fas fa-tachometer-alt'
        ],
        [
            'url' => 'announcements.php',
            'title' => 'Announcements',
            'icon' => 'fas fa-bullhorn'
        ],
        [
            'url' => 'services.php',
            'title' => 'Services',
            'icon' => 'fas fa-stethoscope'
        ],
        [
            'url' => 'schedules.php',
            'title' => 'Schedules',
            'icon' => 'fas fa-calendar'
        ],
        [
            'url' => 'appointments.php',
            'title' => 'Appointments',
            'icon' => 'fas fa-calendar-check'
        ]
    ];

    // Add admin-only items
    if (hasAdminPermission('super_admin')) {
        $nav[] = [
            'url' => 'users.php',
            'title' => 'Admin Users',
            'icon' => 'fas fa-users'
        ];
        $nav[] = [
            'url' => 'settings.php',
            'title' => 'Settings',
            'icon' => 'fas fa-cog'
        ];
    }

    return $nav;
}

/**
 * Generate CSRF token
 */
function generateCSRFToken()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 */
function validateCSRFToken($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Set flash message
 */
function setFlashMessage($type, $message)
{
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 */
function getFlashMessage()
{
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Display flash message HTML
 */
function displayFlashMessage()
{
    $flash = getFlashMessage();
    if ($flash) {
        $type = htmlspecialchars($flash['type']);
        $message = htmlspecialchars($flash['message']);
        echo "<div class='alert alert-{$type}'>{$message}</div>";
    }
}
