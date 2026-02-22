<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Query user by username and password only - role is determined from database
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    $userData = $result->fetch_assoc();

    if ($userData) {
        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['role'] = $userData['role'];
        $_SESSION['username'] = $userData['username'];

        // Redirect based on user's role from database
        if ($userData['role'] === "admin") {
            header("Location: /EMS2/admin/dashboard.php");
        } elseif ($userData['role'] === "voter") {
            header("Location: /EMS2/voter/dashboard.php");
        } elseif ($userData['role'] === "candidate") {
            header("Location: /EMS2/candidate/dashboard.php");
        }
        exit();
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | Election Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        /* Animated background elements */
        body::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: -250px;
            right: -250px;
            animation: float 6s ease-in-out infinite;
        }

        body::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            bottom: -200px;
            left: -200px;
            animation: float 8s ease-in-out infinite reverse;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(20px); }
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 50px 40px;
            width: 100%;
            max-width: 440px;
            position: relative;
            z-index: 1;
            animation: slideUp 0.6s ease-out;
        }

        .back-home-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 10px;
            transition: all 0.3s ease;
            background: rgba(102, 126, 234, 0.1);
        }

        .back-home-btn:hover {
            background: rgba(102, 126, 234, 0.2);
            transform: translateX(-3px);
        }

        .back-home-btn svg {
            width: 18px;
            height: 18px;
            fill: #667eea;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo-section {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .logo-icon svg {
            width: 40px;
            height: 40px;
            fill: white;
        }

        h1 {
            font-size: 32px;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #718096;
            font-size: 15px;
            font-weight: 400;
        }

        .error-message {
            background: linear-gradient(135deg, #fc5c7d 0%, #e74c3c 100%);
            color: white;
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 14px;
            font-weight: 500;
            animation: shake 0.5s;
            box-shadow: 0 4px 15px rgba(252, 92, 125, 0.3);
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .form-group {
            margin-bottom: 24px;
        }

        label {
            display: block;
            color: #2d3748;
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: #f7fafc;
            font-family: 'Inter', sans-serif;
        }

        input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }

        input:hover {
            border-color: #cbd5e0;
        }

        .submit-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
            margin-top: 10px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.5);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .footer-links {
            margin-top: 30px;
            text-align: center;
            color: #718096;
            font-size: 14px;
        }

        .footer-links a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: #764ba2;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-container {
                padding: 40px 30px;
            }

            h1 {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <a href="/EMS2/public/index.php" class="back-home-btn">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
            </svg>
            Back to Home
        </a>
        
        <div class="logo-section">
            <div class="logo-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
                </svg>
            </div>
            <h1>Welcome Back</h1>
            <p class="subtitle">Sign in to access your account</p>
        </div>

        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-wrapper">
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        required 
                        placeholder="Enter your username"
                        autocomplete="username"
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required 
                        placeholder="Enter your password"
                        autocomplete="current-password"
                    >
                </div>
            </div>

            <button type="submit" class="submit-btn">Sign In</button>
        </form>

        <div class="footer-links">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
    </div>
</body>
</html> 