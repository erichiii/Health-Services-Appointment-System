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

                    <!-- Category Header (toggles dropdown) Implemented via JavaScript -->
                    <a href="#" 
                        class="category-header" 
                        id="category-<?php echo $categoryKey; ?>"
                        data-category="<?php echo $categoryKey; ?>">
                        <h3><?php echo $categoryData['title']; ?></h3>
                        <p><?php echo $categoryData['description']; ?></p>
                        <div class="dropdown-arrow"><?php echo ($activeCategory === $categoryKey) ? '⌄' : '⌄'; ?></div>
                    </a>

                    <!-- Subcategory Dropdown -->
                    <div class="subcategory-dropdown <?php echo ($activeCategory === $categoryKey) ? 'active' : ''; ?>">
                        <?php foreach ($categoryData['subcategories'] as $subKey => $subName): ?>
                            <a href="?subcategory=<?php echo $subKey; ?>&confirmed=1#confirmation"
                                class="subcategory-item <?php echo ($selectedSubcategory === $subKey) ? 'selected' : ''; ?>">
                                <span><?php echo $subName; ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
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
        transform: translateY(-5px);
        border: 2px solid #33b6ff;
        box-shadow: 0 8px 25px rgba(51, 182, 255, 0.2);
    }

    /* Category Header */
    .category-header {
        display: block;
        padding: 2rem;
        text-decoration: none;
        color: inherit;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .category-header:hover {
        background-color: #f8f9fa;
    }

    .category-header h3 {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        color: #2c3e50;
    }

    .category-header p {
        font-size: 1rem;
        color: #7f8c8d;
        margin-bottom: 1rem;
    }

    .dropdown-arrow {
        font-size: 1.2rem;
        color: #000;
        transition: transform 0.3s ease;
        text-align: center;
        display: block;
        margin: 0 auto;
    }

    .category-card.active .dropdown-arrow {
        transform: rotate(180deg);
    }

    /* Subcategory Dropdown */
    .subcategory-dropdown {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease;
        background-color: #f8f9fa;
    }

    .subcategory-dropdown.active {
        max-height: 500px; /* Adjust based on content */
    }

    .subcategory-item {
        display: block;
        padding: 1rem 2rem;
        text-decoration: none;
        color: #2c3e50;
        border-bottom: 1px solid #e9ecef;
        transition: all 0.2s ease;
    }

    .subcategory-item:last-child {
        border-bottom: none;
    }

    .subcategory-item:hover {
        background-color: #e9ecef;
        color: #33b6ff;
    }

    .subcategory-item.selected {
        background-color: #33b6ff;
        color: white;
    }

    .subcategory-item.selected:hover {
        background-color: #1b72a1;
        color: white;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .category-grid {
            flex-direction: column;
            align-items: center;
        }

        .category-card {
            min-width: 100%;
            max-width: 100%;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get all category headers
    const categoryHeaders = document.querySelectorAll('.category-header');
    
    categoryHeaders.forEach(header => {
        header.addEventListener('click', function(e) {
            e.preventDefault();
            
            const category = this.getAttribute('data-category');
            const categoryCard = this.closest('.category-card');
            const dropdown = categoryCard.querySelector('.subcategory-dropdown');
            
            // Close all other dropdowns
            document.querySelectorAll('.subcategory-dropdown').forEach(dd => {
                if (dd !== dropdown) {
                    dd.classList.remove('active');
                }
            });
            
            document.querySelectorAll('.category-card').forEach(card => {
                if (card !== categoryCard) {
                    card.classList.remove('active');
                }
            });
            
            // Toggle current dropdown
            if (dropdown.classList.contains('active')) {
                dropdown.classList.remove('active');
                categoryCard.classList.remove('active');
            } else {
                dropdown.classList.add('active');
                categoryCard.classList.add('active');
            }
            
            // Update URL without page reload
            const url = new URL(window.location);
            if (dropdown.classList.contains('active')) {
                url.searchParams.set('category', category);
            } else {
                url.searchParams.delete('category');
            }
            url.searchParams.delete('subcategory');
            url.searchParams.delete('confirmed');
            
            // Update browser history without reload
            window.history.pushState({}, '', url);
        });
    });
    
    // Handle browser back/forward buttons
    window.addEventListener('popstate', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const category = urlParams.get('category');
        
        // Reset all cards
        document.querySelectorAll('.category-card').forEach(card => {
            card.classList.remove('active');
        });
        
        document.querySelectorAll('.subcategory-dropdown').forEach(dropdown => {
            dropdown.classList.remove('active');
        });
        
        // Activate the correct category if specified
        if (category) {
            const targetCard = document.querySelector(`[data-category="${category}"]`).closest('.category-card');
            const targetDropdown = targetCard.querySelector('.subcategory-dropdown');
            
            targetCard.classList.add('active');
            targetDropdown.classList.add('active');
        }
    });
});
</script>