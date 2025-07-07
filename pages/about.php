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
    
?>

<main class="main-content">
    <div class="container">
        <div class="about-content-wide smooth-box">
            <p class="about-welcome">
                Welcome to Village East Clinic, the dedicated health service portal of Village East Executive Homes, located in Barangay Sto. Domingo, Cainta, Rizal. Our clinic was established with one goal in mind: to bring quality, accessible, and community-focused healthcare closer to every resident.
            </p>
            <h2 class="about-section-title">Our Mission and Values</h2>
            <p class="about-section-text">
                We believe that a healthy community is a strong community. Our mission is to provide compassionate, reliable, and inclusive healthcare to all residents of Village East. We value transparency, respect, and service excellence in everything we do.
            </p>
            <h2 class="about-section-title">Our Story</h2>
            <p class="about-section-text">
                Over the years, Village East Clinic has grown alongside the community it serves. From humble beginnings as a basic health post, we've expanded our services and reach—adapting to the evolving needs of our barangay. This website is a continuation of that growth: a step forward in making health services more efficient and accessible for all.
            </p>
            <h2 class="about-section-title">Our Healthcare Philosophy</h2>
            <p class="about-section-text">
                At Village East Clinic, we believe that healthcare should be holistic, proactive, and rooted in community connection. We focus not only on treating illness but also on promoting wellness through education, early intervention, and open communication.
            </p>
        </div>
    </div>
</main>

<style>
.about-content-wide.smooth-box {
    max-width: 1000px;
    margin: 0 auto;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(51,182,255,0.08);
    padding: 2.5rem 2.5rem 2rem 2.5rem;
    font-family: 'Nunito', 'Arimo', Arial, sans-serif;
}
.about-welcome {
    font-size: 1.1rem;
    color: #2c3e50;
    line-height: 1.7;
    margin-bottom: 2rem;
    text-align: center;
    font-family: 'Nunito', 'Arimo', Arial, sans-serif;
}
.about-section-title {
    color: #33b6ff;
    font-family: 'Nunito', 'Arimo', Arial, sans-serif;
    font-size: 1.3rem;
    font-weight: bold;
    margin-top: 2rem;
    margin-bottom: 0.5rem;
}
.about-section-text {
    font-size: 1.05rem;
    color: #2c3e50;
    line-height: 1.7;
    margin-bottom: 1.5rem;
    text-align: justify;
    font-family: 'Nunito', 'Arimo', Arial, sans-serif;
}
</style>

<?php include '../includes/footer.php'; ?>