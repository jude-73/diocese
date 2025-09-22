<?php
// includes/auth.php

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function isSuperAdminLoggedIn() {
    return isset($_SESSION['superadmin_id']);
}

function isParishLoggedIn() {
    return isset($_SESSION['parish_id']);
}

function adminLogin($username, $password) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT id, username, full_name, password, is_superadmin FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $admin = $result->fetch_assoc();
        
        // Plain text password comparison
        if ($password === $admin['password']) {
            if ($admin['is_superadmin']) {
                $_SESSION['superadmin_id'] = $admin['id'];
                $_SESSION['superadmin_username'] = $admin['username'];
                $_SESSION['superadmin_name'] = $admin['full_name'];
            } else {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_name'] = $admin['full_name'];
            }
            return true;
        }
    }
    return false;
}

function parishLogin($username, $password) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT id, name, username, password FROM parishes WHERE username = ?");
        $stmt->execute([$username]);
        $parish = $stmt->fetch();
        
        if ($parish && $password === $parish['password']) {
            $_SESSION['parish_id'] = $parish['id'];
            $_SESSION['parish_name'] = $parish['name'];
            $_SESSION['parish_username'] = $parish['username'];
            return true;
        }
        return false;
    } catch (PDOException $e) {
        error_log("Parish login error: " . $e->getMessage());
        return false;
    }
}

function logout() {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Clear all session variables
    $_SESSION = array();

    // Delete the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), 
            '', 
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // Destroy the session
    session_destroy();

    // Redirect to login page
    header("Location: " . BASE_URL . "/admin/index.php");
    exit();
}
?>