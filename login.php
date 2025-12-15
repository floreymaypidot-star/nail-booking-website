<?php
session_start();

// Database connection
$host = 'localhost';
$db = 'nail_booking_db';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// Initialize error/success messages
$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'login') {
            // Login logic
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $error = 'Email and password are required.';
            } else {
                try {
                    $stmt = $pdo->prepare('SELECT id, name, email, password FROM users WHERE email = ?');
                    $stmt->execute([$email]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($user && password_verify($password, $user['password'])) {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['name'];
                        $_SESSION['user_email'] = $user['email'];
                        $success = 'Login successful! Redirecting...';
                        header('refresh:2; url=dashboard.php');
                    } else {
                        $error = 'Invalid email or password.';
                    }
                } catch (PDOException $e) {
                    $error = 'Database error: ' . $e->getMessage();
                }
            }
        } elseif ($_POST['action'] === 'signup') {
            // Signup logic
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            // Validation
            if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
                $error = 'All fields are required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Invalid email format.';
            } elseif (strlen($password) < 6) {
                $error = 'Password must be at least 6 characters long.';
            } elseif ($password !== $confirm_password) {
                $error = 'Passwords do not match.';
            } else {
                try {
                    // Check if email already exists
                    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
                    $stmt->execute([$email]);
                    
                    if ($stmt->rowCount() > 0) {
                        $error = 'Email already registered. Please login instead.';
                    } else {
                        // Hash password and insert user
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare('INSERT INTO users (name, email, password, created_at) VALUES (?, ?, ?, NOW())');
                        $stmt->execute([$name, $email, $hashed_password]);

                        $success = 'Account created successfully! Please log in.';
                        // Reset form
                        $name = '';
                        $email = '';
                        $password = '';
                        $confirm_password = '';
                    }
                } catch (PDOException $e) {
                    $error = 'Database error: ' . $e->getMessage();
                }
            }
        }
    }
}

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Signup - Nail Booking</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 450px;
            overflow: hidden;
        }

        .form-container {
            padding: 40px;
        }

        .form-title {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
            text-align: center;
        }

        .form-subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .error-message {
            background-color: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #c33;
        }

        .success-message {
            background-color: #efe;
            color: #3c3;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #3c3;
        }

        .btn {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            margin-top: 10px;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #5568d3 0%, #6a3f8f 100%);
        }

        .tab-buttons {
            display: flex;
            gap: 0;
            margin-bottom: 30px;
        }

        .tab-btn {
            flex: 1;
            padding: 12px;
            border: none;
            background-color: #f0f0f0;
            cursor: pointer;
            font-weight: 600;
            color: #666;
            transition: all 0.3s;
            border-bottom: 3px solid transparent;
        }

        .tab-btn.active {
            background-color: white;
            color: #667eea;
            border-bottom-color: #667eea;
        }

        .form-section {
            display: none;
        }

        .form-section.active {
            display: block;
        }

        .toggle-text {
            text-align: center;
            color: #666;
            font-size: 14px;
            margin-top: 20px;
        }

        .toggle-text a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
        }

        .toggle-text a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .container {
                max-width: 100%;
            }

            .form-container {
                padding: 30px 20px;
            }

            .form-title {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <!-- Tab buttons -->
            <div class="tab-buttons">
                <button class="tab-btn active" onclick="switchTab('login')">Login</button>
                <button class="tab-btn" onclick="switchTab('signup')">Sign Up</button>
            </div>

            <!-- Login Form -->
            <div id="login" class="form-section active">
                <div class="form-title">Welcome Back</div>
                <div class="form-subtitle">Sign in to your account</div>

                <?php if ($error && isset($_POST['action']) && $_POST['action'] === 'login'): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="action" value="login">

                    <div class="form-group">
                        <label for="login-email">Email Address</label>
                        <input type="email" id="login-email" name="email" required placeholder="you@example.com">
                    </div>

                    <div class="form-group">
                        <label for="login-password">Password</label>
                        <input type="password" id="login-password" name="password" required placeholder="••••••••">
                    </div>

                    <button type="submit" class="btn btn-primary">Sign In</button>
                </form>

                <div class="toggle-text">
                    Don't have an account? <a onclick="switchTab('signup')">Sign Up</a>
                </div>
            </div>

            <!-- Signup Form -->
            <div id="signup" class="form-section">
                <div class="form-title">Create Account</div>
                <div class="form-subtitle">Join us to book your nail appointments</div>

                <?php if ($error && isset($_POST['action']) && $_POST['action'] === 'signup'): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php elseif ($success && isset($_POST['action']) && $_POST['action'] === 'signup'): ?>
                    <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="action" value="signup">

                    <div class="form-group">
                        <label for="signup-name">Full Name</label>
                        <input type="text" id="signup-name" name="name" required placeholder="John Doe" value="<?php echo htmlspecialchars($name ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="signup-email">Email Address</label>
                        <input type="email" id="signup-email" name="email" required placeholder="you@example.com" value="<?php echo htmlspecialchars($email ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="signup-password">Password</label>
                        <input type="password" id="signup-password" name="password" required placeholder="••••••••" minlength="6">
                    </div>

                    <div class="form-group">
                        <label for="signup-confirm-password">Confirm Password</label>
                        <input type="password" id="signup-confirm-password" name="confirm_password" required placeholder="••••••••" minlength="6">
                    </div>

                    <button type="submit" class="btn btn-primary">Create Account</button>
                </form>

                <div class="toggle-text">
                    Already have an account? <a onclick="switchTab('login')">Sign In</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tab) {
            // Hide all sections
            document.querySelectorAll('.form-section').forEach(section => {
                section.classList.remove('active');
            });

            // Remove active class from all buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });

            // Show selected section
            document.getElementById(tab).classList.add('active');

            // Add active class to clicked button
            if (tab === 'login') {
                document.querySelectorAll('.tab-btn')[0].classList.add('active');
            } else {
                document.querySelectorAll('.tab-btn')[1].classList.add('active');
            }

            // Clear error messages when switching tabs
            document.querySelectorAll('.error-message').forEach(msg => {
                msg.style.display = 'none';
            });
        }
    </script>
</body>
</html>
