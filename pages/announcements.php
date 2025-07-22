<?php

include '../includes/header.php';
include '../includes/db_functions.php';

// Set page-specific content
$page_title = 'Announcements';
$page_subtitle = 'Stay updated with our latest news and health information';

$announcements = getAnnouncements(20, false); // Fetch up to 20 announcements

// Check if a specific announcement ID is requested
$target_announcement_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$target_announcement = null;

// If an ID is specified, find that announcement
if ($target_announcement_id) {
    foreach ($announcements as $announcement) {
        if ($announcement['id'] == $target_announcement_id) {
            $target_announcement = $announcement;
            break;
        }
    }
}

// Function to determine announcement type based on keywords
function getAnnouncementType($title, $content) {
    $title_lower = strtolower($title);
    $content_lower = strtolower($content);
    $text = $title_lower . ' ' . $content_lower;
    
    // Check for explicit type markers in title first (highest priority)
    if (strpos($title_lower, '[reminder]') !== false || strpos($title_lower, 'reminder:') !== false) {
        return 'reminder';
    }
    if (strpos($title_lower, '[urgent]') !== false || strpos($title_lower, '[alert]') !== false || strpos($title_lower, 'urgent:') !== false || strpos($title_lower, 'alert:') !== false) {
        return 'urgent';
    }
    if (strpos($title_lower, '[event]') !== false || strpos($title_lower, 'event:') !== false) {
        return 'event';
    }
    if (strpos($title_lower, '[notice]') !== false || strpos($title_lower, 'notice:') !== false) {
        return 'notice';
    }
    
    // Urgent/Alert keywords - highest priority for content
    $urgent_keywords = ['urgent', 'alert', 'emergency', 'closure', 'outbreak', 'immediate', 'critical', 'warning', 'danger', 'suspension', 'cancellation', 'breaking'];
    
    // Reminder keywords - check these before events
    $reminder_keywords = ['reminder', 'deadline', 'appointment', 'schedule', 'expires', 'due', 'approaching', 'soon', 'last chance', 'final', 'booking', 'last day', 'submit by', 'before', 'cutoff', 'ends today', 'ends tomorrow'];
    
    // Event keywords - more specific to avoid false matches
    $event_keywords = ['seminar', 'workshop', 'training session', 'health fair', 'medical mission', 'health screening event', 'celebration', 'conference', 'community gathering', 'health education program'];
    
    // Check for urgent first (highest priority)
    foreach ($urgent_keywords as $keyword) {
        if (strpos($text, $keyword) !== false) {
            return 'urgent';
        }
    }
    
    // Check for reminders (higher priority than events)
    foreach ($reminder_keywords as $keyword) {
        if (strpos($text, $keyword) !== false) {
            return 'reminder';
        }
    }
    
    // Check for events
    foreach ($event_keywords as $keyword) {
        if (strpos($text, $keyword) !== false) {
            return 'event';
        }
    }
    
    // Additional context-based rules
    if (strpos($text, 'vaccination') !== false && (strpos($text, 'records') !== false || strpos($text, 'submit') !== false || strpos($text, 'deadline') !== false)) {
        return 'reminder';
    }
    
    if (strpos($text, 'screening') !== false && strpos($text, 'schedule') !== false) {
        return 'event';
    }
    
    // Default to public notice
    return 'notice';
}

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
    // Add announcement type to each announcement
    $a['type'] = getAnnouncementType($a['title'], $a['content']);
    
    if ($date === $today) {
        $grouped['today'][] = $a;
    } elseif ($date === $yesterday) {
        $grouped['yesterday'][] = $a;
    } else {
        $grouped['other'][$date][] = $a;
    }
}
?>

<!-- Announcements Banner Section -->
<section class="cntct-banner">
    <div class="cntct-banner-overlay">
        <div class="cntct-banner-content">
            <h1><?php echo htmlspecialchars($page_title); ?></h1>
            <p><?php echo htmlspecialchars($page_subtitle); ?></p>
        </div>
    </div>
</section>

<!-- Main Announcements Section -->
<main class="main-content">
    <div class="announcements-container">
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
            $type = $a['type'];
            $announcement_id = $a['id'];
            
            // Get type display name
            $type_names = [
                'urgent' => 'Urgent',
                'reminder' => 'Reminder', 
                'event' => 'Event',
                'notice' => 'Notice'
            ];
            $type_display = $type_names[$type] ?? 'Notice';
            
            echo '<div class="announcement-card ' . $is_featured . ' ' . $type . '" id="announcement-' . $announcement_id . '">';
            echo '<div class="announcement-card-content">';
            echo '<div class="announcement-main-content">';
            echo '<div class="announcement-type-badge ' . $type . '">' . $type_display . '</div>';
            echo '<div class="announcement-title">' . htmlspecialchars($a['title']) . '</div>';
            echo '<div class="announcement-content">' . htmlspecialchars($a['content']) . '</div>';
            echo '</div>';
            echo '<div class="announcement-arrow" tabindex="0" role="button" aria-label="View details" data-title="' . htmlspecialchars($a['title'], ENT_QUOTES) . '" data-content="' . htmlspecialchars($a['content'], ENT_QUOTES) . '" data-date="' . htmlspecialchars(date('F j, Y', strtotime($date)), ENT_QUOTES) . '" data-type="' . htmlspecialchars($type_display, ENT_QUOTES) . '">→</div>';
            echo '</div>';
            echo '</div>';
        }
        
        if (!$has_today) {
            echo '<div class="announcement-empty">No announcements for today.</div>';
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
            $type = $a['type'];
            $announcement_id = $a['id'];
            
            // Get type display name
            $type_names = [
                'urgent' => 'Urgent',
                'reminder' => 'Reminder', 
                'event' => 'Event',
                'notice' => 'Notice'
            ];
            $type_display = $type_names[$type] ?? 'Notice';
            
            echo '<div class="announcement-card ' . $is_featured . ' ' . $type . '" id="announcement-' . $announcement_id . '">';
            echo '<div class="announcement-card-content">';
            echo '<div class="announcement-main-content">';
            echo '<div class="announcement-type-badge ' . $type . '">' . $type_display . '</div>';
            echo '<div class="announcement-title">' . htmlspecialchars($a['title']) . '</div>';
            echo '<div class="announcement-content">' . htmlspecialchars($a['content']) . '</div>';
            echo '</div>';
            echo '<div class="announcement-arrow" tabindex="0" role="button" aria-label="View details" data-title="' . htmlspecialchars($a['title'], ENT_QUOTES) . '" data-content="' . htmlspecialchars($a['content'], ENT_QUOTES) . '" data-date="' . htmlspecialchars(date('F j, Y', strtotime($date)), ENT_QUOTES) . '" data-type="' . htmlspecialchars($type_display, ENT_QUOTES) . '">→</div>';
            echo '</div>';
            echo '</div>';
        }
        
        if (!$has_yesterday) {
            echo '<div class="announcement-empty">No announcements for yesterday.</div>';
        }
        ?>
        
        <?php if (!empty($grouped['other'])): ?>
            <hr class="announcement-divider" />
            <?php foreach ($grouped['other'] as $date => $list): ?>
                <div class="announcement-date-group">
                    <div class="announcement-date-label"><?php echo date('F j, Y', strtotime($date)); ?></div>
                </div>
                <?php foreach ($list as $a): ?>
                    <?php
                    $type = $a['type'];
                    $type_names = [
                        'urgent' => 'Urgent',
                        'reminder' => 'Reminder', 
                        'event' => 'Event',
                        'notice' => 'Notice'
                    ];
                    $type_display = $type_names[$type] ?? 'Notice';
                    ?>
                    <div class="announcement-card <?php echo $a['is_featured'] ? 'featured' : ''; ?> <?php echo $type; ?>" id="announcement-<?php echo $a['id']; ?>">
                        <div class="announcement-card-content">
                            <div class="announcement-main-content">
                                <div class="announcement-type-badge <?php echo $type; ?>"><?php echo $type_display; ?></div>
                                <div class="announcement-title"><?php echo htmlspecialchars($a['title']); ?></div>
                                <div class="announcement-content"><?php echo htmlspecialchars($a['content']); ?></div>
                            </div>
                            <div class="announcement-arrow" tabindex="0" role="button" aria-label="View details" data-title="<?php echo htmlspecialchars($a['title'], ENT_QUOTES); ?>" data-content="<?php echo htmlspecialchars($a['content'], ENT_QUOTES); ?>" data-date="<?php echo htmlspecialchars(date('F j, Y', strtotime($a['announcement_date'] ?? $a['created_at'])), ENT_QUOTES); ?>" data-type="<?php echo htmlspecialchars($type_display, ENT_QUOTES); ?>">→</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<!-- Modal/Screen Popup for Announcement Details -->
<div class="announcement-modal" id="announcementModal" aria-modal="true" role="dialog" tabindex="-1">
    <div class="announcement-modal-content">
        <button class="announcement-modal-close" id="announcementModalClose" aria-label="Close">&times;</button>
        <div class="announcement-modal-type-badge" id="modalTypeBadge"></div>
        <div class="announcement-modal-title" id="modalTitle"></div>
        <div class="announcement-modal-date" id="modalDate"></div>
        <div class="announcement-modal-body" id="modalContent"></div>
    </div>
</div>

<script>
// Modal logic
const modal = document.getElementById('announcementModal');
const modalClose = document.getElementById('announcementModalClose');
const modalTitle = document.getElementById('modalTitle');
const modalContent = document.getElementById('modalContent');
const modalDate = document.getElementById('modalDate');
const modalTypeBadge = document.getElementById('modalTypeBadge');

// Function to show announcement modal
function showAnnouncementModal(title, content, date, type) {
    modalTitle.textContent = title;
    modalContent.textContent = content;
    modalDate.textContent = date;
    
    // Set type badge
    modalTypeBadge.textContent = type;
    modalTypeBadge.className = 'announcement-modal-type-badge ' + type.toLowerCase();
    
    modal.classList.add('active');
    modal.focus();
}

// Function to remove ID parameter from URL
function removeIdFromUrl() {
    const url = new URL(window.location);
    url.searchParams.delete('id');
    window.history.replaceState({}, document.title, url.pathname);
}

// Handle clicking on announcement arrows
document.querySelectorAll('.announcement-arrow').forEach(arrow => {
    arrow.addEventListener('click', function(e) {
        const title = this.getAttribute('data-title');
        const content = this.getAttribute('data-content');
        const date = this.getAttribute('data-date');
        const type = this.getAttribute('data-type');
        
        showAnnouncementModal(title, content, date, type);
    });
    
    arrow.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            this.click();
        }
    });
});

// Handle modal close
modalClose.addEventListener('click', function() {
    modal.classList.remove('active');
    removeIdFromUrl();
});

modal.addEventListener('click', function(e) {
    if (e.target === modal) {
        modal.classList.remove('active');
        removeIdFromUrl();
    }
});

document.addEventListener('keydown', function(e) {
    if (modal.classList.contains('active') && e.key === 'Escape') {
        modal.classList.remove('active');
        removeIdFromUrl();
    }
});

// Check if we should auto-open a specific announcement
<?php if ($target_announcement): ?>
document.addEventListener('DOMContentLoaded', function() {
    // Scroll to the announcement card
    const targetCard = document.getElementById('announcement-<?php echo $target_announcement['id']; ?>');
    if (targetCard) {
        targetCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        // Highlight the card briefly
        targetCard.style.boxShadow = '0 0 0 3px rgba(51, 182, 255, 0.3)';
        setTimeout(() => {
            targetCard.style.boxShadow = '';
        }, 2000);
        
        // Auto-open the modal after a short delay
        setTimeout(() => {
            const arrow = targetCard.querySelector('.announcement-arrow');
            if (arrow) {
                const title = arrow.getAttribute('data-title');
                const content = arrow.getAttribute('data-content');
                const date = arrow.getAttribute('data-date');
                const type = arrow.getAttribute('data-type');
                
                showAnnouncementModal(title, content, date, type);
            }
        }, 500);
    }
});
<?php endif; ?>
</script>

<?php include '../includes/footer.php'; ?>

<style>
.cntct-banner {
    height: 50vh;
    min-height: 300px;
    background: url('../assets/images/announcements.jpg') no-repeat center 30%;
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

/* Announcements Page Styles */
.main-content {
    background: #f8f9fa;
    padding: 4rem 0 6rem 0;
    min-height: 70vh;
}

.announcements-container {
    width: 100%;
    max-width: 1000px;
    margin: 0 auto;
    padding-left: 2rem;
    padding-right: 2rem;
}

/* Date Group Headers */
.announcement-date-group {
    margin-top: 3rem;
    margin-bottom: 1.5rem;
}

.announcement-date-label {
    font-size: 1.6rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 0.25rem;
    font-family: 'Nunito', sans-serif;
}

.announcement-date-sub {
    font-size: 1rem;
    color: #6c757d;
    margin-bottom: 1.5rem;
    font-family: 'Arimo', sans-serif;
}

.announcement-divider {
    border: none;
    border-top: 2px solid #e9ecef;
    margin: 3rem 0;
    border-radius: 1px;
}

/* Announcement Cards */
.announcement-card {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(51, 182, 255, 0.08);
    margin-bottom: 1.5rem;
    padding: 1.5rem;
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
    position: relative;
    cursor: pointer;
}

.announcement-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(51, 182, 255, 0.15);
}

.announcement-card:target {
    border-left-color: #33b6ff;
    box-shadow: 0 0 0 3px rgba(51, 182, 255, 0.2);
}

/* Type-based styling */
.announcement-card.urgent {
    border-left-color: #dc3545;
}

.announcement-card.reminder {
    border-left-color: #fd7e14;
}

.announcement-card.event {
    border-left-color: #28a745;
}

.announcement-card.notice {
    border-left-color: #6c757d;
}

/* Card Content Layout */
.announcement-card-content {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
}

.announcement-main-content {
    flex: 1;
}

.announcement-type-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.75rem;
    display: inline-block;
    font-family: 'Nunito', sans-serif;
}

.announcement-type-badge.urgent {
    background-color: #dc354520;
    color: #dc3545;
}

.announcement-type-badge.reminder {
    background-color: #fd7e1420;
    color: #fd7e14;
}

.announcement-type-badge.event {
    background-color: #28a74520;
    color: #28a745;
}

.announcement-type-badge.notice {
    background-color: #6c757d20;
    color: #6c757d;
}

.announcement-title {
    font-weight: 700;
    font-size: 1.1rem;
    margin-bottom: 0.5rem;
    color: #2c3e50;
    font-family: 'Nunito', sans-serif;
    line-height: 1.3;
}

.announcement-content {
    font-size: 0.95rem;
    color: #495057;
    line-height: 1.6;
    font-family: 'Arimo', sans-serif;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}

.announcement-arrow {
    flex-shrink: 0;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #33b6ff;
    font-size: 1.2rem;
    transition: all 0.3s ease;
    cursor: pointer;
    border: 2px solid transparent;
}

.announcement-arrow:hover {
    background: #33b6ff;
    color: white;
    transform: scale(1.1);
}

.announcement-arrow:focus {
    outline: none;
    border-color: #33b6ff;
    box-shadow: 0 0 0 3px rgba(51, 182, 255, 0.2);
}

/* Featured announcements */
.announcement-card.featured {
    background: linear-gradient(135deg, #fff 0%, #f8fbff 100%);
    border: 1px solid rgba(51, 182, 255, 0.2);
}

.announcement-card.featured .announcement-title {
    color: #33b6ff;
}

/* Empty state */
.announcement-empty {
    color: #6c757d;
    font-style: italic;
    text-align: center;
    padding: 2rem;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 2rem;
    font-family: 'Arimo', sans-serif;
}

/* Modal styles */
.announcement-modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0, 0, 0, 0.4);
    justify-content: center;
    align-items: center;
    padding: 1rem;
    box-sizing: border-box;
}

.announcement-modal.active {
    display: flex;
}

.announcement-modal-content {
    background: #ffffff;
    border-radius: 16px;
    max-width: 800px;
    width: 100%;
    max-height: 80vh;
    overflow-y: auto;
    padding: 2rem;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    position: relative;
    animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
    from {
        transform: translateY(30px) scale(0.95);
        opacity: 0;
    }
    to {
        transform: translateY(0) scale(1);
        opacity: 1;
    }
}

.announcement-modal-close {
    position: absolute;
    top: 1rem;
    right: 1rem;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #f8f9fa;
    border: none;
    color: #6c757d;
    font-size: 1.2rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.announcement-modal-close:hover {
    background: #e9ecef;
    color: #2c3e50;
}

.announcement-modal-type-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 1rem;
    display: inline-block;
    font-family: 'Nunito', sans-serif;
}

.announcement-modal-type-badge.urgent {
    background-color: #dc354520;
    color: #dc3545;
}

.announcement-modal-type-badge.reminder {
    background-color: #fd7e1420;
    color: #fd7e14;
}

.announcement-modal-type-badge.event {
    background-color: #28a74520;
    color: #28a745;
}

.announcement-modal-type-badge.notice {
    background-color: #6c757d20;
    color: #6c757d;
}

.announcement-modal-title {
    font-size: 1.4rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    color: #2c3e50;
    font-family: 'Nunito', sans-serif;
    padding-right: 2rem;
}

.announcement-modal-date {
    font-size: 0.9rem;
    color: #6c757d;
    margin-bottom: 1.5rem;
    font-family: 'Arimo', sans-serif;
}

.announcement-modal-body {
    font-size: 1rem;
    color: #2c3e50;
    line-height: 1.7;
    white-space: pre-line;
    font-family: 'Arimo', sans-serif;
}

/* Responsive Design */
@media (max-width: 768px) {
    .announcements-container {
        padding-left: 1rem;
        padding-right: 1rem;
    }
    
    .announcement-card {
        padding: 1.25rem;
    }
    
    .announcement-card-content {
        flex-direction: column;
        align-items: stretch;
    }
    
    .announcement-arrow {
        align-self: flex-end;
        margin-top: 1rem;
    }
    
    .announcement-modal-content {
        padding: 1.5rem;
        margin: 1rem;
        max-height: calc(100vh - 2rem);
    }
    
    .announcement-date-label {
        font-size: 1.4rem;
    }
    
    .main-content {
        padding: 2rem 0 4rem 0;
    }
    
    .cntct-banner-content h1 {
        font-size: 2.5rem;
    }
    
    .cntct-banner-content p {
        font-size: 1.1rem;
    }
}

@media (max-width: 480px) {
    .announcement-card {
        padding: 1rem;
    }
    
    .announcement-modal-content {
        padding: 1rem;
    }
    
    .announcement-title {
        font-size: 1rem;
    }
    
    .announcement-content {
        font-size: 0.9rem;
    }
    
    .cntct-banner {
        height: 40vh;
        min-height: 250px;
    }
    
    .cntct-banner-content h1 {
        font-size: 2rem;
    }
    
    .cntct-banner-content p {
        font-size: 1rem;
    }
}
</style>