<?php
require_once 'db_connection.php';

function getAnnouncements($limit = 10, $featured_only = false)
{
    return; // TODO: Implement function
}


//Get events for calendar display grouped by date
function getCalendarEvents($year, $month) {
    global $pdo;
    
    try {
        $start_date = sprintf("%04d-%02d-01", $year, $month);
        $end_date = date("Y-m-t", strtotime($start_date));
        
        $stmt = $pdo->prepare("
            SELECT 
                ss.schedule_date,
                s.id as service_id,
                ss.id as schedule_id,
                s.name as service_name,
                s.category,
                s.description,
                s.duration,
                ss.start_time,
                ss.end_time,
                ss.max_appointments,
                (ss.max_appointments - COALESCE(booked.count, 0)) as available_slots
            FROM service_schedules ss
            JOIN services s ON ss.service_id = s.id
            LEFT JOIN (
                SELECT service_schedule_id, COUNT(*) as count 
                FROM appointments 
                WHERE status IN ('pending', 'confirmed')
                GROUP BY service_schedule_id
            ) booked ON ss.id = booked.service_schedule_id
            WHERE ss.schedule_date BETWEEN ? AND ? 
            AND ss.is_active = 1 
            AND s.is_active = 1
            ORDER BY ss.schedule_date ASC, ss.start_time ASC
        ");
        $stmt->execute([$start_date, $end_date]);
        
        $events = [];
        while ($row = $stmt->fetch()) {
            $date = $row['schedule_date'];
            if (!isset($events[$date])) {
                $events[$date] = [];
            }
            $events[$date][] = $row;
        }
        
        return $events;
    } catch (PDOException $e) {
        error_log("Database error in getCalendarEvents: " . $e->getMessage());
        return [];
    }
}

//Get services for a specific date
function getServicesForDate($date) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                ss.id as schedule_id,
                s.id as service_id,
                s.name as service_name,
                s.description,
                s.category,
                s.duration,
                ss.start_time,
                ss.end_time,
                ss.max_appointments,
                (ss.max_appointments - COALESCE(booked.count, 0)) as available_slots
            FROM service_schedules ss
            JOIN services s ON ss.service_id = s.id
            LEFT JOIN (
                SELECT service_schedule_id, COUNT(*) as count 
                FROM appointments 
                WHERE status IN ('pending', 'confirmed')
                GROUP BY service_schedule_id
            ) booked ON ss.id = booked.service_schedule_id
            WHERE ss.schedule_date = ? 
            AND ss.is_active = 1 
            AND s.is_active = 1
            ORDER BY ss.start_time ASC
        ");
        $stmt->execute([$date]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Database error in getServicesForDate: " . $e->getMessage());
        return [];
    }
}

//Get service category routing information

function getServiceCategoryRoutes() {
    return [
        'vaccine' => [
            'page' => 'pages/reservation.php',
            'action' => 'Register for Vaccination',
            'icon' => 'fas fa-syringe',
            'color' => '#22c55e'
        ],
        'program' => [
            'page' => 'pages/reservation.php',
            'action' => 'Enroll in Program',
            'icon' => 'fas fa-users',
            'color' => '#f59e0b'
        ],
        'appointment' => [
            'page' => 'pages/reservation.php',
            'action' => 'Book Appointment',
            'icon' => 'fas fa-calendar-check',
            'color' => '#3b82f6'
        ]
    ];
}

/**
 * Generate calendar HTML for reservation page
 */
function generateCalendar($year, $month, $events) {
    $first_day = mktime(0, 0, 0, $month, 1, $year);
    $days_in_month = date('t', $first_day);
    $day_of_week = date('w', $first_day);
    $month_name = date('F', $first_day);
    $today = date('Y-m-d');

    // Calculate previous and next month/year
    $prev_month = $month - 1;
    $prev_year = $year;
    if ($prev_month < 1) {
        $prev_month = 12;
        $prev_year--;
    }

    $next_month = $month + 1;
    $next_year = $year;
    if ($next_month > 12) {
        $next_month = 1;
        $next_year++;
    }

    $html = '<div class="calendar-container" id="calendar-section">';
    $html .= '<div class="calendar">';
    $html .= '<div class="calendar-header">';
    $html .= '<a href="?month=' . $prev_month . '&year=' . $prev_year . '#calendar-section" class="calendar-nav-btn">&lt;</a>';
    $html .= '<h3>' . $month_name . ' ' . $year . '</h3>';
    $html .= '<a href="?month=' . $next_month . '&year=' . $next_year . '#calendar-section" class="calendar-nav-btn">&gt;</a>';
    $html .= '</div>';

    $html .= '<div class="calendar-grid">';
    $html .= '<div class="day">Sun</div>';
    $html .= '<div class="day">Mon</div>';
    $html .= '<div class="day">Tue</div>';
    $html .= '<div class="day">Wed</div>';
    $html .= '<div class="day">Thu</div>';
    $html .= '<div class="day">Fri</div>';
    $html .= '<div class="day">Sat</div>';

    // Previous month's trailing days
    $prev_month_days = date('t', mktime(0, 0, 0, $month - 1, 1, $year));
    for ($i = $day_of_week - 1; $i >= 0; $i--) {
        $day = $prev_month_days - $i;
        $html .= '<div class="calendar-date other-month">' . $day . '</div>';
    }

    // Current month days
    for ($day = 1; $day <= $days_in_month; $day++) {
        $date_string = sprintf('%04d-%02d-%02d', $year, $month, $day);
        $classes = ['calendar-date'];

        // Check if it's today
        if ($date_string === $today) {
            $classes[] = 'today';
        }

        // Check if date has events
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
        $url_params = "?month={$month}&year={$year}&date={$date_string}#calendar-section";
        $html .= '<a href="' . $url_params . '" class="' . $class_string . '" ' . $title . '>' . $day . '</a>';
    }

    // Next month's leading days to fill the grid
    $total_cells = 42; // 6 rows × 7 days
    $cells_used = $day_of_week + $days_in_month;
    $remaining_cells = $total_cells - $cells_used;

    for ($day = 1; $day <= $remaining_cells; $day++) {
        $html .= '<div class="calendar-date other-month">' . $day . '</div>';
    }

    $html .= '</div>'; // close calendar-grid

    // Color Legend
    $html .= '<div class="calendar-legend">';
    $html .= '<div class="legend-item">';
}