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
$parish_id = $is_admin ? null : $uploader_id; // Changed to allow NULL for admin uploads
$image_path = '';
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $caption = trim($_POST['caption']);
    $parish_id = $is_admin ? (isset($_POST['parish_id']) && $_POST['parish_id'] !== '' ? intval($_POST['parish_id']) : null) : $uploader_id;
    
    // Validate inputs
    if (empty($caption)) {
        $errors[] = 'Caption is required';
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

<div class="container" style="margin-top: 30px; margin-bottom: 30px;">
    <h2 style="color: #4a00e0; font-weight: 600; font-size: 28px; margin-bottom: 20px;"><?php echo $title; ?></h2>
    
    <?php if (!empty($errors)): ?>
    <div style="background-color: #ffebee; color: #c62828; padding: 15px; border-radius: 8px; margin-bottom: 25px; border-left: 4px solid #c62828;">
        <h4 style="margin-top: 0; margin-bottom: 10px; font-weight: 600;">Please fix the following:</h4>
        <ul style="margin: 0; padding-left: 20px;">
            <?php foreach ($errors as $error): ?>
            <li><?php echo $error; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
    
    <div style="background-color: white; padding: 25px; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.08);">
        <form action="" method="POST" enctype="multipart/form-data">
            <?php if ($is_admin): ?>
            <div style="margin-bottom: 20px;">
                <label for="parish_id" style="display: block; margin-bottom: 8px; font-weight: 500; color: #4a5568;">Parish (Optional)</label>
                <select id="parish_id" name="parish_id" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; background-color: #f8fafc; transition: border-color 0.2s; font-size: 15px;">
                    <option value="">-- No Parish (Admin Upload) --</option>
                    <?php
                    $parishes = $conn->query("SELECT * FROM parishes ORDER BY name");
                    while ($parish = $parishes->fetch_assoc()) {
                        $selected = ($parish_id == $parish['id']) ? 'selected' : '';
                        echo '<option value="' . $parish['id'] . '" ' . $selected . '>' . htmlspecialchars($parish['name']) . '</option>';
                    }
                    ?>
                </select>
                <small style="display: block; margin-top: 5px; color: #718096;">Leave unselected to mark as admin upload</small>
            </div>
            <?php endif; ?>
            
            <div style="margin-bottom: 20px;">
                <label for="caption" style="display: block; margin-bottom: 8px; font-weight: 500; color: #4a5568;">Caption</label>
                <input type="text" id="caption" name="caption" value="<?php echo htmlspecialchars($caption); ?>" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; background-color: #f8fafc; transition: border-color 0.2s; font-size: 15px;" required>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label for="image" style="display: block; margin-bottom: 8px; font-weight: 500; color: #4a5568;">Image</label>
                <?php if (!empty($image_path)): ?>
                <div style="margin-bottom: 15px; display: flex; align-items: flex-start; gap: 15px;">
                    <img src="<?php echo BASE_URL . '/uploads/' . htmlspecialchars($image_path); ?>" alt="Current Image" style="max-width: 300px; max-height: 200px; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                    <input type="hidden" name="old_image" value="<?php echo htmlspecialchars($image_path); ?>">
                </div>
                <?php endif; ?>
                <div style="border: 2px dashed #e2e8f0; border-radius: 8px; padding: 25px; text-align: center; background-color: #f8fafc; transition: all 0.2s;" id="dropzone">
                    <input type="file" id="image" name="image" accept="image/*" style="display: none;" <?php echo ($action == 'add') ? 'required' : ''; ?>>
                    <button type="button" onclick="document.getElementById('image').click()" style="background: linear-gradient(135deg, #8e2de2, #4a00e0); color: white; padding: 12px 20px; border: none; border-radius: 8px; font-weight: 500; cursor: pointer; margin-bottom: 10px; transition: transform 0.2s, box-shadow 0.2s;" 
                            onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(142, 45, 226, 0.3)'" 
                            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 8px;">
                            <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                        </svg>
                        Select Image
                    </button>
                    <p style="margin: 0; color: #718096; font-size: 14px;">or drag and drop files here</p>
                    <p id="file-info" style="margin-top: 10px; font-weight: 500; color: #4a00e0; display: none;"></p>
                </div>
                <?php if ($action == 'edit'): ?>
                <small style="display: block; margin-top: 8px; color: #718096;">Upload a new image only if you want to replace the current one</small>
                <?php endif; ?>
            </div>
            
            <div style="margin-top: 30px; display: flex; gap: 15px;">
                <button type="submit" style="background: linear-gradient(135deg, #8e2de2, #4a00e0); color: white; padding: 12px 25px; border: none; border-radius: 8px; font-weight: 500; cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;"
                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(142, 45, 226, 0.3)'" 
                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 8px;">
                        <path d="M2 1a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H2zm12-1a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h12z"/>
                        <path d="M6.854 4.646a.5.5 0 0 1 0 .708L4.207 8l2.647 2.646a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 0 1 .708 0zm2.292 0a.5.5 0 0 0 0 .708L11.793 8l-2.647 2.646a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708 0z"/>
                    </svg>
                    Save
                </button>
                <a href="<?php echo $is_admin ? '../../admin/gallery.php' : '../../parish/gallery.php'; ?>" 
                   style="padding: 12px 25px; border: 1px solid #e2e8f0; border-radius: 8px; font-weight: 500; cursor: pointer; text-decoration: none; color: #4a5568; transition: all 0.2s;"
                   onmouseover="this.style.backgroundColor='#f8fafc'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.05)'" 
                   onmouseout="this.style.backgroundColor='transparent'; this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    // Enhance file input with drag and drop and file info display
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('image');
    const fileInfo = document.getElementById('file-info');

    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            fileInfo.textContent = this.files[0].name;
            fileInfo.style.display = 'block';
            dropzone.style.borderColor = '#4a00e0';
            dropzone.style.backgroundColor = '#f0e6ff';
        }
    });

    // Drag and drop functionality
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, unhighlight, false);
    });

    function highlight() {
        dropzone.style.borderColor = '#4a00e0';
        dropzone.style.backgroundColor = '#f0e6ff';
    }

    function unhighlight() {
        dropzone.style.borderColor = '#e2e8f0';
        dropzone.style.backgroundColor = '#f8fafc';
    }

    dropzone.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        fileInput.files = files;
        
        if (files.length > 0) {
            fileInfo.textContent = files[0].name;
            fileInfo.style.display = 'block';
            dropzone.style.borderColor = '#4a00e0';
            dropzone.style.backgroundColor = '#f0e6ff';
        }
    }
</script>

<?php include '../../includes/footer.php'; ?>