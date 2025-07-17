<?php

// Map service names to subcategory keys (should match programs.php)
$appointmentToSubcategory = [
    'Free Health Checkup Day' => 'free-health-checkup-day',
    'Specialist Consultation Day' => 'specialist-consultation-day',
    'Health Screening Event' => 'health-screening-event',
    'Dental Care Clinic' => 'dental-care-clinic',
    // Add more if needed
];

$selectedSubcategory = $_GET['subcategory'] ?? '';
$appointmentTypeMap = [
    'general-consultation' => 'General Consultation',
    'specialist-referral' => 'Specialist Referral',
    'lab-tests' => 'Lab Tests',
    'follow-up' => 'Follow-up Visits',
    'dental-care' => 'Dental Care Clinic',
];
$preselectedAppointmentType = $appointmentTypeMap[$selectedSubcategory] ?? '';

// Get parameters passed from reservation.php
$selectedDate = $_GET['date'] ?? '';
$serviceId = $_GET['service_id'] ?? '';
$scheduleId = $_GET['schedule_id'] ?? '';

// Get service schedule details if we have IDs
$scheduleDetails = null;
if ($serviceId && $scheduleId) {
    try {
        include_once '../includes/db_connection.php';
        $db = getDbConnection();
        $stmt = $db->prepare("
            SELECT 
                ss.schedule_date, 
                ss.start_time, 
                ss.end_time,
                s.name as service_name
            FROM service_schedules ss
            JOIN services s ON ss.service_id = s.id
            WHERE s.id = ? AND ss.id = ?
        ");
        $stmt->execute([$serviceId, $scheduleId]);
        $scheduleDetails = $stmt->fetch(PDO::FETCH_ASSOC);

        // If we have service details, try to determine the appointment type from the service name
        if ($scheduleDetails && isset($scheduleDetails['service_name'])) {
            $serviceName = $scheduleDetails['service_name'];

            // Map service name to appointment type
            if (stripos($serviceName, 'health checkup') !== false || stripos($serviceName, 'general') !== false) {
                $preselectedAppointmentType = 'General Consultation';
            } elseif (stripos($serviceName, 'specialist') !== false || stripos($serviceName, 'referral') !== false) {
                $preselectedAppointmentType = 'Specialist Referral';
            } elseif (
                stripos($serviceName, 'lab') !== false || stripos($serviceName, 'test') !== false ||
                stripos($serviceName, 'screening') !== false
            ) {
                $preselectedAppointmentType = 'Lab Tests';
            } elseif (stripos($serviceName, 'follow') !== false || stripos($serviceName, 'follow-up') !== false) {
                $preselectedAppointmentType = 'Follow-up Visits';
            } elseif (stripos($serviceName, 'dental') !== false || stripos($serviceName, 'teeth') !== false) {
                $preselectedAppointmentType = 'Dental Care Clinic';
            }
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
    }
}

// Format the time for display in the dropdown
$formattedTime = '';
if (isset($scheduleDetails['start_time'])) {
    // Convert 24-hour format to AM/PM format
    $timestamp = strtotime($scheduleDetails['start_time']);
    $formattedTime = date('g:i A', $timestamp);
}

// Use schedule date if available, otherwise use the date from URL
$preferredDate = isset($scheduleDetails['schedule_date']) ? $scheduleDetails['schedule_date'] : $selectedDate;

// Get available dates for the selected service
$availableDates = [];
if ($selectedSubcategory) {
    $isSpecialistReferral = ($preselectedAppointmentType === 'Specialist Referral');
    $isLabTests = ($preselectedAppointmentType === 'Lab Tests');
    $isFollowUp = ($preselectedAppointmentType === 'Follow-up Visits');

    // Fetch active appointment services from the database
    include_once '../includes/db_functions.php';
    $appointment_services = [];
    try {
        global $pdo;
        $stmt = $pdo->prepare("SELECT name FROM services WHERE category = 'appointment' AND is_active = 1 ORDER BY name");
        $stmt->execute();
        $appointment_services = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $appointment_services = [];
    }

    // Set conditional flags based on selected subcategory
    $isGeneralConsultation = ($selectedSubcategory === 'free-health-checkup-day');
    $isSpecialistReferral = ($selectedSubcategory === 'specialist-consultation-day');
    $isLabTests = ($selectedSubcategory === 'health-screening-event');
    $isDentalCare = ($selectedSubcategory === 'dental-care-clinic');
}
?>


<form method="POST" action="reservation.php" class="form">
    <!-- Hidden fields for form processing -->
    <input type="hidden" name="service_category" value="appointment">
    <input type="hidden" name="service_subcategory" value="<?php echo htmlspecialchars($selectedSubcategory); ?>">
    <fieldset>
        <legend>Personal Information</legend>
        <div class="form-row-vehai">
            <div class="form-group">
                <label>Vehai ID</label>
                <input type="text" name="vehaiID">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="fullname" required>
            </div>
            <div class="form-group">
                <label>Date of Birth *</label>
                <input type="date" name="birthdate" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Contact Number *</label>
                <input type="text" name="contact" required>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="Optional - For confirmation notices and updates">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Home Address *</label>
                <input type="text" name="address" required>
            </div>
        </div>
    </fieldset>

    <fieldset>
        <legend>Appointment Information</legend>
        <div class="form-row">
            <div class="form-group">
                <label>Appointment Type *</label>
                <select name="appointment_type" required>
                    <option value="">Select</option>
                    <?php foreach ($appointment_services as $service):
                        $name = $service['name'];
                        $selected = ($preselectedAppointmentType === $name) ? 'selected' : '';
                    ?>
                        <option value="<?php echo htmlspecialchars($name); ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($name); ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if ($scheduleDetails && $preselectedAppointmentType): ?>
                    <small>Pre-selected based on the event from the calendar</small>
                <?php endif; ?>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Preferred Date *</label>
                <input type="date" name="preferred_date" value="<?php echo htmlspecialchars($preferredDate ?? ''); ?>" required>
                <?php if ($scheduleDetails): ?>
                    <small>Pre-filled with the selected event date from the calendar</small>
                <?php else: ?>
                    <small>Note: Subject to availability</small>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label>Preferred Time *</label>
                <select name="preferred_time" required>
                    <option value="">Select Time</option>
                    <option value="8:00 AM" <?= $formattedTime === '8:00 AM' ? 'selected' : '' ?>>8:00 AM</option>
                    <option value="9:00 AM" <?= $formattedTime === '9:00 AM' ? 'selected' : '' ?>>9:00 AM</option>
                    <option value="10:00 AM" <?= $formattedTime === '10:00 AM' ? 'selected' : '' ?>>10:00 AM</option>
                    <option value="11:00 AM" <?= $formattedTime === '11:00 AM' ? 'selected' : '' ?>>11:00 AM</option>
                    <option value="1:00 PM" <?= $formattedTime === '1:00 PM' ? 'selected' : '' ?>>1:00 PM</option>
                    <option value="2:00 PM" <?= $formattedTime === '2:00 PM' ? 'selected' : '' ?>>2:00 PM</option>
                    <option value="3:00 PM" <?= $formattedTime === '3:00 PM' ? 'selected' : '' ?>>3:00 PM</option>
                    <?php if ($formattedTime && !in_array($formattedTime, ['8:00 AM', '9:00 AM', '10:00 AM', '11:00 AM', '1:00 PM', '2:00 PM', '3:00 PM'])): ?>
                        <option value="<?= htmlspecialchars($formattedTime) ?>" selected><?= htmlspecialchars($formattedTime) ?></option>
                    <?php endif; ?>
                </select>
                <?php if ($scheduleDetails): ?>
                    <small>Pre-filled with the selected event time from the calendar</small>
                <?php endif; ?>
            </div>
        </div>
    </fieldset>

    <!-- General Consultation Fields -->
    <?php if ($isGeneralConsultation): ?>
        <fieldset>
            <legend>General Consultation Details</legend>
            <div class="form-row">
                <div class="form-group">
                    <label>Purpose of Visit *</label>
                    <select name="purpose_of_visit" required>
                        <option value="">Select</option>
                        <option value="Fever">Fever</option>
                        <option value="Cough">Cough</option>
                        <option value="Regular Check-up">Regular Check-up</option>
                        <option value="Headache">Headache</option>
                        <option value="Body Pain">Body Pain</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Symptoms</label>
                <textarea name="symptoms" placeholder="Please describe your symptoms in detail"></textarea>
            </div>
            <div class="form-group">
                <label><br>Have you visited this clinic before?</label>
                <div class="radio-group">
                    <label><input type="radio" name="previous_visits" value="yes"> Yes</label>
                    <label><input type="radio" name="previous_visits" value="no"> No</label>
                </div>
            </div>
        </fieldset>
    <?php endif; ?>

    <!-- Specialist Referral Fields -->
    <?php if ($isSpecialistReferral): ?>
        <fieldset>
            <legend>Specialist Referral Details</legend>
            <div class="form-row">
                <div class="form-group">
                    <label>Referring Doctor (if any)</label>
                    <input type="text" name="referring_doctor" placeholder="Name of the doctor referring you to a specialist">
                </div>
                <div class="form-group">
                    <label>Specialist Needed *</label>
                    <select name="specialist_needed" required>
                        <option value="">Select</option>
                        <option value="Cardiologist">Cardiologist</option>
                        <option value="Dermatologist">Dermatologist</option>
                        <option value="Neurologist">Neurologist</option>
                        <option value="Orthopedist">Orthopedist</option>
                        <option value="Gynecologist">Gynecologist</option>
                        <option value="Pediatrician">Pediatrician</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Reason for Referral *</label>
                <textarea name="referral_reason" required placeholder="Please explain why you need to see a specialist"></textarea>
            </div>
            <div class="form-group">
                <label><br>Upload Previous Diagnosis or Notes (Optional)</label>
                <input type="file" name="diagnosis_files" accept=".jpg,.jpeg,.png,.pdf">
            </div>
        </fieldset>
    <?php endif; ?>

    <!-- Lab Tests Fields -->
    <?php if ($isLabTests): ?>
        <fieldset>
            <legend>Lab Tests Details</legend>
            <div class="form-group">
                <label>Type of Lab Test *</label>
                <div class="checkbox-group">
                    <label><input type="checkbox" name="lab_tests[]" value="Blood Test"> Blood Test</label>
                    <label><input type="checkbox" name="lab_tests[]" value="X-Ray"> X-Ray</label>
                    <label><input type="checkbox" name="lab_tests[]" value="Urine Test"> Urine Test</label>
                    <label><input type="checkbox" name="lab_tests[]" value="ECG"> ECG</label>
                    <label><input type="checkbox" name="lab_tests[]" value="Ultrasound"> Ultrasound</label>
                    <label><input type="checkbox" name="lab_tests[]" value="Other"> Other</label>
                </div>
            </div>
            <div class="form-group">
                <label><br>Fasting Required? *</label>
                <div class="radio-group">
                    <label><input type="radio" name="fasting_required" value="yes" required> Yes</label>
                    <label><input type="radio" name="fasting_required" value="no" required> No</label>
                </div>
            </div>
            <div class="form-group">
                <label><br>Upload Doctor's Request (Optional)</label>
                <input type="file" name="doctor_request" accept=".jpg,.jpeg,.png,.pdf">
            </div>
        </fieldset>
    <?php endif; ?>

    <!-- Follow-up Visits Fields -->
    <?php if ($isFollowUp): ?>
        <fieldset>
            <legend>Follow-up Visit Details</legend>
            <div class="form-row">
                <div class="form-group">
                    <label>Previous Appointment Date *</label>
                    <input type="date" name="previous_appointment_date" required>
                </div>
                <div class="form-group">
                    <label>Doctor Seen Last Time *</label>
                    <input type="text" name="previous_doctor" required placeholder="Dr. Name">
                </div>
            </div>
            <div class="form-group">
                <label>Progress Notes / Updates *</label>
                <textarea name="progress_notes" required placeholder="Please describe your progress since last visit, any changes in symptoms, medication effects, etc."></textarea>
            </div>
        </fieldset>
    <?php endif; ?>

    <fieldset>
        <legend>Medical Information</legend>
        <div class="form-group">
            <label>Notes / Medical History</label>
            <textarea name="notes" placeholder="Any medical history or notes you want to share with the healthcare provider such as allergies or prior vaccine doses"></textarea>
        </div>
    </fieldset>

    <div class="form-actions">
        <button type="submit" class="btn-primary">Submit Registration</button>
    </div>
</form>

<style>
    .form {
        background-color: #fff;
        padding: 2rem;
        width: 207%;
        margin: 2rem auto;
        transform: translateX(-27%);
        border-radius: 12px;
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
        font-family: 'Nunito', sans-serif;
        font-size: 1rem;
        border-top: 5px solid #33b6ff;
    }

    fieldset {
        border: none;
        margin-bottom: 2rem;
        padding: 0;
    }

    legend {
        font-size: 1.3rem;
        font-weight: 600;
        color: #33b6ff;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        position: relative;
    }

    legend::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 285%;
        height: 2px;
        background-color: #e9ecef;
    }

    .form-row {
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .form-row-vehai {
        display: flex;
        flex-wrap: wrap;
        width: 48.5%;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .form-group {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    label {
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: #2c3e50;
    }

    input[type="text"],
    input[type="email"],
    input[type="date"],
    input[type="file"],
    select,
    textarea {
        padding: 0.7rem 1rem;
        border: 1px solid #ced4da;
        border-radius: 8px;
        font-size: 1rem;
        transition: border-color 0.2s ease-in-out;
        font-family: inherit;
    }

    input[type="file"] {
        position: relative;
        display: inline-block;
        cursor: pointer;
        outline: none;
        text-decoration: none;
        color: #333;
        border: 2px dashed #33b6ff;
        border-radius: 8px;
        background: #f8f9fa;
        padding: 2rem;
        text-align: center;
        transition: all 0.3s ease;
        font-family: 'Nunito', sans-serif;
        width: 100%;
        box-sizing: border-box;
    }

    input[type="file"]:hover {
        background: #e3f2fd;
        border-color: #1b72a1;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(51, 182, 255, 0.2);
    }

    input[type="file"]:focus {
        border-color: #1b72a1;
        background: #e3f2fd;
        outline: none;
    }

    input[type="file"]::file-selector-button {
        background: #33b6ff;
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        margin-right: 1rem;
        transition: all 0.3s ease;
        font-family: 'Nunito', sans-serif;
    }

    input[type="file"]::file-selector-button:hover {
        background: linear-gradient(135deg, #1b72a1 0%, #155a87 100%);
        box-shadow: 0 2px 8px rgba(51, 182, 255, 0.3);
    }

    .radio-group {
        display: flex;
        gap: 30px;
        margin-top: 0.5rem;
    }

    .checkbox-group {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 0.75rem;
        margin-top: 0.5rem;
    }

    .checkbox-group input[type="checkbox"] {
        width: 18px;
        height: 18px;
        accent-color: #33b6ff;
        cursor: pointer;
        appearance: none;
        border: 2px solid #33b6ff;
        border-radius: 3px;
        background-color: white;
        position: relative;
    }

    .checkbox-group input[type="checkbox"]:checked {
        background-color: #33b6ff;
    }

    .checkbox-group input[type="checkbox"]:checked::after {
        content: '✓';
        color: white;
        font-size: 14px;
        font-weight: bold;
        position: absolute;
        top: 45%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    .radio-group label,
    .checkbox-group label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 500;
        cursor: pointer;
        padding: 0.5rem;
        border-radius: 6px;
        transition: background-color 0.2s ease;
    }

    .radio-group label:hover,
    .checkbox-group label:hover {
        background-color: #f8f9fa;
    }

    .radio-group input[type="radio"] {
        width: 18px;
        height: 18px;
        accent-color: #33b6ff;
        cursor: pointer;
    }

    input:focus,
    select:focus,
    textarea:focus {
        border-color: #33b6ff;
        outline: none;
    }

    textarea {
        resize: vertical;
        min-height: 100px;
    }

    small {
        font-size: 0.85rem;
        color: #6c757d;
        margin-top: 0.5rem;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        margin-top: 1rem;
    }

    button.btn-primary {
        background-color: #28a745;
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.2s ease-in-out;
    }

    button.btn-primary:hover {
        background-color: #218838;
    }

    button[type="button"] {
        background-color: #6c757d;
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
        border-radius: 8px;
        cursor: pointer;
    }

    button[type="button"]:hover {
        background-color: #5a6268;
    }

    @media (max-width: 768px) {
        .form {
            width: 95%;
            padding: 1.5rem;
            margin: 1rem auto;
        }

        .form-row {
            flex-direction: column;
        }
    }
</style>