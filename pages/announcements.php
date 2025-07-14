<?php

include '../includes/header.php';
include '../includes/db_functions.php';

$page_title = 'Announcements';
$page_subtitle = 'Stay updated with our latest news and health information';

$announcements = getAnnouncements(20, false); // Fetch up to 20 announcements

// Group announcements by date (today, yesterday, others)
$grouped = [
    'today' => [],
    'yesterday' => [],
    'other' => []
];
$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));
foreach ($announcements as $a) {
    $date = $a['announcement_date'] ?? $a['created_at'];
    if ($date === $today) {
        $grouped['today'][] = $a;
    } elseif ($date === $yesterday) {
        $grouped['yesterday'][] = $a;
    } else {
        $grouped['other'][$date][] = $a;
    }
}
?>

<style>
.announcement-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.07);
    margin-bottom: 20px;
    padding: 18px 28px 18px 28px;
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
    transition: box-shadow 0.2s, background 0.2s, color 0.2s;
    color: #222;
}
.announcement-card .arrow {
    font-size: 2rem;
    color: #55c7fa;
    margin-left: 20px;
    transition: color 0.2s;
}
.announcement-title {
    font-weight: bold;
    font-size: 1.08rem;
    margin-bottom: 2px;
}
.announcement-content {
    font-size: 0.98rem;
    color: #444;
    transition: color 0.2s;
}
.announcement-card:hover {
    background: #55c7fa;
    color: #fff;
}
.announcement-card:hover .announcement-title,
.announcement-card:hover .announcement-content {
    color: #eaf7ff;
}
.announcement-card:hover .arrow {
    color: #fff;
}
.announcement-date-group {
    margin-top: 40px;
    margin-bottom: 18px;
}
.announcement-date-label {
    font-size: 1.4rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 0;
}
.announcement-date-sub {
    font-size: 1.1rem;
    color: #888;
    margin-bottom: 18px;
}
.announcement-divider {
    border: none;
    border-top: 1px solid #e0e0e0;
    margin: 36px 0 36px 0;
}
@media (max-width: 600px) {
    .announcement-card { flex-direction: column; align-items: flex-start; padding: 16px; }
    .announcement-card .arrow { margin-left: 0; margin-top: 10px; }
}
</style>

<div style="max-width: 900px; margin: 0 auto; padding: 30px 0 60px 0;">
    <div class="announcement-date-group">
        <div class="announcement-date-label">Today</div>
        <div class="announcement-date-sub"><?php echo date('F j, Y'); ?></div>
    </div>
    <?php
    $has_today = false;
    foreach ($grouped['today'] as $a) {
        $has_today = true;
        $is_featured = $a['is_featured'] ? 'featured' : '';
        echo '<div class="announcement-card ' . $is_featured . '">';
        echo '<div>';
        echo '<div class="announcement-title">' . htmlspecialchars($a['title']) . '</div>';
        echo '<div class="announcement-content">' . nl2br(htmlspecialchars($a['content'])) . '</div>';
        echo '</div>';
        echo '<div class="arrow">&rarr;</div>';
        echo '</div>';
    }
    if (!$has_today) {
        echo '<div style="color:#aaa; margin-bottom:30px;">No announcements for today.</div>';
    }
    ?>
    <hr class="announcement-divider" />
    <div class="announcement-date-group">
        <div class="announcement-date-label">Yesterday</div>
        <div class="announcement-date-sub"><?php echo date('F j, Y', strtotime('-1 day')); ?></div>
    </div>
    <?php
    $has_yesterday = false;
    foreach ($grouped['yesterday'] as $a) {
        $has_yesterday = true;
        $is_featured = $a['is_featured'] ? 'featured' : '';
        echo '<div class="announcement-card ' . $is_featured . '">';
        echo '<div>';
        echo '<div class="announcement-title">' . htmlspecialchars($a['title']) . '</div>';
        echo '<div class="announcement-content">' . nl2br(htmlspecialchars($a['content'])) . '</div>';
        echo '</div>';
        echo '<div class="arrow">&rarr;</div>';
        echo '</div>';
    }
    if (!$has_yesterday) {
        echo '<div style="color:#aaa; margin-bottom:30px;">No announcements for yesterday.</div>';
    }
    ?>
    <?php if (!empty($grouped['other'])): ?>
        <hr class="announcement-divider" />
        <?php foreach ($grouped['other'] as $date => $list): ?>
            <div class="announcement-date-group">
                <div class="announcement-date-label"><?php echo date('F j, Y', strtotime($date)); ?></div>
            </div>
            <?php foreach ($list as $a): ?>
                <div class="announcement-card <?php echo $a['is_featured'] ? 'featured' : ''; ?>">
                    <div>
                        <div class="announcement-title"><?php echo htmlspecialchars($a['title']); ?></div>
                        <div class="announcement-content"><?php echo nl2br(htmlspecialchars($a['content'])); ?></div>
                    </div>
                    <div class="arrow">&rarr;</div>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>