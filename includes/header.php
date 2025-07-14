<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Village East Clinic</title>
    <?php 
    // Determine if we're in a subdirectory by checking if includes directory exists
    $base_path = file_exists('includes') ? '' : '../';
    ?>
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/layout.css">
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Arimo:wght@400;700&family=Nunito:wght@200;400;600;700&display=swap" rel="stylesheet">
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
                <a href="<?php echo $base_path; ?>pages/reservation.php" class="book-btn">Book Now</a>
            </nav>
        </div>
    </header>