<?php

$programTypeMap = [
    'senior-health' => 'Senior Citizen Health Plan',
    'maternal-health' => 'Maternal Health Program',
    'diabetes-management' => 'Diabetes Management',
    'hypertension-monitoring' => 'Hypertension Monitoring'
];


$selectedSubcategory = $_SESSION['selected_subcategory'] ?? $_GET['subcategory'] ?? '';
$preselectedProgramType = $programTypeMap[$selectedSubcategory] ?? '';

$isSeniorPlan = ($preselectedProgramType === 'Senior Citizen Health Plan');
$isMaternalHealth = ($preselectedProgramType === 'Maternal Health Program');
?>


<form method = "POST" action = "submit-form.php" class = "form">
    <fieldset>
        <legend>Personal Information</legend>
        <div class = "form-row">
            <div class = "form-group">
                <label>Full Name *</label>
                <input type = "text" name = "fullname" required>
            </div>
            <div class = "form-group">
                <label>Date of Birth *</label>
                <input type = "date" name = "birthdate" required>
            </div>
        </div>
        <div class = "form-row">
            <div class = "form-group">
                <label>Contact Number *</label>
                <input type = "text" name = "contact" required>
            </div>
            <div class = "form-group">
                <label>Email Address</label>
                <input type = "email" name = "email" placeholder = "Optional - For confirmation notices and updates">
            </div>
        </div>
    </fieldset>


    <fieldset>
        <legend>Program Information</legend>
        <div class = "form-row">
            <div class = "form-group">
                <label>Program Type *</label>
                <select name = "vaccine_type" required>
                    <option value = "">Select</option>
                    <option value = "Senior Citizen Health Plan" <?= $preselectedProgramType === 'Senior Citizen Health Plan' ? 'selected' : ''?>>Senior Citizen Health Plan</option>
                    <option value = "Maternal Health Program" <?= $preselectedProgramType === 'Maternal Health Program' ? 'selected' : ''?>>Maternal Health Program</option>
                    <option value = "Diabetes Management" <?= $preselectedProgramType === 'Diabetes Management' ? 'selected' : ''?>>Diabetes Management</option>
                    <option value = "Hypertension Monitoring" <?= $preselectedProgramType === 'Hypertension Monitoring' ? 'selected' : ''?>>Hypertension Monitoring</option>
                </select>
            </div>
            <div class = "form-group">
                <label>Preferred Date *</label> <!--should also be prefilled if clicked from homepage -->
                <input type = "date" name = "preferred_date" required>
                <small>Note: Subject to availability</small>
            </div>
            <div class = "form-group">
                <label>Preferred Time *</label> <!-- should be based on available dates. connect to database later -->
                <select name = "preferred_time:" required>
                    <option>8:00 AM</option>
                    <option>9:00 AM</option>
                    <option>10:00 AM</option>
                    <option>11:00 AM</option>
                    <option>1:00 PM</option>
                    <option>2:00 PM</option>
                    <option>3:00 PM</option>
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
        <div class = "form-group">
            <?php if ($isMaternalHealth): ?>
                <div class="form-group">
                    <label>Expected Delivery Date *</label>
                    <input type="date" name="expected_delivery" required>
                </div>
            <?php endif; ?>
            <label>Notes / Medical History</label>
            <textarea name = "notes" placeholder="Any medical history or notes you want to share, such as prior conditions or if joining with a child"></textarea>
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

