<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (isParishLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if (empty($username) || empty($password)) {
        $error = 'Both username and password are required';
    } elseif (parishLogin($username, $password)) {
        header("Location: dashboard.php");
        exit();
    } else {
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parish Login - Keta-Akatsi Diocese</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        main {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 0 30px rgba(0,0,0,0.1);
            margin: 20px 0;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header img {
            height: 80px;
            margin-bottom: 15px;
        }
        
        .login-header h2 {
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .title-container {
            position: relative;
            height: 40px;
            overflow: hidden;
        }
        
        .title-text {
            position: absolute;
            width: 100%;
            text-align: center;
            color: #7f8c8d;
            font-size: 14px;
            transition: all 0.5s ease-in-out;
        }
        
        .title-text.initial {
            opacity: 1;
            transform: translateY(0);
        }
        
        .title-text.hidden {
            opacity: 0;
            transform: translateY(-100%);
        }
        
        .title-text.new {
            opacity: 0;
            transform: translateY(100%);
        }
        
        .title-text.visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            border-color: #3498db;
            outline: none;
        }
        
        .btn {
            width: 100%;
            padding: 12px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #2980b9;
        }
        
        .error {
            color: #e74c3c;
            margin-bottom: 20px;
            padding: 12px;
            background-color: #fde8e8;
            border-radius: 6px;
            text-align: center;
            font-size: 14px;
        }
        
        .login-footer {
            text-align: center;
            margin-top: 20px;
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .password-container {
            position: relative;
        }
        
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main>
        <div class="login-container">
            <div class="login-header">
                <img src="<?php echo BASE_URL; ?>/assets/images/dioceselogo.png" alt="Diocese Logo">
                <h2>Parish Login</h2>
                <div class="title-container">
                    <p class="title-text initial">Keta-Akatsi Diocese Data Center</p>
                    <p class="title-text new">Keta-Akatsi Diocese Data Management System</p>
                </div>
            </div>
            
            <?php if ($error): ?>
            <div class="error">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>
            
            <form action="" method="POST">
                <div class="form-group">
                    <label for="username"><i class="fas fa-church"></i> Parish Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Password</label>
                    <div class="password-container">
                        <input type="password" id="password" name="password" required>
                        <i class="fas fa-eye toggle-password" onclick="togglePassword()"></i>
                    </div>
                </div>
                
                <button type="submit" class="btn">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
            
            <div class="login-footer">
                <p>© <?php echo date('Y'); ?> Keta-Akatsi Diocese. All rights reserved.</p>
            </div>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    
    <script>
        // Text animation
        document.addEventListener('DOMContentLoaded', function() {
            const initialText = document.querySelector('.title-text.initial');
            const newText = document.querySelector('.title-text.new');
            
            // Start the animation after 2 seconds
            setTimeout(() => {
                initialText.classList.add('hidden');
                newText.classList.add('visible');
                
                // Optional: Make it loop every 8 seconds
                setInterval(() => {
                    initialText.classList.remove('hidden');
                    newText.classList.remove('visible');
                    
                    setTimeout(() => {
                        initialText.classList.add('hidden');
                        newText.classList.add('visible');
                    }, 2000);
                }, 8000);
            }, 2000);
        });
        
        // Password visibility toggle
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const icon = document.querySelector('.toggle-password');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>