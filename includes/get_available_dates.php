<?php
require_once 'db_functions.php';

header('Content-Type: application/json');

// Get the category and subcategory from the request
$category = $_GET['category'] ?? '';
$subcategory = $_GET['subcategory'] ?? '';

if (!$category || !$subcategory) {
    echo json_encode([]);
    exit;
}

try {
    // Get available dates for the subcategory
    $availableDates = getAvailableDatesForService($category, $subcategory);
    echo json_encode($availableDates);
} catch (Exception $e) {
    error_log("Error in get_available_dates.php: " . $e->getMessage());
    echo json_encode([]);
}
?>
