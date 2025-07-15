<?php

// Map service names to subcategory keys (should match programs.php)
$programToSubcategory = [
    'Senior Citizen Health Plan' => 'senior-health',
    'Maternal Health Program' => 'maternal-health',
    'Diabetes Management Program' => 'diabetes-management',
    'Hypertension Monitoring Program' => 'hypertension-monitoring',
    'Blood Pressure Monitoring Program' => 'blood-pressure-monitoring',
    // Add more if needed
];

$selectedSubcategory = $_SESSION['selected_subcategory'] ?? $_GET['subcategory'] ?? '';
$preselectedProgramType = $selectedSubcategory;

// Fetch active program services from the database
include_once '../includes/db_functions.php';
$program_services = [];
try {
    global $pdo;
    $stmt = $pdo->prepare("SELECT name FROM services WHERE category = 'program' AND is_active = 1 ORDER BY name");
    $stmt->execute();
    $program_services = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $program_services = [];
}
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
    <input type="hidden" name="service_category" value="program">
    <input type="hidden" name="service_subcategory" value="<?php echo htmlspecialchars($selectedSubcategory ?? ''); ?>">

    <fieldset>
        <legend>Program Information</legend>
        <div class="form-row">
            <div class="form-group">
                <label>Program Type *</label>
                <select name="program_type" required>
                    <option value="">Select</option>
                    <?php foreach ($program_services as $service):
                        $name = $service['name'];
                        $subcat = isset($programToSubcategory[$name]) ? $programToSubcategory[$name] : '';
                        if (!$subcat) continue;
                    ?>
                        <option value="<?php echo htmlspecialchars($subcat); ?>" <?php if ($preselectedProgramType === $subcat) echo 'selected'; ?>><?php echo htmlspecialchars($name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Preferred Date *</label> <!--should also be prefilled if clicked from homepage -->
                <input type="date" name="preferred_date" required>
                <small>Note: Subject to availability</small>
            </div>
            <div class="form-group">
                <label>Preferred Time *</label>
                <select name="preferred_time" required>
                    <option value="">Select Time</option>
                    <option value="8:00 AM">8:00 AM</option>
                    <option value="9:00 AM">9:00 AM</option>
                    <option value="10:00 AM">10:00 AM</option>
                    <option value="11:00 AM">11:00 AM</option>
                    <option value="1:00 PM">1:00 PM</option>
                    <option value="2:00 PM">2:00 PM</option>
                    <option value="3:00 PM">3:00 PM</option>
                </select>
            </div>
        </div>
    </fieldset>

    <?php if ($isSeniorPlan): ?>
        <fieldset>
            <legend>Senior Citizen Requirements</legend>
            <div class="form-group">
                <label>Upload Proof of Eligibility (e.g., Senior Citizen ID) *</label>
                <input type="file" name="proof_id" accept=".jpg,.jpeg,.png,.pdf" required>
            </div>
        </fieldset>
    <?php endif; ?>

    <fieldset>
        <legend>Medical Information</legend>
        <div class="form-group">
            <?php if ($isMaternalHealth): ?>
                <div class="form-group-if">
                    <label>Expected Delivery Date *</label>
                    <input type="date" name="expected_delivery" required>
                </div>
            <?php endif; ?>
            <div class="form-group"></div>
            <label>Notes / Medical History</label>
            <textarea name="notes" placeholder="Any medical history or notes you want to share, such as prior conditions or if joining with a child"></textarea>
        </div>
    </fieldset>

    <div class="form-actions">
        <label>
            <input type="checkbox" name="consent" required>
            I agree to provide my information for program registration.
        </label>
        <button type="submit" class="btn-primary">Submit Enrollment</button>
    </div>
</form>

<style>
    .form {
        background-color: #fff;
        padding: 2rem;
        width: 208%;
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

    .form-group-if {
        flex: 1;
        padding-bottom: 1.5rem;
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
        flex-direction: column;
        align-items: flex-end;
        gap: 1rem;
        margin-top: 1rem;
    }

    .form-actions label {
        display: flex;
        align-items: center;
        gap: 10px;
        font-family: 'Nunito', sans-serif;
        font-weight: 500;
        color: #555;
        cursor: pointer;
    }

    .form-actions input[type="checkbox"] {
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

    .form-actions input[type="checkbox"]:checked {
        background-color: #33b6ff;
    }

    .form-actions input[type="checkbox"]:checked::after {
        content: '✓';
        color: white;
        font-size: 14px;
        font-weight: bold;
        position: absolute;
        top: 45%;
        left: 50%;
        transform: translate(-50%, -50%);
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