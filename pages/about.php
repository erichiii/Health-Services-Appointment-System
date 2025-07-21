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

<section class="about-banner">
    <div class="about-banner-content">
        <h1><?php echo htmlspecialchars($page_title); ?></h1>
        <p><?php echo htmlspecialchars($page_subtitle); ?></p>
    </div>
</section>

<main class="about-main-content">
    <div class="about-container">
        <div class="about-section-content">
            <p class="about-welcome-text">
                Welcome to Village East Clinic, the dedicated health service portal of Village East Executive Homes, located in Barangay Sto. Domingo, Cainta, Rizal. Our clinic was established with one goal in mind: to bring quality, accessible, and community-focused healthcare closer to every resident.
            </p>
        </div>

        <!-- Mission Section -->
        <section class="about-section">
            <div class="about-section-content">
                <h2 class="about-section-title">Our Mission and Values</h2>
                <div class="about-section-divider"></div>
                <p class="about-section-text">
                    We believe that a healthy community is a strong community. Our mission is to provide compassionate, reliable, and inclusive healthcare to all residents of Village East. We value transparency, respect, and service excellence in everything we do.
                </p>
            </div>
        </section>

        <!-- Story Section -->
        <section class="about-section about-section-alt">
            <div class="about-section-content">
                <h2 class="about-section-title">Our Story</h2>
                <div class="about-section-divider"></div>
                <p class="about-section-text">
                    Over the years, Village East Clinic has grown alongside the community it serves. From humble beginnings as a basic health post, we've expanded our services and reach—adapting to the evolving needs of our barangay. This website is a continuation of that growth: a step forward in making health services more efficient and accessible for all.
                </p>
            </div>
        </section>

        <!-- Philosophy Section -->
        <section class="about-section">
            <div class="about-section-content">
                <h2 class="about-section-title">Our Healthcare Philosophy</h2>
                <div class="about-section-divider"></div>
                <p class="about-section-text">
                    At Village East Clinic, we believe that healthcare should be holistic, proactive, and rooted in community connection. We focus not only on treating illness but also on promoting wellness through education, early intervention, and open communication.
                </p>
            </div>
        </section>
    </div>
</main>

<style>
/* About Page Main Content */
.about-main-content {
    background: #f8f9fa;
    padding: 4rem 0;
    min-height: 70vh;
}

.about-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 0 2rem;
}

/* Welcome Section - Special styling for intro */
.about-welcome-section {
    background: #ffffff;
    margin: -2rem auto 3rem auto;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(51, 182, 255, 0.08);
    position: relative;
    z-index: 2;
}

.about-welcome-section .about-section-content {
    padding: 3rem 2.5rem;
}

.about-welcome-text {
    font-size: 1.2rem;
    color: #2c3e50;
    line-height: 1.8;
    margin: 0;
    text-align: center;
    font-family: 'Nunito', 'Arimo', Arial, sans-serif;
    font-weight: 400;
}

/* Regular Sections */
.about-section {
    margin-bottom: 4rem;
}

.about-section-alt {
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 2px 12px rgba(51, 182, 255, 0.06);
}

.about-section-content {
    padding: 2.5rem 2rem;
}

.about-section-alt .about-section-content {
    padding: 3rem 2.5rem;
}

.about-section-title {
    color: #33b6ff;
    font-family: 'Nunito', sans-serif;
    font-size: 1.8rem;
    font-weight: 700;
    margin: 0 0 0.5rem 0;
    text-align: center;
}

.about-section-divider {
    width: 60px;
    height: 3px;
    background: #33b6ff;
    margin: 0 auto 2rem auto;
    border-radius: 2px;
}

.about-section-text {
    font-size: 1.1rem;
    color: #2c3e50;
    line-height: 1.8;
    margin: 0;
    text-align: center;
    font-family: 'Arimo', Arial, sans-serif;
    font-weight: 400;
    max-width: 800px;
    margin: 0 auto;
}

/* Responsive Design */
@media (max-width: 768px) {
    .about-main-content {
        padding: 2rem 0;
    }
    
    .about-container {
        padding: 0 1rem;
    }
    
    .about-welcome-section {
        margin: -1rem auto 2rem auto;
        border-radius: 8px;
    }
    
    .about-welcome-section .about-section-content,
    .about-section-alt .about-section-content {
        padding: 2rem 1.5rem;
    }
    
    .about-section-content {
        padding: 2rem 1rem;
    }
    
    .about-welcome-text {
        font-size: 1.1rem;
        line-height: 1.7;
    }
    
    .about-section-title {
        font-size: 1.5rem;
    }
    
    .about-section-text {
        font-size: 1rem;
        line-height: 1.7;
    }
    
    .about-section {
        margin-bottom: 2.5rem;
    }
}

@media (max-width: 480px) {
    .about-main-content {
        padding: 1.5rem 0;
    }
    
    .about-welcome-section .about-section-content,
    .about-section-alt .about-section-content {
        padding: 1.5rem 1rem;
    }
    
    .about-section-content {
        padding: 1.5rem 0.5rem;
    }
    
    .about-welcome-text {
        font-size: 1rem;
    }
    
    .about-section-title {
        font-size: 1.4rem;
    }
    
    .about-section-text {
        font-size: 0.95rem;
    }
}
</style>

<?php include '../includes/footer.php'; ?>