<?php
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit();
}

$error_message = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Simple authentication (you should use a database in production)
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header('Location: dashboard.php');
        exit();
    } else {
        $error_message = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Village East Clinic</title>
    <link rel="stylesheet" href="../assets/layout.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Arimo:wght@400;700&family=Nunito:wght@200;400;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <div class="admin-login-container">
        <div class="login-form-wrapper">
            <div class="clinic-logo-section">
                <h2>Village East Clinic</h2>
                <p>Administrative Access</p>
            </div>

            <form class="login-form" method="POST" action="">
                <h3>Admin Login</h3>

                <?php if ($error_message): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <div class="input-group">
                    <label for="username">Username</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" required
                            value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                    </div>
                </div>

                <div class="input-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" required>
                    </div>
                </div>

                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    Login
                </button>
            </form>

            <div class="login-footer">
                <p><a href="../index.php"><i class="fas fa-arrow-left"></i> Back to Website</a></p>
            </div>
        </div>
    </div>

    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .admin-login-container {
            width: 100%;
            max-width: 400px;
            padding: 2rem;
        }

        .login-form-wrapper {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .clinic-logo-section {
            background: #2c3e50;
            color: white;
            text-align: center;
            padding: 2rem;
        }

        .clinic-logo-section h2 {
            margin: 0 0 0.5rem 0;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .clinic-logo-section p {
            margin: 0;
            opacity: 0.8;
            font-size: 0.9rem;
        }

        .login-form {
            padding: 2rem;
        }

        .login-form h3 {
            text-align: center;
            margin: 0 0 1.5rem 0;
            color: #2c3e50;
            font-weight: 600;
        }

        .error-message {
            background: #ffe6e6;
            color: #d63031;
            padding: 0.75rem;
            border-radius: 6px;
            margin-bottom: 1rem;
            border-left: 4px solid #d63031;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        .input-group {
            margin-bottom: 1.2rem;
        }

        .input-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2c3e50;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
        }

        .input-wrapper input {
            width: 100%;
            padding: 0.75rem 0.75rem 0.75rem 2.5rem;
            border: 2px solid #ecf0f1;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s;
            box-sizing: border-box;
        }

        .input-wrapper input:focus {
            outline: none;
            border-color: #3498db;
        }

        .login-btn {
            width: 100%;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border: none;
            padding: 0.875rem;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .login-btn:hover {
            background: linear-gradient(135deg, #2980b9, #1f5f99);
            transform: translateY(-1px);
        }

        .login-footer {
            padding: 1rem 2rem;
            background: #f8f9fa;
            text-align: center;
        }

        .login-footer a {
            color: #7f8c8d;
            text-decoration: none;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: color 0.3s;
        }

        .login-footer a:hover {
            color: #2c3e50;
        }

        @media (max-width: 480px) {
            .admin-login-container {
                padding: 1rem;
            }

            .login-form {
                padding: 1.5rem;
            }

            .clinic-logo-section {
                padding: 1.5rem;
            }
        }
    </style>
</body>

</html>