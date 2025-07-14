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
    grid-template-columns: repeat(auto-fit, minmax(420px, 1fr));
    gap: 32px;
    margin-bottom: 40px;
}
.program-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    padding: 28px 28px 24px 28px;
    min-height: 320px;
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
    margin-bottom: 0;
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
    margin-top: 18px;
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
@media (max-width: 600px) {
    .programs-grid {
        grid-template-columns: 1fr;
        gap: 18px;
    }
    .program-card {
        padding: 16px 8px 12px 8px;
    }
    .program-enroll-btn {
        padding: 12px 0;
        font-size: 1rem;
    }
}
</style>
<div class="main-content">
    <h1 style="font-size:2.2rem; margin-bottom: 1.5rem; font-weight:600;">Vaccine Registration</h1>
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
            echo '<div class="program-card-header">';
            echo '<div class="program-avatar"><i class="fa fa-calendar"></i></div>';
            echo '<div>';
            echo '<div class="program-title">' . htmlspecialchars($p['name']) . '</div>';
            echo '<div class="program-time">' . htmlspecialchars($time) . '</div>';
            echo '</div>';
            echo '</div>';
            echo '<div class="program-label">Description</div>';
            echo '<div class="program-description">' . htmlspecialchars($p['description']) . '</div>';
            echo '<div class="program-label">Slot Availability</div>';
            if ($is_available) {
                echo '<div class="program-slots program-slot-available"><i class="fa fa-check-square-o"></i> ' . $slots . ' Slots Available</div>';
            } else {
                echo '<div class="program-slots program-slot-unavailable"><i class="fa fa-times-circle-o"></i> No slots available</div>';
            }
            echo '<a class="program-enroll-btn" href="reservation.php?service_id=' . $p['service_id'] . '" ' . ($is_available ? '' : 'disabled style=\"pointer-events:none;opacity:0.6;\"') . '>Join Program</a>';
            echo '</div>';
        }
        if (!$has_program) {
            echo '<div style="color:#888; font-size:1.1rem;">No active programs available at this time.</div>';
        }
        ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>