<?php

include '../includes/header.php';

// Set page-specific content
$page_title = 'Contact Us';
$page_subtitle = 'We are here for you! How can we help?';

?>

<section class="cntct-banner">
    <div class="cntct-banner-overlay">
        <div class="cntct-banner-content">
            <h1><?php echo htmlspecialchars($page_title); ?></h1>
            <p><?php echo htmlspecialchars($page_subtitle); ?></p>
        </div>
    </div>
</section>

<!-- Main Contact Section -->
<main class="cntct-main-content">
    <div class="cntct-container">
        <div class="cntct-card">
            <div class="cntct-card-content">
                <!-- Left Column: Contact Information -->
                <div class="cntct-info-column">
                    <h2>Get in Touch</h2>
                    <p class="cntct-info-subtitle">Reach out to us for any health-related inquiries or appointments.</p>
                    
                    <div class="cntct-info-items">
                        <div class="cntct-info-item">
                            <div class="cntct-info-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="cntct-info-details">
                                <h4>Head Office</h4>
                                <p>Brgy. Sto Domingo, Village East Executive<br>Homes, Cainta, Rizal, Philippines</p>
                            </div>
                        </div>
                        
                        <div class="cntct-info-item">
                            <div class="cntct-info-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="cntct-info-details">
                                <h4>Phone Number</h4>
                                <p>(012) 3456-7890</p>
                            </div>
                        </div>
                        
                        <div class="cntct-info-item">
                            <div class="cntct-info-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="cntct-info-details">
                                <h4>Email Address</h4>
                                <p>villageeast@center.clinic</p>
                            </div>
                        </div>
                        
                        <div class="cntct-info-item">
                            <div class="cntct-info-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="cntct-info-details">
                                <h4>Operating Hours</h4>
                                <p>Monday - Friday: 8:00 AM - 5:00 PM<br>Saturday: 8:00 AM - 12:00 PM</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="cntct-social-media">
                        <h4>Follow Us</h4>
                        <div class="cntct-social-icons">
                            <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                            <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                            <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column: Contact Form -->
                <div class="cntct-form-column">
                    <h2>Send us a Message</h2>
                    <p class="cntct-form-subtitle">Fill out the form below and we'll get back to you as soon as possible.</p>
                    
                    <?php if (isset($_GET['inquiry']) && $_GET['inquiry'] === 'success'): ?>
                        <div style="max-width:480px;margin:0.5rem auto 1.2rem auto;padding:0.7rem 1.2rem;background:#d1f5e0;color:#14532d;border:1px solid #a7e6c1;border-radius:8px;text-align:left;font-size:1rem;display:flex;align-items:center;gap:0.5rem;box-shadow:0 2px 8px rgba(40,167,69,0.08);">
                            <span style="display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;background:#b6ecc7;border-radius:50%;"><svg width="14" height="14" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="10" fill="#22c55e"/><path d="M6 10.5L9 13.5L14 8.5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                            Thank you for reaching out! Your inquiry has been received. We will get back to you soon.
                        </div>
                    <?php endif; ?>

                    <form class="cntct-contact-form" action="../pages/submit-form.php" method="POST">
                        <div class="cntct-form-row">
                            <div class="cntct-form-group">
                                <label for="name">Full Name <span class="cntct-required-asterisk">*</span></label>
                                <input type="text" id="name" name="name" required placeholder="Enter your full name">
                            </div>
                            <div class="cntct-form-group">
                                <label for="email">Email Address <span class="cntct-required-asterisk">*</span></label>
                                <input type="email" id="email" name="email" required placeholder="Enter your email">
                            </div>
                        </div>
                        
                        <div class="cntct-form-row">
                            <div class="cntct-form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" placeholder="Enter your phone number">
                            </div>
                            <div class="cntct-form-group">
                                <label for="subject">Subject <span class="cntct-required-asterisk">*</span></label>
                                <select id="subject" name="subject" required>
                                    <option value="">Select a subject</option>
                                    <option value="appointment">Appointment Inquiry</option>
                                    <option value="services">Services Information</option>
                                    <option value="emergency">Emergency Contact</option>
                                    <option value="feedback">Feedback</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="cntct-form-group">
                            <label for="message">Your Message <span class="cntct-required-asterisk">*</span></label>
                            <textarea id="message" name="message" rows="6" required placeholder="Tell us how we can help you..."></textarea>
                        </div>
                        
                        <div class="cntct-form-group">
                            <button type="submit" class="cntct-submit-btn">
                                <i class="fas fa-paper-plane"></i>
                                Send Message
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>

<style>
    .cntct-banner {
    height: 50vh;
    min-height: 300px;
    background: url('../assets/images/contact-us.jpg') no-repeat center 30%;
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
</style>