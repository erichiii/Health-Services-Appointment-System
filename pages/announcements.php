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

<div class="main-content">
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