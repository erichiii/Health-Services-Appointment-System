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
    $page_title = 'About Us';
    $page_subtitle = 'Learn more about Village East Clinic and our commitment to healthcare';
    $page_features = [
        'Our mission and values',
        'Medical team and staff profiles',
        'Clinic history and milestones',
        'Healthcare philosophy and approach'
    ];
    
    // Include the reusable progress page
    include '../includes/in_progress.php';
?>

<?php include '../includes/footer.php'; ?>