<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!isParishLoggedIn()) {
    header("Location: index.php");
    exit();
}

$parish_id = $_SESSION['parish_id'];
$parish_name = $_SESSION['parish_name'];

$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$breadcrumb = 'Parish Dashboard &raquo; Members';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $parish_name; ?> - Members Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="../assets/favicon.ico" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="180x180" href="../assets/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/favicon-16x16.png">
    <style>
        .members-container {
            margin: 30px 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .page-title {
            color: #2c3e50;
            font-weight: 600;
            margin: 0;
        }
        
        .search-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.05);
            margin-bottom: 25px;
        }
        
        .search-form {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .search-input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .search-input:focus {
            border-color: #3498db;
            outline: none;
        }
        
        .search-btn {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(52,152,219,0.3);
        }
        
        .reset-btn {
            padding: 10px 20px;
            color: #3498db;
            text-decoration: none;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            transition: background-color 0.2s;
        }
        
        .reset-btn:hover {
            background-color: #f8f9fa;
        }
        
        .filter-links {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .filter-link {
            color: #7f8c8d;
            text-decoration: none;
            font-weight: 500;
            padding: 5px 0;
            position: relative;
            transition: color 0.2s;
            white-space: nowrap;
        }
        
        .filter-link:hover {
            color: #3498db;
        }
        
        .filter-link.active {
            color: #3498db;
            font-weight: 600;
        }
        
        .filter-link.active:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: #3498db;
        }
        
        .members-table {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.05);
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 12px 15px;
            text-align: left;
            font-weight: 500;
        }
        
        td {
            padding: 12px 15px;
            border-bottom: 1px solid #f0f0f0;
            color: #34495e;
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 10px;
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
        
        .badge-baptized {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .action-link {
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            font-size: 13px;
            margin-right: 8px;
            transition: all 0.2s;
        }
        
        .edit-link {
            background-color: #e3f2fd;
            color: #1976d2;
            border: 1px solid #bbdefb;
        }
        
        .edit-link:hover {
            background-color: #bbdefb;
        }
        
        .delete-link {
            background-color: #ffebee;
            color: #d32f2f;
            border: 1px solid #ffcdd2;
        }
        
        .delete-link:hover {
            background-color: #ffcdd2;
        }
        
        .empty-state {
            padding: 30px;
            text-align: center;
            color: #7f8c8d;
        }
        
        .empty-state i {
            font-size: 50px;
            color: #bdc3c7;
            margin-bottom: 15px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            padding: 10px 20px;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(39, 174, 96, 0.3);
            color: white;
        }
        
        @media (max-width: 768px) {
            .search-form {
                flex-direction: column;
            }
            
            .filter-links {
                flex-direction: column;
                gap: 10px;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="members-container">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-users" style="margin-right: 10px; color: #3498db;"></i><?php echo $parish_name; ?> Members
            </h1>
            <a href="actions/member-crud.php?action=add" class="btn-primary">
                <i class="fas fa-plus" style="margin-right: 8px;"></i>Add New Member
            </a>
        </div>

        <div class="search-box">
            <form action="" method="GET" class="search-form">
                <input type="text" name="search" placeholder="Search members..." value="<?php echo htmlspecialchars($search); ?>" class="search-input">
                <button type="submit" class="search-btn">
                    <i class="fas fa-search" style="margin-right: 8px;"></i>Search
                </button>
                <a href="members.php" class="reset-btn">
                    <i class="fas fa-sync-alt" style="margin-right: 8px;"></i>Reset
                </a>
            </form>
            
            <div class="filter-links">
                <a href="members.php" class="filter-link <?php echo empty($filter) ? 'active' : ''; ?>">
                    <i class="fas fa-users" style="margin-right: 8px;"></i>All Members
                </a>
                <a href="members.php?filter=communicants" class="filter-link <?php echo $filter == 'communicants' ? 'active' : ''; ?>">
                    <i class="fas fa-wine-glass-alt" style="margin-right: 8px;"></i>Communicants
                </a>
                <a href="members.php?filter=confirmed" class="filter-link <?php echo $filter == 'confirmed' ? 'active' : ''; ?>">
                    <i class="fas fa-cross" style="margin-right: 8px;"></i>Confirmed
                </a>
                <a href="members.php?filter=baptized" class="filter-link <?php echo $filter == 'baptized' ? 'active' : ''; ?>">
                    <i class="fas fa-water" style="margin-right: 8px;"></i>Baptized
                </a>
            </div>
        </div>

        <div class="members-table">
            <?php
            // Build query based on filters
            $query = "SELECT * FROM members WHERE parish_id = ?";
            $params = [$parish_id];
            $types = "i";
            
            if ($filter == 'communicants') {
                $query .= " AND is_communicant = 1";
                $breadcrumb .= ' &raquo; Communicants';
            } elseif ($filter == 'confirmed') {
                $query .= " AND nlc IS NOT NULL AND nlc != ''";
                $breadcrumb .= ' &raquo; Confirmed';
            } elseif ($filter == 'baptized') {
                $query .= " AND baptism_date IS NOT NULL";
                $breadcrumb .= ' &raquo; Baptized';
            }
            
            if (!empty($search)) {
                $query .= " AND (last_name LIKE ? OR other_names LIKE ? OR contact LIKE ?)";
                $search_term = "%$search%";
                $params = array_merge($params, [$search_term, $search_term, $search_term]);
                $types .= "sss";
            }
            
            $query .= " ORDER BY last_name, other_names";
            
            $stmt = $conn->prepare($query);
            
            // Bind parameters dynamically
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Baptism</th>
                            <th>Communion</th>
                            <th>Confirmation</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['last_name'] . ', ' . $row['other_names']); ?></td>
                            <td><?php echo htmlspecialchars($row['contact']); ?></td>
                            <td>
                                <?php if (!empty($row['baptism_date'])): ?>
                                    <span class="badge badge-baptized">
                                        <?php echo date('M d, Y', strtotime($row['baptism_date'])); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-no">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?php echo $row['is_communicant'] ? 'badge-yes' : 'badge-no'; ?>">
                                    <?php echo $row['is_communicant'] ? 'Yes' : 'No'; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?php echo !empty($row['nlc']) ? 'badge-confirmed' : 'badge-no'; ?>">
                                    <?php echo !empty($row['nlc']) ? 'Confirmed' : 'Not Confirmed'; ?>
                                </span>
                            </td>
                            <td>
                                <a href="actions/member-crud.php?action=edit&id=<?php echo $row['id']; ?>" class="action-link edit-link">
                                    <i class="fas fa-edit" style="margin-right: 5px;"></i>Edit
                                </a>
                                <a href="actions/member-crud.php?action=delete&id=<?php echo $row['id']; ?>" class="action-link delete-link" onclick="return confirm('Are you sure you want to delete this member?')">
                                    <i class="fas fa-trash-alt" style="margin-right: 5px;"></i>Delete
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-user-slash"></i>
                    <p>No members found matching your criteria</p>
                    <a href="members.php" class="reset-btn" style="margin-top: 10px;">
                        <i class="fas fa-sync-alt" style="margin-right: 8px;"></i>Reset Filters
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>