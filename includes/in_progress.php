<?php
// Default values if not set
$page_title = isset($page_title) ? $page_title : 'Page';
$page_subtitle = isset($page_subtitle) ? $page_subtitle : 'Coming soon with new features and content';
$page_features = isset($page_features) ? $page_features : [
    'Enhanced user experience',
    'Updated content and information',
    'Improved functionality',
    'Mobile-responsive design'
];
?>

<main class="main-content">
    <div class="container">
        <div class="page-header">
            <h1><?php echo htmlspecialchars($page_title); ?></h1>
            <p class="page-subtitle"><?php echo htmlspecialchars($page_subtitle); ?></p>
        </div>

        <div class="progress-notice">
            <div class="progress-icon">
                <i class="fas fa-tools"></i>
            </div>
            <h2>Page Under Construction</h2>
            <p>We're working hard to bring you the best experience. This page will be available soon with:</p>
            <ul class="feature-list">
                <?php foreach ($page_features as $feature): ?>
                    <li><i class="fas fa-check"></i> <?php echo htmlspecialchars($feature); ?></li>
                <?php endforeach; ?>
            </ul>
            <p>Thank you for your patience!</p>
        </div>
    </div>
</main>

<style>
    .main-content {
        min-height: 70vh;
        padding: 2rem 0;
        background-color: #f8f9fa;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 2rem;
    }

    .page-header {
        text-align: center;
        margin-bottom: 3rem;
    }

    .page-header h1 {
        font-size: 2.5rem;
        color: #2c3e50;
        margin-bottom: 0.5rem;
        font-family: 'Nunito', sans-serif;
        font-weight: 700;
    }

    .page-subtitle {
        font-size: 1.1rem;
        color: #7f8c8d;
        margin-bottom: 0;
    }

    .progress-notice {
        background: white;
        border-radius: 12px;
        padding: 3rem;
        text-align: center;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        max-width: 600px;
        margin: 0 auto;
    }

    .progress-icon {
        font-size: 3rem;
        color: #3498db;
        margin-bottom: 1.5rem;
    }

    .progress-notice h2 {
        color: #2c3e50;
        margin-bottom: 1rem;
        font-family: 'Nunito', sans-serif;
        font-weight: 600;
    }

    .progress-notice p {
        color: #5a6c7d;
        line-height: 1.6;
        margin-bottom: 1.5rem;
    }

    .feature-list {
        list-style: none;
        padding: 0;
        margin: 2rem 0;
        text-align: left;
        display: inline-block;
    }

    .feature-list li {
        color: #5a6c7d;
        margin-bottom: 0.8rem;
        display: flex;
        align-items: center;
    }

    .feature-list li i {
        color: #27ae60;
        margin-right: 0.8rem;
        font-size: 0.9rem;
    }

    @media (max-width: 768px) {
        .container {
            padding: 0 1rem;
        }

        .page-header h1 {
            font-size: 2rem;
        }

        .progress-notice {
            padding: 2rem 1.5rem;
        }
    }
</style>