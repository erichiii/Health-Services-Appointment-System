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
$page_title = 'Book Appointment';
$page_subtitle = 'Schedule your healthcare appointment with ease';
$page_features = [
    'Online appointment booking system',
    'Real-time availability calendar',
    'Service selection and scheduling',
    'Appointment confirmation and reminders'
];

// Include the reusable progress page
include '../includes/in_progress.php';
?>

<?php include '../includes/footer.php'; ?>