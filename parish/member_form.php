<?php
// Use consistent path handling with your login page
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!isParishLoggedIn()) {
    header("Location: index.php");
    exit();
}

$parish_id = $_SESSION['parish_id'];
$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Initialize member data
$member = [
    'mother_parish' => '',
    'outstation' => '',
    'title' => '',
    'last_name' => '',
    'other_names' => '',
    'dob' => '',
    'baptism_date' => '',
    'baptism_place' => '',
    'nlb' => '',
    'is_communicant' => 0,
    'confirmation_date' => '',
    'confirmation_place' => '',
    'nlc' => '',
    'confraternity' => '',
    'confraternity_position' => '',
    'marital_status' => '',
    'children_count' => 0,
    'occupation' => '',
    'is_welfare_member' => 0,
    'relative_name' => '',
    'relative_contact' => '',
    'address' => '',
    'christian_community' => '',
    'contact' => '',
    'image_path' => '',
    'mission_help' => 0.00,
    'special_contribution' => 0.00
];

$errors = [];

// NLB Check Logic
if (isset($_POST['check_nlb'])) {
    $nlb = trim($_POST['nlb']);
    if (!empty($nlb)) {
        $stmt = $conn->prepare("SELECT id FROM members WHERE nlb = ?");
        $stmt->bind_param("s", $nlb);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $_SESSION['message'] = 'Member with this NLB already exists. You can now update their record.';
            header("Location: member_form.php?action=edit&id=".$row['id']);
            exit();
        }
    } else {
        $errors[] = 'NLB number is required';
    }
}

// Form Submission Handling
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['check_nlb'])) {
    // Process form data
    $member['mother_parish'] = trim($_POST['mother_parish']);
    $member['outstation'] = trim($_POST['outstation']);
    $member['title'] = trim($_POST['title']);
    $member['last_name'] = trim($_POST['last_name']);
    $member['other_names'] = trim($_POST['other_names']);
    $member['dob'] = !empty($_POST['dob']) ? $_POST['dob'] : null;
    $member['baptism_date'] = !empty($_POST['baptism_date']) ? $_POST['baptism_date'] : null;
    $member['baptism_place'] = trim($_POST['baptism_place']);
    $member['nlb'] = trim($_POST['nlb']);
    $member['is_communicant'] = isset($_POST['is_communicant']) ? 1 : 0;
    $member['confirmation_date'] = !empty($_POST['confirmation_date']) ? $_POST['confirmation_date'] : null;
    $member['confirmation_place'] = trim($_POST['confirmation_place']);
    $member['nlc'] = trim($_POST['nlc']);
    $member['confraternity'] = trim($_POST['confraternity']);
    $member['confraternity_position'] = trim($_POST['confraternity_position']);
    $member['marital_status'] = trim($_POST['marital_status']);
    $member['children_count'] = intval($_POST['children_count']);
    $member['occupation'] = trim($_POST['occupation']);
    $member['is_welfare_member'] = isset($_POST['is_welfare_member']) ? 1 : 0;
    $member['relative_name'] = trim($_POST['relative_name']);
    $member['relative_contact'] = trim($_POST['relative_contact']);
    $member['address'] = trim($_POST['address']);
    $member['christian_community'] = trim($_POST['christian_community']);
    $member['contact'] = trim($_POST['contact']);
    $member['mission_help'] = !empty($_POST['mission_help']) ? floatval($_POST['mission_help']) : 0.00;
    $member['special_contribution'] = !empty($_POST['special_contribution']) ? floatval($_POST['special_contribution']) : 0.00;

    // Validate required fields
    if (empty($member['mother_parish'])) $errors[] = 'Mother Parish is required';
    if (empty($member['outstation'])) $errors[] = 'Outstation is required';
    if (empty($member['last_name'])) $errors[] = 'Last name is required';
    if (empty($member['other_names'])) $errors[] = 'Other names are required';
    if (empty($member['contact'])) $errors[] = 'Contact number is required';
    if (empty($member['nlb'])) $errors[] = 'NLB number is required';

    // Handle file upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/members/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_ext, $allowed_ext)) {
            $file_name = uniqid('member_', true) . '.' . $file_ext;
            $file_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $file_path)) {
                $member['image_path'] = 'members/' . $file_name;
                
                // Delete old image if exists
                if ($action == 'edit' && !empty($_POST['old_image'])) {
                    $old_image_path = '../uploads/' . $_POST['old_image'];
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
        $member['image_path'] = $_POST['old_image'];
    }
    
    // Database Operations
    if (empty($errors)) {
        if ($action == 'add') {
            // Insert new member
            $stmt = $conn->prepare("INSERT INTO members (
                parish_id, mother_parish, outstation, title, image_path, last_name, other_names, dob, baptism_date, baptism_place, nlb, 
                is_communicant, confirmation_date, confirmation_place, nlc, confraternity, confraternity_position, 
                marital_status, children_count, occupation, is_welfare_member, relative_name, relative_contact, 
                address, christian_community, contact, mission_help, special_contribution
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->bind_param("issssssssssissssssiissssssdd", 
                $parish_id,
                $member['mother_parish'],
                $member['outstation'],
                $member['title'],
                $member['image_path'],
                $member['last_name'],
                $member['other_names'],
                $member['dob'],
                $member['baptism_date'],
                $member['baptism_place'],
                $member['nlb'],
                $member['is_communicant'],
                $member['confirmation_date'],
                $member['confirmation_place'],
                $member['nlc'],
                $member['confraternity'],
                $member['confraternity_position'],
                $member['marital_status'],
                $member['children_count'],
                $member['occupation'],
                $member['is_welfare_member'],
                $member['relative_name'],
                $member['relative_contact'],
                $member['address'],
                $member['christian_community'],
                $member['contact'],
                $member['mission_help'],
                $member['special_contribution']
            );
        } elseif ($action == 'edit' && $id > 0) {
            // Update member
            $stmt = $conn->prepare("UPDATE members SET 
                mother_parish = ?,
                outstation = ?,
                title = ?, 
                image_path = ?, 
                last_name = ?, 
                other_names = ?, 
                dob = ?, 
                baptism_date = ?, 
                baptism_place = ?, 
                nlb = ?, 
                is_communicant = ?, 
                confirmation_date = ?, 
                confirmation_place = ?, 
                nlc = ?, 
                confraternity = ?, 
                confraternity_position = ?, 
                marital_status = ?, 
                children_count = ?, 
                occupation = ?, 
                is_welfare_member = ?, 
                relative_name = ?, 
                relative_contact = ?, 
                address = ?, 
                christian_community = ?, 
                contact = ?,
                mission_help = ?,
                special_contribution = ?
                WHERE id = ? AND parish_id = ?");
            
            $stmt->bind_param("ssssssssssissssssiissssssddii", 
                $member['mother_parish'],
                $member['outstation'],
                $member['title'],
                $member['image_path'],
                $member['last_name'],
                $member['other_names'],
                $member['dob'],
                $member['baptism_date'],
                $member['baptism_place'],
                $member['nlb'],
                $member['is_communicant'],
                $member['confirmation_date'],
                $member['confirmation_place'],
                $member['nlc'],
                $member['confraternity'],
                $member['confraternity_position'],
                $member['marital_status'],
                $member['children_count'],
                $member['occupation'],
                $member['is_welfare_member'],
                $member['relative_name'],
                $member['relative_contact'],
                $member['address'],
                $member['christian_community'],
                $member['contact'],
                $member['mission_help'],
                $member['special_contribution'],
                $id,
                $parish_id
            );
        }
        
        if ($stmt->execute()) {
            $_SESSION['message'] = 'Member ' . ($action == 'add' ? 'added' : 'updated') . ' successfully';
            header("Location: members.php");
            exit();
        } else {
            $errors[] = 'Database error: ' . $conn->error;
        }
    }
} elseif ($action == 'edit' && $id > 0) {
    // Load member data for editing
    $stmt = $conn->prepare("SELECT * FROM members WHERE id = ? AND parish_id = ?");
    $stmt->bind_param("ii", $id, $parish_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $member = $result->fetch_assoc();
    } else {
        $_SESSION['error'] = 'Member not found or not authorized';
        header("Location: members.php");
        exit();
    }
}

$title = $action == 'add' ? 'Add New Member' : 'Edit Member';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> - Keta-Akatsi Diocese</title>
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
            padding: 20px;
        }
        
        .form-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 0 30px rgba(0,0,0,0.1);
            padding: 30px;
        }
        
        h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #3498db;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        
        .required-field::after {
            content: " *";
            color: #e74c3c;
        }
        
        input[type="text"],
        input[type="date"],
        input[type="number"],
        select,
        textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input:focus,
        select:focus,
        textarea:focus {
            border-color: #3498db;
            outline: none;
        }
        
        textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .checkbox-group input {
            margin-right: 10px;
        }
        
        .member-photo {
            max-width: 200px;
            max-height: 200px;
            border-radius: 6px;
            border: 1px solid #ddd;
            object-fit: cover;
            margin-bottom: 15px;
        }
        
        .error-message {
            background-color: #fde8e8;
            color: #e74c3c;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #e74c3c;
        }
        
        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn {
            padding: 12px 20px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-primary {
            background-color: #3498db;
            color: white;
            border: none;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
        }
        
        .btn-outline {
            background-color: white;
            color: #7f8c8d;
            border: 1px solid #ddd;
        }
        
        .btn-outline:hover {
            background-color: #f8f9fa;
        }
        
        .nlb-check-form {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border: 1px solid #ddd;
        }
        
        .nlb-check-form h3 {
            margin-top: 0;
            color: #2c3e50;
        }
        
        @media (max-width: 768px) {
            .form-container {
                padding: 20px;
            }
            
            .button-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main>
        <div class="form-container">
            <h2><?php echo $title; ?></h2>
            
            <?php if (!empty($errors)): ?>
            <div class="error-message">
                <ul>
                    <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <?php if ($action == 'add'): ?>
            <div class="nlb-check-form">
                <h3>Check NLB First</h3>
                <form action="" method="POST">
                    <div class="form-group">
                        <label for="nlb" class="required-field">NLB Number</label>
                        <input type="text" id="nlb" name="nlb" value="<?php echo isset($_POST['nlb']) ? htmlspecialchars($_POST['nlb']) : ''; ?>" required>
                    </div>
                    <div class="button-group">
                        <button type="submit" name="check_nlb" class="btn btn-primary">Check NLB</button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
            
            <div id="memberForm" <?php echo ($action == 'add' && !isset($_POST['check_nlb'])) ? 'style="display:none;"' : ''; ?>>
                <form action="" method="POST" enctype="multipart/form-data">
                    <?php if ($action == 'edit'): ?>
                    <input type="hidden" name="old_image" value="<?php echo htmlspecialchars($member['image_path']); ?>">
                    <?php endif; ?>
                    
                    <div class="form-grid">
                        <div>
                            <h3>Parish Information</h3>
                            
                            <div class="form-group">
                                <label for="mother_parish" class="required-field">Mother Parish</label>
                                <input type="text" id="mother_parish" name="mother_parish" value="<?php echo htmlspecialchars($member['mother_parish'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="outstation">Outstation</label>
                                <input type="text" id="outstation" name="outstation" value="<?php echo htmlspecialchars($member['outstation'] ?? ''); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-grid">
                        <div>
                            <h3>Personal Information</h3>
                            
                            <div class="form-group">
                                <label for="title">Title</label>
                                <select id="title" name="title">
                                    <option value="">Select Title</option>
                                    <option value="Mr" <?php echo ($member['title'] == 'Mr') ? 'selected' : ''; ?>>Mr</option>
                                    <option value="Mrs" <?php echo ($member['title'] == 'Mrs') ? 'selected' : ''; ?>>Mrs</option>
                                    <option value="Miss" <?php echo ($member['title'] == 'Miss') ? 'selected' : ''; ?>>Miss</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="last_name" class="required-field">Last Name</label>
                                <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($member['last_name']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="other_names" class="required-field">Other Names</label>
                                <input type="text" id="other_names" name="other_names" value="<?php echo htmlspecialchars($member['other_names']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="dob">Date of Birth</label>
                                <input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($member['dob']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="contact" class="required-field">Contact Number</label>
                                <input type="text" id="contact" name="contact" value="<?php echo htmlspecialchars($member['contact']); ?>" required>
                            </div>
                        </div>
                        
                        <div>
                            <h3>Sacramental Information</h3>
                            
                            <div class="form-group">
                                <label for="baptism_date">Date of Baptism</label>
                                <input type="date" id="baptism_date" name="baptism_date" value="<?php echo htmlspecialchars($member['baptism_date']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="baptism_place">Place of Baptism</label>
                                <input type="text" id="baptism_place" name="baptism_place" value="<?php echo htmlspecialchars($member['baptism_place']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="nlb" class="required-field">NLB Number</label>
                                <input type="text" id="nlb" name="nlb" value="<?php echo htmlspecialchars($member['nlb']); ?>" required>
                            </div>
                            
                            <div class="checkbox-group">
                                <input type="checkbox" id="is_communicant" name="is_communicant" value="1" <?php echo ($member['is_communicant'] == 1) ? 'checked' : ''; ?>>
                                <label for="is_communicant">Is Communicant</label>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirmation_date">Date of Confirmation</label>
                                <input type="date" id="confirmation_date" name="confirmation_date" value="<?php echo htmlspecialchars($member['confirmation_date']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="confirmation_place">Place of Confirmation</label>
                                <input type="text" id="confirmation_place" name="confirmation_place" value="<?php echo htmlspecialchars($member['confirmation_place']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="nlc">NLC Number</label>
                                <input type="text" id="nlc" name="nlc" value="<?php echo htmlspecialchars($member['nlc']); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-grid">
                        <div>
                            <h3>Additional Information</h3>
                            
                            <div class="form-group">
                                <label for="marital_status">Marital Status</label>
                                <select id="marital_status" name="marital_status">
                                    <option value="">Select Status</option>
                                    <option value="Single" <?php echo ($member['marital_status'] == 'Single') ? 'selected' : ''; ?>>Single</option>
                                    <option value="Married" <?php echo ($member['marital_status'] == 'Married') ? 'selected' : ''; ?>>Married</option>
                                    <option value="Other" <?php echo ($member['marital_status'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="children_count">Number of Children</label>
                                <input type="number" id="children_count" name="children_count" value="<?php echo htmlspecialchars($member['children_count']); ?>" min="0">
                            </div>
                            
                            <div class="form-group">
                                <label for="occupation">Occupation</label>
                                <input type="text" id="occupation" name="occupation" value="<?php echo htmlspecialchars($member['occupation']); ?>">
                            </div>
                            
                            <div class="checkbox-group">
                                <input type="checkbox" id="is_welfare_member" name="is_welfare_member" value="1" <?php echo ($member['is_welfare_member'] == 1) ? 'checked' : ''; ?>>
                                <label for="is_welfare_member">Member of Church Welfare</label>
                            </div>
                            
                            <div class="form-group">
                                <label for="relative_name">Name of Close Relative</label>
                                <input type="text" id="relative_name" name="relative_name" value="<?php echo htmlspecialchars($member['relative_name']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="relative_contact">Relative's Contact</label>
                                <input type="text" id="relative_contact" name="relative_contact" value="<?php echo htmlspecialchars($member['relative_contact']); ?>">
                            </div>
                        </div>
                        
                        <div>
                            <h3>Contributions</h3>
                            
                            <div class="form-group">
                                <label for="mission_help">Mission Help (GHS)</label>
                                <input type="number" id="mission_help" name="mission_help" value="<?php echo htmlspecialchars($member['mission_help']); ?>" min="0" step="0.01">
                            </div>
                            
                            <div class="form-group">
                                <label for="special_contribution">Special Contribution (GHS)</label>
                                <input type="number" id="special_contribution" name="special_contribution" value="<?php echo htmlspecialchars($member['special_contribution']); ?>" min="0" step="0.01">
                            </div>
                            
                            <div class="form-group">
                                <label for="confraternity">Confraternity/Group/Society</label>
                                <input type="text" id="confraternity" name="confraternity" value="<?php echo htmlspecialchars($member['confraternity']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="confraternity_position">Position in Confraternity</label>
                                <input type="text" id="confraternity_position" name="confraternity_position" value="<?php echo htmlspecialchars($member['confraternity_position']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="address">House No/Digital Address</label>
                                <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($member['address']); ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="christian_community">Small Christian Community</label>
                                <input type="text" id="christian_community" name="christian_community" value="<?php echo htmlspecialchars($member['christian_community']); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <h3>Member Photo</h3>
                        
                        <?php if (!empty($member['image_path'])): ?>
                        <div class="form-group">
                            <img src="<?php echo BASE_URL; ?>/uploads/<?php echo htmlspecialchars($member['image_path']); ?>" alt="Member Photo" class="member-photo">
                        </div>
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="image"><?php echo empty($member['image_path']) ? 'Upload Photo' : 'Change Photo'; ?></label>
                            <input type="file" id="image" name="image" accept="image/*">
                        </div>
                    </div>
                    
                    <div class="button-group">
                        <button type="submit" class="btn btn-primary">Save Member</button>
                        <a href="members.php" class="btn btn-outline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
    
    <script>
        // Show form if editing or after NLB check
        <?php if ($action == 'edit' || isset($_POST['check_nlb'])) { ?>
            document.getElementById('memberForm').style.display = 'block';
        <?php } ?>
    </script>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>