<?php
require_once '../includes/config.php';

if (!isset($_GET['id'])) {
    die('Invalid request');
}

$memberId = intval($_GET['id']);

// Clear cache to prevent refresh issues
header("Cache-Control: no-cache, must-revalidate");

$query = "SELECT m.*, p.name as parish_name FROM members m JOIN parishes p ON m.parish_id = p.id WHERE m.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $memberId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('Member not found');
}

$row = $result->fetch_assoc();
?>

<style>
    .horizontal-profile {
        max-width: 1200px;
        margin: 20px auto;
        font-family: 'Segoe UI', Tahoma, sans-serif;
        color: #333;
    }
    
    .profile-row {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .photo-column {
        width: 250px;
        flex-shrink: 0;
    }
    
    .member-photo {
        width: 100%;
        height: 300px;
        object-fit: cover;
        border: 1px solid #e0e0e0;
        background: #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .no-photo {
        width: 100%;
        height: 300px;
        background: #f5f5f5;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #bdc3c7;
        font-size: 60px;
    }
    
    .details-column {
        flex: 1;
        min-width: 300px;
    }
    
    .member-name {
        font-size: 24px;
        margin: 0 0 15px 0;
        color: #2c3e50;
        padding-bottom: 8px;
        border-bottom: 2px solid #3498db;
    }
    
    .details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 15px;
    }
    
    .detail-group {
        margin-bottom: 15px;
    }
    
    .group-title {
        font-size: 18px;
        margin: 0 0 10px 0;
        color: #3498db;
        display: flex;
        align-items: center;
    }
    
    .group-title i {
        margin-right: 8px;
    }
    
    .detail-row {
        display: flex;
        margin-bottom: 8px;
    }
    
    .detail-label {
        width: 150px;
        font-weight: 600;
        color: #7f8c8d;
        flex-shrink: 0;
    }
    
    .detail-value {
        flex: 1;
        color: #2c3e50;
    }
    
    .status-tag {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 3px;
        font-size: 13px;
    }
    
    .status-yes {
        background: #e8f8f5;
        color: #27ae60;
    }
    
    .status-no {
        background: #fdedec;
        color: #e74c3c;
    }
    
    @media (max-width: 768px) {
        .photo-column {
            width: 100%;
        }
        
        .detail-row {
            flex-direction: column;
        }
        
        .detail-label {
            width: 100%;
            margin-bottom: 3px;
        }
    }
</style>

<div class="horizontal-profile">
    <div class="profile-row">
        <!-- Photo Column -->
        <div class="photo-column">
            <?php if (!empty($row['image_path'])): ?>
                <img src="<?php echo BASE_URL . '/uploads/' . htmlspecialchars($row['image_path']); ?>" class="member-photo" alt="Member Photo">
            <?php else: ?>
                <div class="no-photo">
                    <i class="fas fa-user"></i>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Details Column -->
        <div class="details-column">
            <h1 class="member-name">
                <?php echo htmlspecialchars($row['title'] ?? '') . ' ' . htmlspecialchars($row['last_name']) . ', ' . htmlspecialchars($row['other_names']); ?>
            </h1>
            
            <div class="details-grid">
                <!-- Parish Information -->
                <div class="detail-group">
                    <h3 class="group-title"><i class="fas fa-church"></i> Parish Information</h3>
                    <div class="detail-row">
                        <div class="detail-label">Current Parish:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($row['parish_name']); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Mother Parish:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($row['mother_parish']); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Outstation:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($row['outstation']); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Christian Community:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($row['christian_community']); ?></div>
                    </div>
                </div>
                
                <!-- Personal Information -->
                <div class="detail-group">
                    <h3 class="group-title"><i class="fas fa-user"></i> Personal Information</h3>
                    <div class="detail-row">
                        <div class="detail-label">Contact:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($row['contact']); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Date of Birth:</div>
                        <div class="detail-value"><?php echo !empty($row['dob']) ? date('M d, Y', strtotime($row['dob'])) : 'N/A'; ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Address:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($row['address']); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Marital Status:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($row['marital_status']); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Children:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($row['children_count']); ?></div>
                    </div>
                </div>
                
                <!-- Sacramental Information -->
                <div class="detail-group">
                    <h3 class="group-title"><i class="fas fa-cross"></i> Sacraments</h3>
                    <div class="detail-row">
                        <div class="detail-label">Baptism:</div>
                        <div class="detail-value">
                            <?php if (!empty($row['baptism_date'])): ?>
                                <?php echo date('M d, Y', strtotime($row['baptism_date'])); ?>
                                <?php if (!empty($row['baptism_place'])): ?>
                                    <br><?php echo htmlspecialchars($row['baptism_place']); ?>
                                <?php endif; ?>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">NLB Number:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($row['nlb']); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Confirmation:</div>
                        <div class="detail-value">
                            <?php if (!empty($row['confirmation_date'])): ?>
                                <?php echo date('M d, Y', strtotime($row['confirmation_date'])); ?>
                                <?php if (!empty($row['confirmation_place'])): ?>
                                    <br><?php echo htmlspecialchars($row['confirmation_place']); ?>
                                <?php endif; ?>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">NLC Number:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($row['nlc']); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Communicant:</div>
                        <div class="detail-value">
                            <span class="status-tag status-<?php echo $row['is_communicant'] ? 'yes' : 'no'; ?>">
                                <?php echo $row['is_communicant'] ? 'Yes' : 'No'; ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Confraternity Information -->
                <div class="detail-group">
                    <h3 class="group-title"><i class="fas fa-users"></i> Confraternity</h3>
                    <div class="detail-row">
                        <div class="detail-label">Confraternity:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($row['confraternity']); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Position:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($row['confraternity_position']); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Welfare Member:</div>
                        <div class="detail-value">
                            <span class="status-tag status-<?php echo $row['is_welfare_member'] ? 'yes' : 'no'; ?>">
                                <?php echo $row['is_welfare_member'] ? 'Yes' : 'No'; ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Family Information -->
                <div class="detail-group">
                    <h3 class="group-title"><i class="fas fa-user-friends"></i> Family</h3>
                    <div class="detail-row">
                        <div class="detail-label">Relative Name:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($row['relative_name']); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Relative Contact:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($row['relative_contact']); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Prevent form resubmission on refresh
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}
</script>