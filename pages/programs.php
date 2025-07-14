<?php

include '../includes/header.php';
include '../includes/db_functions.php';

$page_title = 'Health Programs';
$page_subtitle = 'Comprehensive healthcare programs for our community';

$programs = getActiveProgramsAndSchedules();

?>
<div class="main-content">
    <h1 style="font-size:2.2rem; margin-bottom: 1.5rem; font-weight:600;">Program Enrollment</h1>
    <div class="programs-grid">
        <?php
        $has_program = false;
        foreach ($programs as $p) {
            if (!$p['schedule_id']) continue; // Only show with schedule
            $has_program = true;
            $slots = (int)$p['available_slots'];
            $is_available = $slots > 0;
            $time = ($p['start_time'] && $p['end_time']) ?
                date('g:i A', strtotime($p['start_time'])) . ' - ' . date('g:i A', strtotime($p['end_time'])) : '';
            echo '<div class="program-card">';
            echo '<div class="program-card-left-border"></div>';
            echo '<div class="program-card-content">';
            echo '<div class="program-title">' . htmlspecialchars($p['name']) . '</div>';
            echo '<div class="program-time">' . htmlspecialchars($time) . '</div>';
            echo '<div class="program-slots">';
            if ($is_available) {
                echo '<span class="program-slot-available"><i class="fa fa-check-square-o"></i> ' . $slots . ' slots available</span>';
            } else {
                echo '<span class="program-slot-unavailable"><i class="fa fa-times-circle-o"></i> No slots available</span>';
            }
            echo '</div>';
            echo '</div>';
            echo '<a class="program-enroll-btn" href="reservation.php?service_id=' . $p['service_id'] . '" ' . ($is_available ? '' : 'disabled style="pointer-events:none;opacity:0.6;"') . '>Enroll in Program</a>';
            echo '</div>';
        }
        if (!$has_program) {
            echo '<div style="color:#888; font-size:1.1rem;">No active programs available at this time.</div>';
        }
        ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>