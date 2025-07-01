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
$page_title = 'Health Programs';
$page_subtitle = 'Comprehensive healthcare programs for our community';
$page_features = [
    'Vaccination programs and schedules',
    'Health screening initiatives',
    'Wellness education workshops',
    'Community health outreach programs'
];

// Include the reusable progress page
include '../includes/in_progress.php';
?>

<?php include '../includes/footer.php'; ?>