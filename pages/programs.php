<?php

include '../includes/header.php';
include '../includes/db_functions.php';

// Set page-specific content
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
    'Anti-Rabies Vaccination Campaign' => 'anti-rabies-vaccination',
    'Community Vaccination Drive' => 'community-vaccination',
    // Program Enrollment
    'Senior Citizen Health Plan' => 'senior-health',
    'Maternal Health Program' => 'maternal-health',
    'Diabetes Management Program' => 'diabetes-management',
    'Hypertension Monitoring Program' => 'hypertension-monitoring',
    'Blood Pressure Monitoring Program' => 'blood-pressure-monitoring',
    // General Appointment
    'Free Health Checkup Day' => 'general-consultation',
    'Specialist Consultation Day' => 'specialist-referral',
    'Dental Care Clinic' => 'dental-care',
    'Health Screening Event' => 'lab-tests',
    // Add more mappings as needed
];
?>

<!--Programs Banner Section-->
<section class="programs-banner">
    <div class="programs-banner-content">
        <h1><?php echo htmlspecialchars($page_title); ?></h1>
        <p><?php echo htmlspecialchars($page_subtitle); ?></p>
    </div>
</section>

<style>
/* Programs Page Styles */
.programs-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
    gap: 32px;
    margin-bottom: 40px;
}
.program-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    display: flex;
    flex-direction: column;
    align-items: stretch;
    min-height: 170px;
    position: relative;
    overflow: hidden;
    border: none;
    margin-bottom: 0;
}
.program-card-left-border {
    width: 8px;
    background: #f59e0b;
    border-radius: 8px 0 0 8px;
    flex-shrink: 0;
}
.program-card-content {
    flex: 1 1 auto;
    padding: 28px 24px 18px 28px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.program-title {
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 4px;
    color: #222;
}
.program-time {
    font-size: 1.05rem;
    color: #444;
    margin-bottom: 10px;
}
.program-slots {
    margin-bottom: 18px;
}
.program-slot-available {
    color: #22c55e;
    font-weight: 500;
    font-size: 1.08rem;
    display: flex;
    align-items: center;
    gap: 6px;
}
.program-slot-unavailable {
    color: #aaa;
    font-weight: 500;
    font-size: 1.08rem;
    display: flex;
    align-items: center;
    gap: 6px;
}
.program-btn-wrapper {
    width: 100%;
    margin-top: auto;
    display: flex;
    flex-direction: column;
}
.program-enroll-btn {
    display: inline-block;
    background: #f59e0b;
    color: #fff;
    font-weight: 600;
    font-size: 1.08rem;
    border: none;
    border-radius: 8px;
    padding: 14px 0;
    text-align: center;
    width: 90%;
    margin: 0 5% 0 5%;
    text-decoration: none;
    transition: background 0.18s, color 0.18s, opacity 0.18s;
    cursor: pointer;
}
.program-enroll-btn:disabled,
.program-enroll-btn[disabled] {
    background: #f59e0b;
    opacity: 0.6;
    cursor: not-allowed;
}

/* Program Cards & Grid Styles (moved from programs.php) */
.programs-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(420px, 1fr));
    gap: 20px;
    margin-bottom: 32px;
}
.program-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    padding: 18px 32px 24px 32px;
    min-height: 180px;
    position: relative;
    border: 1.5px solid #e6eaf0;
    margin-bottom: 0;
    transition: box-shadow 0.18s;
}
.program-card:hover {
    box-shadow: 0 4px 18px rgba(0,0,0,0.10);
}
.program-card-header {
    display: flex;
    align-items: flex-start;
    margin-bottom: 18px;
}
.program-avatar {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    background: #e6f0fa;
    margin-right: 18px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: #7bb6e6;
}
.program-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #222;
    margin-bottom: 2px;
}
.program-time {
    font-size: 1.02rem;
    color: #666;
    margin-bottom: 0;
}
.program-label {
    font-weight: 700;
    margin-top: 12px;
    margin-bottom: 2px;
    font-size: 1.01rem;
    color: #222;
}
.program-description {
    font-size: 1.01rem;
    color: #444;
    margin-bottom: 18px;
}
.program-slots {
    margin-bottom: 18px;
    font-weight: bold;
    color: #22c55e;
    font-size: 1.08rem;
    display: flex;
    align-items: center;
    gap: 6px;
}
.program-slot-unavailable {
    color: #aaa;
    font-weight: bold;
    font-size: 1.08rem;
    display: flex;
    align-items: center;
    gap: 6px;
}
.program-btn-wrapper {
    width: 100%;
    margin-top: auto;
    display: flex;
    flex-direction: column;
}
.program-enroll-btn {
    display: block;
    background: #55c7fa;
    color: #fff;
    font-weight: 700;
    font-size: 1.13rem;
    border: none;
    border-radius: 7px;
    padding: 15px 0;
    text-align: center;
    width: 100%;
    margin: 0;
    text-decoration: none;
    transition: background 0.18s, color 0.18s, opacity 0.18s;
    cursor: pointer;
    letter-spacing: 0.01em;
}
.program-enroll-btn:disabled,
.program-enroll-btn[disabled] {
    background: #55c7fa;
    opacity: 0.6;
    cursor: not-allowed;
}
.category-section-title {
    font-size: 2.1rem;
    font-weight: 700;
    margin-top: 2.5rem;
    margin-bottom: 1.2rem;
    color: #181818;
}
.main-content {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    padding-left: 32px;
    padding-right: 32px;
    padding-top: 30px;
    padding-bottom: 60px;
}
@media (max-width: 600px) {
    .programs-grid {
        grid-template-columns: 1fr;
        gap: 18px;
    }
    .program-card-content {
        padding: 18px 10px 12px 16px;
    }
    .program-enroll-btn {
        padding: 12px 0;
        font-size: 1rem;
    }
    .programs-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }
    .program-card {
        padding: 10px 4px 8px 4px;
    }
    .program-enroll-btn {
        padding: 10px 0;
        font-size: 0.95rem;
    }
    .main-content {
        padding-left: 8px;
        padding-right: 8px;
        padding-top: 18px;
        padding-bottom: 24px;
    }
}
</style>

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