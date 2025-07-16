<?php

$vaccineTypeMap = [
    'child-immunization' => 'Child',
    'adult-vaccine' => 'Adult',
    'travel-vaccine' => 'Travel',
    'booster-shot' => 'Booster',
    'anti-rabies-vaccination' => 'Anti-Rabies',
    'community-vaccination' => 'Community',
];

// Get parameters passed from reservation.php
$selectedSubcategory = $_GET['subcategory'] ?? '';
$preselectedVaccineType = $vaccineTypeMap[$selectedSubcategory] ?? '';

// Get service details if available
$selectedDate = $_GET['date'] ?? '';
$serviceId = $_GET['service_id'] ?? '';
$scheduleId = $_GET['schedule_id'] ?? '';

// Get service schedule details if we have IDs
$scheduleDetails = null;
if ($serviceId && $scheduleId) {
    try {
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
?>


<form method="POST" action="reservation.php" class="form">
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

    <!-- Hidden fields for form processing -->
    <input type="hidden" name="service_category" value="vaccine">
    <input type="hidden" name="service_subcategory" value="<?php echo htmlspecialchars($selectedSubcategory ?? ''); ?>">

    <fieldset>
        <legend>Vaccine Information</legend>
        <div class="form-row">
            <div class="form-group">
                <label>Vaccine Type *</label>
                <select name="vaccine_type" required>
                    <option value="">Select</option>
                    <option value="Child" <?= $preselectedVaccineType === 'Child' ? 'selected' : '' ?>>Child Immunization</option>
                    <option value="Adult" <?= $preselectedVaccineType === 'Adult' ? 'selected' : '' ?>>Adult Vaccine</option>
                    <option value="Travel" <?= $preselectedVaccineType === 'Travel' ? 'selected' : '' ?>>Travel Vaccine</option>
                    <option value="Booster" <?= $preselectedVaccineType === 'Booster' ? 'selected' : '' ?>>Booster Shot</option>
                    <option value="Anti-Rabies" <?= $preselectedVaccineType === 'Anti-Rabies' ? 'selected' : '' ?>>Anti-Rabies Vaccination</option>
                    <option value="Community" <?= $preselectedVaccineType === 'Community' ? 'selected' : '' ?>>Community Vaccination</option>
                </select>
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
    select,
    textarea {
        padding: 0.7rem 1rem;
        border: 1px solid #ced4da;
        border-radius: 8px;
        font-size: 1rem;
        transition: border-color 0.2s ease-in-out;
        font-family: inherit;
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