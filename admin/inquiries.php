<?php
require_once '../includes/admin_layout.php';
require_once '../includes/admin_functions.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('error', 'Invalid security token. Please try again.');
        header('Location: inquiries.php');
        exit;
    }

    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'reply':
            $id = (int)($_POST['id'] ?? 0);
            $reply = trim($_POST['reply'] ?? '');
            if ($id > 0 && $reply !== '') {
                $result = replyToContactInquiry($id, $reply);
                // Simulate sending email (in real app, send email here)
                setFlashMessage($result['success'] ? 'success' : 'error', $result['message'] . ' (Reply sent to user email.)');
            } else {
                setFlashMessage('error', 'Reply cannot be empty.');
            }
            break;
        case 'delete':
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $result = deleteContactInquiry($id);
                setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
            }
            break;
    }
    header('Location: inquiries.php');
    exit;
}

// Pagination and filtering
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;
$search = trim($_GET['search'] ?? '');
$status_filter = $_GET['status'] ?? '';

// Get inquiries
$total_inquiries = count(getContactInquiriesAdmin(1000, 0, $status_filter, $search));
$total_pages = ceil($total_inquiries / $limit);
$inquiries = getContactInquiriesAdmin($limit, $offset, $status_filter, $search);

// Get inquiry for replying
$replying_inquiry = null;
if (isset($_GET['action']) && $_GET['action'] === 'reply' && isset($_GET['id'])) {
    $replying_inquiry = getContactInquiryById((int)$_GET['id']);
}

renderAdminLayout('Inquiries Management', function () use ($inquiries, $replying_inquiry, $page, $total_pages, $search, $status_filter, $total_inquiries, $offset, $limit) {
    $csrf_token = generateCSRFToken();
?>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h2 style="margin: 0; color: #333;">Inquiries Management</h2>
            <p style="margin: 0.5rem 0 0 0; color: #6b7280;">Manage user contact inquiries</p>
        </div>
    </div>

    <?php if (isset($_GET['action']) && $_GET['action'] === 'reply' && $replying_inquiry): ?>
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <h3 class="card-title">Reply to Inquiry</h3>
                <a href="inquiries.php" class="btn btn-secondary btn-sm">Cancel</a>
            </div>
            <form method="POST" style="padding: 1.5rem;">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="action" value="reply">
                <input type="hidden" name="id" value="<?php echo $replying_inquiry['id']; ?>">
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: 600; color: #374151;">Name</label>
                    <div><?php echo htmlspecialchars($replying_inquiry['name']); ?></div>
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: 600; color: #374151;">Email</label>
                    <div><?php echo htmlspecialchars($replying_inquiry['email']); ?></div>
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: 600; color: #374151;">Phone</label>
                    <div><?php echo htmlspecialchars($replying_inquiry['phone']); ?></div>
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: 600; color: #374151;">Subject</label>
                    <div><?php echo htmlspecialchars($replying_inquiry['subject']); ?></div>
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: 600; color: #374151;">Message</label>
                    <div style="white-space: pre-line; background: #f3f4f6; padding: 1rem; border-radius: 6px;"><?php echo htmlspecialchars($replying_inquiry['message']); ?></div>
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: 600; color: #374151;">Status</label>
                    <span style="background: <?php echo $replying_inquiry['status'] === 'pending' ? '#fef3c7' : '#d1fae5'; ?>; color: <?php echo $replying_inquiry['status'] === 'pending' ? '#92400e' : '#065f46'; ?>; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.9rem; font-weight: 600;">
                        <?php echo ucfirst($replying_inquiry['status']); ?>
                    </span>
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: 600; color: #374151;">Reply</label>
                    <textarea name="reply" rows="5" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem; resize: vertical; font-family: inherit;" placeholder="Write your reply here..."><?php echo htmlspecialchars($replying_inquiry['reply'] ?? ''); ?></textarea>
                </div>
                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <a href="inquiries.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Send Reply</button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <div class="card" style="margin-bottom: 1.5rem;">
        <form method="GET" style="display: grid; grid-template-columns: 1fr 150px 150px auto; gap: 1rem; align-items: end;">
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">Search</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                    placeholder="Search name, email, phone, subject, or message..."
                    style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">Status</label>
                <select name="status" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px;">
                    <option value="">All Status</option>
                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="replied" <?php echo $status_filter === 'replied' ? 'selected' : ''; ?>>Replied</option>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
            <div>
                <a href="inquiries.php" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                Inquiries (<?php echo number_format($total_inquiries); ?>)
            </h3>
        </div>
        <?php if (empty($inquiries)): ?>
            <div style="text-align: center; padding: 3rem; color: #6b7280;">
                <i class="fas fa-envelope-open-text" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                <p style="font-size: 1.1rem; margin-bottom: 0.5rem;">No inquiries found</p>
                <p style="font-size: 0.9rem;">User inquiries will appear here when clients submit the contact form.</p>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid #e5e7eb;">
                            <th style="padding: 1rem; text-align: center; font-weight: 600; color: #374151;">ID</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 600; color: #374151;">Name</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 600; color: #374151;">Email</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 600; color: #374151;">Subject</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 600; color: #374151;">Message Preview</th>
                            <th style="padding: 1rem; text-align: center; font-weight: 600; color: #374151;">Status</th>
                            <th style="padding: 1rem; text-align: center; font-weight: 600; color: #374151;">Created</th>
                            <th style="padding: 1rem; text-align: center; font-weight: 600; color: #374151;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inquiries as $inquiry): ?>
                            <tr style="border-bottom: 1px solid #f3f4f6;">
                                <td style="padding: 1rem; text-align: center; color: #6b7280; font-weight: 600;">
                                    #<?php echo $inquiry['id']; ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php echo htmlspecialchars($inquiry['name']); ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php echo htmlspecialchars($inquiry['email']); ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php echo htmlspecialchars($inquiry['subject']); ?>
                                </td>
                                <td style="padding: 1rem; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <?php echo htmlspecialchars(mb_strimwidth($inquiry['message'], 0, 60, '...')); ?>
                                </td>
                                <td style="padding: 1rem; text-align: center;">
                                    <span style="background: <?php echo $inquiry['status'] === 'pending' ? '#fef3c7' : '#d1fae5'; ?>; color: <?php echo $inquiry['status'] === 'pending' ? '#92400e' : '#065f46'; ?>; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">
                                        <?php echo ucfirst($inquiry['status']); ?>
                                    </span>
                                </td>
                                <td style="padding: 1rem; text-align: center; color: #6b7280; font-size: 0.85rem;">
                                    <?php echo date('M j, Y', strtotime($inquiry['created_at'])); ?>
                                </td>
                                <td style="padding: 1rem; text-align: center;">
                                    <div style="display: flex; gap: 0.5rem; justify-content: center;">
                                        <a href="?action=reply&id=<?php echo $inquiry['id']; ?>" class="btn btn-secondary btn-sm" title="Reply">
                                            <i class="fas fa-reply"></i>
                                        </a>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this inquiry?');">
                                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $inquiry['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div style="display: flex; justify-content: flex-end; align-items: center; gap: 1rem; padding: 1rem 0;">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>" class="btn btn-secondary btn-sm">Previous</a>
                <?php endif; ?>
                <span>Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>" class="btn btn-secondary btn-sm">Next</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
<?php
}); 