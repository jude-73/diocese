<?php
require_once 'config.php';
require_once 'auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keta-Akatsi Diocese Parish Management System</title>
    <link rel="icon" href="<?php echo BASE_URL; ?>/assets/images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #D8BFD8;
            --primary-dark: #662d91;
            --accent: #f72585;
            --light: #f8f9fa;
            --dark: #212529;
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        body {
            background-color: var(--light);
            color: var(--dark);
            line-height: 1.6;
        }

        header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 1.5rem 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .container {
            width: 90%;
            max-width: 1400px;
            margin: 0 auto;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 2rem;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo img {
            height: 50px;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
        }

        .logo h1 {
            font-size: 1.8rem;
            font-weight: 700;
            background: linear-gradient(to right, #fff, #e0e0e0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        nav ul {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        nav ul li a {
            color: white;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            padding: 0.5rem 0;
            position: relative;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        nav ul li a i {
            font-size: 1rem;
        }

        nav ul li a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 3px;
            background: var(--accent);
            transition: var(--transition);
        }

        nav ul li a:hover::after {
            width: 100%;
        }

        .auth-buttons {
            display: flex;
            gap: 1.5rem;
        }

        .auth-buttons a {
            padding: 0.8rem 1.5rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }

        .login-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .login-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .logout-btn {
            background: var(--accent);
            color: white;
            border: none;
        }

        .logout-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(247, 37, 133, 0.3);
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% { transform: translateY(-3px) scale(1); }
            50% { transform: translateY(-3px) scale(1.05); }
            100% { transform: translateY(-3px) scale(1); }
        }

        .breadcrumb {
            padding: 1.2rem 0;
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .breadcrumb a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .breadcrumb a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .breadcrumb a:not(:last-child)::after {
            content: '›';
            margin: 0 0.8rem;
            color: var(--primary);
        }

        @media (max-width: 992px) {
            .header-content {
                flex-direction: column;
                gap: 1.5rem;
            }
            
            nav ul {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .auth-buttons {
                justify-content: center;
                width: 100%;
            }
        }

        @media (max-width: 576px) {
            nav ul {
                gap: 1rem;
            }
            
            nav ul li a {
                font-size: 1rem;
            }
            
            .auth-buttons {
                flex-direction: column;
                gap: 1rem;
            }
            
            .auth-buttons a {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <img src="<?php echo BASE_URL; ?>/assets/images/dioceselogo.png" alt="Keta-Akatsi Diocese Logo">
                    <h1>Keta-Akatsi Diocese</h1>
                </div>
                <nav>
                    <ul>
                        <li><a href="<?php echo BASE_URL; ?>/public/index.php"><i class="fas fa-home"></i> Home</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/public/gallery.php"><i class="fas fa-images"></i> Gallery</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/public/search.php"><i class="fas fa-search"></i> Find Member</a></li>
                        <?php if (isAdminLoggedIn()): ?>
                            <li><a href="<?php echo BASE_URL; ?>/admin/dashboard.php"><i class="fas fa-cog"></i> Admin Panel</a></li>
                        <?php elseif (isParishLoggedIn()): ?>
                            <li><a href="<?php echo BASE_URL; ?>/parish/dashboard.php"><i class="fas fa-church"></i> Parish Panel</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <div class="auth-buttons">
    <?php if (isAdminLoggedIn() || isParishLoggedIn()): ?>
        <a href="<?php echo BASE_URL; ?>/includes/logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    <?php else: ?>
        <a href="<?php echo BASE_URL; ?>/admin/index.php" class="login-btn">
            <i class="fas fa-user-shield"></i> Admin Login
        </a>
        <a href="<?php echo BASE_URL; ?>/parish/index.php" class="login-btn">
            <i class="fas fa-user"></i> Parish Login
        </a>
    <?php endif; ?>
</div>
                </div>
            </div>
        </div>
    </header>
    
    <?php if (isset($breadcrumb)): ?>
    <div class="breadcrumb container">
        <a href="<?php echo BASE_URL; ?>/public/index.php">Home</a>
        <?php echo $breadcrumb; ?>
    </div>
    <?php endif; ?>

    <main class="container">
        <?php if (isset($content)) echo $content; ?>
    </main>
</body>
</html>