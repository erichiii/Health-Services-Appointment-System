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
        <h1 style="color: #33b6ff; font-family: 'Nunito', sans-serif; font-weight: 700; text-align: center; margin-bottom: 0.5rem;">About Us</h1>
        <p class="page-subtitle" style="text-align: center; color: #7f8c8d; font-size: 1.1rem; margin-bottom: 2.5rem;">Learn more about Village East Clinic and our commitment to healthcare</p>

        <p style="font-size: 1.1rem; color: #2c3e50; line-height: 1.7; margin-bottom: 2rem; text-align: center;">
            Welcome to Village East Clinic, the dedicated health service portal of Village East Executive Homes, located in Barangay Sto. Domingo, Cainta, Rizal. Our clinic was established with one goal in mind: to bring quality, accessible, and community-focused healthcare closer to every resident.
        </p>

        <h2 style="color: #33b6ff; font-family: 'Nunito', sans-serif; font-size: 1.3rem; margin-top: 2rem; margin-bottom: 0.5rem;">Our Mission and Values</h2>
        <p style="font-size: 1.05rem; color: #2c3e50; line-height: 1.7; margin-bottom: 1.5rem;">
            We believe that a healthy community is a strong community. Our mission is to provide compassionate, reliable, and inclusive healthcare to all residents of Village East. We value transparency, respect, and service excellence in everything we do.
        </p>

        <h2 style="color: #33b6ff; font-family: 'Nunito', sans-serif; font-size: 1.3rem; margin-top: 2rem; margin-bottom: 0.5rem;">Our Story</h2>
        <p style="font-size: 1.05rem; color: #2c3e50; line-height: 1.7; margin-bottom: 1.5rem;">
            Over the years, Village East Clinic has grown alongside the community it serves. From humble beginnings as a basic health post, we've expanded our services and reach—adapting to the evolving needs of our barangay. This website is a continuation of that growth: a step forward in making health services more efficient and accessible for all.
        </p>

        <h2 style="color: #33b6ff; font-family: 'Nunito', sans-serif; font-size: 1.3rem; margin-top: 2rem; margin-bottom: 0.5rem;">Our Healthcare Philosophy</h2>
        <p style="font-size: 1.05rem; color: #2c3e50; line-height: 1.7;">
            At Village East Clinic, we believe that healthcare should be holistic, proactive, and rooted in community connection. We focus not only on treating illness but also on promoting wellness through education, early intervention, and open communication.
        </p>
    </div>
</main>

<?php include '../includes/footer.php'; ?>