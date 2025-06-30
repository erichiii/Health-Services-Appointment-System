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

<div class = "hero-section">
  <div class = "hero-section-content">
    <h1>Welcome to Village East Clinic</h1>
    <p>Your health is our top priority.</p>
    <a href="pages/about.php" class="about-us-btn">About Us</a>
  </div>
</div>

<div class = "first-section">
  <div class = "services-schedule">
    <h2><strong>Services Schedule</strong></h2>
    <p class = "subtitle">Select a date to see available services.</p>

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
    
    // sample events data - should be replaced with database data. this for sample only
    $events = [
        '2025-06-07' => ['Anti-Rabies Vaccination'],
        '2025-06-30' => ['General Health Checkup', 'Blood Pressure Monitoring'],
        '2025-07-15' => ['Health Checkup'],
        '2025-07-20' => ['Dental Care'],
        '2025-08-10' => ['Vaccination Drive']
    ];
    
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
    
    <div class = "calendar-container" id="calendar-section">
      <div class="calendar">
        <div class="calendar-header">
          <a href="?month=<?php echo $prev_month; ?>&year=<?php echo $prev_year; ?><?php echo $selected_date ? '&date=' . $selected_date : ''; ?>#calendar-section" class="calendar-nav-btn"><</a>
          <h3><?php echo $month_name . ' ' . $current_year; ?></h3>
          <a href="?month=<?php echo $next_month; ?>&year=<?php echo $next_year; ?><?php echo $selected_date ? '&date=' . $selected_date : ''; ?>#calendar-section" class="calendar-nav-btn">></a>
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
                  $title = 'title="' . implode(', ', $events[$date_string]) . '"';
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

      </div>
    </div>

    <?php if ($selected_date): ?>
    <div class="event-display-box">
        <div class="event-header">
            <h4>Events for <?php echo date('F j, Y', strtotime($selected_date)); ?></h4>
            <a href="?month=<?php echo $current_month; ?>&year=<?php echo $current_year; ?>#calendar-section" class="close-btn">&times;</a>
        </div>
        <div class="event-content">
            <?php if (isset($events[$selected_date]) && !empty($events[$selected_date])): ?>
                <ul class="event-list">
                    <?php foreach ($events[$selected_date] as $event): ?>
                        <li class="event-item">
                            <span class="event-name"><?php echo htmlspecialchars($event); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="event-actions">
                    <a href="pages/reservation.php?date=<?php echo $selected_date; ?>" class="book-event-btn">Book Appointment</a>
                </div>
            <?php else: ?>
                <div class="no-events">
                    <p>No services scheduled for this date.</p>
                    <a href="pages/contact.php" class="contact-btn">Contact us for availability</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

      <a href="pages/programs.php" class="view-all-programs-btn">View All Programs and Schedule</a>
  </div>  

  <div class = "announcements-column">
    <h2><strong>Announcements</strong></h2>
    <!-- placeholder only -->
    <div class="announcement-item">
        <div class="item-content">
            <div class="item-title">Anti-Rabies Vaccination</div>
            <div class="item-date">📅 June 7, 2025</div>
        </div>
        <div class="item-arrow">→</div>
    </div>
    <div class="announcement-item">
        <div class="item-content">
            <div class="item-title">Anti-Rabies Vaccination</div>
            <div class="item-date">📅 June 7, 2025</div>
        </div>
        <div class="item-arrow">→</div>
    </div>
    <div class="announcement-item">
        <div class="item-content">
            <div class="item-title">Anti-Rabies Vaccination</div>
            <div class="item-date">📅 June 7, 2025</div>
        </div>
        <div class="item-arrow">→</div>
    </div>
    <a href="pages/announcements.php" class="view-all-btn">View All Announcements</a>
  </div>
</div>

<div class = "second-section">
  <div class = "our-services">
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

<div class = "call-to-action-section">
  <div class = "call-to-action-content">
    <h2><strong>Visit Us Today</strong></h2>
    <p class = "subtitle">Here to serve you with quality healthcare services 24/7.</p>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
    
</body>
</html>

