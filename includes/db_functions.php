<?php
require_once 'db_connection.php';

function getAnnouncements($limit = 10, $featured_only = false)
{
     global $pdo;
    try {
        $sql = "SELECT * FROM announcements WHERE is_active = 1";
        if ($featured_only) {
            $sql .= " AND is_featured = 1";
        }
        $sql .= " ORDER BY is_featured DESC, announcement_date DESC, created_at DESC LIMIT ?";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching announcements: " . $e->getMessage());
        return [];
    }
}


//Get events for calendar display grouped by date
function getCalendarEvents($year, $month)
{
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
function getServicesForDate($date)
{
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

function getServiceCategoryRoutes()
{
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
function generateCalendar($year, $month, $events)
{
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
            $categories = array_unique(array_map(function ($event) {
                return $event['category'];
            }, $events[$date_string]));

            // Add category-specific classes
            if (count($categories) > 1) {
                $classes[] = 'has-mixed';
            } else {
                $classes[] = 'has-' . $categories[0];
            }

            $event_names = array_map(function ($event) {
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

// Function to submit a reservation from the frontend
function submitReservation($data)
{
    global $pdo;

    try {
        // Validate required fields
        $required_fields = ['client_name', 'date_of_birth', 'contact_number', 'home_address', 'service_category', 'service_subcategory', 'preferred_date', 'preferred_time'];

        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => 'Please fill in all required fields.'];
            }
        }

        // Get service ID based on category and subcategory
        $service_id = getServiceIdByCategory($data['service_category'], $data['service_subcategory']);
        if (!$service_id) {
            return ['success' => false, 'message' => 'Invalid service selection.'];
        }

        // Convert time format if needed
        $preferred_time = convertTimeFormat($data['preferred_time']);

        // Insert reservation
        $sql = "INSERT INTO reservations (
            service_id, service_category, service_subcategory, vehai_id, 
            client_name, date_of_birth, contact_number, email_address, 
            home_address, preferred_date, preferred_time, notes, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $service_id,
            $data['service_category'],
            $data['service_subcategory'],
            $data['vehai_id'] ?? null,
            $data['client_name'],
            $data['date_of_birth'],
            $data['contact_number'],
            $data['email_address'] ?? null,
            $data['home_address'],
            $data['preferred_date'],
            $preferred_time,
            $data['notes'] ?? null
        ]);

        $reservation_id = $pdo->lastInsertId();

        return [
            'success' => true,
            'message' => 'Your reservation has been submitted successfully! We will contact you soon to confirm your appointment.',
            'reservation_id' => $reservation_id
        ];
    } catch (PDOException $e) {
        error_log("Error submitting reservation: " . $e->getMessage());
        return ['success' => false, 'message' => 'Sorry, there was an error submitting your reservation. Please try again.'];
    }
}

// Helper function to get service ID based on category and subcategory
function getServiceIdByCategory($category, $subcategory)
{
    global $pdo;

    try {
        // First, try to find the service by converting the subcategory key back to a service name
        // The subcategory key is created by converting service names to lowercase with hyphens
        $sql = "SELECT id, name FROM services WHERE category = ? AND is_active = 1";
        $serviceMap = [
            'child-immunization' => 'Child Immunization Campaign',
            'adult-vaccine' => 'Adult Vaccine Drive',
            'travel-vaccine' => 'Travel Vaccine Clinic',
            'booster-shot' => 'COVID-19 Booster Campaign',
            'senior-health' => 'Senior Citizen Health Plan',
            'maternal-health' => 'Maternal Health Program',
            'diabetes-management' => 'Diabetes Management Program',
            'hypertension-monitoring' => 'Hypertension Monitoring Program',
            'general-consultation' => 'Free Health Checkup Day',
            'specialist-referral' => 'Specialist Consultation Day',
            'lab-tests' => 'Health Screening Event',
            'follow-up' => 'Free Health Checkup Day'
        ];

        $service_name = $serviceMap[$subcategory] ?? null;

        if (!$service_name) {
            // If no exact match, try to find a service by category
            $sql = "SELECT id FROM services WHERE category = ? AND is_active = 1 LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$category]);
            $result = $stmt->fetch();
            return $result ? $result['id'] : null;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$category]);
        $services = $stmt->fetchAll();

        foreach ($services as $service) {
            // Create the subcategory key from the service name
            $serviceKey = strtolower(str_replace([' ', '-'], ['-', '-'], $service['name']));
            if ($serviceKey === $subcategory) {
                return $service['id'];
            }
        }

        // If no exact match found, try to find a service by category
        $sql = "SELECT id FROM services WHERE category = ? AND is_active = 1 LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$category]);
        $result = $stmt->fetch();

        return $result ? $result['id'] : null;
    } catch (PDOException $e) {
        error_log("Error getting service ID: " . $e->getMessage());
        return [];
    }
}

function getActiveProgramsAndSchedules()
{
    global $pdo;
    $sql = "
        SELECT s.id as service_id, s.name, s.description, s.duration, s.category,
               ss.id as schedule_id, ss.schedule_date, ss.start_time, ss.end_time, ss.max_appointments,
               (ss.max_appointments - COALESCE(booked.count, 0)) as available_slots
        FROM services s
        LEFT JOIN (
            SELECT ss.*, 
                   (SELECT COUNT(*) FROM appointments a WHERE a.service_schedule_id = ss.id AND a.status IN ('pending','confirmed')) as booked_count
            FROM service_schedules ss
            WHERE ss.is_active = 1 AND ss.schedule_date >= CURDATE()
        ) ss ON ss.service_id = s.id
        LEFT JOIN (
            SELECT service_schedule_id, COUNT(*) as count 
            FROM appointments 
            WHERE status IN ('pending', 'confirmed')
            GROUP BY service_schedule_id
        ) booked ON ss.id = booked.service_schedule_id
        WHERE s.is_active = 1
        ORDER BY ss.schedule_date ASC, s.name ASC
    ";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $programs = $stmt->fetchAll();
        return $programs;
    } catch (PDOException $e) {
        error_log("Error fetching programs: " . $e->getMessage());
        return [];
    }
}

// Helper function to convert time format
function convertTimeFormat($time_string)
{
    // Handle different time formats
    $time_string = trim($time_string);

    // If it's already in HH:MM format, return as is
    if (preg_match('/^\d{2}:\d{2}$/', $time_string)) {
        return $time_string;
    }

    // Convert from formats like "8:00 AM" to "08:00"
    if (preg_match('/(\d{1,2}):(\d{2})\s*(AM|PM)/i', $time_string, $matches)) {
        $hour = (int)$matches[1];
        $minute = $matches[2];
        $ampm = strtoupper($matches[3]);

        if ($ampm === 'PM' && $hour !== 12) {
            $hour += 12;
        } elseif ($ampm === 'AM' && $hour === 12) {
            $hour = 0;
        }

        return sprintf('%02d:%s', $hour, $minute);
    }

    // Default return
    return $time_string;
}

// Function to get all active services for dropdowns
function getActiveServices()
{
    global $pdo;

    try {
        $sql = "SELECT id, name, category, description FROM services WHERE is_active = 1 ORDER BY category, name";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching active services: " . $e->getMessage());
        return [];
    }
}
