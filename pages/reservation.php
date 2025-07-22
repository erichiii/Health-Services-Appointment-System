<?php
include '../includes/header.php';
include '../includes/db_functions.php';

// Handle form submission
$success_message = '';
$error_message = '';

// Check for success parameter (after redirect)
if (isset($_GET['success']) && $_GET['success'] == '1') {
    $success_message = 'Your reservation has been submitted successfully! We will contact you soon to confirm your appointment.';
}

$page_title = 'Book Your Appointment';
$page_subtitle = 'Schedule your healthcare appointment with ease.';

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

    // Log the submitted data for debugging
    error_log("Form submission data: " . print_r($reservation_data, true));

    // Submit reservation
    $result = submitReservation($reservation_data);

    if ($result['success']) {
        $success_message = $result['message'];
        // Clear form data after successful submission
        $_POST = [];
        // Redirect to prevent form resubmission on refresh
        $current_url = $_SERVER['REQUEST_URI'];
        // Remove any existing success parameter and add new one
        $redirect_url = strtok($current_url, '?') . '?success=1';
        if (isset($_GET['subcategory'])) {
            $redirect_url .= '&subcategory=' . urlencode($_GET['subcategory']) . '&confirmed=1#confirmation';
        }
        header("Location: $redirect_url");
        exit;
    } else {
        $error_message = $result['message'];
    }
}

// Get parameters from URL for auto-selection
$serviceType = isset($_GET['type']) ? $_GET['type'] : null;
$serviceId = isset($_GET['service_id']) ? $_GET['service_id'] : null;
$scheduleId = isset($_GET['schedule_id']) ? $_GET['schedule_id'] : null;
$selectedDate = isset($_GET['date']) ? $_GET['date'] : null;

// Set active category based on URL parameter or user selection
$activeCategory = isset($_GET['category']) ? $_GET['category'] : ($serviceType ?? 'null');
$selectedSubcategory = isset($_GET['subcategory']) ? $_GET['subcategory'] : null;

// If we have a service_id and schedule_id, get the service details
$serviceDetails = null;
if ($serviceId && $scheduleId) {
    // Get service details from database
    try {
        $db = getDbConnection();
        $stmt = $db->prepare("
            SELECT 
                s.id as service_id, 
                s.name as service_name, 
                s.category, 
                ss.schedule_date, 
                ss.start_time, 
                ss.end_time
            FROM services s
            JOIN service_schedules ss ON s.id = ss.service_id
            WHERE s.id = ? AND ss.id = ?
        ");
        $stmt->execute([$serviceId, $scheduleId]);
        $serviceDetails = $stmt->fetch(PDO::FETCH_ASSOC);

        // Map service name to subcategory code
        if ($serviceDetails) {
            // This is a simple mapping based on service name keywords
            // You may need to adjust this based on your actual service names
            $subcategoryMap = [
                'Child' => 'child-immunization',
                'Adult' => 'adult-vaccine',
                'Travel' => 'travel-vaccine',
                'Booster' => 'booster-shot',
                'Anti-Rabies' => 'anti-rabies-vaccination',
                'Community' => 'community-vaccination',
                'Senior' => 'senior-health',
                'Maternal' => 'maternal-health',
                'Diabetes' => 'diabetes-management',
                'Hypertension' => 'hypertension-monitoring',
                'Blood Pressure' => 'blood-pressure-monitoring',
                'General' => 'general-consultation',
                'Specialist' => 'specialist-referral',
                'Lab' => 'lab-tests',
                'Follow-up' => 'follow-up',
                'Dental' => 'dental-care'
            ];

            // Try to match service name to subcategory
            foreach ($subcategoryMap as $keyword => $code) {
                if (stripos($serviceDetails['service_name'], $keyword) !== false) {
                    $selectedSubcategory = $code;
                    break;
                }
            }
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
    }
}

$serviceCategories = [
    'vaccine' => [
        'title' => 'Vaccine Registration',
        'description' => 'Immunizations and vaccine services',
        'subcategories' => [
            'child-immunization' => 'Child Immunization',
            'adult-vaccine' => 'Adult Vaccine',
            'travel-vaccine' => 'Travel Vaccine',
            'booster-shot' => 'Booster Shot',
            'anti-rabies-vaccination' => 'Anti-Rabies Vaccination',
            'community-vaccination' => 'Community Vaccination',
        ]
    ],
    'program' => [
        'title' => 'Program Enrollment',
        'description' => 'Health programs and wellness plans',
        'subcategories' => [
            'senior-health' => 'Senior Citizen Health Plan',
            'maternal-health' => 'Maternal Health Program',
            'diabetes-management' => 'Diabetes Management',
            'hypertension-monitoring' => 'Hypertension Monitoring',
            'blood-pressure-monitoring' => 'Blood Pressure Monitoring',
        ],
    ],
    'general' => [
        'title' => 'General Appointment',
        'description' => 'Regular consultations and checkups',
        'subcategories' => [
            'general-consultation' => 'General Consultation',
            'specialist-referral' => 'Specialist Referral',
            'lab-tests' => 'Lab Tests',
            'follow-up' => 'Follow-up Visits',
            'dental-care' => 'Dental Care Clinic',
        ]
    ]
];

$subcategoryForms = [
    'child-immunization' => 'vaccine.php',
    'adult-vaccine' => 'vaccine.php',
    'travel-vaccine' => 'vaccine.php',
    'booster-shot' => 'vaccine.php',
    'anti-rabies-vaccination' => 'vaccine.php',
    'community-vaccination' => 'vaccine.php',
    'senior-health' => 'program-enrollment.php',
    'maternal-health' => 'program-enrollment.php',
    'diabetes-management' => 'program-enrollment.php',
    'hypertension-monitoring' => 'program-enrollment.php',
    'blood-pressure-monitoring' => 'program-enrollment.php',
    'general-consultation' => 'appointment.php',
    'specialist-referral' => 'appointment.php',
    'lab-tests' => 'appointment.php',
    'follow-up' => 'appointment.php',
    'dental-care' => 'appointment.php',
];


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

<!--Reservation Banner Section-->

<section class="cntct-banner">
    <div class="cntct-banner-overlay">
        <div class="cntct-banner-content">
            <h1><?php echo htmlspecialchars($page_title); ?></h1>
            <p><?php echo htmlspecialchars($page_subtitle); ?></p>
        </div>
    </div>
</section>

<!--
REVERT: Restoring layout to previous structure before main-aligned-container refactor.
-->

<div class="second-section">
    <div class="our-services">
        <h2>What is this reservation for?</h2>
    </div>

    <div class="aligned-container">
        <div class="category-grid" id="categoryGrid">
            <?php foreach ($serviceCategories as $categoryKey => $categoryData): ?>
                <div class="category-card <?php echo ($activeCategory === $categoryKey) ? 'active' : ''; ?> <?php echo $selectedSubcategory && in_array($selectedSubcategory, array_keys($categoryData['subcategories'])) ? 'has-selection' : ''; ?>" data-category="<?php echo $categoryKey; ?>">
                    <!-- Category Header (toggles dropdown) -->
                    <div class="category-header" id="category-<?php echo $categoryKey; ?>" tabindex="0" role="button" data-category="<?php echo $categoryKey; ?>">
                        <h3><?php echo $categoryData['title']; ?></h3>
                        <p><?php echo $categoryData['description']; ?></p>
                        <div class="dropdown-arrow">⌄</div>
                    </div>
                    <!-- Subcategory Dropdown -->
                    <div class="subcategory-dropdown" style="display: <?php echo ($activeCategory === $categoryKey) ? 'block' : 'none'; ?>;">
                        <?php foreach ($categoryData['subcategories'] as $subKey => $subName): ?>
                            <a href="?subcategory=<?php echo $subKey; ?>&confirmed=1<?php echo $selectedDate ? '&date=' . $selectedDate : ''; ?><?php echo $serviceId ? '&service_id=' . $serviceId : ''; ?><?php echo $scheduleId ? '&schedule_id=' . $scheduleId : ''; ?>#confirmation"
                                class="subcategory-item <?php echo ($selectedSubcategory === $subKey) ? 'selected' : ''; ?>">
                                <span><?php echo $subName; ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Success/Error Messages -->
        <?php if (!empty($success_message)): ?>
            <div class="success-message-box" style="margin: 2rem auto; max-width: 800px; padding: 1rem 1.5rem; background: #d1e7dd; border: 1px solid #badbcc; border-radius: 8px; color: #0a3622; font-size: 1rem; box-shadow: none;">
                <i class="fas fa-check-circle" style="color: #0a3622; margin-right: 8px;"></i><?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="error-message-box" style="margin: 2rem auto; max-width: 800px; padding: 1rem 1.5rem; background: #f8d7da; border: 1px solid #f1aeb5; border-radius: 8px; color: #58151c; font-size: 1rem; box-shadow: none;">
                <i class="fas fa-exclamation-circle" style="color: #58151c; margin-right: 8px;"></i><?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <div id="confirmation" class="form-section">
            <div class="form-container">
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

                    // Add the subcategory to the URL to ensure it's preserved
                    echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            // Add event listener to all forms to ensure subcategory is preserved
                            var forms = document.querySelectorAll('.form');
                            forms.forEach(function(form) {
                                form.addEventListener('submit', function(e) {
                                    // Check if the subcategory field exists and has a value
                                    var subcategoryField = form.querySelector('input[name=\"service_subcategory\"]');
                                    if (!subcategoryField || !subcategoryField.value) {
                                        // If not, add the subcategory from URL
                                        var hiddenField = document.createElement('input');
                                        hiddenField.type = 'hidden';
                                        hiddenField.name = 'service_subcategory';
                                        hiddenField.value = '" . htmlspecialchars($selectedSubcategory) . "';
                                        form.appendChild(hiddenField);
                                    }
                                });
                            });
                        });
                    </script>";

                    // Include the form with additional parameters
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
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<style>
    .cntct-banner {
    height: 50vh;
    min-height: 300px;
    background: url('../assets/images/contact-us.jpg') no-repeat center 30%;
    background-size: cover;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

.cntct-banner::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.5); /* Dimmed overlay */
    z-index: 1;
}

.cntct-banner-overlay {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    position: relative;
    z-index: 2;
}

.cntct-banner-content {
    max-width: 1200px;
    width: 100%;
    padding-left: 2.5rem;
    padding-right: 2.5rem;
    color: white;
    text-align: center;
    position: relative;
}

.cntct-banner-content h1,
.cntct-banner-content p {
    margin-left: 0;
}

.cntct-banner-content h1 {
    font-size: 2.3rem;
    font-weight: 600;
    margin-bottom: 1rem;
    margin-left: 0;
    color: white !important;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
    font-family: 'Poppins', Arial, sans-serif;
}

.cntct-banner-content p {
    font-size: 1.1rem;
    margin-bottom: 2rem;
    margin-left: 0;
    color: white !important;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.7);
    font-family: 'Poppins', Arial, sans-serif;
}

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

    /* Personal Information Form Section */
    .form-section {
        width: 100%;
        max-width: 1200px; 
        margin: 3rem auto 0;
        padding: 0; 
        background: #fff;
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); 
        position: relative;
        transform: none; 
        left: auto; 
        overflow: hidden;
        border-top: 4px solid #33b6ff;
    }

    .form-container {
        max-width: 100%;
        width: 100%;
        margin: 0 auto;
        padding: 2.5rem; 
        box-sizing: border-box;
    }

    /* Override form CSS from included files */
    .form-section .form {
        width: 100% !important;
        max-width: none !important;
        margin: 0 !important;
        padding: 0 !important;
        box-shadow: none !important;
        border-radius: 0 !important;
        background: transparent !important;
        border-top: none !important;
        border: none !important;
        transform: none !important;
    }

    .form-section input,
    .form-section select,
    .form-section textarea {
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
    }

    .form-section .form-row {
        display: flex;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
        width: 100%;
    }

    .form-section .form-row-vehai {
        display: flex;
        width: 48.5% !important; 
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .form-section .form-group {
        flex: 1;
        width: 100%;
        display: flex;
        flex-direction: column;
    }

    .form-section .form-group.full-width {
        flex: 1 1 100%;
        width: 100%;
    }

    .form-section fieldset {
        border: none;
        padding: 0;
        margin-bottom: 2rem;
    }

    .form-section legend {
        font-size: 1.3rem;
        font-weight: 600;
        color: #33b6ff;
        margin: 0 0 1.5rem 0;
        padding: 1rem 0;
        width: 100%;
        position: relative;
        display: block;
        border: none;
        background: transparent;
    }

    .form-section legend::after {
        display: none; /* Remove the old pseudo-element */
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

    .aligned-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 2rem;
    }
    
    /* For mobile responsiveness */
    @media (max-width: 1240px) {
        .form-section {
            margin-left: 2rem;
            margin-right: 2rem;
            max-width: none;
        }
        
        .category-grid {
            margin: 0 auto;
            max-width: none;
        }
    }

    @media (max-width: 768px) {
        .form-section {
            margin-left: 1rem;
            margin-right: 1rem;
        }

        .form-container {
            padding: 1.5rem;
        }
        
        .aligned-container {
            padding: 0 1rem;
        }

        .form-section .form-row {
            flex-direction: column;
            gap: 1rem;
        }

        .form-section .form-row-vehai {
            width: 100% !important;
            flex-direction: column;
        }
    }
</style>

<script>
    // JS for toggling category dropdowns without reload
    const categoryGrid = document.getElementById('categoryGrid');
    const cards = categoryGrid.querySelectorAll('.category-card');

    cards.forEach(card => {
        const header = card.querySelector('.category-header');
        const dropdown = card.querySelector('.subcategory-dropdown');
        header.addEventListener('click', function(e) {
            // Collapse all
            cards.forEach(c => {
                c.classList.remove('active');
                c.querySelector('.subcategory-dropdown').style.display = 'none';
            });
            // Expand this one
            card.classList.add('active');
            dropdown.style.display = 'block';
            // Optionally update URL
            const cat = card.getAttribute('data-category');
            const url = new URL(window.location);
            url.searchParams.set('category', cat);
            url.searchParams.delete('subcategory');
            url.searchParams.delete('confirmed');
            window.history.pushState({}, '', url);
        });
        // Keyboard accessibility
        header.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                header.click();
            }
        });
    });

    // Auto-select subcategory if we have service details
    <?php if ($selectedSubcategory && $serviceType): ?>
        // Scroll to confirmation section
        document.addEventListener('DOMContentLoaded', function() {
            const confirmationSection = document.getElementById('confirmation');
            if (confirmationSection) {
                confirmationSection.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    <?php endif; ?>
</script>