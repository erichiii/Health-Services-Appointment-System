<form method = "POST" action = "submit-form.php" class = "vaccine-form">
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
        <legend>Vaccine Information</legend>
        <div class = "form-row">
            <div class = "form-group">
                <label>Vaccine Type *</label> <!-- will make this dynamic later -->
                <select name = "vaccine_type" required>
                    <option value = "">Select</option>
                    <option value = "Child Immunization" <?= $preselectedVaccineType === 'Child Immunization' ? 'selected' : ''?>>Child</option>
                    <option value = "Adult Vaccine" <?= $preselectedVaccineType === 'Adult Vaccine' ? 'selected' : ''?>>Adult Vaccine</option>
                    <option value = "Travel Vaccine" <?= $preselectedVaccineType === 'Travel Vaccine' ? 'selected' : ''?>>Travel Vaccine</option>
                    <option value = "Booster Shot" <?= $preselectedVaccineType === 'Booster Shot' ? 'selected' : ''?>>Booster Shot</option>
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

    <fieldset>
        <legend>Medical Information</legend>
        <div class = "form-group">
            <label>Notes / Medical History</label>
            <textarea name = "notes" placeholder = "Any medical history or notes you want to share with the healthcare provider such as allergies or prior vaccine doses"></textarea>
        </div>    
    </fieldset>

    <div class="form-actions">
        <button type="submit" class="btn-primary">Schedule Appointment</button>
    </div>
</form>