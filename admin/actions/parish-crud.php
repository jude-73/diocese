<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';

if (!isAdminLoggedIn()) {
    header("Location: ../../admin/index.php");
    exit();
}

$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Initialize variables
$name = $username = $deanery = $address = '';
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $deanery = trim($_POST['deanery']);
    $address = trim($_POST['address']);
    $password = trim($_POST['password']);
    
    // Validate inputs
    if (empty($name)) {
        $errors[] = 'Parish name is required';
    }
    
    if (empty($username)) {
        $errors[] = 'Username is required';
    } else {
        // Check if username exists (for add action)
        if ($action == 'add') {
            $stmt = $conn->prepare("SELECT id FROM parishes WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                $errors[] = 'Username already exists';
            }
        }
    }
    
    if ($action == 'add' && empty($password)) {
        $password = 'parish123'; // Default password
    }
    
    if (empty($errors)) {
        if ($action == 'add') {
            // Add new parish (store plain text password)
            $stmt = $conn->prepare("INSERT INTO parishes (name, username, password, deanery, address) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $name, $username, $password, $deanery, $address);
            
            if ($stmt->execute()) {
                $_SESSION['message'] = 'Parish added successfully';
                header("Location: ../parishes.php");
                exit();
            } else {
                $errors[] = 'Error adding parish: ' . $conn->error;
            }
        } elseif ($action == 'edit') {
            // Update parish
            if (!empty($password)) {
                $stmt = $conn->prepare("UPDATE parishes SET name = ?, username = ?, password = ?, deanery = ?, address = ? WHERE id = ?");
                $stmt->bind_param("sssssi", $name, $username, $password, $deanery, $address, $id);
            } else {
                $stmt = $conn->prepare("UPDATE parishes SET name = ?, username = ?, deanery = ?, address = ? WHERE id = ?");
                $stmt->bind_param("ssssi", $name, $username, $deanery, $address, $id);
            }
            
            if ($stmt->execute()) {
                $_SESSION['message'] = 'Parish updated successfully';
                header("Location: ../parishes.php");
                exit();
            } else {
                $errors[] = 'Error updating parish: ' . $conn->error;
            }
        }
    }
} elseif ($action == 'edit' && $id > 0) {
    // Fetch parish data for editing
    $stmt = $conn->prepare("SELECT * FROM parishes WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $parish = $result->fetch_assoc();
        $name = $parish['name'];
        $username = $parish['username'];
        $deanery = $parish['deanery'];
        $address = $parish['address'];
    } else {
        $_SESSION['message'] = 'Parish not found';
        header("Location: ../parishes.php");
        exit();
    }
}

$title = $action == 'add' ? 'Add New Parish' : 'Edit Parish';
$breadcrumb = 'Admin Dashboard &raquo; Manage Parishes &raquo; ' . $title;
?>
<?php include '../../includes/header.php'; ?>

<style>
    :root {
        --primary: #3f51b5;
        --primary-light: #757de8;
        --primary-dark: #002984;
        --secondary: #ff9800;
        --danger: #f44336;
        --success: #4caf50;
        --dark: #212121;
        --light: #f5f5f5;
        --gray: #9e9e9e;
        --border: #e0e0e0;
        --shadow-sm: 0 1px 3px rgba(0,0,0,0.12);
        --shadow: 0 4px 6px rgba(0,0,0,0.1);
        --shadow-md: 0 10px 20px rgba(0,0,0,0.1);
        --radius-sm: 4px;
        --radius: 8px;
        --radius-lg: 12px;
        --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    }

    .container {
        max-width: 800px;
        margin: 2rem auto;
        padding: 0 1.5rem;
    }

    h2 {
        color: var(--dark);
        font-size: 1.75rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
        position: relative;
        padding-bottom: 0.75rem;
    }

    h2::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 50px;
        height: 3px;
        background: var(--primary);
    }

    .form-container {
        background: white;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: var(--dark);
        font-size: 0.95rem;
    }

    input[type="text"],
    input[type="password"],
    textarea,
    select {
        width: 100%;
        padding: 0.875rem 1rem;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        font-size: 0.95rem;
        transition: var(--transition);
        background-color: white;
    }

    input[type="text"]:focus,
    input[type="password"]:focus,
    textarea:focus,
    select:focus {
        border-color: var(--primary);
        outline: none;
        box-shadow: 0 0 0 3px rgba(63, 81, 181, 0.2);
    }

    textarea {
        min-height: 100px;
        resize: vertical;
    }

    small {
        display: block;
        margin-top: 0.25rem;
        color: var(--gray);
        font-size: 0.85rem;
    }

    .error-message {
        background-color: #fdecea;
        color: var(--danger);
        padding: 1rem;
        border-radius: var(--radius-sm);
        margin-bottom: 1.5rem;
        border-left: 4px solid var(--danger);
    }

    .error-message ul {
        margin: 0;
        padding-left: 1.25rem;
    }

    .error-message li {
        margin-bottom: 0.25rem;
    }

    .button-group {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: var(--radius-sm);
        font-weight: 500;
        font-size: 0.95rem;
        cursor: pointer;
        transition: var(--transition);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none;
    }

    .btn-primary {
        background-color: var(--primary);
        color: white;
    }

    .btn-primary:hover {
        background-color: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: var(--shadow);
    }

    .btn-outline {
        background-color: white;
        color: var(--primary);
        border: 1px solid var(--primary);
    }

    .btn-outline:hover {
        background-color: rgba(63, 81, 181, 0.04);
    }

    @media (max-width: 768px) {
        .container {
            padding: 0 1rem;
        }
        
        .form-container {
            padding: 1.5rem;
        }
        
        .button-group {
            flex-direction: column;
        }
        
        .btn {
            width: 100%;
        }
    }
</style>

<div class="container">
    <h2><?php echo $title; ?></h2>
    
    <?php if (!empty($errors)): ?>
    <div class="error-message">
        <ul>
            <?php foreach ($errors as $error): ?>
            <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
    
    <div class="form-container">
        <form action="" method="POST">
            <div class="form-group">
                <label for="name">Parish Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>
            
            <?php if ($action == 'add'): ?>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password">
                <small>Default password will be "parish123" if not specified</small>
            </div>
            <?php else: ?>
            <div class="form-group">
                <label for="password">New Password (leave blank to keep current)</label>
                <input type="password" id="password" name="password">
            </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="deanery">Deanery</label>
                <input type="text" id="deanery" name="deanery" value="<?php echo htmlspecialchars($deanery); ?>">
            </div>
            
            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address"><?php echo htmlspecialchars($address); ?></textarea>
            </div>
            
            <div class="button-group">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="../parishes.php" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>