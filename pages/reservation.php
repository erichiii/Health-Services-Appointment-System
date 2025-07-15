<?php
include '../includes/header.php';
include '../includes/db_functions.php';

// Handle form submission
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Prepare data for reservation submission
    $reservation_data = [
        'service_category' => $_POST['service_category'] ?? '',
        'service_subcategory' => $_POST['service_subcategory'] ?? '',
        'vehai_id' => $_POST['vehaiID'] ?? null,
        'client_name' => $_POST['fullname'] ?? '',
        'date_of_birth' => $_POST['birthdate'] ?? '',
        'contact_number' => $_POST['contact'] ?? '',
        'email_address' => $_POST['email'] ?? null,
        'home_address' => $_POST['address'] ?? '',
        'preferred_date' => $_POST['preferred_date'] ?? '',
        'preferred_time' => $_POST['preferred_time'] ?? '',
        'notes' => $_POST['notes'] ?? null
    ];

    // Submit reservation
    $result = submitReservation($reservation_data);

    if ($result['success']) {
        $success_message = $result['message'];
        // Clear form data after successful submission
        $_POST = [];
    } else {
        $error_message = $result['message'];
    }
}

// Get active services from database
$activeServices = getActiveServices();

// Organize services by category
$serviceCategories = [
    'vaccine' => [
        'title' => 'Vaccine Registration',
        'description' => 'Immunizations and vaccine services',
        'subcategories' => []
    ],
    'program' => [
        'title' => 'Program Enrollment',
        'description' => 'Health programs and wellness plans',
        'subcategories' => []
    ],
    'appointment' => [
        'title' => 'General Appointment',
        'description' => 'Regular consultations and checkups',
        'subcategories' => []
    ]
];

// Populate subcategories from database
foreach ($activeServices as $service) {
    $category = $service['category'];
    if (isset($serviceCategories[$category])) {
        // Create a subcategory key from the service name
        $subcategoryKey = strtolower(str_replace([' ', '-'], ['-', '-'], $service['name']));
        $serviceCategories[$category]['subcategories'][$subcategoryKey] = $service['name'];
    }
}

// Map subcategories to form files
$subcategoryForms = [
    // Vaccine forms
    'anti-rabies-vaccination-campaign' => 'vaccine.php',
    'child-immunization-campaign' => 'vaccine.php',
    'adult-vaccine-drive' => 'vaccine.php',
    'travel-vaccine-clinic' => 'vaccine.php',
    'covid-19-booster-campaign' => 'vaccine.php',
    'community-vaccination-drive' => 'vaccine.php',
    
    // Program forms
    'senior-citizen-health-plan' => 'program-enrollment.php',
    'maternal-health-program' => 'program-enrollment.php',
    'diabetes-management-program' => 'program-enrollment.php',
    'hypertension-monitoring-program' => 'program-enrollment.php',
    'blood-pressure-monitoring-program' => 'program-enrollment.php',
    
    // Appointment forms
    'free-health-checkup-day' => 'appointment.php',
    'specialist-consultation-day' => 'appointment.php',
    'dental-care-clinic' => 'appointment.php',
    'health-screening-event' => 'appointment.php'
];

$activeCategory = isset($_GET['category']) ? $_GET['category'] : 'null';
$selectedSubcategory = isset($_GET['subcategory']) ? $_GET['subcategory'] : null;

$selectedCategoryName = "";
$selectedSubcategoryName = "";
if ($selectedSubcategory) {
    foreach ($serviceCategories as $catKey => $catData) {
        if (isset($catData['subcategories'][$selectedSubcategory])) {
            $selectedCategoryName = $catData['title'];
            $selectedSubcategoryName = $catData['subcategories'][$selectedSubcategory];
            break;
        }
    }
}
?>

<div class="hero-section">
    <div class="hero-section-content">
        <h1>Book Your Appointment</h1>
        <p>Schedule your healthcare appointment with ease.</p>
    </div>
</div>

<div class="second-section">
    <div class="our-services">
        <h2>What is this reservation for?</h2>
    </div>

    <div class="category-grid">
        <?php foreach ($serviceCategories as $categoryKey => $categoryData): ?>
            <?php if (!empty($categoryData['subcategories'])): ?>
                <!-- <?php echo ucfirst($categoryKey); ?> Card -->
                <div class="category-card <?php echo ($activeCategory === $categoryKey) ? 'active' : ''; ?> <?php echo $selectedSubcategory && in_array($selectedSubcategory, array_keys($categoryData['subcategories'])) ? 'has-selection' : ''; ?>" data-category="<?php echo $categoryKey; ?>">

                    <!-- Category Header (toggles dropdown) -->
                    <a href="?category=<?php echo ($activeCategory === $categoryKey) ? '' : $categoryKey; ?>"
                        class="category-header" id="category-<?php echo $categoryKey; ?>">
                        <h3><?php echo $categoryData['title']; ?></h3>
                        <p><?php echo $categoryData['description']; ?></p>
                        <div class="dropdown-arrow"><?php echo ($activeCategory === $categoryKey) ? '⌄' : '⌄'; ?></div>
                    </a>

                    <!-- Subcategory Dropdown -->
                    <?php if ($activeCategory === $categoryKey): ?>
                        <div class="subcategory-dropdown">
                            <?php foreach ($categoryData['subcategories'] as $subKey => $subName): ?>
                                <a href="?subcategory=<?php echo $subKey; ?>&confirmed=1#confirmation"
                                    class="subcategory-item <?php echo ($selectedSubcategory === $subKey) ? 'selected' : ''; ?>">
                                    <span><?php echo $subName; ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <!-- Success/Error Messages -->
    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success" style="margin: 2rem auto; max-width: 800px; padding: 1rem; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; color: #155724;">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-error" style="margin: 2rem auto; max-width: 800px; padding: 1rem; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; color: #721c24;">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <div id="confirmation" class="form-section" style="margin-top: 3rem; max-width: 800px; margin-left: auto; margin-right: auto;">
        <?php
        if ($selectedSubcategory && isset($subcategoryForms[$selectedSubcategory])) {
            $formFile = $subcategoryForms[$selectedSubcategory];
            $formPath = __DIR__ . '/' . $formFile;

            if (file_exists($formPath)) {
                // Pass the selected subcategory and category to the form
                $current_category = '';
                foreach ($serviceCategories as $catKey => $catData) {
                    if (isset($catData['subcategories'][$selectedSubcategory])) {
                        $current_category = $catKey;
                        break;
                    }
                }
                include $formPath;
            } else {
                echo "<p>Sorry, the form for this service is currently unavailable.</p>";
            }
        } elseif (isset($_GET['confirmed'])) {
            echo "<p style='text-align: center; padding: 2rem;'>Please select a valid subcategory.</p>";
        }
        ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<style>
    .hero-section-content h1 {
        font-size: 2.5rem;
        color: #2c3e50;
        margin-bottom: 0.5rem;
        font-family: 'Nunito', sans-serif;
        font-weight: 700;
    }

    .hero-section-content p {
        font-size: 1.1rem;
        color: #7f8c8d;
        margin-bottom: 0;
    }

    /* Category Grid */
    .category-grid {
        display: flex;
        justify-content: center;
        gap: 2rem;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 2rem;
        align-items: flex-start;
        /* Align cards to top */
    }

    /* Category Cards */
    .category-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        overflow: hidden;
        position: relative;
        min-width: 370px;
        max-width: 400px;
        flex: 1;
        /* Fixed height for inactive cards */
        min-height: 150px;
    }

    .category-card:hover {
        transform: translateY(-5px);
        border: 2px solid #33b6ff;
        box-shadow: 0 8px 25px rgba(51, 182, 255, 0.2);
    }

    /* Only active cards can expand beyond min-height */
    .category-card.active {
        min-height: auto;
    }

    .category-card.active:hover {
        border-color: #1b72a1;
    }

    /* Category Header */
    .category-header {
        text-align: center;
        position: relative;
        display: block;
        text-decoration: none;
        color: #2c3e50;
        border-bottom: 1px solid #e9ecef;
        /* Fixed height to maintain consistency */
        min-height: 150px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .category-header:hover {
        text-decoration: none;
        color: #2c3e50;
    }

    .category-header h3 {
        font-family: 'Arimo', sans-serif;
        font-weight: 600;
        font-size: 1.3rem;
        color: #2c3e50;
    }

    .category-header p {
        margin: 0;
        color: #6c757d;
        font-size: 0.9rem;
        line-height: 1.4;
    }

    .dropdown-arrow {
        position: absolute;
        bottom: 1rem;
        left: 50%;
        transform: translateX(-50%);
        font-size: 1.2rem;
        color: #6c757d;
        transition: transform 0.3s ease;
    }

    .category-card.active .dropdown-arrow {
        transform: translateX(-50%) rotate(180deg);
    }

    /* Subcategory Dropdown */
    .subcategory-dropdown {
        background: white;
        animation: slideDown 0.3s ease;
        /* Ensure dropdown doesn't affect other cards */
        position: relative;
        z-index: 10;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
            max-height: 0;
        }

        to {
            opacity: 1;
            transform: translateY(0);
            max-height: 300px;
        }
    }

    .subcategory-item {
        padding: 1rem 2rem;
        display: block;
        text-decoration: none;
        color: #495057;
        font-family: 'Arimo', sans-serif;
        font-weight: 500;
        transition: all 0.2s ease;
        border-bottom: 1px solid #f1f5f9;
    }

    .subcategory-item:last-child {
        border-bottom: none;
    }

    .subcategory-item:hover {
        background: #33b6ff;
        color: white;
        text-decoration: none;
    }

    .subcategory-item.selected {
        background-color: #5bc0de;
        color: white;
        font-weight: 600;
    }

    .subcategory-item.selected:hover {
        background-color: #46b8da;
        color: white;
    }

    /* Inactive cards maintain fixed size */
    .category-card:not(.active) {
        height: 150px;
        overflow: hidden;
    }

    .category-card:not(.active) .category-header {
        height: 100%;
        border-bottom: none;
    }

    /* Responsive Design */
    @media (max-width: 992px) {
        .category-grid {
            flex-direction: column;
            align-items: center;
            gap: 1.5rem;
        }

        .category-card {
            min-width: 280px;
            max-width: 400px;
        }
    }

    @media (max-width: 768px) {
        .category-grid {
            padding: 0 1rem;
        }

        .category-card {
            min-width: 250px;
            max-width: 100%;
        }

        .category-header {
            padding: 1.5rem;
        }

        .our-services h2 {
            font-size: 1.5rem;
        }
    }

    @media (max-width: 480px) {
        .second-section {
            padding: 2rem 0;
        }

        .category-header {
            padding: 1.2rem;
        }

        .category-header h3 {
            font-size: 1.1rem;
        }

        .subcategory-item {
            padding: 0.8rem 1.5rem;
        }
    }

    /* Smooth scroll for anchor links */
    html {
        scroll-behavior: smooth;
    }
</style>