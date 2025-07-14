<link rel="stylesheet" href="Health-Services-Appointment-System\assets\layout.css">

<?php

include '../includes/header.php';

// Set page-specific content
$page_title = 'Contact Us';
$page_subtitle = 'Get in touch with our clinic';
$page_features = [
    'Interactive contact form',
    'Location and directions',
    'Office hours and availability',
    'Emergency contact information'
];

?>

<main class="main-content">
    <div class="container">
        <div class="page-header">
            <h1><?php echo htmlspecialchars($page_title); ?></h1>
            <p class="page-subtitle"><?php echo htmlspecialchars($page_subtitle); ?></p>
        </div>
    </div>
    
    <div class="container">
        <div class="contact-content">
            <!-- Contact Information Section -->
            <div class="contact-info-section smooth-box">
                <h2 class="contact-section-title">Get In Touch</h2>
                <div class="contact-grid">
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="contact-details">
                            <h3>Location</h3>
                            <p>Village East Executive Homes<br>
                            Barangay Sto. Domingo<br>
                            Cainta, Rizal</p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="contact-details">
                            <h3>Office Hours</h3>
                            <p>Monday - Friday: 8:00 AM - 5:00 PM<br>
                            Saturday: 8:00 AM - 12:00 PM<br>
                            Sunday: Closed</p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="contact-details">
                            <h3>Phone</h3>
                            <p>Main: (012) 3456-7890<br>
                            Emergency: (02) 8-XXX-XXXX</p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="contact-details">
                            <h3>Email</h3>
                            <p style="min-height:3.5em;display:flex;flex-direction:column;justify-content:center;">
                                ve@center.clinic<br>
                                appointments@center.clinic
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Form Section -->
            <div class="contact-form-section smooth-box">
                <h2 class="contact-section-title">Send Us a Message</h2>
                <form class="contact-form" action="../pages/submit-form.php" method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Full Name <span class="required-asterisk">*</span></label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address <span class="required-asterisk">*</span></label>
                            <input type="email" id="email" name="email" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone">
                        </div>
                        <div class="form-group">
                            <label for="subject">Subject <span class="required-asterisk">*</span></label>
                            <select id="subject" name="subject" required>
                                <option value="">Select a subject</option>
                                <option value="appointment">Appointment Inquiry</option>
                                <option value="general">General Information</option>
                                <option value="feedback">Feedback</option>
                                <option value="emergency">Emergency</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Message <span class="required-asterisk">*</span></label>
                        <textarea id="message" name="message" rows="6" required placeholder="Please describe your inquiry or concern..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="submit-btn">Send Message</button>
                    </div>
                </form>
            </div>

            <!-- Map Section -->
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>