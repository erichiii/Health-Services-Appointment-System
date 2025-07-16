<?php

include '../includes/header.php';

// Set page-specific content
$page_title = 'Get In Touch';
$page_subtitle = 'We are here for you! How can we help?';

?>

<main class="cntct-main-content">
    <div class="cntct-container">
        <div class="cntct-flex-layout" style="display: flex; gap: 2.5rem; align-items: flex-start; justify-content: center; min-height: 600px;">
            <!-- Left: Form Section -->
            <div class="cntct-form-side" style="flex: 1 1 0; max-width: 480px;">
                <h1 style="margin-bottom: 0.25em;"><?php echo htmlspecialchars($page_title); ?></h1>
                <p class="cntct-page-subtitle" style="margin-bottom: 1.5em; color: #555; font-size: 1.1em;">
                    <?php echo htmlspecialchars($page_subtitle); ?>
                </p>
                <form class="cntct-contact-form" action="../pages/submit-form.php" method="POST">
                    <div class="cntct-form-group">
                        <label for="name">Name <span class="cntct-required-asterisk">*</span></label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="cntct-form-group">
                        <label for="email">Email <span class="cntct-required-asterisk">*</span></label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="cntct-form-group">
                        <label for="message">Your Message <span class="cntct-required-asterisk">*</span></label>
                        <textarea id="message" name="message" rows="5" required></textarea>
                    </div>
                    <div class="cntct-form-group">
                        <button type="submit" class="cntct-submit-btn">Submit</button>
                    </div>
                </form>
            </div>

            <!-- Right: Contact Info (aligned at the bottom) -->
            <div class="cntct-info-side" style="flex: 1 1 0; min-width: 320px; max-width: 400px; display: flex; flex-direction: column; align-items: flex-start; gap: 2em; justify-content: flex-end; min-height: 100%;">
                <div class="cntct-contact-info-section" style="width: 100%;">
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        <li style="display: flex; align-items: center; margin-bottom: 1.2em;">
                            <span style="margin-right: 1em;"><i class="fas fa-map-marker-alt"></i></span>
                            <span>Brgy. Sto Domingo, Village East Executive<br>Homes, Cainta, Rizal, Philippines</span>
                        </li>
                        <li style="display: flex; align-items: center; margin-bottom: 1.2em;">
                            <span style="margin-right: 1em;"><i class="fas fa-phone"></i></span>
                            <span>(012) 3456-7890</span>
                        </li>
                        <li style="display: flex; align-items: center; margin-bottom: 1.2em;">
                            <span style="margin-right: 1em;"><i class="fas fa-envelope"></i></span>
                            <span>villageeast@center.clinic</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>