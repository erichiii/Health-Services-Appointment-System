<?php
// Adjust the path for includes since we're in a subdirectory
$base_path = '../';

// Update CSS path for subdirectory
ob_start();
include '../includes/header.php';
$header = ob_get_clean();

// Fix CSS paths for subdirectory
$header = str_replace('href="assets/', 'href="../assets/', $header);
echo $header;

// Set page-specific content
$page_title = 'Contact Us';
$page_subtitle = 'Get in touch with our healthcare team';
$page_features = [
    'Interactive contact form',
    'Location and directions',
    'Office hours and availability',
    'Emergency contact information'
];

// Include the reusable progress page
include '../includes/in_progress.php';
?>

<?php include '../includes/footer.php'; ?>