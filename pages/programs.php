<?php

include '../includes/header.php';
include '../includes/db_functions.php';

$page_title = 'Health Programs';
$page_subtitle = 'Comprehensive healthcare programs for our community';

$programs = getActiveProgramsAndSchedules();

// Segregate by category
$categories = [
    'vaccine' => [],
    'program' => [],
    'appointment' => []
];
foreach ($programs as $p) {
    if (!$p['schedule_id']) continue;
    $cat = $p['category'];
    if (isset($categories[$cat])) {
        $categories[$cat][] = $p;
    }
}
$category_titles = [
    'vaccine' => 'Vaccine Registration',
    'program' => 'Program Enrollment',
    'appointment' => 'General Appointment'
];

// Add mapping from program name to subcategory key for all categories
$programToSubcategory = [
    // Vaccine
    'Child Immunization Campaign' => 'child-immunization',
    'Adult Vaccine Drive' => 'adult-vaccine',
    'Travel Vaccine Clinic' => 'travel-vaccine',
    'COVID-19 Booster Campaign' => 'booster-shot',
    // Add more if needed
    // Program Enrollment
    'Senior Citizen Health Plan' => 'senior-health',
    'Maternal Health Program' => 'maternal-health',
    'Diabetes Management Program' => 'diabetes-management',
    'Hypertension Monitoring Program' => 'hypertension-monitoring',
    // General Appointment
    'Free Health Checkup Day' => 'general-consultation',
    'Specialist Consultation Day' => 'specialist-referral',
    'Lab Tests' => 'lab-tests',
    'Follow-up Visits' => 'follow-up',
    'Dental Care Clinic' => 'lab-tests', // If this is a lab test, otherwise map accordingly
    // Add more mappings as needed
];
?>
<div class="main-content">
    <?php foreach ($categories as $cat => $list): ?>
        <?php if (count($list)): ?>
            <div class="category-section-title"><?php echo $category_titles[$cat]; ?></div>
            <div class="programs-grid">
                <?php foreach ($list as $p):
                    $slots = (int)$p['available_slots'];
                    $is_available = $slots > 0;
                    $time = ($p['start_time'] && $p['end_time']) ?
                        date('g:i A', strtotime($p['start_time'])) . ' - ' . date('g:i A', strtotime($p['end_time'])) : '';
                ?>
                <div class="program-card">
                    <div class="program-card-header">
                        <div class="program-avatar"><i class="fa fa-calendar"></i></div>
                        <div>
                            <div class="program-title"><?php echo htmlspecialchars($p['name']); ?></div>
                            <div class="program-time"><?php echo htmlspecialchars($time); ?></div>
                        </div>
                    </div>
                    <div class="program-label">Description</div>
                    <div class="program-description"><?php echo htmlspecialchars($p['description']); ?></div>
                    <?php if ($is_available): ?>
                        <div class="program-slots"><i class="fa fa-check-square-o"></i> <?php echo $slots; ?> Slots Available</div>
                    <?php else: ?>
                        <div class="program-slot-unavailable"><i class="fa fa-times-circle-o"></i> No slots available</div>
                    <?php endif; ?>
                    <div class="program-btn-wrapper">
                        <a class="program-enroll-btn" href="<?php
                            $subcat = isset($programToSubcategory[$p['name']]) ? $programToSubcategory[$p['name']] : '';
                            if ($subcat) {
                                echo 'reservation.php?subcategory=' . urlencode($subcat) . '&confirmed=1#confirmation';
                            } else {
                                echo 'reservation.php?service_id=' . $p['service_id'];
                            }
                        ?>" <?php if (!$is_available) echo 'disabled style="pointer-events:none;opacity:0.6;"'; ?>>Join Program</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
    <?php if (!count($categories['vaccine']) && !count($categories['program']) && !count($categories['appointment'])): ?>
        <div style="color:#888; font-size:0.95rem; margin: 1.5rem 0 0.5rem 0; padding: 0;">No active programs available at this time.</div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>