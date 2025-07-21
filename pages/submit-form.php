<?php
include '../includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $errors = [];

    if ($name === '' || $email === '' || $subject === '' || $message === '') {
        $errors[] = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (empty($errors)) {
        try {
            $sql = "INSERT INTO contact_inquiries (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $email, $phone, $subject, $message]);
            header('Location: contact.php?inquiry=success');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Failed to submit your inquiry. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact Inquiry Submission</title>
    <link rel="stylesheet" href="../assets/main.css">
</head>
<body>
<main class="main-content">
    <div class="container">
        <div class="page-header">
            <h1>Contact Us</h1>
        </div>
        <div class="card" style="max-width: 600px; margin: 0 auto; padding: 2rem; text-align: center;">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger" style="color: #dc2626; margin-bottom: 1rem;">
                    <?php foreach ($errors as $error): ?>
                        <div><?php echo htmlspecialchars($error); ?></div>
                    <?php endforeach; ?>
                </div>
                <a href="contact.php" class="btn btn-secondary">Back to Contact Form</a>
            <?php elseif (!empty($success)): ?>
                <div class="alert alert-success" style="color: #16a34a; margin-bottom: 1rem;">
                    Thank you for reaching out! Your inquiry has been received. We will get back to you soon.
                </div>
                <a href="../index.php" class="btn btn-primary">Return to Home</a>
            <?php else: ?>
                <div class="alert alert-info" style="color: #2563eb; margin-bottom: 1rem;">
                    Please submit your inquiry using the contact form.
                </div>
                <a href="contact.php" class="btn btn-secondary">Back to Contact Form</a>
            <?php endif; ?>
        </div>
    </div>
</main>
</body>
</html>