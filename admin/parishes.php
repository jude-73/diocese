<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!isAdminLoggedIn()) {
    header("Location: index.php");
    exit();
}

$breadcrumb = 'Admin Dashboard &raquo; Manage Parishes';

// Handle parish deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM parishes WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $_SESSION['message'] = 'Parish deleted successfully';
    header("Location: parishes.php");
    exit();
}

// Handle password updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_password'])) {
    $id = intval($_POST['parish_id']);
    $new_password = trim($_POST['new_password']);
    
    if (!empty($new_password)) {
        // Store plain text password (no hashing)
        $stmt = $conn->prepare("UPDATE parishes SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $new_password, $id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = 'Password updated successfully';
        } else {
            $_SESSION['error'] = 'Error updating password';
        }
    } else {
        $_SESSION['error'] = 'Password cannot be empty';
    }
    header("Location: parishes.php");
    exit();
}

// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where = '';
$params = [];

if (!empty($search)) {
    $where = "WHERE name LIKE ? OR username LIKE ? OR deanery LIKE ?";
    $searchTerm = "%$search%";
    $params = [$searchTerm, $searchTerm, $searchTerm];
}

// Get all parishes with search filter if applicable
$query = "SELECT * FROM parishes";
if (!empty($where)) {
    $query .= " $where";
}
$query .= " ORDER BY name";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param("sss", ...$params);
}
$stmt->execute();
$parishes = $stmt->get_result();
?>
<?php include '../includes/header.php'; ?>

<style>
    :root {
        --primary: #3f51b5;
        --primary-light: #757de8;
        --primary-dark: #002984;
        --secondary: #ff9800;
        --accent: #4caf50;
        --danger: #f44336;
        --light: #f5f5f5;
        --dark: #212121;
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
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 1.5rem;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    h2 {
        color: var(--dark);
        font-size: 1.75rem;
        font-weight: 600;
        margin: 0;
    }

    .alert {
        padding: 1rem;
        border-radius: var(--radius-sm);
        margin-bottom: 1.5rem;
    }

    .alert-success {
        background-color: #e8f5e9;
        color: #2e7d32;
        border-left: 4px solid var(--accent);
    }

    .alert-error {
        background-color: #ffebee;
        color: var(--danger);
        border-left: 4px solid var(--danger);
    }

    .card {
        background-color: white;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .search-container {
        margin-bottom: 1.5rem;
        display: flex;
        gap: 0.5rem;
    }

    .search-input {
        flex: 1;
        padding: 0.75rem 1rem;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        font-size: 1rem;
    }

    .search-button {
        background-color: var(--primary);
        color: white;
        border: none;
        border-radius: var(--radius-sm);
        padding: 0 1.5rem;
        cursor: pointer;
        transition: var(--transition);
    }

    .search-button:hover {
        background-color: var(--primary-dark);
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table thead th {
        background-color: var(--light);
        color: var(--dark);
        font-weight: 600;
        text-align: left;
        padding: 1rem;
        border-bottom: 2px solid var(--border);
    }

    .table tbody td {
        padding: 1rem;
        border-bottom: 1px solid var(--border);
        vertical-align: middle;
    }

    .table tbody tr:last-child td {
        border-bottom: none;
    }

    .table tbody tr:hover {
        background-color: rgba(0,0,0,0.02);
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.625rem 1.25rem;
        border-radius: var(--radius-sm);
        font-weight: 500;
        font-size: 0.875rem;
        cursor: pointer;
        transition: var(--transition);
        text-decoration: none;
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

    .btn-danger {
        background-color: white;
        color: var(--danger);
        border: 1px solid var(--danger);
    }

    .btn-danger:hover {
        background-color: rgba(244, 67, 54, 0.04);
    }

    .btn-accent {
        background-color: var(--accent);
        color: white;
    }

    .btn-accent:hover {
        background-color: #43a047;
    }

    .btn-info {
        background-color: var(--secondary);
        color: white;
    }

    .btn-info:hover {
        background-color: #e65100;
    }

    .action-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    dialog {
        border: none;
        border-radius: var(--radius);
        box-shadow: var(--shadow-md);
        width: 100%;
        max-width: 500px;
        padding: 0;
    }

    dialog::backdrop {
        background-color: rgba(0,0,0,0.5);
    }

    .modal-header {
        padding: 1.5rem 1.5rem 1rem;
        border-bottom: 1px solid var(--border);
    }

    .modal-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin: 0;
        color: var(--dark);
    }

    .modal-content {
        padding: 1.5rem;
    }

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        padding: 1rem 1.5rem;
        border-top: 1px solid var(--border);
    }

    .parish-details {
        margin-top: 1rem;
    }

    .detail-row {
        display: flex;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }

    .detail-row:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .detail-label {
        font-weight: 600;
        min-width: 120px;
        color: var(--dark);
    }

    .detail-value {
        flex: 1;
        word-break: break-word;
    }

    .password-container {
        display: flex;
        align-items: center;
    }

    .password-field {
        flex: 1;
        padding: 0.5rem;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        background-color: var(--light);
        font-family: monospace;
    }

    .toggle-password {
        margin-left: 0.5rem;
        padding: 0.5rem;
        background-color: var(--primary);
        color: white;
        border: none;
        border-radius: var(--radius-sm);
        cursor: pointer;
    }

    .toggle-password:hover {
        background-color: var(--primary-dark);
    }

    @media (max-width: 768px) {
        .container {
            padding: 0 1rem;
        }
        
        .table thead {
            display: none;
        }
        
        .table, .table tbody, .table tr, .table td {
            display: block;
            width: 100%;
        }
        
        .table tr {
            margin-bottom: 1rem;
            border-bottom: 2px solid var(--border);
            position: relative;
            padding-top: 1rem;
        }
        
        .table td {
            padding: 0.5rem 1rem;
            text-align: right;
            position: relative;
            padding-left: 50%;
        }
        
        .table td::before {
            content: attr(data-label);
            position: absolute;
            left: 1rem;
            width: 45%;
            padding-right: 1rem;
            font-weight: 600;
            text-align: left;
            color: var(--dark);
        }
        
        .table td:last-child {
            text-align: center;
            padding-left: 1rem;
        }
        
        .table td:last-child::before {
            display: none;
        }

        .detail-row {
            flex-direction: column;
        }

        .detail-label {
            margin-bottom: 0.25rem;
        }
    }
</style>

<div class="container">
    <div class="page-header">
        <h2>Manage Parishes</h2>
        <a href="actions/parish-crud.php?action=add" class="btn btn-primary">Add New Parish</a>
    </div>
    
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    
    <div class="search-container">
        <form method="GET" action="parishes.php" style="display: flex; width: 100%;">
            <input type="text" name="search" class="search-input" placeholder="Search parishes by name, username or deanery..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="search-button">Search</button>
            <?php if (!empty($search)): ?>
                <a href="parishes.php" class="btn btn-outline" style="margin-left: 0.5rem;">Clear</a>
            <?php endif; ?>
        </form>
    </div>
    
    <div class="card">
        <table class="table">
            <thead>
                <tr>
                    <th>Parish Name</th>
                    <th>Username</th>
                    <th>Deanery</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($parishes->num_rows > 0): ?>
                    <?php while ($parish = $parishes->fetch_assoc()): ?>
                    <tr>
                        <td data-label="Parish Name"><?php echo htmlspecialchars($parish['name']); ?></td>
                        <td data-label="Username"><?php echo htmlspecialchars($parish['username']); ?></td>
                        <td data-label="Deanery"><?php echo htmlspecialchars($parish['deanery']); ?></td>
                        <td>
                            <div class="action-buttons">
                                <button onclick="document.getElementById('view-modal-<?= $parish['id'] ?>').showModal()" class="btn btn-info">
                                    View
                                </button>
                                <a href="actions/parish-crud.php?action=edit&id=<?php echo $parish['id']; ?>" class="btn btn-primary">Edit</a>
                                <a href="parishes.php?delete=<?php echo $parish['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this parish?')">Delete</a>
                                <button onclick="document.getElementById('password-modal-<?= $parish['id'] ?>').showModal()" class="btn btn-accent">
                                    Set Password
                                </button>
                            </div>
                            
                            <!-- View Parish Modal -->
                            <dialog id="view-modal-<?= $parish['id'] ?>">
                                <div class="modal-header">
                                    <h3 class="modal-title"><?= htmlspecialchars($parish['name']) ?> Details</h3>
                                </div>
                                <div class="modal-content">
                                    <div class="parish-details">
                                        <div class="detail-row">
                                            <div class="detail-label">Name:</div>
                                            <div class="detail-value"><?= htmlspecialchars($parish['name']) ?></div>
                                        </div>
                                        <div class="detail-row">
                                            <div class="detail-label">Username:</div>
                                            <div class="detail-value"><?= htmlspecialchars($parish['username']) ?></div>
                                        </div>
                                        <div class="detail-row">
                                            <div class="detail-label">Password:</div>
                                            <div class="detail-value">
                                                <div class="password-container">
                                                    <input type="password" value="<?= htmlspecialchars($parish['password']) ?>" class="password-field" id="password-<?= $parish['id'] ?>" readonly>
                                                    <button type="button" class="toggle-password" onclick="togglePassword('password-<?= $parish['id'] ?>', this)">
                                                        Show
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="detail-row">
                                            <div class="detail-label">Deanery:</div>
                                            <div class="detail-value"><?= htmlspecialchars($parish['deanery']) ?></div>
                                        </div>
                                        <?php if (!empty($parish['address'])): ?>
                                        <div class="detail-row">
                                            <div class="detail-label">Address:</div>
                                            <div class="detail-value"><?= htmlspecialchars($parish['address']) ?></div>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (!empty($parish['phone'])): ?>
                                        <div class="detail-row">
                                            <div class="detail-label">Phone:</div>
                                            <div class="detail-value"><?= htmlspecialchars($parish['phone']) ?></div>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (!empty($parish['email'])): ?>
                                        <div class="detail-row">
                                            <div class="detail-label">Email:</div>
                                            <div class="detail-value"><?= htmlspecialchars($parish['email']) ?></div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" onclick="this.closest('dialog').close()" class="btn btn-primary">Close</button>
                                </div>
                            </dialog>
                            
                            <!-- Password Modal -->
                            <dialog id="password-modal-<?= $parish['id'] ?>">
                                <div class="modal-header">
                                    <h3 class="modal-title">Set Password for <?= htmlspecialchars($parish['name']) ?></h3>
                                </div>
                                <div class="modal-content">
                                    <form method="POST">
                                        <input type="hidden" name="parish_id" value="<?= $parish['id'] ?>">
                                        <div style="margin-bottom: 1rem;">
                                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">New Password</label>
                                            <input type="password" name="new_password" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: var(--radius-sm);">
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" onclick="this.closest('dialog').close()" class="btn btn-outline">Cancel</button>
                                    <button type="submit" name="update_password" form="password-form-<?= $parish['id'] ?>" class="btn btn-primary">Update</button>
                                </div>
                            </dialog>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 2rem;">No parishes found <?php echo !empty($search) ? 'matching your search' : ''; ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function togglePassword(inputId, button) {
        const input = document.getElementById(inputId);
        if (input.type === 'password') {
            input.type = 'text';
            button.textContent = 'Hide';
        } else {
            input.type = 'password';
            button.textContent = 'Show';
        }
    }
</script>

<?php include '../includes/footer.php'; ?>