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
$page_title = 'Announcements';
$page_subtitle = 'Stay updated with our latest news and health information';
$page_features = [
    'Latest clinic announcements',
    'Health tips and articles',
    'Important notifications',
    'Community health updates'
];

// Include the reusable progress page
include '../includes/in_progress.php';
?>

<?php include '../includes/footer.php'; ?>