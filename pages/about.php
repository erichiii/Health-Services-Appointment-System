<?php

    include '../includes/header.php';

    // Set page-specific content
    $page_title = 'About Us';
    $page_subtitle = 'Learn more about Village East Clinic and our commitment to healthcare';
    $page_features = [
        'Our mission and values',
        'Medical team and staff profiles',
        'Clinic history and milestones',
        'Healthcare philosophy and approach'
    ];
    
    // Include the reusable progress page
    include '../includes/in_progress.php';
?>

<main class="main-content">
    <div class="container about-section" style="max-width: 800px; margin: 0 auto; padding: 3rem 1.5rem; background: #fff; border-radius: 12px; box-shadow: 0 4px 12px rgba(51,182,255,0.08); margin-top: 3rem; margin-bottom: 3rem;">
        <h1 style="color: #33b6ff; font-family: 'Nunito', sans-serif; font-weight: 700; text-align: center; margin-bottom: 1.5rem;">About Us</h1>
        <p style="font-size: 1.1rem; color: #2c3e50; line-height: 1.7; text-align: center; margin-bottom: 2.5rem;">
            Welcome to Village East Clinic, the official online platform of Village East Executive Homes located in Barangay Sto. Domingo, Cainta, Rizal.
        </p>
        <p style="font-size: 1.1rem; color: #2c3e50; line-height: 1.7; margin-bottom: 1.5rem;">
            This website was created to support our community's growing healthcare needs by making health services more accessible, responsive, and community-centered. Whether it's receiving important health announcements, staying informed about ongoing programs, or reaching out to clinic staff, Village East Clinic is here to make communication and service delivery faster and more efficient.
        </p>
        <p style="font-size: 1.1rem; color: #2c3e50; line-height: 1.7;">
            We aim to bridge the gap between our residents and their local health providers—ensuring that care, support, and health information are always within reach. This platform reflects our ongoing commitment to building a healthier and more connected barangay for all.
        </p>
    </div>
</main>

<?php include '../includes/footer.php'; ?>