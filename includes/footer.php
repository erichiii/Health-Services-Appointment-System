<footer class = "main-footer">
    <div class = "footer-container">

    <!-- Contact Div -->
        <div class = "contact-info">
            <div class = "logo">
                <img src = "<?php echo isset($base_path) ? $base_path : ''; ?>images/logo.png" alt = "Clinic Logo" class = "clinic-logo">
            </div>
            <p><i class = "fa fa-map-marker-alt" style = "color: white;"></i><strong>&emsp;Brgy. Sto Domingo, Village East Executive</strong></p> 
            <p><strong>&emsp;&emsp;Homes, Cainta, Rizal, Philippines</strong></p>
            <p><i class="fa fa-phone" style = "color: white;"></i>&emsp;(012) 3456-7890</p>
            <p><i class="fa fa-envelope" style = "color: white;"></i>&emsp;villageeast@center.clinic</p>
        </div>
    
    <!-- Services Div -->
        <div class = "footer-col">
            <h4>SERVICES</h4>
            <ul>
                <li><a href = "<?php echo isset($base_path) ? $base_path : ''; ?>index.php#services-section">Medical Consultation</a></li>
                <li><a href = "<?php echo isset($base_path) ? $base_path : ''; ?>index.php#services-section">Vaccination Services</a></li>
                <li><a href = "<?php echo isset($base_path) ? $base_path : ''; ?>index.php#services-section">Health Check-ups</a></li>
                <li><a href = "<?php echo isset($base_path) ? $base_path : ''; ?>index.php#services-section">Urgent Care</a></li>
            </ul>
        </div>
    
    <!-- Health Programs Div -->
        <div class = "footer-col">
            <h4>HEALTH PROGRAMS</h4>
            <ul>
                <li><a href = "<?php echo isset($base_path) ? $base_path : ''; ?>pages/programs.php">Vaccine Registration</a></li>
                <li><a href = "<?php echo isset($base_path) ? $base_path : ''; ?>pages/programs.php">Program Enrollment</a></li>
                <li><a href = "<?php echo isset($base_path) ? $base_path : ''; ?>pages/reservation.php">Appointment Booking</a></li>
            </ul>
        </div>

    <!-- Announcements Div + Connect -->
        <div class = "footer-col">
            <h4>ANNOUNCEMENTS</h4>
            <ul>
                <li><a href="<?php echo isset($base_path) ? $base_path : ''; ?>pages/announcements.php">General Information</a></li>
                <li><a href="<?php echo isset($base_path) ? $base_path : ''; ?>pages/announcements.php">Health Articles</a></li>
                <li><a href="<?php echo isset($base_path) ? $base_path : ''; ?>pages/announcements.php">News Network</a></li>
            </ul>
            
            <h4>CONNECT</h4>
            <div class="social-icons">
                <a href="#"><i class="fab fa-facebook-square"></i></a>
                <a href="#"><i class="fab fa-linkedin"></i></a>
                <a href="#"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
    </div>
</footer>