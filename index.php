<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Village East Clinic</title>
</head>

<body>

    <?php include 'includes/header.php'; ?>
    <?php include 'includes/db_functions.php'; ?>

    <div class="hero-section">
        <!-- Hero Carousel -->
        <div class="hero-carousel">
            <div class="hero-slide active" data-bg="hero1">
                <div class="hero-overlay"></div>
            </div>
            <div class="hero-slide" data-bg="hero2">
                <div class="hero-overlay"></div>
            </div>
            <div class="hero-slide" data-bg="hero3">
                <div class="hero-overlay"></div>
            </div>
            <div class="hero-slide" data-bg="hero4">
                <div class="hero-overlay"></div>
            </div>
        </div>
        
        <!-- Hero Content -->
        <div class="hero-section-content">
            <h1>Bringing Health Services Closer to Home</h1>
            <p>A centralized and user-friendly platform for efficient services, clear communication, and stronger community health.</p>
            <a href="pages/about.php" class="about-us-btn">About Us</a>
        </div>
        
        <!-- Carousel Indicators -->
        <div class="hero-indicators">
            <span class="indicator active" data-slide="0"></span>
            <span class="indicator" data-slide="1"></span>
            <span class="indicator" data-slide="2"></span>
            <span class="indicator" data-slide="3"></span>
        </div>
        
        <!-- Carousel Navigation -->
        <button class="hero-nav prev" onclick="changeSlide(-1)">&#8249;</button>
        <button class="hero-nav next" onclick="changeSlide(1)">&#8250;</button>
    </div>

    <div class="first-section">
        <div class="services-schedule">
            <h2><strong>Services Schedule</strong></h2>
            <p class="subtitle">Select a date to see special events and programs. Regular appointments available daily.</p>

            <!-- Calendar -->
            <?php
            $current_month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
            $current_year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
            $selected_date = isset($_GET['date']) ? $_GET['date'] : null;

            // ensure valid month and year
            if ($current_month < 1) {
                $current_month = 12;
                $current_year--;
            } elseif ($current_month > 12) {
                $current_month = 1;
                $current_year++;
            }

            // Get events from database using the new functions
            $events = getCalendarEvents($current_year, $current_month);
            
            // Get service category routes
            $categoryRoutes = getServiceCategoryRoutes();

            // get calendar data
            $first_day = mktime(0, 0, 0, $current_month, 1, $current_year);
            $days_in_month = date('t', $first_day);
            $day_of_week = date('w', $first_day);
            $month_name = date('F', $first_day);
            $today = date('Y-m-d');

            // calculate previous and next month/year
            $prev_month = $current_month - 1;
            $prev_year = $current_year;
            if ($prev_month < 1) {
                $prev_month = 12;
                $prev_year--;
            }

            $next_month = $current_month + 1;
            $next_year = $current_year;
            if ($next_month > 12) {
                $next_month = 1;
                $next_year++;
            }
            ?>

            <div class="calendar-container" id="calendar-section">
                <div class="calendar">
                    <div class="calendar-header">
                        <a href="?month=<?php echo $prev_month; ?>&year=<?php echo $prev_year; ?><?php echo $selected_date ? '&date=' . $selected_date : ''; ?>#calendar-section" class="calendar-nav-btn">‹</a>
                        <h3><?php echo $month_name . ' ' . $current_year; ?></h3>
                        <a href="?month=<?php echo $next_month; ?>&year=<?php echo $next_year; ?><?php echo $selected_date ? '&date=' . $selected_date : ''; ?>#calendar-section" class="calendar-nav-btn">›</a>
                    </div>

                    <div class="calendar-grid">
                        <div class="day">Sun</div>
                        <div class="day">Mon</div>
                        <div class="day">Tue</div>
                        <div class="day">Wed</div>
                        <div class="day">Thu</div>
                        <div class="day">Fri</div>
                        <div class="day">Sat</div>

                        <?php
                        // previous month's trailing days
                        $prev_month_days = date('t', mktime(0, 0, 0, $current_month - 1, 1, $current_year));
                        for ($i = $day_of_week - 1; $i >= 0; $i--) {
                            $day = $prev_month_days - $i;
                            echo '<div class="calendar-date other-month">' . $day . '</div>';
                        }

                        // current month days
                        for ($day = 1; $day <= $days_in_month; $day++) {
                            $date_string = sprintf('%04d-%02d-%02d', $current_year, $current_month, $day);
                            $classes = ['calendar-date'];

                            // check if it's today
                            if ($date_string === $today) {
                                $classes[] = 'today';
                            }

                            // check if date is selected
                            if ($selected_date === $date_string) {
                                $classes[] = 'selected';
                            }

                            // check if date has events
                            if (isset($events[$date_string])) {
                                $classes[] = 'has-event';
                                
                                // Get event categories for this date
                                $categories = array_unique(array_map(function($event) {
                                    return $event['category'];
                                }, $events[$date_string]));
                                
                                // Add category-specific classes
                                if (count($categories) > 1) {
                                    $classes[] = 'has-mixed';
                                } else {
                                    $classes[] = 'has-' . $categories[0];
                                }
                                
                                $event_names = array_map(function($event) {
                                    return $event['service_name'];
                                }, $events[$date_string]);
                                $title = 'title="' . implode(', ', $event_names) . '"';
                            } else {
                                $title = '';
                            }

                            $class_string = implode(' ', $classes);
                            $url_params = "?month={$current_month}&year={$current_year}&date={$date_string}#calendar-section";
                            echo '<a href="' . $url_params . '" class="' . $class_string . '" ' . $title . '>' . $day . '</a>';
                        }

                        // next month's leading days to fill the grid
                        $total_cells = 42; // 6 rows × 7 days
                        $cells_used = $day_of_week + $days_in_month;
                        $remaining_cells = $total_cells - $cells_used;

                        for ($day = 1; $day <= $remaining_cells; $day++) {
                            echo '<div class="calendar-date other-month">' . $day . '</div>';
                        }
                        ?>
                    </div>

                    <!-- Color Legend -->
                    <div class="calendar-legend">
                        <div class="legend-item">
                            <div class="legend-dot vaccine"></div>
                            <span>Vaccine Registration</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-dot program"></div>
                            <span>Program Enrollment</span>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($selected_date): ?>
                <div class="event-display-box">
                    <div class="event-header">
                        <h4>Services for <?php echo date('F j, Y', strtotime($selected_date)); ?></h4>
                        <a href="?month=<?php echo $current_month; ?>&year=<?php echo $current_year; ?>#calendar-section" class="close-btn">&times;</a>
                    </div>
                    <div class="event-content">
                        <?php if (isset($events[$selected_date]) && !empty($events[$selected_date])): ?>
                            <div class="event-list">
                                <?php 
                                foreach ($events[$selected_date] as $event): 
                                    $categoryClass = $event['category'] . '-card';
                                ?>
                                    <div class="event-item <?php echo $categoryClass; ?>">
                                        <div class="event-details">
                                            <h5 class="event-name">
                                                <?php echo htmlspecialchars($event['service_name']); ?>
                                            </h5>
                                            <p class="event-time"><?php echo date('g:i A', strtotime($event['start_time'])) . ' - ' . date('g:i A', strtotime($event['end_time'])); ?></p>
                                            <p class="event-slots">
                                                <?php if ($event['available_slots'] > 0): ?>
                                                    <span class="slots-available">✅ <?php echo $event['available_slots']; ?> slots available</span>
                                                <?php else: ?>
                                                    <span class="slots-full">❌ Fully booked</span>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                        <div class="event-actions">
                                            <?php if ($event['available_slots'] > 0): ?>
                                                <a href="<?php echo $categoryRoutes[$event['category']]['page']; ?>?service_id=<?php echo $event['service_id']; ?>&schedule_id=<?php echo $event['schedule_id']; ?>&type=<?php echo $event['category']; ?>&date=<?php echo $selected_date; ?>" 
                                                   class="book-event-btn category-<?php echo $event['category']; ?>">
                                                    <?php echo $categoryRoutes[$event['category']]['action']; ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="fully-booked-text">Fully Booked</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="no-events">
                                <p>No special events scheduled for this date.</p>
                                <p><strong>Need a regular appointment?</strong></p>
                                <p>General consultations, health checkups, and routine services are available daily.</p>
                                <div class="appointment-buttons">
                                    <a href="pages/appointment.php?date=<?php echo $selected_date; ?>" class="contact-btn">Book General Appointment</a>
                                    <a href="pages/contact.php" class="contact-btn secondary">Contact Us</a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <a href="pages/programs.php" class="view-all-programs-btn">View All Programs and Schedule</a>
        </div>

        <div class="announcements-column">
            <h2><strong>Announcements</strong></h2>
            
            <?php
            // Get announcements from database
            try {
                $db = getDbConnection();
                $stmt = $db->prepare("SELECT * FROM announcements WHERE is_active = 1 ORDER BY announcement_date DESC LIMIT 3");
                $stmt->execute();
                $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (!empty($announcements)) {
                    foreach ($announcements as $announcement) {
                        echo '<div class="announcement-item">';
                        echo '<div class="item-content">';
                        echo '<div class="item-title">' . htmlspecialchars($announcement['title']) . '</div>';
                        echo '<div class="item-date">📅 ' . date('F j, Y', strtotime($announcement['announcement_date'])) . '</div>';
                        echo '</div>';
                        echo '<div class="item-arrow">→</div>';
                        echo '</div>';
                    }
                } else {
                    // Fallback to placeholder if no announcements
                    echo '<div class="announcement-item">';
                    echo '<div class="item-content">';
                    echo '<div class="item-title">Anti-Rabies Vaccination</div>';
                    echo '<div class="item-date">📅 June 7, 2025</div>';
                    echo '</div>';
                    echo '<div class="item-arrow">→</div>';
                    echo '</div>';
                }
            } catch (PDOException $e) {
                error_log("Database error: " . $e->getMessage());
                // Fallback to placeholder
                echo '<div class="announcement-item">';
                echo '<div class="item-content">';
                echo '<div class="item-title">Anti-Rabies Vaccination</div>';
                echo '<div class="item-date">📅 June 7, 2025</div>';
                echo '</div>';
                echo '<div class="item-arrow">→</div>';
                echo '</div>';
            }
            ?>
            
            <a href="pages/announcements.php" class="view-all-btn">View All Announcements</a>
        </div>
    </div>

    <div class="second-section" id="services-section">
        <div class="our-services">
            <h2><strong>Our Services</strong></h2>

            <div class="card-grid cols-4">
                <div class="card">
                    <div class="card-icon"><i class="fas fa-user-md"></i></div>
                    <h3 class="card-title">Medical Consultation</h3>
                    <p class="card-text">Medical consultations with experienced healthcare providers for comprehensive health assessments and personalized treatment plans.</p>
                </div>

                <div class="card">
                    <div class="card-icon"><i class="fas fa-syringe"></i></div>
                    <h3 class="card-title">Vaccination Services</h3>
                    <p class="card-text">Complete vaccination programs for all ages including routine immunizations, travel vaccines, and seasonal flu shots to protect your health.</p>
                </div>

                <div class="card">
                    <div class="card-icon"><i class="fas fa-heartbeat"></i></div>
                    <h3 class="card-title">Health Check-Ups</h3>
                    <p class="card-text">Regular comprehensive health screenings and check-ups to monitor your well-being and detect potential health issues early.</p>
                </div>

                <div class="card">
                    <div class="card-icon"><i class="fas fa-ambulance"></i></div>
                    <h3 class="card-title">Emergency Care</h3>
                    <p class="card-text">Immediate medical attention and urgent care services available for emergency situations and critical health conditions.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="call-to-action-section">
        <div class="call-to-action-content">
            <h2><strong>Visit Us Today</strong></h2>
            <p class="subtitle">Here to serve you with quality healthcare services.</p>
            <div class="card-grid cols-3">
                <div class="card">
                    <div class="icon"><i class="far fa-clock"></i></div>
                    <h3 class="card-title">Operating Hours</h3>
                    <p class="card-text">Mon-Fri: 8:00 AM - 5:00 PM</p>
                    <p class="card-text">Sat: 8:00 AM - 12:00 PM</p>
                    <p class="card-text">Sun: Closed</p>
                </div>

                <div class="card">
                    <div class="icon"><i class="fas fa-phone"></i></div>
                    <h3 class="card-title">Emergency Hotline</h3>
                    <p class="card-text">24/7 Emergency Services</p>
                    <p class="card-text"><strong>(02) 123-4567</strong></p>
                </div>

                <div class="card">
                    <div class="icon"><i class="far fa-heart"></i></div>
                    <h3 class="card-title">Health Programs</h3>
                    <p class="card-text">Free vaccination programs</p>
                    <p class="card-text">Health education sessions</p>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Hero Carousel JavaScript
        let currentSlide = 0;
        const slides = document.querySelectorAll('.hero-slide');
        const indicators = document.querySelectorAll('.indicator');
        const totalSlides = slides.length;
        
        // Auto-play interval (5 seconds)
        let autoPlayInterval;
        
        function showSlide(index) {
            // Remove active class from all slides and indicators
            slides.forEach(slide => slide.classList.remove('active'));
            indicators.forEach(indicator => indicator.classList.remove('active'));
            
            // Add active class to current slide and indicator
            slides[index].classList.add('active');
            indicators[index].classList.add('active');
            
            currentSlide = index;
        }
        
        function nextSlide() {
            const next = (currentSlide + 1) % totalSlides;
            showSlide(next);
        }
        
        function prevSlide() {
            const prev = (currentSlide - 1 + totalSlides) % totalSlides;
            showSlide(prev);
        }
        
        function changeSlide(direction) {
            if (direction === 1) {
                nextSlide();
            } else {
                prevSlide();
            }
            resetAutoPlay();
        }
        
        function goToSlide(index) {
            showSlide(index);
            resetAutoPlay();
        }
        
        function startAutoPlay() {
            autoPlayInterval = setInterval(nextSlide, 5000); // 5 seconds
        }
        
        function stopAutoPlay() {
            clearInterval(autoPlayInterval);
        }
        
        function resetAutoPlay() {
            stopAutoPlay();
            startAutoPlay();
        }
        
        // Initialize carousel
        document.addEventListener('DOMContentLoaded', function() {
            // Add click handlers to indicators
            indicators.forEach((indicator, index) => {
                indicator.addEventListener('click', () => goToSlide(index));
            });
            
            // Start auto-play
            startAutoPlay();
            
            // Pause auto-play on hover
            const heroSection = document.querySelector('.hero-section');
            heroSection.addEventListener('mouseenter', stopAutoPlay);
            heroSection.addEventListener('mouseleave', startAutoPlay);
            
            // Keyboard navigation
            document.addEventListener('keydown', function(e) {
                if (e.key === 'ArrowLeft') {
                    changeSlide(-1);
                } else if (e.key === 'ArrowRight') {
                    changeSlide(1);
                }
            });
        });
        
        // Touch/swipe support for mobile
        let touchStartX = 0;
        let touchEndX = 0;
        
        const heroSection = document.querySelector('.hero-section');
        
        heroSection.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
        });
        
        heroSection.addEventListener('touchend', function(e) {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        });
        
        function handleSwipe() {
            const swipeThreshold = 50;
            const diff = touchStartX - touchEndX;
            
            if (Math.abs(diff) > swipeThreshold) {
                if (diff > 0) {
                    // Swipe left - next slide
                    changeSlide(1);
                } else {
                    // Swipe right - previous slide
                    changeSlide(-1);
                }
            }
        }
    </script>

</body>

</html>