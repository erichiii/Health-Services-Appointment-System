-- DDL for database
CREATE TABLE services (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    duration INT DEFAULT 30, -- duration in minutes
    category ENUM('vaccine', 'program', 'appointment') DEFAULT 'appointment',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE admin_users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
);

CREATE TABLE admin_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT NOT NULL,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admin_users(id) ON DELETE CASCADE,
    INDEX idx_session_token (session_token),
    INDEX idx_expires_at (expires_at)
);

CREATE TABLE announcements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    announcement_date DATE NOT NULL,
    is_featured BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_announcement_date (announcement_date),
    INDEX idx_is_active (is_active)
);

CREATE TABLE service_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    service_id INT NOT NULL,
    schedule_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    max_appointments INT DEFAULT 10,
    notes TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    INDEX idx_service_date (service_id, schedule_date),
    INDEX idx_schedule_date (schedule_date)
);

CREATE TABLE appointments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    service_schedule_id INT NOT NULL,
    client_name VARCHAR(255) NOT NULL,
    client_email VARCHAR(255),
    client_phone VARCHAR(20),
    appointment_time TIME,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (service_schedule_id) REFERENCES service_schedules(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_schedule_status (service_schedule_id, status)
);

-- Reservations table for initial reservation requests
CREATE TABLE reservations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    service_id INT NOT NULL,
    service_category ENUM('vaccine', 'program', 'general') NOT NULL,
    service_subcategory VARCHAR(100) NOT NULL,
    vehai_id VARCHAR(50) NULL, -- Optional health ID (e.g., 2698 R1, 2799 HR3)
    client_name VARCHAR(255) NOT NULL,
    date_of_birth DATE NOT NULL,
    contact_number VARCHAR(20) NOT NULL,
    email_address VARCHAR(255) NULL, -- Optional
    home_address TEXT NOT NULL,
    preferred_date DATE NOT NULL,
    preferred_time TIME NOT NULL,
    notes TEXT NULL, -- Optional medical history/notes
    status ENUM('pending', 'scheduled', 'cancelled') DEFAULT 'pending',
    appointment_id INT NULL, -- Links to appointments table when scheduled
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_service_category (service_category),
    INDEX idx_preferred_date (preferred_date),
    INDEX idx_client_name (client_name),
    INDEX idx_service_status (service_id, status)
);

-- Insert default admin user (username: admin, password: admin123)
INSERT INTO admin_users (username, email, password_hash, full_name) VALUES
('admin', 'admin@villagesteastclinic.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator');

-- Insert sample services focused on special events and programs 
INSERT INTO services (name, description, duration, category) VALUES

-- Special Vaccine Events/Campaigns
('Anti-Rabies Vaccination Campaign', 'Special anti-rabies vaccination drive for the community', 20, 'vaccine'),
('Child Immunization Campaign', 'Complete vaccination program for children', 20, 'vaccine'),
('Adult Vaccine Drive', 'Special vaccination drive for adults', 15, 'vaccine'),
('Travel Vaccine Clinic', 'Travel-related vaccinations and certificates', 25, 'vaccine'),
('COVID-19 Booster Campaign', 'Special COVID-19 booster vaccination drive', 15, 'vaccine'),
('Community Vaccination Drive', 'Large-scale community vaccination program', 15, 'vaccine'),

-- Health Program Enrollments
('Senior Citizen Health Plan', 'Health monitoring program enrollment for seniors', 60, 'program'),
('Maternal Health Program', 'Pre and post-natal care program enrollment', 45, 'program'),
('Diabetes Management Program', 'Diabetes monitoring and education program', 50, 'program'),
('Hypertension Monitoring Program', 'Blood pressure management program enrollment', 40, 'program'),
('Blood Pressure Monitoring Program', 'Regular blood pressure check program enrollment', 30, 'program'),

-- Special Appointment Events (not regular daily appointments)
('Free Health Checkup Day', 'Special free comprehensive health examination', 45, 'appointment'),
('Specialist Consultation Day', 'Special day with visiting specialists', 45, 'appointment'),
('Dental Care Clinic', 'Special dental examination and treatment clinic', 60, 'appointment'),
('Health Screening Event', 'Community health screening event', 30, 'appointment');

-- Insert sample schedules with only important/special events
INSERT INTO service_schedules (service_id, schedule_date, start_time, end_time, max_appointments) VALUES
-- June 2025 - Special vaccination events
(1, '2025-06-07', '09:00:00', '16:00:00', 20), -- Anti-Rabies Vaccination Campaign

-- July 2025 - Important health programs and vaccination drives
(6, '2025-07-05', '08:00:00', '15:00:00', 50), -- Community Vaccination Drive
(8, '2025-07-08', '14:00:00', '16:00:00', 15), -- Maternal Health Program Enrollment
(9, '2025-07-10', '10:00:00', '15:00:00', 20), -- Diabetes Management Program
(2, '2025-07-15', '09:00:00', '17:00:00', 30), -- Child Immunization Campaign
(4, '2025-07-18', '09:00:00', '15:00:00', 25), -- Travel Vaccine Clinic
(7, '2025-07-22', '08:00:00', '16:00:00', 20), -- Senior Citizen Health Plan Enrollment
(10, '2025-07-25', '14:00:00', '17:00:00', 15), -- Hypertension Monitoring Program
(5, '2025-07-28', '09:00:00', '12:00:00', 40), -- COVID-19 Booster Campaign

-- August 2025 - Special events
(3, '2025-08-05', '08:00:00', '16:00:00', 35), -- Adult Vaccine Drive
(11, '2025-08-10', '09:00:00', '12:00:00', 25), -- Blood Pressure Monitoring Program
(6, '2025-08-15', '08:00:00', '15:00:00', 50), -- Back-to-School Vaccination Drive

-- September 2025 - Special appointment events
(12, '2025-09-05', '08:00:00', '17:00:00', 30), -- Free Health Checkup Day
(13, '2025-09-12', '09:00:00', '16:00:00', 15), -- Specialist Consultation Day
(14, '2025-09-20', '10:00:00', '16:00:00', 20); -- Dental Care Clinic

-- Note: Regular appointments (general consultations, routine checkups, etc.) are available daily and do not require special scheduling entries.
-- Only special events, campaigns, and program enrollments are scheduled here.

-- Audit log for all admin actions
CREATE TABLE admin_action_audit_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT,
    action ENUM('create', 'edit', 'delete') NOT NULL,
    target_table VARCHAR(64) NOT NULL,
    target_id INT,
    action_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changes TEXT,
    FOREIGN KEY (admin_id) REFERENCES admin_users(id) ON DELETE SET NULL,
    INDEX idx_admin_id (admin_id),
    INDEX idx_target_table (target_table),
    INDEX idx_action_time (action_time)
);

-- Table for storing contact inquiries from the Contact Us page
CREATE TABLE contact_inquiries (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('pending', 'replied') DEFAULT 'pending',
    reply TEXT NULL,
    replied_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_email (email),
    INDEX idx_subject (subject)
);