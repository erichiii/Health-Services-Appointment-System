<?php

include '../includes/header.php';
include '../includes/db_functions.php';

$page_title = 'Health Programs';
$page_subtitle = 'Comprehensive healthcare programs for our community';

$programs = getActiveProgramsAndSchedules();

?>
<style>
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
    flex-direction: row;
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
    margin: 0 5% 18px 5%;
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
}
</style>
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