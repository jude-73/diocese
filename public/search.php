<?php
require_once '../includes/config.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$parish_filter = isset($_GET['parish']) ? intval($_GET['parish']) : 0;

$breadcrumb = 'Member Search';
?>
<?php include '../includes/header.php'; ?>

<div class="search-page">
    <div class="page-header">
        <h1>Member Search</h1>
        <p class="subtitle">Find members of our diocesan community</p>
    </div>
    
    <div class="search-container">
        <form action="" method="GET" class="search-form">
            <div class="input-group">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Search by name, contact, NLB, or other details..." 
                       value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn-primary">
                    Search <i class="fas fa-arrow-right"></i>
                </button>
            </div>
            
            <?php if ($parish_filter > 0): ?>
            <input type="hidden" name="parish" value="<?php echo $parish_filter; ?>">
            <?php endif; ?>
        </form>
    </div>
    
    <?php if (!empty($search) || $parish_filter > 0): ?>
    <div class="results-section">
        <?php
        $query = "SELECT m.*, p.name as parish_name FROM members m JOIN parishes p ON m.parish_id = p.id WHERE ";
        $params = [];
        $types = "";
        
        if (!empty($search)) {
            $query .= "(m.last_name LIKE ? OR m.other_names LIKE ? OR m.contact LIKE ? OR m.relative_name LIKE ? OR m.christian_community LIKE ? OR m.nlb LIKE ?)";
            $search_term = "%$search%";
            $params = array_merge($params, [$search_term, $search_term, $search_term, $search_term, $search_term, $search_term]);
            $types .= "ssssss";
        }
        
        if ($parish_filter > 0) {
            if (!empty($search)) {
                $query .= " AND ";
            }
            $query .= "m.parish_id = ?";
            $params[] = $parish_filter;
            $types .= "i";
        }
        
        $query .= " ORDER BY m.last_name, m.other_names";
        
        $stmt = $conn->prepare($query);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo '<div class="results-table">';
            echo '<table>';
            echo '<thead>';
            echo '<tr>';
            echo '<th>Name</th>';
            echo '<th>Parish</th>';
            echo '<th>Contact</th>';
            echo '<th>NLB</th>';
            echo '<th>Date of Birth</th>';
            echo '<th>Baptism</th>';
            echo '<th>Communion</th>';
            echo '<th>Community</th>';
            echo '<th>Actions</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            
            while ($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($row['title'] ?? '') . ' ' . htmlspecialchars($row['last_name']) . ', ' . htmlspecialchars($row['other_names']) . '</td>';
                echo '<td>' . htmlspecialchars($row['parish_name']) . '</td>';
                echo '<td>' . htmlspecialchars($row['contact']) . '</td>';
                echo '<td>' . htmlspecialchars($row['nlb']) . '</td>';
                echo '<td>' . (!empty($row['dob']) ? date('M d, Y', strtotime($row['dob'])) : 'N/A') . '</td>';
                echo '<td>' . (!empty($row['baptism_date']) ? date('M d, Y', strtotime($row['baptism_date'])) . '<br>' . htmlspecialchars($row['baptism_place']) : 'N/A') . '</td>';
                echo '<td><span class="status-badge ' . ($row['is_communicant'] ? 'active' : 'inactive') . '">' . ($row['is_communicant'] ? 'Yes' : 'No') . '</span></td>';
                echo '<td>' . htmlspecialchars($row['christian_community']) . '</td>';
                echo '<td><button class="view-btn" data-member-id="' . $row['id'] . '">View Details</button></td>';
                echo '</tr>';
            }
            
            echo '</tbody>';
            echo '</table>';
            echo '</div>';
            
            // Modal for member details
            echo '<div id="memberModal" class="modal">';
            echo '<div class="modal-content">';
            echo '<span class="close-modal">&times;</span>';
            echo '<div id="memberDetails"></div>';
            echo '</div>';
            echo '</div>';
        } else {
            echo '<div class="no-results">';
            echo '<i class="fas fa-search"></i>';
            echo '<p>No members found matching your search</p>';
            echo '</div>';
        }
        ?>
    </div>
    <?php endif; ?>
</div>

<style>
/* Modern Color Scheme */
:root {
    --primary: #2c5e8f;
    --primary-light: #e1f0fa;
    --secondary: #d4a017;
    --accent: #e74c3c;
    --light: #f8f9fa;
    --dark: #2c3e50;
    --text: #333;
    --text-light: #666;
    --border: #e0e0e0;
}

/* Base Styles */
.search-page {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 0 1.5rem;
    min-height: calc(100vh - 200px); /* Ensures footer stays at bottom */
}

.page-header {
    text-align: center;
    margin-bottom: 2.5rem;
}

.page-header h1 {
    font-size: 2.2rem;
    color: var(--primary);
    margin-bottom: 0.5rem;
}

.subtitle {
    color: var(--text-light);
    font-size: 1.1rem;
}

/* Search Form */
.search-container {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    margin-bottom: 2.5rem;
}

.input-group {
    display: flex;
    align-items: center;
    position: relative;
}

.input-group i {
    position: absolute;
    left: 1rem;
    color: var(--text-light);
}

.input-group input {
    flex: 1;
    padding: 0.9rem 1rem 0.9rem 2.5rem;
    border: 1px solid var(--border);
    border-radius: 4px 0 0 4px;
    font-size: 1rem;
}

.input-group input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(44, 94, 143, 0.1);
}

.btn-primary {
    background-color: var(--primary);
    color: white;
    border: none;
    padding: 0.9rem 1.5rem;
    border-radius: 0 4px 4px 0;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn-primary:hover {
    background-color: #1d4b75;
}

.view-btn {
    background-color: var(--secondary);
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.2s ease;
}

.view-btn:hover {
    background-color: #b38612;
}

/* Results Table */
.results-table {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    overflow: hidden;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    background-color: var(--primary);
    color: white;
    padding: 1rem;
    text-align: left;
    font-weight: 500;
}

td {
    padding: 1rem;
    border-bottom: 1px solid var(--border);
    color: var(--text);
}

tr {
    transition: all 0.2s ease;
}

tr:hover {
    background-color: var(--primary-light);
}

/* Status Badges */
.status-badge {
    display: inline-block;
    padding: 0.3rem 0.8rem;
    border-radius: 50px;
    font-size: 0.85rem;
    font-weight: 500;
}

.active {
    background-color: rgba(46, 204, 113, 0.2);
    color: #27ae60;
}

.inactive {
    background-color: rgba(236, 240, 241, 0.8);
    color: var(--text-light);
}

/* No Results */
.no-results {
    text-align: center;
    padding: 3rem;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.no-results i {
    font-size: 2.5rem;
    color: var(--text-light);
    margin-bottom: 1rem;
}

.no-results p {
    color: var(--text);
    font-size: 1.1rem;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.4);
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    width: 80%;
    max-width: 800px;
    position: relative;
}

.close-modal {
    position: absolute;
    right: 1.5rem;
    top: 1rem;
    color: #aaa;
    font-size: 1.5rem;
    font-weight: bold;
    cursor: pointer;
}

.close-modal:hover {
    color: var(--dark);
}

#memberDetails {
    margin-top: 1rem;
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.detail-card {
    background: white;
    padding: 1.2rem;
    border-radius: 6px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    border-left: 3px solid var(--primary);
}

.detail-card h4 {
    color: var(--primary);
    margin-bottom: 0.8rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.detail-card p {
    margin-bottom: 0.5rem;
    color: var(--text);
}

.member-photo {
    max-width: 200px;
    max-height: 200px;
    border-radius: 4px;
    border: 1px solid var(--border);
    object-fit: cover;
    margin-bottom: 1rem;
}

/* Responsive */
@media (max-width: 768px) {
    .search-page {
        padding: 0 1rem;
    }
    
    .input-group {
        flex-direction: column;
    }
    
    .input-group input {
        width: 100%;
        border-radius: 4px;
        margin-bottom: 0.5rem;
    }
    
    .btn-primary {
        width: 100%;
        border-radius: 4px;
    }
    
    .modal-content {
        width: 95%;
        margin: 10% auto;
        padding: 1.5rem;
    }
    
    table {
        display: block;
        overflow-x: auto;
    }
    
    td {
        padding: 0.75rem;
    }
}
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get modal elements
    const modal = document.getElementById('memberModal');
    const closeBtn = document.querySelector('.close-modal');
    const viewBtns = document.querySelectorAll('.view-btn');
    
    // Close modal when clicking X
    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });
    
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    });
    
    // Handle view button clicks
    viewBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const memberId = this.getAttribute('data-member-id');
            fetchMemberDetails(memberId);
        });
    });
    
    // Fetch member details via AJAX
    function fetchMemberDetails(memberId) {
        fetch('../includes/get_member_details.php?id=' + memberId)
            .then(response => response.text())
            .then(data => {
                document.getElementById('memberDetails').innerHTML = data;
                modal.style.display = 'block';
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('memberDetails').innerHTML = '<p>Error loading member details</p>';
                modal.style.display = 'block';
            });
    }
});

</script>

<?php include '../includes/footer.php'; ?>