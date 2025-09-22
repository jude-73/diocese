<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';

// Determine if this is admin or parish access
$is_admin = strpos($_SERVER['REQUEST_URI'], '/admin/') !== false;

if ($is_admin) {
    if (!isAdminLoggedIn()) {
        header("Location: ../../admin/index.php");
        exit();
    }
    $uploaded_by = 'admin';
    $uploader_id = $_SESSION['admin_id'];
} else {
    if (!isParishLoggedIn()) {
        header("Location: ../../parish/index.php");
        exit();
    }
    $uploaded_by = 'parish';
    $uploader_id = $_SESSION['parish_id'];
}

$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Handle gallery item deletion
if ($action == 'delete' && $id > 0) {
    // Verify ownership before deletion
    $query = "SELECT image_path FROM gallery WHERE id = ?";
    if (!$is_admin) {
        $query .= " AND parish_id = ? AND uploaded_by = 'parish'";
    }
    
    $stmt = $conn->prepare($query);
    
    if ($is_admin) {
        $stmt->bind_param("i", $id);
    } else {
        $stmt->bind_param("ii", $id, $uploader_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $item = $result->fetch_assoc();
        $image_path = '../../uploads/' . $item['image_path'];
        
        // Delete the file
        if (file_exists($image_path)) {
            unlink($image_path);
        }
        
        // Delete the record
        $stmt = $conn->prepare("DELETE FROM gallery WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        $_SESSION['message'] = 'Image deleted successfully';
    } else {
        $_SESSION['error'] = 'Image not found or not authorized';
    }
    
    header("Location: " . ($is_admin ? "../../admin/gallery.php" : "../../parish/gallery.php"));
    exit();
}

// Initialize variables
$caption = '';
$parish_id = $is_admin ? 0 : $uploader_id;
$image_path = '';
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $caption = trim($_POST['caption']);
    $parish_id = $is_admin ? intval($_POST['parish_id']) : $uploader_id;
    
    // Validate inputs
    if (empty($caption)) {
        $errors[] = 'Caption is required';
    }
    
    if ($is_admin && $parish_id <= 0) {
        $errors[] = 'Please select a parish';
    }
    
    // Handle file upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = '../../uploads/gallery/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_ext, $allowed_ext)) {
            $file_name = uniqid('gallery_', true) . '.' . $file_ext;
            $file_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $file_path)) {
                $image_path = 'gallery/' . $file_name;
                
                // Delete old image if exists
                if ($action == 'edit' && !empty($_POST['old_image'])) {
                    $old_image_path = '../../uploads/' . $_POST['old_image'];
                    if (file_exists($old_image_path)) {
                        unlink($old_image_path);
                    }
                }
            } else {
                $errors[] = 'Error uploading image';
            }
        } else {
            $errors[] = 'Invalid file type. Only JPG, JPEG, PNG, GIF are allowed';
        }
    } elseif ($action == 'edit') {
        $image_path = $_POST['old_image'];
    } else {
        $errors[] = 'Image is required';
    }
    
    if (empty($errors)) {
        if ($action == 'add') {
            // Insert new gallery item
            $stmt = $conn->prepare("INSERT INTO gallery (parish_id, image_path, caption, uploaded_by, uploader_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("isssi", $parish_id, $image_path, $caption, $uploaded_by, $uploader_id);
            
            if ($stmt->execute()) {
                $_SESSION['message'] = 'Image added successfully';
                header("Location: " . ($is_admin ? "../../admin/gallery.php" : "../../parish/gallery.php"));
                exit();
            } else {
                $errors[] = 'Error adding image: ' . $conn->error;
            }
        } elseif ($action == 'edit' && $id > 0) {
            // Update gallery item
            $query = "UPDATE gallery SET caption = ?, image_path = ?";
            $params = [$caption, $image_path];
            $types = "ss";
            
            if ($is_admin) {
                $query .= ", parish_id = ?";
                $params[] = $parish_id;
                $types .= "i";
            }
            
            $query .= " WHERE id = ?";
            $params[] = $id;
            $types .= "i";
            
            if (!$is_admin) {
                $query .= " AND parish_id = ? AND uploaded_by = 'parish'";
                $params[] = $uploader_id;
                $types .= "i";
            }
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param($types, ...$params);
            
            if ($stmt->execute()) {
                $_SESSION['message'] = 'Image updated successfully';
                header("Location: " . ($is_admin ? "../../admin/gallery.php" : "../../parish/gallery.php"));
                exit();
            } else {
                $errors[] = 'Error updating image: ' . $conn->error;
            }
        }
    }
} elseif ($action == 'edit' && $id > 0) {
    // Fetch gallery item for editing
    $query = "SELECT * FROM gallery WHERE id = ?";
    if (!$is_admin) {
        $query .= " AND parish_id = ? AND uploaded_by = 'parish'";
    }
    
    $stmt = $conn->prepare($query);
    
    if ($is_admin) {
        $stmt->bind_param("i", $id);
    } else {
        $stmt->bind_param("ii", $id, $uploader_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $item = $result->fetch_assoc();
        $caption = $item['caption'];
        $parish_id = $item['parish_id'];
        $image_path = $item['image_path'];
    } else {
        $_SESSION['error'] = 'Image not found or not authorized';
        header("Location: " . ($is_admin ? "../../admin/gallery.php" : "../../parish/gallery.php"));
        exit();
    }
}

$title = $action == 'add' ? 'Add New Image' : 'Edit Image';
$breadcrumb = ($is_admin ? 'Admin Dashboard' : 'Parish Dashboard') . ' &raquo; Gallery &raquo; ' . $title;
?>
<?php include '../../includes/header.php'; ?>

<style>
    :root {
        --primary-color: #4a6fa5;
        --secondary-color: #5cb85c;
        --danger-color: #d9534f;
        --light-gray: #f8f9fa;
        --border-color: #dee2e6;
        --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        --transition: all 0.3s ease;
    }

    .container {
        max-width: 800px;
        margin: 2rem auto;
        padding: 0 1rem;
    }

    .page-title {
        color: var(--primary-color);
        margin-bottom: 1.5rem;
        font-weight: 600;
    }

    .form-container {
        background-color: white;
        padding: 2rem;
        border-radius: 0.5rem;
        box-shadow: var(--box-shadow);
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: #495057;
    }

    .form-control {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid var(--border-color);
        border-radius: 0.375rem;
        font-size: 1rem;
        transition: var(--transition);
    }

    .form-control:focus {
        border-color: var(--primary-color);
        outline: none;
        box-shadow: 0 0 0 3px rgba(74, 111, 165, 0.2);
    }

    .form-select {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid var(--border-color);
        border-radius: 0.375rem;
        font-size: 1rem;
        background-color: white;
    }

    .current-image {
        max-width: 100%;
        height: auto;
        max-height: 300px;
        border: 1px solid var(--border-color);
        border-radius: 0.375rem;
        margin-bottom: 1rem;
        padding: 0.5rem;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.75rem 1.5rem;
        border-radius: 0.375rem;
        font-weight: 500;
        text-decoration: none;
        transition: var(--transition);
        cursor: pointer;
        border: none;
        font-size: 1rem;
    }

    .btn-primary {
        background-color: var(--primary-color);
        color: white;
    }

    .btn-primary:hover {
        background-color: #3a5a8a;
        transform: translateY(-2px);
    }

    .btn-outline {
        background-color: white;
        color: var(--primary-color);
        border: 1px solid var(--primary-color);
        margin-left: 0.75rem;
    }

    .btn-outline:hover {
        background-color: var(--light-gray);
    }

    .alert-error {
        background-color: #f8d7da;
        color: #721c24;
        padding: 1rem;
        border-radius: 0.375rem;
        margin-bottom: 1.5rem;
    }

    .alert-error ul {
        margin: 0;
        padding-left: 1.25rem;
    }

    .form-note {
        color: #6c757d;
        font-size: 0.875rem;
        margin-top: 0.5rem;
    }
</style>

<div class="container">
    <h1 class="page-title"><?php echo $title; ?></h1>
    
    <?php if (!empty($errors)): ?>
    <div class="alert-error">
        <ul>
            <?php foreach ($errors as $error): ?>
            <li><?php echo $error; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
    
    <div class="form-container">
        <form action="" method="POST" enctype="multipart/form-data">
            <?php if ($is_admin): ?>
            <div class="form-group">
                <label for="parish_id" class="form-label">Parish</label>
                <select id="parish_id" name="parish_id" class="form-select" required>
                    <option value="">Select Parish</option>
                    <?php
                    $parishes = $conn->query("SELECT * FROM parishes ORDER BY name");
                    while ($parish = $parishes->fetch_assoc()) {
                        $selected = ($parish_id == $parish['id']) ? 'selected' : '';
                        echo '<option value="' . $parish['id'] . '" ' . $selected . '>' . htmlspecialchars($parish['name']) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="caption" class="form-label">Caption</label>
                <input type="text" id="caption" name="caption" value="<?php echo htmlspecialchars($caption); ?>" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="image" class="form-label">Image</label>
                <?php if (!empty($image_path)): ?>
                <div>
                    <img src="<?php echo BASE_URL . '/uploads/' . htmlspecialchars($image_path); ?>" alt="Current Image" class="current-image">
                    <input type="hidden" name="old_image" value="<?php echo htmlspecialchars($image_path); ?>">
                </div>
                <?php endif; ?>
                <input type="file" id="image" name="image" accept="image/*" class="form-control" <?php echo ($action == 'add') ? 'required' : ''; ?>>
                <?php if ($action == 'edit'): ?>
                <p class="form-note">Upload a new image only if you want to replace the current one</p>
                <?php endif; ?>
            </div>
            
            <div>
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="<?php echo $is_admin ? '../../admin/gallery.php' : '../../parish/gallery.php'; ?>" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>