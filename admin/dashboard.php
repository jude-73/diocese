<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in as admin
if (!isAdminLoggedIn()) {
    header("Location: index.php");
    exit();
}

$breadcrumb = 'Admin Dashboard';

// Get statistics data
try {
    // Get parishes count
    $parishes_stmt = $conn->prepare("SELECT COUNT(*) as total FROM parishes");
    $parishes_stmt->execute();
    $parishes_count = $parishes_stmt->get_result()->fetch_assoc()['total'];
    $parishes_stmt->close();

    // Get members count
    $members_stmt = $conn->prepare("SELECT COUNT(*) as total FROM members");
    $members_stmt->execute();
    $members_count = $members_stmt->get_result()->fetch_assoc()['total'];
    $members_stmt->close();

    // Get communicants count
    $communicants_stmt = $conn->prepare("SELECT COUNT(*) as total FROM members WHERE is_communicant = 1");
    $communicants_stmt->execute();
    $communicants_count = $communicants_stmt->get_result()->fetch_assoc()['total'];
    $communicants_stmt->close();

    // Get confirmed members count
    $confirmed_stmt = $conn->prepare("SELECT COUNT(*) as total FROM members WHERE nlc IS NOT NULL AND nlc != ''");
    $confirmed_stmt->execute();
    $confirmed_count = $confirmed_stmt->get_result()->fetch_assoc()['total'];
    $confirmed_stmt->close();

    // Get baptized members count (using baptism_date field)
    $baptized_stmt = $conn->prepare("SELECT COUNT(*) as total FROM members WHERE baptism_date IS NOT NULL");
    $baptized_stmt->execute();
    $baptized_count = $baptized_stmt->get_result()->fetch_assoc()['total'];
    $baptized_stmt->close();

    // Get gallery items count
    $gallery_stmt = $conn->prepare("SELECT COUNT(*) as total FROM gallery");
    $gallery_stmt->execute();
    $gallery_count = $gallery_stmt->get_result()->fetch_assoc()['total'];
    $gallery_stmt->close();

    // Get recent members
    $recent_members_stmt = $conn->prepare("
        SELECT m.*, p.name as parish_name 
        FROM members m 
        JOIN parishes p ON m.parish_id = p.id 
        ORDER BY m.created_at DESC 
        LIMIT 5
    ");
    $recent_members_stmt->execute();
    $recent_members = $recent_members_stmt->get_result();
    $recent_members_stmt->close();

    // Get recent gallery items
    $recent_gallery_stmt = $conn->prepare("
        SELECT g.*, p.name as parish_name 
        FROM gallery g 
        JOIN parishes p ON g.parish_id = p.id 
        ORDER BY g.uploaded_at DESC 
        LIMIT 4
    ");
    $recent_gallery_stmt->execute();
    $recent_gallery = $recent_gallery_stmt->get_result();
    $recent_gallery_stmt->close();

} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    die("Error loading dashboard data");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Keta-Akatsi Diocese</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="<?php echo BASE_URL; ?>/assets/favicon.ico" type="image/x-icon">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --danger-color: #e74c3c;
            --purple-color: #9b59b6;
            --orange-color: #f39c12;
            --teal-color: #1abc9c;
            --dark-color: #2c3e50;
            --light-color: #ecf0f1;
            --gray-color: #7f8c8d;
        }
        
        body {
            background-color: var(--light-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
        }
        
        .dashboard-container {
            margin: 30px auto;
            max-width: 1400px;
            padding: 0 20px;
        }
        
        .dashboard-header {
            margin-bottom: 30px;
            color: var(--dark-color);
            font-weight: 600;
            position: relative;
            padding-bottom: 15px;
        }
        
        .dashboard-header:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--purple-color));
            border-radius: 2px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-top: 4px solid;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .stat-card:nth-child(1) {
            border-color: var(--primary-color);
        }
        
        .stat-card:nth-child(2) {
            border-color: var(--secondary-color);
        }
        
        .stat-card:nth-child(3) {
            border-color: var(--danger-color);
        }
        
        .stat-card:nth-child(4) {
            border-color: var(--purple-color);
        }
        
        .stat-card:nth-child(5) {
            border-color: var(--teal-color);
        }
        
        .stat-card:nth-child(6) {
            border-color: var(--orange-color);
        }
        
        .stat-title {
            margin-bottom: 15px;
            color: var(--gray-color);
            font-size: 16px;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        
        .stat-title i {
            margin-right: 10px;
            font-size: 20px;
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--dark-color);
        }
        
        .stat-link {
            display: inline-block;
            margin-top: 15px;
            color: var(--gray-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
            font-size: 14px;
        }
        
        .stat-link:hover {
            color: var(--primary-color);
        }
        
        .stat-link i {
            margin-left: 5px;
            transition: transform 0.3s ease;
        }
        
        .stat-link:hover i {
            transform: translateX(3px);
        }
        
        .recent-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .recent-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .recent-title {
            margin-bottom: 20px;
            color: var(--dark-color);
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        
        .recent-title i {
            margin-right: 10px;
            color: var(--primary-color);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 8px;
            overflow: hidden;
        }
        
        th {
            background: linear-gradient(90deg, var(--primary-color), #2980b9);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 500;
        }
        
        td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--light-color);
            color: #34495e;
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-yes {
            background-color: #d4edda;
            color: #155724;
        }
        
        .badge-no {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .badge-confirmed {
            background-color: #cce5ff;
            color: #004085;
        }
        
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .gallery-item {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .gallery-item:hover {
            transform: translateY(-5px);
        }
        
        .gallery-image {
            width: 100%;
            height: 120px;
            object-fit: cover;
        }
        
        .gallery-caption {
            padding: 10px;
            font-size: 14px;
            color: var(--dark-color);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        /* Responsive adjustments */
        @media (max-width: 1024px) {
            .recent-section {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }
            
            .stat-card {
                padding: 20px;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
            
            .gallery-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
        }

        @media (max-width: 576px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="dashboard-container">
        <h1 class="dashboard-header">
            <i class="fas fa-tachometer-alt"></i> Admin Dashboard Overview
        </h1>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3 class="stat-title"><i class="fas fa-church" style="color: var(--primary-color);"></i> Parishes</h3>
                <div class="stat-value"><?php echo htmlspecialchars($parishes_count); ?></div>
                <a href="parishes.php" class="stat-link">
                    View All Parishes <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <div class="stat-card">
                <h3 class="stat-title"><i class="fas fa-users" style="color: var(--secondary-color);"></i> Total Members</h3>
                <div class="stat-value"><?php echo htmlspecialchars($members_count); ?></div>
                <a href="members.php" class="stat-link">
                    View All Members <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <div class="stat-card">
                <h3 class="stat-title"><i class="fas fa-wine-glass-alt" style="color: var(--danger-color);"></i> Communicants</h3>
                <div class="stat-value"><?php echo htmlspecialchars($communicants_count); ?></div>
                <a href="members.php?filter=communicants" class="stat-link">
                    View Communicants <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <div class="stat-card">
                <h3 class="stat-title"><i class="fas fa-cross" style="color: var(--purple-color);"></i> Confirmed Members</h3>
                <div class="stat-value"><?php echo htmlspecialchars($confirmed_count); ?></div>
                <a href="members.php?filter=confirmed" class="stat-link">
                    View Confirmed <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <div class="stat-card">
                <h3 class="stat-title"><i class="fas fa-water" style="color: var(--teal-color);"></i> Total Baptised</h3>
                <div class="stat-value"><?php echo htmlspecialchars($baptized_count); ?></div>
                <a href="members.php" class="stat-link">
                    View Members <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <div class="stat-card">
                <h3 class="stat-title"><i class="fas fa-images" style="color: var(--orange-color);"></i> Gallery Items</h3>
                <div class="stat-value"><?php echo htmlspecialchars($gallery_count); ?></div>
                <a href="gallery.php" class="stat-link">
                    View Gallery <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
        
        <div class="recent-section">
            <div class="recent-card">
                <h3 class="recent-title"><i class="fas fa-user-clock"></i> Recently Added Members</h3>
                <?php if ($recent_members->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Parish</th>
                                <th>Communion</th>
                                <th>Confirmation</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $recent_members->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['last_name'] . ', ' . $row['other_names']); ?></td>
                                    <td><?php echo htmlspecialchars($row['parish_name']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $row['is_communicant'] ? 'badge-yes' : 'badge-no'; ?>">
                                            <?php echo $row['is_communicant'] ? 'Yes' : 'No'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo !empty($row['nlc']) ? 'badge-confirmed' : 'badge-no'; ?>">
                                            <?php echo !empty($row['nlc']) ? 'Yes' : 'No'; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="color: var(--gray-color); text-align: center;">No members found.</p>
                <?php endif; ?>
            </div>
            
            <div class="recent-card">
                <h3 class="recent-title"><i class="fas fa-images"></i> Recent Gallery Items</h3>
                <?php if ($recent_gallery->num_rows > 0): ?>
                    <div class="gallery-grid">
                        <?php while ($row = $recent_gallery->fetch_assoc()): ?>
                            <div class="gallery-item">
                                <img src="<?php echo BASE_URL . '/uploads/' . htmlspecialchars($row['image_path']); ?>" 
                                     class="gallery-image" 
                                     alt="<?php echo htmlspecialchars($row['caption']); ?>">
                                <div class="gallery-caption" title="<?php echo htmlspecialchars($row['caption']); ?>">
                                    <?php echo htmlspecialchars($row['caption']); ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    <a href="gallery.php" style="display: inline-block; margin-top: 15px; color: var(--primary-color); text-decoration: none; font-weight: 500;">
                        View All Gallery Items <i class="fas fa-arrow-right"></i>
                    </a>
                <?php else: ?>
                    <p style="color: var(--gray-color); text-align: center;">No gallery items found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>