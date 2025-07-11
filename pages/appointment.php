<?php

$appointmentTypeMap = [
    'general-consultation' => 'General Consultation',
    'specialist-referral' => 'Specialist Referral',
    'lab-tests' => 'Lab Tests',
    'follow-up visits' => 'Follow-up Visits'
];

$selectedSubcategory = $_GET['subcategory'] ?? '';
$preselectedAppointmentType = $appointmentTypeMap[$selectedSubcategory] ?? '';

$isGeneralConsultation = ($preselectedAppointmentType === 'General Consultation');
$isSpecialistReferral = ($preselectedAppointmentType === 'Specialist Referral');
$isLabTests = ($preselectedAppointmentType === 'Lab Tests');
$isFollowUp = ($preselectedAppointmentType === 'Follow-up Visits');
?>


<form method = "POST" action = "submit-form.php" class = "form">
    <fieldset>
        <legend>Personal Information</legend>
        <div class = "form-row-vehai">
            <div class = "form-group">
                <label>Vehai ID</label>
                <input type = "text" name = "vehaiID">
            </div>
        </div>
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
        <div class = "form-row">
            <div class = "form-group">
                <label>Home Address *</label>
                <input type = "text" name = "address" required>
            </div>
        </div>
    </fieldset>

    <fieldset>
        <legend>Appointment Information</legend>
        <div class = "form-row">
            <div class = "form-group">
                <label>Appointment Type *</label>
                <select name = "appointment_type" required>
                    <option value = "">Select</option>
                    <option value = "General Consultation" <?= $preselectedAppointmentType === 'General Consultation' ? 'selected' : ''?>>General Consultation</option>
                    <option value = "Specialist Referral" <?= $preselectedAppointmentType === 'Specialist Referral' ? 'selected' : ''?>>Specialist Referral</option>
                    <option value = "Lab Tests" <?= $preselectedAppointmentType === 'Lab Tests' ? 'selected' : ''?>>Lab Tests</option>
                    <option value = "Follow-up Visits" <?= $preselectedAppointmentType === 'Follow-up Visits' ? 'selected' : ''?>>Follow-up Visits</option>
                </select>
            </div>
        </div>
        <div class = "form-row">
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


    <fieldset>
        <legend>Medical Information</legend>
        <div class = "form-group">
            <label>Notes / Medical History</label>
            <textarea name = "notes" placeholder = "Any medical history or notes you want to share with the healthcare provider such as allergies or prior vaccine doses"></textarea>
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

    .radio-group,
    .checkbox-group {
        display: flex;
        gap: 0.75rem;
        margin-top: 0.5rem;
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
   
    .radio-group input[type="radio"],
    .checkbox-group input[type="checkbox"] {
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