<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Village East Clinic</title>
    <?php 
    // Determine if we're in a subdirectory by checking if includes directory exists
    $base_path = file_exists('includes') ? '' : '../';
    $cache_version = '?v=' . time(); // Cache busting parameter
    ?>
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/layout.css<?php echo $cache_version; ?>">
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/main.css<?php echo $cache_version; ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Arimo:wght@400;700&family=Nunito:wght@200;400;600;700&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header class="clinic-header">
        <div class="header-container">
            <div class="clinic-title">
                <a href="<?php echo $base_path; ?>index.php">Village East Clinic</a>
            </div>
            <nav class="clinic-nav">
                <a href="<?php echo $base_path; ?>pages/about.php">About Us</a>
                <a href="<?php echo $base_path; ?>index.php#services-section">Services</a>
                <a href="<?php echo $base_path; ?>pages/announcements.php">Announcements</a>
                <a href="<?php echo $base_path; ?>pages/programs.php">Health Programs</a>
                <a href="<?php echo $base_path; ?>pages/contact.php">Contact Us</a>
                <div class="dropdown">
                    <a href="#" class="dropdown-toggle">Login <i class="fas fa-chevron-down"></i></a>
                    <div class="dropdown-menu">
                        <a href="<?php echo $base_path; ?>admin/login.php">Staff</a>
                    </div>
                </div>
                <a href="<?php echo $base_path; ?>pages/reservation.php" class="book-btn">Book Now</a>
            </nav>
        </div>
    </header>

    <style>
        /* Dropdown styles */
        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-toggle {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: inherit;
            cursor: pointer;
        }

        .dropdown-toggle i {
            margin-left: 5px;
            font-size: 0.8em;
            transition: transform 0.3s ease;
        }

        .dropdown:hover .dropdown-toggle i {
            transform: rotate(180deg);
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            min-width: 120px;
            z-index: 1000;
        }

        .dropdown:hover .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-menu a {
            display: block;
            padding: 12px 16px;
            text-decoration: none;
            color: #2c3e50;
            font-size: 0.95em;
            transition: background-color 0.2s ease;
            border-radius: 6px;
        }

        .dropdown-menu a:hover {
            background-color: #f8f9fa;
            color: #33b6ff;
        }

        /* Ensure dropdown works properly with the existing nav styles */
        .clinic-nav .dropdown {
            display: inline-block;
        }

        .clinic-nav .dropdown-toggle {
            font-family: inherit;
            font-size: inherit;
            font-weight: inherit;
        }
    </style>