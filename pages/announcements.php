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
    cursor: pointer;
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
    max-width: 600px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: block;
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
/* Modal styles */
.announcement-modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0; top: 0; width: 100vw; height: 100vh;
    background: rgba(0,0,0,0.35);
    justify-content: center;
    align-items: center;
}
.announcement-modal.active {
    display: flex;
}
.announcement-modal-content {
    background: #fff;
    border-radius: 12px;
    max-width: 700px;
    width: 98vw;
    padding: 32px 28px 24px 28px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.18);
    position: relative;
    animation: modalIn 0.18s;
}
@keyframes modalIn {
    from { transform: translateY(40px) scale(0.98); opacity: 0; }
    to { transform: none; opacity: 1; }
}
.announcement-modal-close {
    position: absolute;
    top: 16px; right: 18px;
    font-size: 1.5rem;
    color: #888;
    background: none;
    border: none;
    cursor: pointer;
    transition: color 0.2s;
}
.announcement-modal-close:hover {
    color: #222;
}
.announcement-modal-title {
    font-size: 1.2rem;
    font-weight: bold;
    margin-bottom: 10px;
}
.announcement-modal-date {
    font-size: 0.98rem;
    color: #888;
    margin-bottom: 18px;
}
.announcement-modal-body {
    font-size: 1.05rem;
    color: #333;
    white-space: pre-line;
}
@media (max-width: 600px) {
    .announcement-card { flex-direction: column; align-items: flex-start; padding: 16px; }
    .announcement-card .arrow { margin-left: 0; margin-top: 10px; }
    .announcement-modal-content { padding: 18px 8px 12px 8px; }
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
        $date = $a['announcement_date'] ?? $a['created_at'];
        echo '<div class="announcement-card ' . $is_featured . '">';
        echo '<div>';
        echo '<div class="announcement-title">' . htmlspecialchars($a['title']) . '</div>';
        echo '<div class="announcement-content">' . htmlspecialchars($a['content']) . '</div>';
        echo '</div>';
        echo '<div class="arrow" tabindex="0" role="button" aria-label="View details" data-title="' . htmlspecialchars($a['title'], ENT_QUOTES) . '" data-content="' . htmlspecialchars($a['content'], ENT_QUOTES) . '" data-date="' . htmlspecialchars(date('F j, Y', strtotime($date)), ENT_QUOTES) . '">&rarr;</div>';
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
        $date = $a['announcement_date'] ?? $a['created_at'];
        echo '<div class="announcement-card ' . $is_featured . '">';
        echo '<div>';
        echo '<div class="announcement-title">' . htmlspecialchars($a['title']) . '</div>';
        echo '<div class="announcement-content">' . htmlspecialchars($a['content']) . '</div>';
        echo '</div>';
        echo '<div class="arrow" tabindex="0" role="button" aria-label="View details" data-title="' . htmlspecialchars($a['title'], ENT_QUOTES) . '" data-content="' . htmlspecialchars($a['content'], ENT_QUOTES) . '" data-date="' . htmlspecialchars(date('F j, Y', strtotime($date)), ENT_QUOTES) . '">&rarr;</div>';
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
                        <div class="announcement-content"><?php echo htmlspecialchars($a['content']); ?></div>
                    </div>
                    <div class="arrow" tabindex="0" role="button" aria-label="View details" data-title="<?php echo htmlspecialchars($a['title'], ENT_QUOTES); ?>" data-content="<?php echo htmlspecialchars($a['content'], ENT_QUOTES); ?>" data-date="<?php echo htmlspecialchars(date('F j, Y', strtotime($a['announcement_date'] ?? $a['created_at'])), ENT_QUOTES); ?>">&rarr;</div>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modal/Screen Popup for Announcement Details -->
<!-- Utilizes JavaScript for an easier implementation -->
<div class="announcement-modal" id="announcementModal" aria-modal="true" role="dialog" tabindex="-1">
    <div class="announcement-modal-content">
        <button class="announcement-modal-close" id="announcementModalClose" aria-label="Close">&times;</button>
        <div class="announcement-modal-title" id="modalTitle"></div>
        <div class="announcement-modal-date" id="modalDate"></div>
        <div class="announcement-modal-body" id="modalContent"></div>
    </div>
</div>
<script>

// Modal logic -- JavaScript starts here
const modal = document.getElementById('announcementModal');
const modalClose = document.getElementById('announcementModalClose');
const modalTitle = document.getElementById('modalTitle');
const modalContent = document.getElementById('modalContent');
const modalDate = document.getElementById('modalDate');

document.querySelectorAll('.announcement-card .arrow').forEach(arrow => {
    arrow.addEventListener('click', function(e) {
        modalTitle.textContent = this.getAttribute('data-title');
        modalContent.textContent = this.getAttribute('data-content');
        modalDate.textContent = this.getAttribute('data-date');
        modal.classList.add('active');
        modal.focus();
    });
    arrow.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            this.click();
        }
    });
});
modalClose.addEventListener('click', function() {
    modal.classList.remove('active');
});
modal.addEventListener('click', function(e) {
    if (e.target === modal) {
        modal.classList.remove('active');
    }
});
document.addEventListener('keydown', function(e) {
    if (modal.classList.contains('active') && e.key === 'Escape') {
        modal.classList.remove('active');
    }
});
</script>

<?php include '../includes/footer.php'; ?>